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

        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        $sqlet = "SELECT b.* FROM campaign AS a LEFT JOIN pupilsightTemplate AS b ON a.email_template_id = b.pupilsightTemplateID WHERE a.id = " . $campid . " ";
        $resultet = database::doSelectOne($sqlet);

        if(!empty($resultet)){
            $emailSubjct_camp = $resultet['subject'];
            $emailquote = $resultet['description'];
        } else {
            $emailSubjct_camp = 'Application Status';
            $emailquote = 'Your Application Submitted Successfully';
        }

        $sqlst = "SELECT b.* FROM campaign AS a LEFT JOIN pupilsightTemplate AS b ON a.sms_template_id = b.pupilsightTemplateID WHERE a.id = " . $campid . " ";
        $resultst = database::doSelectOne($sqlst);

        if(!empty($resultst)){
            $smsquote = $resultst['description'];
        } else {
            $smsquote = 'Your Application Submitted Successfully';
        }

        $subid = $submissionId;
        $crtd =  date('Y-m-d H:i:s');
        
        
        //$emailAttachment = $_FILES['emailAttachment'];
        
        $crtd =  date('Y-m-d H:i:s');

        $cuid = '001';

        $sqle = "SELECT response FROM wp_fluentform_submissions WHERE id = " . $subid . " ";
        $resulte = $connection2->query($sqle);
        $rowdata = $resulte->fetch();
        $sd = json_decode($rowdata['response'], TRUE);
        $email = "";
        $names = "";
        $ft_number = '';
        $mt_number = '';
        $gt_number = '';
        $ft_email = '';
        $mt_email = '';
        $gt_email = '';

        if ($sd) {
            $names = implode(' ', $sd['student_name']);
            $email = $sd['father_email'];
            if (!empty($sd['father_mobile'])) {
                $ft_number = $sd['father_mobile'];
            } 
            if (!empty($sd['mother_mobile'])) {
                $mt_number = $sd['mother_mobile'];
            } 
            if (!empty($sd['guardian_mobile'])) {
                $gt_number = $sd['guardian_mobile'];
            } 

            if (!empty($sd['father_email'])) {
                $ft_email = $sd['father_email'];
            } 
            if (!empty($sd['mother_email'])) {
                $mt_email = $sd['mother_email'];
            } 
            if (!empty($sd['guardian_email'])) {
                $gt_email = $sd['guardian_email'];
            } 
        }

        //$email = "it.rakesh@gmail.com";
        $subject = nl2br($emailSubjct_camp);
        $body = nl2br($emailquote);
        $msg = $smsquote;

        $sqlm = "SELECT field_name,field_value FROM wp_fluentform_entry_details WHERE submission_id = " . $subid . " And field_name = 'numeric-field_1' ";
        $resultm = $connection2->query($sqlm);
        $rowdatm = $resultm->fetch();

        if (!empty($emailquote) && !empty($body)) {
            
            if(!empty($ft_email)){
                $url = $base_url.'/cms/mailsend.php';
                $url .= "?to=" . $ft_email;
                $url .= "&subject=" . rawurlencode($subject);
                $url .= "&body=" . rawurlencode($body);
                sendEmail($ft_email, $subject, $body, $subid, $cuid, $connection2, $url);
            }
            if(!empty($mt_email)){
                $url = $base_url.'/cms/mailsend.php';
                $url .= "?to=" . $mt_email;
                $url .= "&subject=" . rawurlencode($subject);
                $url .= "&body=" . rawurlencode($body);
                echo $url;
                sendEmail($mt_email, $subject, $body, $subid, $cuid, $connection2, $url);
            }
            if(!empty($gt_email)){
                $url = $base_url.'/cms/mailsend.php';
                $url .= "?to=" . $gt_email;
                $url .= "&subject=" . rawurlencode($subject);
                $url .= "&body=" . rawurlencode($body);
                sendEmail($gt_email, $subject, $body, $subid, $cuid, $connection2, $url);
            }
        }

        if (!empty($smsquote) && !empty($msg)) {
            if(!empty($ft_number)){
                sendSMS($ft_number, $msg, $subid, $cuid, $connection2);
            }
            if(!empty($mt_number)){
                sendSMS($mt_number, $msg, $subid, $cuid, $connection2);
            }
            if(!empty($gt_number)){
                sendSMS($gt_number, $msg, $subid, $cuid, $connection2);
            }
        }



    }
}

if ($type == 'saveApplicantForm') {
    $submissionId = $_SESSION['submissionId'];
    $data = base64_decode($_POST['pdf']);
    // print_r($data);
    file_put_contents("../public/applicationpdf/" . $submissionId . "-application.pdf", $data);
}
//echo $msg;


function sendSMS($number, $msg, $subid, $cuid, $connection2){
    $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
    $urls .= "&send_to=" . $number;
    $urls .= "&msg=" . rawurlencode($msg);
    $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
    $resms = file_get_contents($urls);

    $sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", phone=" . $number . ", description='" . stripslashes($msg) . "', pupilsightPersonID=" . $cuid . " ";
    $connection2->query($sq);
}

function sendEMail($to, $subject, $body, $subid, $cuid, $connection2, $url){
   
    //sending attachment

        $res = file_get_contents($url);
        $sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', pupilsightPersonID=" . $cuid . " ";
        $connection2->query($sq);
    
}
