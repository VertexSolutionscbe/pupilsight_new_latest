<?php
require('cms/admin/php/phpmailer/5.2.10/PHPMailerAutoload.php');
require('cms/admin/php/phpmailer/5.2.10/class.phpmailer.php');
require('cms/admin/php/phpmailer/5.2.10/class.smtp.php');
include_once 'pupilsight.php';
//$mail->SMTPDebug = 3;                              
// Enable verbose debug output

$mail = new PHPMailer;
$mail->isSMTP();                                     
 // Set mailer to use SMTP
$mail->Host = getSval('mailerSMTPHost',$connection2);             

// Specify main and backup SMTP servers
$mail->SMTPAuth = true;                              
 // Enable SMTP authentication
$mail->Username = getSval('mailerSMTPUsername',$connection2);                 // SMTP username
$mail->Password = getSval('mailerSMTPPassword',$connection2);                          
 // SMTP password 
$mail->SMTPSecure = getSval('mailerSMTPSecure',$connection2);                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = getSval('mailerSMTPPort',$connection2);;                                    // TCP port to connect to

$mail->From =  getSval('mailerSMTPUsername',$connection2); 
$mail->FromName =  'pupilsight'; 
$mail->isHTML(true);
//$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
/*
$mail->addAddress($to);              
// Name is optional

//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = $sub;
$mail->Body    = $msg;
$mail->AltBody = '';

$mail->Send();
 if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}
*/

function getSval($name,$connection2){
    $sql1=' SELECT name, value FROM pupilsightSetting WHERE name="'.$name.'"';
   
 
    $resu_h= $connection2->query($sql1);
    $datam_h  = $resu_h->fetch();
    if(!empty($datam_h)){
        return $datam_h['value'];
    } else{
      return false;
    }


}
?>