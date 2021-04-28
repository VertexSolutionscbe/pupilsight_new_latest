<?php
/*
Pupilsight, Flexible & Open School System
*/
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $link = "https";
else
    $link = "http";
  
// Here append the common URL characters.
$link .= "://";
  
// Append the host(domain name, ip) to the URL.
$link .= $_SERVER['HTTP_HOST'];
  
// Append the requested resource location to the URL
//$link .= $_SERVER['REQUEST_URI'];
      
// Print the link
// echo $link;
// die();

include 'pupilsight.php';
use Pupilsight\Contracts\Comms\Mailer;

// ini_set( 'display_errors', 1 );
// error_reporting( E_ALL );

// $input = json_decode($data, true);

// $to = $_GET['to'];
// $subject = $_GET['subject'];
// $body = $_GET['body'];

$randstring = RandomString();

$val = $_POST['val'];

$sqlp = 'SELECT username, email, pupilsightPersonID FROM pupilsightPerson WHERE username = "'.$val.'" OR email = "'.$val.'" ';
$resultp = $connection2->query($sqlp);
$rowdataprog = $resultp->fetch();

$email = $rowdataprog['email'];
$pupilsightPersonID = $rowdataprog['pupilsightPersonID'];

if(!empty($email)){
    $to = $email;
    $subject = 'Password Reset Mail';

    $reslink = $link.'/reset_password.php?key='.$randstring;

    $body = 'Dear User, <br><br> Thank you for registering with Pupilpod. <br><br> Don’t remember your password? Worry not! Simply reset your password by clicking on the below link: <br><br> <a target="_blank" href="'.$reslink.'">'.$reslink.'</a> <br><br> Please use updated password to login to your account on pupilpod. <br><br> Regards, <br><br> Pupilpod <br><br> If you are not the intended recipient of this mail or have not initiated this registration request on pupilod, please drop us an email on support@pupilpod.in';

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

    // $mail = $container->get(Mailer::class);
    // $mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

    $mail->AddAddress($to);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->IsHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;
    //$mail->Send();

    if ($mail->send()) {
        echo "sent";
        $data1 = array('password_reset_key' => $randstring, 'pupilsightPersonID' => $pupilsightPersonID);
        $sql1 = "UPDATE pupilsightPerson SET password_reset_key=:password_reset_key WHERE pupilsightPersonID=:pupilsightPersonID";
        $result = $connection2->prepare($sql1);
        $result->execute($data1);
    } else {
        echo "Mailer Error: " . $mail->ErrorInfo;
        
    }

}
function RandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}