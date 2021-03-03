<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;
use Pupilsight\Contracts\Comms\SMS;

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Attendance/report_summary_byDate.php';

if (isActionAccessible($guid, $connection2, '/modules/Attendance/send_attendance_email_sms.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $sms = $container->get(SMS::class);
    $stuId = $_POST['stuid'];
    $crtd =  date('Y-m-d H:i:s');
    $emailquote = $_POST['emailquote'];
    $smsquote = $_POST['smsquote'];
    if ($stuId == '') {
        echo "error1";
    } else {
        $msg = $smsquote;
        if (!empty($msg)) {
            $studentId = explode(',', $stuId);
            foreach ($studentId as $st) {
                $m = "SELECT p.pupilsightPersonID,  p.email, p.phone1, p.officialName FROM pupilsightFamilyRelationship as r LEFT JOIN pupilsightPerson as p
            ON r.pupilsightPersonID1 = p.pupilsightPersonID WHERE r.pupilsightPersonID2=" . $st . " AND r.relationship='Mother'";
                $m = $connection2->query($m);
                $mother_data = $m->fetch();
                $f = "SELECT p.pupilsightPersonID, p.email, p.phone1, p.officialName FROM pupilsightFamilyRelationship as r LEFT JOIN pupilsightPerson as p
            ON r.pupilsightPersonID1 = p.pupilsightPersonID WHERE r.pupilsightPersonID2=" . $st . " AND r.relationship='Father'";
                $f = $connection2->query($f);
                $father_data = $f->fetch();
                if (!empty($mother_data['phone1'])) {
                    $msgto=$mother_data['pupilsightPersonID'];
                    $msgby=$_SESSION[$guid]["pupilsightPersonID"];
                    $res = $sms->sendSMSPro($mother_data['phone1'], $msg, $msgto, $msgby);
                    //send_sms($mother_data['phone1'], $msg);
                }
                if (!empty($father_data['phone1'])) {
                    $msgto=$father_data['pupilsightPersonID'];
                    $msgby=$_SESSION[$guid]["pupilsightPersonID"];
                    //send_sms($father_data['phone1'], $msg);
                    $res = $sms->sendSMSPro($father_data['phone1'], $msg, $msgto, $msgby);
                }
            }
            echo "success";
        } else {
            echo "message not empty";
        }
    }
}
function send_sms($number, $msg)
{
    /*$number="8777281040";*/
    /*
    $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
    $urls .="&send_to=".$number;
    $urls .="&msg=".rawurlencode($msg);
    $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
    $resms = file_get_contents($urls);
    */
}
