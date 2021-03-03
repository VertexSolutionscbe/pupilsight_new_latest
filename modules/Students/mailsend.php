<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;

$input = json_decode($data, true);

$to = $_GET['to'];
$subject = $_GET['sub'];
$body = $_GET['body'];
//die();

$mail = $container->get(Mailer::class);
$mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

$mail->AddAddress($to);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->IsHTML(true);
$mail->Subject = $subject;
$mail->Body = $body;
$mail->Send();

$data=array('email'=>$to);
$sql="SELECT pupilsightPersonID FROM pupilsightPerson WHERE email=:email";
$result = $connection2->prepare($sql);
$result->execute($data);
if ($result->rowCount() > 0) {
    while ($rowppid = $result->fetch()) {
        $ppid = $rowppid['pupilsightPersonID'];


        $msgby = $_SESSION[$guid]["pupilsightPersonID"];
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

}
