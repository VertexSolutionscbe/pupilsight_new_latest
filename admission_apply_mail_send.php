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



if (isset($_POST['to'])) {
    $to = $_POST['to'];
}

if (isset($_POST['stuname'])) {
    $stu_name = $_POST['stuname'];
}

$subject = 'Application Form Applied';


$body =
    "Dear Admissions  Head,
</br>
 <b>" . $stu_name . "</b> has submitted the application form. Kindly login to pupilpod and verify the form and change the status to document verified.";


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


if (isset($_SESSION['submissionId'])) {
    $sid = $_SESSION['submissionId'];

    $sql = 'SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = "'.$sid.'" AND field_name = "student_transport_opted"  ';
    $result = $connection2->query($sql);
    $chkStData = $result->fetch();
    if(!empty($chkStData)){
        if($chkStData['field_value'] == 'Yes'){
            $sql = 'SELECT * FROM wp_fluentform_entry_details WHERE submission_id = "'.$sid.'"  ';
            $result = $connection2->query($sql);
            $data = $result->fetchAll();

            $len = count($data);
            $i = 0;
            $dt = array();
            while($i<$len){
                $dt[$data[$i]["field_name"]] = $data[$i]["field_value"];
                $i++;
            }
            
            
            $address = $dt["student_address"];
            $student_postal_code = $dt["student_postal_code"];
            $student_bus_service = $dt["student_bus_service"];
            $student_transport_start_date = $dt["student_transport_start_date"];

            //$to = 'accounts@gigis.edu.sg';
            $to = 'anand.r@thoughtnet.in';
            $subject = 'Student Transport Details';
            $body = 'Hi,
            </br>
            '.$stu_name.' has opted for transport. Following are the details.
            1. Student Name         = '.$stu_name.'</br>
            2. Address              = '.$address.'</br>
            3. Postal Code          = '.$student_postal_code.'</br>
            4. Bus Service          = '.$student_bus_service.'</br>
            5. Transport Start Date = '.$student_transport_start_date;

            $mail1 = $container->get(Mailer::class);
            $mail1->SetFrom($email, $name);
            $mail1->AddAddress($to);
            $mail1->CharSet = 'UTF-8';
            $mail1->Encoding = 'base64';
            $mail1->IsHTML(true);
            $mail1->Subject = $subject;
            $mail1->Body = nl2br($body);
            $res1 = $mail1->Send();
        }
    }
}
echo 'done';
