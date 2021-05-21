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

if (isset($_POST['sid'])) {
    $sid = $_POST['sid'];
}

if (isset($_POST['stu_name'])) {
    $stu_name = $_POST['stu_name'];
}

$cdt = date('Y-m-d H:i:s');
$cuid = $_SESSION[$guid]['pupilsightPersonID'];

$sql = 'SELECT a.campaign_id, b.to_state, c.form_id FROM campaign_form_status AS a LEFT JOIN workflow_transition AS b ON a.state_id = b.id LEFT JOIN campaign AS c ON a.campaign_id = c.id WHERE a.submission_id = "'.$sid.'" ORDER BY a.id DESC ';
$result = $connection2->query($sql);
$chkStData = $result->fetch();
$to_state = $chkStData['to_state'];
$campaign_id = $chkStData['campaign_id'];
$form_id = $chkStData['form_id'];

if(!empty($to_state)){
    $sql1 = 'SELECT id, transition_display_name FROM workflow_transition  WHERE from_state = "'.$to_state.'" ';
    $result1 = $connection2->query($sql1);
    $chkStData1 = $result1->fetch();
    if(!empty($chkStData1)){
        $state_id = $chkStData1['id'];
        $state_name = $chkStData1['transition_display_name'];

        $data = array('campaign_id' => $campaign_id,'form_id' => $form_id, 'submission_id' => $sid, 'state' => $state_name,  'state_id' => $state_id, 'status' => '1', 'pupilsightPersonID' => $cuid, 'cdt' => $cdt);

        $sql = "INSERT INTO campaign_form_status SET campaign_id=:campaign_id,form_id=:form_id, submission_id=:submission_id,state=:state,state_id=:state_id, status=:status, pupilsightPersonID=:pupilsightPersonID, cdt=:cdt";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    }
}



$sq = "update wp_fluentform_submissions SET is_contract_generated = '1' where id = " . $sid . " ";
$connection2->query($sq);

//$to = "admissions@gigis.edu.sg";
$to = 'anand.r@thoughtnet.in';
$subject = 'Student Contract Generated';

//$otp = '1234';
$body =
    "Hi,
</br>
 <b>" . $stu_name . "</b> has generated a student contract.";
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


//$to = "accounts@gigis.edu.sg";
$to = 'anand.r@thoughtnet.in';
$subject = 'Student Contract Generated';

//$otp = '1234';
$body =
    "Hi,
</br>
 <b>" . $stu_name . "</b> has generated student contract and term fee has been generated.";

$mail1 = $container->get(Mailer::class);
$mail1->SetFrom($email, $name);
$mail1->AddAddress($to);
$mail1->CharSet = 'UTF-8';
$mail1->Encoding = 'base64';
$mail1->IsHTML(true);
$mail1->Subject = $subject;
$mail1->Body = nl2br($body);
$res1 = $mail1->Send();
echo 'done';
