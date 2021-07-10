<?php

include 'pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;


$param = array_merge($_GET, $_POST);
if (empty($param["to"]) || empty($param["subject"]) || empty($param["body"])) {
    echo "Invalid Parameters";
    die();
}

$body = nl2br($param["body"]);

$mail = $container->get(Mailer::class);
$mail->SetFrom($_SESSION[$guid]['organisationEmail'], $_SESSION[$guid]['organisationName']);

$mail->AddAddress($param["to"]);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->isHTML(true);
$mail->Subject = $param["subject"];
$mail->Body = $body;

// $mail->AddAttachment($_FILES['emailAttachment']['tmp_name'],
// $_FILES['emailAttachment']['name']);

$mail->Send();
