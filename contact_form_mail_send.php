<?php
/*
Pupilsight, Flexible & Open School System
*/

include 'pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;

//$input = json_decode($data, true);

$to = 'bikash@thoughtnet.in';
$subject = 'OTP For Contract Form';

$otp = '1234';
$body = "Dear Parent,
</br>
Use ".$otp." as your authorization OTP to accept GIGIS Student eContract. OTP is confidential and valid for 10 mins. Sharing it with anyone gives them complete access to your eContract.
</br></br>
Regards,
</br>
GIGIS Admin Team";
//die();

$mail = $container->get(Mailer::class);
$mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

$mail->AddAddress($to);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->IsHTML(true);
$mail->Subject = $subject;
$mail->Body = nl2br($body);
$mail->Send();


