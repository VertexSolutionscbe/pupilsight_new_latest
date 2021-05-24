<?php
// $number = "8867776787";
// $msg = "test sm dara";
// sendSMS($number, $msg);
// function sendSMS($number, $msg)
// {
//     try {
//         $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
//         $urls .= "&send_to=" . $number;
//         $urls .= "&msg=" . rawurlencode($msg);
//         $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
//         $resms = file_get_contents($urls);
//         print_r($resms);
//     } catch (Exception $ex) {
//         print_r($ex);
//     }
// }

// die();
include 'pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;

// ini_set( 'display_errors', 1 );
// error_reporting( E_ALL );

// $input = json_decode($data, true);
$to = "bikash@thoughtnet.in";
$subject = "Mail Testing";
$body = "Mail Testing";

/*
$to = $_GET['to'];
$subject = $_GET['subject'];
$body = $_GET['body'];
*/
$mail = $container->get(Mailer::class);
$mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

$mail->AddAddress($to);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->isHTML(true);
$mail->Subject = $subject;
$mail->Body = $body;

// $mail->AddAttachment($_FILES['emailAttachment']['tmp_name'],
// $_FILES['emailAttachment']['name']);

$mail->Send();

die();
// $date = date('Y-m-d H:i:s');
// echo $date;
// $commoadPath = "lowriter --convert-to pdf " . $_SERVER['DOCUMENT_ROOT'] . "/thirdparty/phpword/templates/refund_receipt.docx";
// echo "\n<br>\n" . $commoadPath;

// echo "\n";
// $command = escapeshellcmd($commoadPath);
// $highlight = shell_exec($command);
// print_r($highlight);
