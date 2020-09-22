<?php
require('cms/admin/php/phpmailer/5.2.10/PHPMailerAutoload.php');
require('cms/admin/php/phpmailer/5.2.10/class.phpmailer.php');
require('cms/admin/php/phpmailer/5.2.10/class.smtp.php');

 $mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.googlemail.com';              // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'messages@pupilpod.in';                 // SMTP username
$mail->Password = 'Tnet@007';                           // SMTP password
$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465;                                    // TCP port to connect to

$mail->From = $_REQUEST['from'];
$mail->FromName = $_REQUEST['frm_name'];
//$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
$mail->addAddress($_REQUEST['to']);               // Name is optional

//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = $_REQUEST['sub'];
$mail->Body    = $_REQUEST['msg'];
$mail->AltBody = '';

$mail->Send();
 if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}
?>