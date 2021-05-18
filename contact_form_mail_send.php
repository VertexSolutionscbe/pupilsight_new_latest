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

$otp = rand(1000, 9999);
$_SESSION['contract_form_otp'] = $otp;
if (isset($_POST['to'])) {
    $to = $_POST['to'];
}
//$to = "rakesh@thoughtnet.in";

//$to = 'bikash@thoughtnet.in';
$subject = 'OTP For Contract Form';

//$otp = '1234';
$body =
    "Dear Parent,
</br>
Use <b>" .
    $otp .
    "</b> as your authorization OTP to accept GIGIS Student eContract. OTP is confidential and valid for 10 mins. Sharing it with anyone gives them complete access to your eContract.
</br></br>
Regards,
</br>
GIGIS Admin Team";
//die();

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
//echo $name ." ---  ".$email;
//print_r($_POST);
//print_r($res);
echo 'done';