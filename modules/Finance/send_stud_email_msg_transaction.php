<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
use Pupilsight\Contracts\Comms\Mailer;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/invoice_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/send_stud_email_msg.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
   
//    echo "<pre>";
//   print_r($_REQUEST);
//   die();
    $stuId = $_POST['stuid'];
    $crtd =  date('Y-m-d H:i:s');
    $emailquote = $_POST['emailquote'];
    $subjectquote = $_POST['subjectquote'];
    $smsquote = $_POST['smsquote'];
    $type = $_POST['type'];
    $types = explode(',', $type);
    $crtd =  date('Y-m-d H:i:s');
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
//print_r($_FILES["email_attach_inv"]);

   
    if ($stuId == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    }  else {

        $studentId = explode(',', $stuId);
        //print_r($submissionId);die();

        $attachmentStatus = "No";
        $NewNameFile = '';
        $errStatus = "No";
        if (!empty($_FILES["email_attach_inv"]["name"])) {
            $fileData = pathinfo(basename($_FILES["email_attach_inv"]["name"]));
            $ex = explode(".", $_FILES["email_attach_inv"]["name"]);
            $extension = end($ex);
            $NewNameFile = time() . '.' . $extension;
            $sourcePath = $_FILES['email_attach_inv']['tmp_name'];

            //$uploaddir = '../../public/attactments_campaign/';
            $uploaddir = $_SERVER['DOCUMENT_ROOT'] . "/public/attachments/";
            $uploadfile = $uploaddir . $NewNameFile;

            //echo "\nupload file path : ".$uploadfile."\n";
            if (move_uploaded_file($sourcePath, $uploadfile)) {
                $attachmentStatus = "Yes";
            }
            //echo $uploadfile;
            //echo $attachmentStatus;
        }
        //die();
       
        foreach($studentId as $st_inv){
            $sqle = "SELECT pupilsightPersonID FROM fn_fees_collection WHERE id = ".$st_inv." ";
            $resulte = $connection2->query($sqle);
            $stdata = $resulte->fetch();
            $st = $stdata['pupilsightPersonID'];


            if(!empty($types)){
                foreach($types as $tp){
                    if($tp == 'fatherMobile' || $tp == 'fatherEmail'){
                        $rtype = 'Father';
                    }
                    if($tp == 'motherMobile' || $tp == 'motherEmail'){
                        $rtype = 'Mother';
                    }
                    if($tp == 'guardianMobile' || $tp == 'guardianEmail'){
                        $rtype = 'Other';
                    }

                    $sqle = "SELECT b.email, b.phone1, b.officialName FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightPersonID2 = ".$st." AND a.relationship = '".$rtype."' ";
                    $resulte = $connection2->query($sqle);
                    $rowdata = $resulte->fetch();
                        
                    $to = $rowdata['email'];
                    $subject = nl2br($subjectquote);
                    $body = nl2br($emailquote);
                    $msg = $smsquote;
                    $number = $rowdata['phone1'];


                    if(!empty($body) && !empty($to)){ 
                        $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/mailsend.php';
                        $url .="&to=".$to;
                        $url .="&sub=".rawurlencode($subject);
                        $url .="&body=".rawurlencode($body);

                        if ($attachmentStatus == "Yes") {
                            $from = $_SESSION[$guid]['organisationAdministratorEmail'];
                            $fromName = $_SESSION[$guid]['organisationAdministratorName'];
                            // sendEmailAttactment($to,$subject,$body,$NewNameFile,$from, $fromName);
        
        
                            try {
                                $mail = $container->get(Mailer::class);
                                $mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);
        
                                $mail->AddAddress($to);
                                $mail->CharSet = 'UTF-8';
                                $mail->Encoding = 'base64';
                                $mail->AddAttachment($uploadfile);                        // Optional name
                                $mail->isHTML(true);
                                $mail->Subject = $subject;
                                $mail->Body = $body;
        
                                $mail->Send();
                                $sq = "INSERT INTO user_email_sms_sent_details SET type='2', sent_to = '1', pupilsightPersonID = " . $st . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', attachment= '" . $NewNameFile . "', uid=" . $cuid . " ";
                                $connection2->query($sq);
                            } catch (Exception $ex) {
                                print_r($x);
                            }
                        } else {
                            $res = file_get_contents($url);
                            $sq = "INSERT INTO user_email_sms_sent_details SET type='2', sent_to = '1', pupilsightPersonID = " . $st . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', uid=" . $cuid . " ";
                            $connection2->query($sq);
                        }
                        
                    }    
        
                    if(!empty($msg) && !empty($number)){
                        $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                        $urls .="&send_to=".$number;
                        $urls .="&msg=".rawurlencode($msg);
                        $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                        //echo $urls;
                        $resms = file_get_contents($urls);

                        $sq = "INSERT INTO user_email_sms_sent_details SET type='1', sent_to = '1', pupilsightPersonID = " . $st . ", phone=" . $number . ", description='" . stripslashes($msg) . "', uid=" . $cuid . " ";
                        $connection2->query($sq);
                    }
                    
                }
            }
            
            //sendingmail($to);

           // echo $sub;
            // $data = array('campaign_id' => $campaignId,'form_id' => $formId, 'submission_id' => $sub, 'state' => $statename,  'state_id' => $stateid, 'status' => '1', 'cdt' => $crtd);
                            
            //  $sql = "INSERT INTO campaign_form_status SET campaign_id=:campaign_id,form_id=:form_id, submission_id=:submission_id,state=:state,state_id=:state_id, status=:status, cdt=:cdt";
            //     $result = $connection2->prepare($sql);
            //     $result->execute($data);
            
            
        }
        
        //echo $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/campaignFormList.php&id='.$campaignId.'&search=';
               // header("Location: {$URL}");
                
       
    }
}
