<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

use Pupilsight\Contracts\Comms\SMS;
use Pupilsight\Contracts\Comms\Mailer;

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Students/student_view.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/send_stud_email_msg.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!

    //    echo "<pre>";
    //   print_r($_REQUEST);
    $stuId = $_POST['stuid'];
    $crtd =  date('Y-m-d H:i:s');
    $emailquote = $_POST['emailquote'];
    $subjectquote = $_POST['subjectquote'];
    $smsquote = $_POST['smsquote'];
    // $type = $_POST['type'];
    // $types = explode(',', $type);
    $crtd =  date('Y-m-d H:i:s');
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    //print_r($_FILES["email_attach"]);


    if ($stuId == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $sms = $container->get(SMS::class);
        $studentId = explode(',', $stuId);
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
            $uploaddir = $_SERVER['DOCUMENT_ROOT'] . "/public/attachments/";
            $uploadfile = $uploaddir . $NewNameFile;

            //echo "\nupload file path : ".$uploadfile."\n";
            if (move_uploaded_file($sourcePath, $uploadfile)) {
                $attachmentStatus = "Yes";
            }
        }

        foreach ($studentId as $st) {
            $sql = "SELECT pupilsightPersonID, email, phone1, officialName, pupilsightRoleIDPrimary FROM pupilsightPerson WHERE pupilsightPersonID = " . $st . " ";
            $result = $connection2->query($sql);
            $userData = $result->fetch();

            if($userData['pupilsightRoleIDPrimary'] == '003'){
                $sqlf = 'SELECT pupilsightFamilyID FROM pupilsightFamilyChild WHERE pupilsightPersonID= ' . $st . ' ';
                $resultf = $connection2->query($sqlf);
                $fdata = $resultf->fetch();
                $pupilsightFamilyID = $fdata['pupilsightFamilyID'];

                $sqle = "SELECT b.pupilsightPersonID, b.email, b.phone1, b.officialName FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightFamilyID = " . $pupilsightFamilyID . " AND a.relationship = 'Father' ";
                $resulte = $connection2->query($sqle);
                $rowdata = $resulte->fetch();

                if(!empty($rowdata)){
                    $to = $rowdata['email'];
                    $subject = nl2br($subjectquote);
                    $body = nl2br($emailquote);
                    $msg = $smsquote;
                    $number = $rowdata['phone1'];
                    $smspupilsightPersonID = $rowdata['pupilsightPersonID'];
                } else {
                    $sqle1 = "SELECT b.pupilsightPersonID, b.email, b.phone1, b.officialName FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightFamilyID = " . $pupilsightFamilyID . " AND a.relationship = 'Mother' ";
                    $resulte1 = $connection2->query($sqle1);
                    $rowdata1 = $resulte1->fetch();

                    $to = $rowdata1['email'];
                    $subject = nl2br($subjectquote);
                    $body = nl2br($emailquote);
                    $msg = $smsquote;
                    $number = $rowdata1['phone1'];
                    $smspupilsightPersonID = $rowdata1['pupilsightPersonID'];
                }
            } else {
                $to = $userData['email'];
                $subject = nl2br($subjectquote);
                $body = nl2br($emailquote);
                $msg = $smsquote;
                $number = $userData['phone1'];
                $smspupilsightPersonID = $userData['pupilsightPersonID'];
            }
                

            if (!empty($body) && !empty($to)) {
                $url = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Students/mailsend.php';
                $url .= "&to=" . $to;
                $url .= "&sub=" . rawurlencode($subject);
                $url .= "&body=" . rawurlencode($body);

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
                        $sq = "INSERT INTO user_email_sms_sent_details SET type='2', sent_to = '1', pupilsightPersonID = " . $st . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', attachment= '" . $NewNameFile . "', uid=" . $cuid . " ";
                        $connection2->query($sq);

                        $msgby=$_SESSION[$guid]["pupilsightPersonID"];
                        //$msgto=$smspupilsightPersonID;

                        $msgby =$_SESSION[$guid]["pupilsightPersonID"];
                        Updatemessesnger($connection2,$msgby,$st,$body,$subject);
                        $nowtime =date("Y-m-d H:i:s");
                        $savedata = "INSERT INTO pupilsightMessengerReceipt SET pupilsightMessengerID='".$msgby."', pupilsightPersonID='".$msgby."', targetType='Individuals', targetID='".$st."', contactType='Email', contactDetail='".$to."', `key`='NA', confirmed='N', confirmedTimestamp='$nowtime' ";
                        $connection2->query($savedata);
                    } catch (Exception $ex) {
                        print_r($x);
                    }
                } else {
                    $res = file_get_contents($url);

                    $senderid=$_SESSION[$guid]["pupilsightPersonID"];
                    Updatemessesnger($connection2,$senderid,$st,$body,'na');
                    $res = file_get_contents($url);
                    $sq = "INSERT INTO user_email_sms_sent_details SET type='2', sent_to = '1', pupilsightPersonID = " . $st . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', uid=" . $cuid . " ";
                    $connection2->query($sq);
                    $nowtime =date("Y-m-d H:i:s");
                    $savedata = "INSERT INTO pupilsightMessengerReceipt SET pupilsightMessengerID='".$senderid."', pupilsightPersonID='".$senderid."', targetType='Individuals', targetID='".$st."', contactType='Email', contactDetail='".$to."', `key`='NA', confirmed='N', confirmedTimestamp='$nowtime' ";
                    $connection2->query($savedata);
                }
            }

            if (!empty($msg) && !empty($number)) {
                /*$urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                $urls .="&send_to=".$number;
                $urls .="&msg=".rawurlencode($msg);
                $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                //echo $urls;
                $resms = file_get_contents($urls);*/
                $msgto=$st;
                $msgby=$_SESSION[$guid]["pupilsightPersonID"];
                $res = $sms->sendSMSPro($number, $msg, $msgto, $msgby);
                if ($res) {
                    $sq = 'INSERT INTO user_email_sms_sent_details SET type="1", sent_to = "1", pupilsightPersonID = ' . $st . ', phone=' . $number . ', description="' . stripslashes($msg) . '", uid=' . $cuid . ' ';
                    $connection2->query($sq);
                }
            }
                
            

            //sendingmail($to);

            // echo $sub;
            // $data = array('campaign_id' => $campaignId,'form_id' => $formId, 'submission_id' => $sub, 'state' => $statename,  'state_id' => $stateid, 'status' => '1', 'cdt' => $crtd);

            //  $sql = "INSERT INTO campaign_form_status SET campaign_id=:campaign_id,form_id=:form_id, submission_id=:submission_id,state=:state,state_id=:state_id, status=:status, cdt=:cdt";
            //     $result = $connection2->prepare($sql);
            //     $result->execute($data);


        }

        //echo $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/campaignFormList.php&id='.$campaignId.'&search=';
        // header("Location: {$URL}");


    }
}
function Updatemessesnger($connection2,$sender,$st, $body="", $subject=""){
    //echo "hi"; //die();
    $ppid = $st;


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
}