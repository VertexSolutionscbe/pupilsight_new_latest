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

$mail = $container->get(Mailer::class);
$mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

$mail->AddAddress($to);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
// $mail->addAttachment($emailAttachment);                 // Add attachments
// $mail->addAttachment('');                               // Optional name
$mail->isHTML(true); 
$mail->Subject = $subject;
$mail->Body = $body;
    
// $mail->AddAttachment($_FILES['emailAttachment']['tmp_name'],
// $_FILES['emailAttachment']['name']);

$mail->Send();

