<?php
/*
Pupilsight, Flexible & Open School System
*/
include $_SERVER["DOCUMENT_ROOT"] . '/pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;
$container = new League\Container\Container();
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Campaign/campaignFormList.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/send_camp_email_msg') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // `id``from_state``to_state``transition_display_name``tansition_action``cuid``auto_gen_inv``tansition_action``cuid`

    //   echo "<pre>";
    //   print_r($_POST);exit;
    $subid = $_POST['submit_id'];
    $crtd =  date('Y-m-d H:i:s');
    $emailquote = $_POST['emailquote'];
    $smsquote = $_POST['smsquote'];
    //$emailAttachment = $_FILES['emailAttachment'];
    $emailSubjct_camp = $_POST['emailSubjct_camp'];
    $crtd =  date('Y-m-d H:i:s');

    $cuid = $_SESSION[$guid]['pupilsightPersonID'];


    if ($subid == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $sub_id = explode(',', $subid);
        //print_r($submissionId);die();
        $attachmentStatus = "No";
        $NewNameFile = '';
        $errStatus = "No";
        if (!empty($_FILES["email_attach"]["name"])) {
            $fileData = pathinfo(basename($_FILES["email_attach"]["name"]));
            $ex = explode(".", $_FILES["email_attach"]["name"]);
            $extension = end($ex);
            $NewNameFile = time() . '.' . $extension;
            $sourcePath = $_FILES['email_attach']['tmp_name'];

            //$uploaddir = '../../public/attactments_campaign/';
            $uploaddir = $_SERVER['DOCUMENT_ROOT'] . "/public/attachments_campaign/";
            $uploadfile = $uploaddir . $NewNameFile;

            //echo "\nupload file path : ".$uploadfile."\n";
            if (move_uploaded_file($sourcePath, $uploadfile)) {
                $attachmentStatus = "Yes";
            }
        }

        foreach ($sub_id as $si) {
            $sqle = "SELECT response FROM wp_fluentform_submissions WHERE id = " . $si . " ";
            $resulte = $connection2->query($sqle);
            $rowdata = $resulte->fetch();
            $sd = json_decode($rowdata['response'], TRUE);
            $email = "";
            $names = "";
            $ft_number = '';
            $mt_number = '';
            $gt_number = '';
            $ft_email = '';
            $mt_email = '';
            $gt_email = '';

            if ($sd) {
                $names = implode(' ', $sd['student_name']);
                $email = $sd['father_email'];
                if (!empty($sd['father_mobile'])) {
                    $ft_number = $sd['father_mobile'];
                }
                if (!empty($sd['mother_mobile'])) {
                    $mt_number = $sd['mother_mobile'];
                }
                if (!empty($sd['guardian_mobile'])) {
                    $gt_number = $sd['guardian_mobile'];
                }

                if (!empty($sd['father_email'])) {
                    $ft_email = $sd['father_email'];
                }
                if (!empty($sd['mother_email'])) {
                    $mt_email = $sd['mother_email'];
                }
                if (!empty($sd['guardian_email'])) {
                    $gt_email = $sd['guardian_email'];
                }
            }

            //$email = "it.rakesh@gmail.com";
            $subject = nl2br($emailSubjct_camp);
            $body = nl2br($emailquote);
            $msg = $smsquote;

            $sqlm = "SELECT field_name,field_value FROM wp_fluentform_entry_details WHERE submission_id = " . $si . " And field_name = 'numeric-field_1' ";
            $resultm = $connection2->query($sqlm);
            $rowdatm = $resultm->fetch();

            if (!empty($emailquote) && !empty($body)) {

                if (!empty($ft_email)) {
                    $url = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Campaign/mailsend.php';
                    $url .= "&to=" . $ft_email;
                    $url .= "&subject=" . rawurlencode($subject);
                    $url .= "&body=" . rawurlencode($body);
                    sendEmail($container, $ft_email, $subject, $body, $subid, $cuid, $uploadfile, $NewNameFile, $connection2, $url, $attachmentStatus);
                }
                if (!empty($mt_email)) {
                    $url = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Campaign/mailsend.php';
                    $url .= "&to=" . $mt_email;
                    $url .= "&subject=" . rawurlencode($subject);
                    $url .= "&body=" . rawurlencode($body);
                    sendEmail($container, $mt_email, $subject, $body, $subid, $cuid, $uploadfile, $NewNameFile, $connection2, $url, $attachmentStatus);
                }
                if (!empty($gt_email)) {
                    $url = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Campaign/mailsend.php';
                    $url .= "&to=" . $gt_email;
                    $url .= "&subject=" . rawurlencode($subject);
                    $url .= "&body=" . rawurlencode($body);
                    sendEmail($container, $gt_email, $subject, $body, $subid, $cuid, $uploadfile, $NewNameFile, $connection2, $url, $attachmentStatus);
                }
            }

            if (!empty($smsquote) && !empty($msg)) {
                if (!empty($ft_number)) {
                    sendSMS($ft_number, $msg, $subid, $cuid, $connection2);
                }
                if (!empty($mt_number)) {
                    sendSMS($mt_number, $msg, $subid, $cuid, $connection2);
                }
                if (!empty($gt_number)) {
                    sendSMS($gt_number, $msg, $subid, $cuid, $connection2);
                }
            }
        }
    }
}

function sendSMS($number, $msg, $subid, $cuid, $connection2)
{
    $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
    $urls .= "&send_to=" . $number;
    $urls .= "&msg=" . rawurlencode($msg);
    $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
    $resms = file_get_contents($urls);

    $sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", phone=" . $number . ", description='" . stripslashes($msg) . "', pupilsightPersonID=" . $cuid . " ";
    $connection2->query($sq);
}

function sendEMail($container, $to, $subject, $body, $subid, $cuid, $uploadfile, $NewNameFile, $connection2, $url, $attachmentStatus)
{

    //sending attachment
    if ($attachmentStatus == "Yes") {
        $from = $_SESSION[$guid]['organisationAdministratorEmail'];
        $fromName = $_SESSION[$guid]['organisationAdministratorName'];
        // sendEmailAttactment($to,$subject,$body,$NewNameFile,$from, $fromName);


        try {
            $mail = $container->get(Mailer::class);
            $mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

            $mail->AddAddress($to);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->AddAttachment($uploadfile);                        // Optional name
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->Send();
            $sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', attachment= '" . $NewNameFile . "', pupilsightPersonID=" . $cuid . " ";
            $connection2->query($sq);
            $msgby =$_SESSION[$guid]["pupilsightPersonID"];
            Updatemessesnger($connection2,$_SESSION[$guid]["pupilsightPersonID"],$to,$body,$subject);


        } catch (Exception $ex) {
            print_r($ex);
        }
    } else {
        $res = file_get_contents($url);
        $sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', pupilsightPersonID=" . $cuid . " ";
        $connection2->query($sq);
        $msgby =$_SESSION[$guid]["pupilsightPersonID"];
        Updatemessesnger($connection2,$_SESSION[$guid]["pupilsightPersonID"],$to,$body,$subject);

    }
}

//not using for attachments
function sendEmailAttactment($to, $subject, $message, $filename, $from, $fromName)
{
    //$fileattname = "../../public/attactments_campaign/".$filename; //name that you want to use to send or you //can use the same name
    $path = "../../public/attactments_campaign/";
    $file = $path . $filename;
    $content = file_get_contents($file);
    $content = chunk_split(base64_encode($content));
    $uid = md5(uniqid(time()));
    $name = basename($file);

    // header
    $header = "From: " . $fromName . " <" . $from . ">\r\n";
    //$header .= "Reply-To: ".$replyto."\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"\r\n\r\n";

    // message & attachment
    $nmessage = "--" . $uid . "\r\n";
    $nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
    $nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $nmessage .= $message . "\r\n\r\n";
    $nmessage .= "--" . $uid . "\r\n";
    $nmessage .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"\r\n";
    $nmessage .= "Content-Transfer-Encoding: base64\r\n";
    $nmessage .= "Content-Disposition: attachment; filename=\"" . $filename . "\"\r\n\r\n";
    $nmessage .= $content . "\r\n\r\n";
    $nmessage .= "--" . $uid . "--";

    // Send the email
    if (mail($to, $subject, $nmessage, $header)) {
        return true; // Or do something here
    } else {
        return false;
    }
}


function Updatemessesnger($connection2,$sender,$smspupilsightPersonID, $body="", $subject="")
{
    //   echo "hi"; die();
    $data = array('email' => $smspupilsightPersonID);
    $sql = "SELECT pupilsightPersonID FROM pupilsightPerson WHERE email=:email";
    $result = $connection2->prepare($sql);
    $result->execute($data);
    if ($result->rowCount() > 0) {
        while ($rowppid = $result->fetch()) {
            $ppid = $rowppid['pupilsightPersonID'];

            $msgby = $sender;
            $msgto = $ppid;
            //$emailreportp=$sms->updateMessengerTableforEmail($msgto,$subject,$body,$msgby);

            $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightMessenger'";
            $resultAI = $connection2->query($sqlAI);
            $rowAI = $resultAI->fetch();
            $AI = str_pad($rowAI['Auto_increment'], 12, "0", STR_PAD_LEFT);

            $email = "Y";
            $messageWall = "N";
            $sms = "N";
            $date1 = date('Y-m-d');

            $data = array("email" => $email, "messageWall" => $messageWall, "messageWall_date1" => $date1, "sms" => $sms, "subject" => $subject, "body" => $body, "pupilsightPersonID" => $msgby, "category" => 'Other', "timestamp" => date("Y-m-d H:i:s"));
            $sql = "INSERT INTO pupilsightMessenger SET email=:email, messageWall=:messageWall, messageWall_date1=:messageWall_date1, sms=:sms, subject=:subject, body=:body, pupilsightPersonID=:pupilsightPersonID,messengercategory=:category, timestamp=:timestamp";
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $data = array("AI" => $AI, "t" => $msgto);
            $sql = "INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Individuals', id=:t";
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $savedata = "INSERT INTO pupilsightMessengerReceipt SET pupilsightMessengerID='$msgby', pupilsightPersonID=$msgby, targetType='Individuals', targetID=$msgto, contactType='Email', contactDetail='".$smspupilsightPersonID."', `key`='NA', confirmed='N'";
            $connection2->query($savedata);
        }
    }
}