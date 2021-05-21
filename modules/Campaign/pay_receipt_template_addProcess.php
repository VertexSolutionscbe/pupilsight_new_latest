<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
ini_set('max_execution_time', 7200);
use Pupilsight\Contracts\Comms\Mailer;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address']).'/check_status.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/check_status.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // print_r($_FILES);
    // die();
    //Proceed!
    $type = $_POST['type'];
    $campaign_id = $_POST['campaign_id'];
    $submission_id = $_POST['submission_id'];
    $pay_amount = $_POST['pay_amount'];
    $date = str_replace('/', '-', $_POST['pay_date']);
    $pay_date = date('Y-m-d', strtotime($date));
   
   
    if ($pay_amount == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('type' => $type, 'campaign_id' => $campaign_id, 'submission_id' => $submission_id);
            $sql = 'SELECT * FROM campaign_payment_attachment WHERE type=:type AND campaign_id=:campaign_id AND submission_id=:submission_id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Check for other currents
            
                //Write to database
                try {

                    $attachment = '';
                    //Move attached image  file, if there is one
                    if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0){
                        $allowed = array("docx" => "docx", "pdf" => "pdf");
                        $filename = $_FILES["file"]["name"];
                        $filetype = $_FILES["file"]["type"];
                        $filesize = $_FILES["file"]["size"];
                       
                        // Verify file extension
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
    
    
                        $filename = time() . '_' .  $_FILES["file"]["name"];
                        $fileTarget = $_SERVER['DOCUMENT_ROOT']."/public/pay_receipt/" . $filename;	
                        $attachment = '/public/pay_receipt/' . $filename;
                        if(move_uploaded_file($_FILES["file"]["tmp_name"], $fileTarget)){
                            echo "Receipt upload successfully";
                        } else {
                                echo "No";
                        }
                    } else{
                        // echo "Error: " . $_FILES["file"]["error"];
                    }
    
                    $data = array('type' => $type, 'campaign_id' => $campaign_id, 'submission_id' => $submission_id, 'pay_attachment' => $attachment, 'pay_amount' => $pay_amount, 'pay_date' => $pay_date);
                    $sql = "INSERT INTO campaign_payment_attachment SET type=:type, campaign_id=:campaign_id, submission_id=:submission_id, pay_attachment=:pay_attachment, pay_amount=:pay_amount, pay_date=:pay_date";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);

                    if($type == 'Registration Fee Paid'){
                        try {
                            $sql = 'SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id= "'.$submission_id.'" AND field_name = "student_name" ';
                            $result = $connection2->query($sql);
                            $attachData = $result->fetch();
                            $student_name = $attachData['field_value'];

                            //$to = 'accounts@gigis.edu.sg';
                            $to = 'anand.r@thoughtnet.in';
                            $subject = 'Pay Receipt Uploaded';
                            $body = 'Hi,
                            </br>
                            '.$student_name.' has paid registration fee.
                            1. Paid Amount = '.$pay_amount.'</br>
                            2. Payment Date = '.$_POST['pay_date'].'</br>
                            3. Attachment =  <a href="'.$_SESSION[$guid]['absoluteURL'].$attachment.'" download>Pay Receipt</a>
                            ';

                            $uploaddir = $_SERVER['DOCUMENT_ROOT']."/public/pay_receipt/";
                            $uploadfile = $uploaddir . $filename;

                            $mail = $container->get(Mailer::class);
                            $mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

                            $mail->AddAddress($to);
                            $mail->CharSet = 'UTF-8';
                            $mail->Encoding = 'base64';
                            $mail->AddAttachment($uploadfile);                        // Optional name
                            $mail->isHTML(true);
                            $mail->Subject = $subject;
                            $mail->Body = nl2br($body);
                            $mail->Send();
                            
                        } catch (Exception $ex) {
                            print_r($ex);
                            //die();
                        }
                    } else {
                        try {
                            $sql = 'SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id= "'.$submission_id.'" AND field_name = "student_name" ';
                            $result = $connection2->query($sql);
                            $attachData = $result->fetch();
                            $student_name = $attachData['field_value'];

                            //$to = 'accounts@gigis.edu.sg';
                            $to = 'anand.r@thoughtnet.in';
                            $subject = 'Pay Receipt Uploaded';
                            $body = 'Hi,
                            </br>
                            '.$student_name.' has uploaded transactions details for term fee, please verify and update the fee in pupilpod.
                            1. Paid Amount = '.$pay_amount.'</br>
                            2. Payment Date = '.$_POST['pay_date'].'</br>
                            3. Attachment =  <a href="'.$_SESSION[$guid]['absoluteURL'].$attachment.'" download>Pay Receipt</a>
                            ';

                            $uploaddir = $_SERVER['DOCUMENT_ROOT']."/public/pay_receipt/";
                            $uploadfile = $uploaddir . $filename;

                            $mail = $container->get(Mailer::class);
                            $mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

                            $mail->AddAddress($to);
                            $mail->CharSet = 'UTF-8';
                            $mail->Encoding = 'base64';
                            $mail->AddAttachment($uploadfile);                        // Optional name
                            $mail->isHTML(true);
                            $mail->Subject = $subject;
                            $mail->Body = nl2br($body);
                            $mail->Send();
                            
                        } catch (Exception $ex) {
                            print_r($ex);
                            //die();
                        }
                    }
                } catch (PDOException $e) {
                    print_r($e);
                    //die();
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }
   
                // $URL .= "&return=success0&editID=$AI";
              
                $URL .= '&return=success0';
                header("Location: {$URL}");
           
        }
    }
}