<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
use Pupilsight\Contracts\Comms\Mailer;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/manage_test_results.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/send_stud_test_result_email_msg.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
   // `id``from_state``to_state``transition_display_name``tansition_action``cuid``auto_gen_inv``tansition_action``cuid`

//   echo "<pre>";
//   print_r($_REQUEST);exit;
    $stuId = $_POST['stuid'];
    $crtd =  date('Y-m-d H:i:s');
    $emailquote = $_POST['emailquote'];
    $smsquote = $_POST['smsquote'];


   
    if ($stuId == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    }  else {

        $studentId = explode(',', $stuId);
        //print_r($submissionId);die();
        foreach($studentId as $st){
            $sqle = "SELECT b.email, b.phone1, b.officialName FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightPersonID2 = ".$st." ";
           
           // $sqle = "SELECT a.email, a.phone1, a.officialName FROM  pupilsightPerson AS a  WHERE a.pupilsightPersonID = ".$st." ";
            $resulte = $connection2->query($sqle);
            $rowdata = $resulte->fetch();
            
            $to = $rowdata['email'];
         //   $to = '';
            $subject = 'Student Update';
            $body = nl2br($emailquote);
            $msg = $smsquote;
          //  $number = '';
           $number = $rowdata['phone1'];
            //sendingmail($to);
            
            if(!empty($body) && !empty($to)){ 
                $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/mailsend_test_result.php';
                $url .="&to=".$to;
                $url .="&sub=".rawurlencode($subject);
                $url .="&body=".rawurlencode($body);
                $res = file_get_contents($url);
            }    

            if(!empty($msg) && !empty($number)){
                $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                $urls .="&send_to=".$number;
                $urls .="&msg=".rawurlencode($msg);
                $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                $resms = file_get_contents($urls);
            }
            
        }
        
        //echo $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/campaignFormList.php&id='.$campaignId.'&search=';
               // header("Location: {$URL}");
                
       
    }
}
