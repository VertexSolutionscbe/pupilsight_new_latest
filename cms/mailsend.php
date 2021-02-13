<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../pupilsight.php';
use Pupilsight\Contracts\Comms\Mailer;

// ini_set( 'display_errors', 1 );
// error_reporting( E_ALL );

// $input = json_decode($data, true);

$to = $_GET['to'];
$subject = $_GET['subject'];
$body = $_GET['body'];

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
// $mail->addAttachment($emailAttachment);                 // Add attachments
// $mail->addAttachment('');                               // Optional name
$mail->isHTML(true); 
$mail->Subject = $subject;
$mail->Body = $body;
    
// $mail->AddAttachment($_FILES['emailAttachment']['tmp_name'],
// $_FILES['emailAttachment']['name']);

$mail->Send();

