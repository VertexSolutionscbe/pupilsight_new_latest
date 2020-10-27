<?php
include_once 'w2f/adminLib.php';
include '../pupilsight.php';
$adminlib = new adminlib();
session_start();
//$input = $_SESSION['campaignuserdata'];
$type = $_POST['type'];
if ($type == 'insertcampaigndetails') {
    $campid = $_POST['val'];
    $pupilsightProgramID = $_POST['pid'];
    $form_id = $_POST['fid'];
    $pupilsightYearGroupID = $_POST['clid'];
    $submissionId = $_SESSION['submissionId'];
    if (!empty($pupilsightYearGroupID) && !empty($submissionId)) {
        //$insert = $adminlib->createCampaignRegistration($input, $campid);

        // $sql = "SELECT b.id, b.formatval FROM campaign AS a LEFT JOIN fn_fee_series AS b ON a.application_series_id = b.id WHERE a.id = " . $campid . " ";
        // $result = database::doSelectOne($sql);

        // if (!empty($result['formatval'])) {
        //     $seriesId = $result['id'];
        //     $invformat = explode('$', $result['formatval']);
        //     $iformat = '';
        //     $orderwise = 0;
        //     foreach ($invformat as $inv) {
        //         if ($inv == '{AB}') {
        //             $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=' . $seriesId . ' AND type= "numberwise"';
        //             $formatvalues = database::doSelectOne($sqlfort);


        //             $str_length = $formatvalues['no_of_digit'];

        //             $iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);

        //             $lastnoadd = $formatvalues['last_no'] + 1;

        //             $lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);

        //             $sql1 = "UPDATE fn_fee_series_number_format SET last_no= " . $lastno . " WHERE fn_fee_series_id= " . $seriesId . " AND type= 'numberwise'  ";
        //             $result1 = database::doUpdate($sql1);
        //         } else {
        //             $iformat .= $inv;
        //         }
        //         $orderwise++;
        //     }
        //     $application_id = $iformat;
        // } else {
        //     $application_id = '';
        // }

        $application_id = '';

        // $insert = $adminlib->updateApplicantData($submissionId, $pupilsightProgramID, $pupilsightYearGroupID, $application_id);
        //unset($_SESSION["submissionId"]);

        $insert = $adminlib->updateApplicantData($submissionId, $pupilsightProgramID, $pupilsightYearGroupID);

        


    }
}

if ($type == 'saveApplicantForm') {
    $submissionId = $_SESSION['submissionId'];
    $data = base64_decode($_POST['pdf']);
    // print_r($data);
    file_put_contents("../public/applicationpdf/" . $submissionId . "-application.pdf", $data);
}
//echo $msg;



