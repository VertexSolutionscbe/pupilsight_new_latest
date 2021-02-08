<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;
use Pupilsight\Contracts\Comms\SMS;

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";


$subid = '100';
$crtd =  date('Y-m-d H:i:s');
$emailquote = 'Email Content';
$smsquote = 'SMS Content';
//$emailAttachment = $_FILES['emailAttachment'];
$emailSubjct_camp = 'Email Subject Content';
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

    if (!empty($ft_email)) {
        $url = $base_url . '/cms/mailsend.php';
        $url .= "?to=" . $ft_email;
        $url .= "&subject=" . rawurlencode($subject);
        $url .= "&body=" . rawurlencode($body);
        sendEmail($ft_email, $subject, $body, $subid, $cuid, $connection2, $url);
    }
    if (!empty($mt_email)) {
        $url = $base_url . '/cms/mailsend.php';
        $url .= "?to=" . $mt_email;
        $url .= "&subject=" . rawurlencode($subject);
        $url .= "&body=" . rawurlencode($body);
        sendEmail($mt_email, $subject, $body, $subid, $cuid, $connection2, $url);
    }
    if (!empty($gt_email)) {
        $url = $base_url . '/cms/mailsend.php';
        $url .= "?to=" . $gt_email;
        $url .= "&subject=" . rawurlencode($subject);
        $url .= "&body=" . rawurlencode($body);
        sendEmail($gt_email, $subject, $body, $subid, $cuid, $connection2, $url);
    }
}

if (!empty($smsquote) && !empty($msg)) {
    if (!empty($ft_number)) {
        sendSMS($ft_number, $msg, $subid, $cuid, $connection2, $container);
    }
    if (!empty($mt_number)) {
        sendSMS($mt_number, $msg, $subid, $cuid, $connection2, $container);
    }
    if (!empty($gt_number)) {
        sendSMS($gt_number, $msg, $subid, $cuid, $connection2, $container);
    }
}

function sendSMS($number, $msg, $subid, $cuid, $connection2, $container)
{
    $sms = $container->get(SMS::class);
    $res = $sms->sendSMSPro($number, $msg);
    /*
    $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
    $urls .= "&send_to=" . $number;
    $urls .= "&msg=" . rawurlencode($msg);
    $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
    $resms = file_get_contents($urls);
    */

    if ($res) {
        $sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", phone=" . $number . ", description='" . stripslashes($msg) . "', pupilsightPersonID=" . $cuid . " ";
        $connection2->query($sq);
    }
}

function sendEMail($to, $subject, $body, $subid, $cuid, $connection2, $url)
{

    //sending attachment

    $res = file_get_contents($url);
    $sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', pupilsightPersonID=" . $cuid . " ";
    $connection2->query($sq);
}
