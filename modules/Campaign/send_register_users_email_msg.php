<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;

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
            $sqle = "SELECT * FROM campaign_parent_registration WHERE id = " . $si . " ";
            $resulte = $connection2->query($sqle);
            $rowdata = $resulte->fetch();
            // $sd = json_decode($rowdata['response'], TRUE);
            // $email = "";
            // $names = "";

            if(!empty($rowdata)) {
                $names = $rowdata['name'];
                $email = $rowdata['email'];
                $number = $rowdata['mobile'];
            }

            //$email = "it.rakesh@gmail.com";
            $to = $email;
            $subject = nl2br($emailSubjct_camp);
            $body = nl2br($emailquote);
            $msg = $smsquote;

          

            if (!empty($emailquote) && !empty($body) && !empty($to)) {
                $url = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Campaign/mailsend.php';
                $url .= "&to=" . $to;
                $url .= "&subject=" . rawurlencode($subject);
                $url .= "&body=" . rawurlencode($body);

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
                        $sq = "INSERT INTO campaign_email_sms_sent_details SET  campaign_parent_registration_id = " . $subid . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', attachment= '" . $NewNameFile . "', pupilsightPersonID=" . $cuid . " ";
                        $connection2->query($sq);
                    } catch (Exception $ex) {
                        print_r($x);
                    }
                } else {
                    $res = file_get_contents($url);
                    $sq = "INSERT INTO campaign_email_sms_sent_details SET  campaign_parent_registration_id = " . $subid . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', pupilsightPersonID=" . $cuid . " ";
                    $connection2->query($sq);
                }
            }

            if (!empty($smsquote) && !empty($msg) && !empty($number)) {
                $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                $urls .= "&send_to=" . $number;
                $urls .= "&msg=" . rawurlencode($msg);
                $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                $resms = file_get_contents($urls);

                $sq = "INSERT INTO campaign_email_sms_sent_details SET  campaign_parent_registration_id = " . $subid . ", phone=" . $number . ", description='" . stripslashes($msg) . "', pupilsightPersonID=" . $cuid . " ";
                $connection2->query($sq);
            }
        }
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
