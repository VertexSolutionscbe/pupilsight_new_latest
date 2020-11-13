<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
use Pupilsight\Contracts\Comms\Mailer;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/send_stud_email_msg.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
   
//   echo "<pre>";
//   print_r($_REQUEST);exit;
    $stuId = $_POST['stuid'];
    $crtd =  date('Y-m-d H:i:s');
    $emailquote = $_POST['emailquote'];
    $subjectquote = $_POST['subjectquote'];
    $smsquote = $_POST['smsquote'];
    $type = $_POST['type'];
    $types = explode(',', $type);
    $crtd =  date('Y-m-d H:i:s');
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];


   
    if ($stuId == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    }  else {

        $studentId = explode(',', $stuId);
        //print_r($submissionId);die();
        foreach($studentId as $st){
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
                        $res = file_get_contents($url);

                        $sq = "INSERT INTO user_email_sms_sent_details SET  pupilsightPersonID = " . $st . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', uid=" . $cuid . " ";
                        $connection2->query($sq);
                    }    
        
                    if(!empty($msg) && !empty($number)){
                        $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                        $urls .="&send_to=".$number;
                        $urls .="&msg=".rawurlencode($msg);
                        $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                        $resms = file_get_contents($urls);

                        $sq = "INSERT INTO user_email_sms_sent_details SET  pupilsightPersonID = " . $st . ", phone=" . $number . ", description='" . stripslashes($msg) . "', uid=" . $cuid . " ";
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
