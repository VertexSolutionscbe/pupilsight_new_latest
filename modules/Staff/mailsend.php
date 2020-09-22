<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
use Pupilsight\Contracts\Comms\Mailer;

$input = json_decode($data, true);

$to = $_GET['to'];
$subject = $_GET['subject'];
$body = $_GET['body'];
//die();

$mail = $container->get(Mailer::class);
$mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

$mail->AddAddress($to);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->IsHTML(true);
$mail->Subject = 'Staff Notification';
$mail->Body = $body;
$mail->Send();

