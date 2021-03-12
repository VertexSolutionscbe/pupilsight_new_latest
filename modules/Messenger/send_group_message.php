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
    //   print_r($_POST);
    //   die();
    $sms = $container->get(SMS::class);

    
    $msg = $_POST['msgval'];
    $type = $_POST['msgtype'];

    $crtd =  date('Y-m-d H:i:s');
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];

    if(!empty($_POST['grpval'])){
        $groupIds = explode(',', $_POST['grpval']);
        foreach($groupIds as $groupId){
            $sql = "SELECT b.pupilsightPersonID, b.email, b.phone1, b.officialName FROM pupilsightGroupPerson AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightGroupID = " . $groupId . "  ";
            $result = $connection2->query($sql);
            $rowdata = $result->fetchAll();

            if(!empty($rowdata)){
                foreach($rowdata as $data){
                    $pupilsightPersonID = $data['pupilsightPersonID'];
                    $email = $data['email'];
                    $number = $data['phone1'];
                    $name = $data['officialName'];
                    if($type == 'sms'){
                        if (!empty($msg) && !empty($number)) {
                            
                            $msgto=$pupilsightPersonID;
                            $msgby=$cuid;
                            $res = $sms->sendSMSPro($number, $msg, $msgto, $msgby);
                            
                        }
                    }
                    if($type == 'email'){
                        $to = $email;
                        $subject = nl2br($_POST['subject']);
                        $body = nl2br($_POST['body']);
                        if (!empty($body) && !empty($to)) {
                            $url = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Students/mailsend.php';
                            $url .= "&to=" . $to;
                            $url .= "&sub=" . rawurlencode($subject);
                            $url .= "&body=" . rawurlencode($body);
                            $attachmentStatus = 'No';
                            $st = $pupilsightPersonID;

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
                                //echo $url;
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
                    }
                }
            }
        }
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
