<?php 
include_once 'w2f/adminLib.php';
$adminlib = new adminlib();
session_start();
//$input = $_SESSION['campaignuserdata'];
$type = $_POST['type'];
if($type == 'insertcampaigndetails'){
    $campid = $_POST['val'];
    $pupilsightProgramID = $_POST['pid'];
    $form_id = $_POST['fid'];
    $pupilsightYearGroupID = $_POST['clid'];
    $submissionId = $_SESSION['submissionId'];
    if(!empty($pupilsightYearGroupID) && !empty($submissionId)){
        //$insert = $adminlib->createCampaignRegistration($input, $campid);
        $insert = $adminlib->updateApplicantData($submissionId, $pupilsightProgramID, $pupilsightYearGroupID);
        unset($_SESSION["submissionId"]);
    }
}

if($type == 'saveApplicantForm'){
    $submissionId = $_SESSION['submissionId'];
    $data = base64_decode($_POST['pdf']);
    // print_r($data);
    file_put_contents( "../public/applicationpdf/".$submissionId."-application.pdf", $data );
}
//echo $msg;

?>