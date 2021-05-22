<?php
/*
Pupilsight, Flexible & Open School System
*/

include 'pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;

//$input = json_decode($data, true);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



if (isset($_POST['to'])) {
    $to = $_POST['to'];
}

if (isset($_POST['stuname'])) {
    $stu_name = $_POST['stuname'];
}

$subject = 'Application Form Applied';


$body =
    "Dear Admissions  Head,
</br>
 <b>" . $stu_name . "</b> has submitted the application form. Kindly login to pupilpod and verify the form and change the status to document verified.";


$sql = "SELECT value FROM pupilsightSetting WHERE name = 'organisationName' ";
$result = $connection2->query($sql);
$nameData = $result->fetch();
$name = $nameData['value'];

$sqle = "SELECT value FROM pupilsightSetting WHERE name = 'organisationEmail' ";
$resulte = $connection2->query($sqle);
$emailData = $resulte->fetch();
$email = $emailData['value'];

$mail = $container->get(Mailer::class);
$mail->SetFrom($email, $name);
$mail->AddAddress($to);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->IsHTML(true);
$mail->Subject = $subject;
$mail->Body = nl2br($body);
$res = $mail->Send();

echo 'done';
