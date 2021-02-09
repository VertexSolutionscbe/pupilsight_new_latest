<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Module\Attendance\AttendanceView;
use Pupilsight\Contracts\Comms\SMS;

//Pupilsight system-wide includes
require __DIR__ . '/../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
//$pupilsightProgramID = $_POST['pupilsightProgramID'];
$ProgramID = $_POST['ProgramID'];
$pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
$pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
$session = $_POST['session'];
$currentDate = $_POST['currentDate'];
$pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
$today = date('Y-m-d');
$sms_usrs = explode(',', $_POST['sms_usrs']);
$q = $_POST['q'];
$session_data = [];
$session_data[] = $session;
if (isset($_POST['capy_to'])) {
    $capy_to = $_POST['capy_to'];
    foreach ($capy_to as $val) {
        $session_data[] = $val;
    }
}
$response_status = [];
$URL = "";/*$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$q."&pupilsightProgramID=$pupilsightProgramID&pupilsightYearGroupID=$pupilsightYearGroupID&pupilsightRollGroupID=$pupilsightRollGroupID&currentDate=".dateConvertBack($guid, $currentDate)."&session=".$session;*/
if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byRollGroup.php') == false) {
    $URL .= '&return=error0';
    $response_status['status'] = "Error";
    $response_status['msg'] = "No access error";
    //header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $smsenable = 0;
    $sms_recipients = "";
    $sms_tmp = "";
    $entities_d = array();
    $entities = "";
    if (!empty($ProgramID)) {
        $sqlp = 'SELECT att.enable_sms_absent,att.sms_recipients,tmp.entities,tmp.description
        FROM attn_settings as att
        LEFT JOIN pupilsightTemplateForAttendance as tmp
        ON att.sms_template_id = tmp.id  
        WHERE att.pupilsightProgramID="' . $ProgramID . '" AND  FIND_IN_SET("' . $pupilsightYearGroupID . '",att.pupilsightYearGroupID) > 0 AND att.enable_sms_absent="1"';
        $resultp = $connection2->query($sqlp);
        $rowdatasession = $resultp->fetch();
        $smsenable = $rowdatasession['enable_sms_absent'];
        $entities = $rowdatasession['entities'];
        $sms_tmp = $rowdatasession['description'];
        $entities = explode(',', $rowdatasession['entities']);
        $sms_recipients = explode(',', $rowdatasession['sms_recipients']);
        /* foreach ($entities as $val) {
          $val=trim($val);
          $v="@".$val;
          $entities_d[$v]="teset";
      }*/
    }

    $highestAction = getHighestGroupedAction($guid, '/modules/Attendance/attendance_take_byRollGroup.php', $connection2);
    // print_r($highestAction);die();
    if ($highestAction == false) {
        /*echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';*/
        $response_status['status'] = "Error";
        $response_status['msg'] = "The highest grouped action cannot be determined.";
        echo json_encode($response_status);
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightRollGroupID == '' and $currentDate == '') {
            $URL .= '&return=error1';
            //header("Location: {$URL}");
            //echo "Error";
            $response_status['status'] = "Error";
            $response_status['msg'] = "Parametters Missing (pupilsightRollGroupID Or currentDate).";
            echo json_encode($response_status);
        } else {
            try {
                // print_r($highestAction);
                if ($highestAction == 'Attendance By Section') {
                    $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID);
                    $sql = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightRollGroupID=:pupilsightRollGroupID';
                } else {
                    $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = "SELECT * FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroup.attendance = 'Y' AND pupilsightRollGroupID=:pupilsightRollGroupID ORDER BY LENGTH(name), name";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                //header("Location: {$URL}");
                //echo "some parameters error";
                $response_status['status'] = "Error";
                $response_status['msg'] = "some parameters error.";
                echo json_encode($response_status);
                exit();
            }
            // echo $result->rowCount();
            if ($result->rowCount() != 1) {
                $URL .= '&return=error99';
                //echo "error99";
                $response_status['status'] = "Error";
                $response_status['msg'] = "error99.";
                //header("Location: {$URL}");
            } else {
                //Check that date is not in the future
                $i = 0;
                foreach ($session_data as  $session) {
                    if ($currentDate > $today) {
                        $URL .= '&return=error3';
                        //echo "error3";
                        $response_status['status'] = "Error";
                        $response_status['msg'] = "Your request failed because the specified date is in the future, or is not a school day.";
                        echo json_encode($response_status);
                        exit();
                        //header("Location: {$URL}");
                    } else {
                        //Check that date is a school day
                        // check special day
                        $SpecialDays = array('date' => $currentDate);
                        $sqlSpecialDays = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date";
                        $resultSpecialDays = $connection2->prepare($sqlSpecialDays);
                        $resultSpecialDays->execute($SpecialDays);
                        $specialDaysCounts = $resultSpecialDays->fetch();
                        //check special day ends 
                        if (isSchoolOpen($guid, $currentDate, $connection2) == false and empty($specialDaysCounts)) {
                            $URL .= '&return=error3';
                            //echo "error3";
                            $response_status['status'] = "Error";
                            $response_status['msg'] = "Your request failed because the specified date is in the future, or is not a school day.";
                            echo json_encode($response_status);
                            exit();
                            // header("Location: {$URL}");
                        } else {
                            //Write to database
                            require_once __DIR__ . '/src/AttendanceView.php';
                            $attendance = new AttendanceView($pupilsight, $pdo);

                            try {
                                $data = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'session_no' => $session, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                                $sql = 'INSERT INTO pupilsightAttendanceLogRollGroup SET pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightYearGroupID=:pupilsightYearGroupID, session_no=:session_no,pupilsightDepartmentID=:pupilsightDepartmentID, date=:date, timestampTaken=:timestampTaken';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);

                                $pupilsightAttendanceLogID = $connection2->lastInsertID();
                            } catch (PDOException $e) {
                                $URL .= '&return=error2';
                                //header("Location: {$URL}");
                                $response_status['status'] = "Error";
                                $response_status['msg'] = "Your request failed due to a database error.";
                                echo json_encode($response_status);
                                exit();
                            }

                            $count = $_POST['count'];
                            $partialFail = false;

                            for ($i = 0; $i < $count; ++$i) {
                                $pupilsightPersonID = $_POST[$i . '-pupilsightPersonID'];
                                $studentName = $_POST[$i . '-pupilsightPersonName'];
                                $std_class = $_POST[$i . '-class'];
                                $std_section = $_POST[$i . '-section'];
                                $type = $_POST[$i . '-type'];
                                $reason = $_POST[$i . '-reason'];
                                $phone1_std = $_POST[$i . '-phone1'];
                                $comment = $_POST[$i . '-comment'];

                                $attendanceCode = $attendance->getAttendanceCodeByType($type);
                                $direction = $attendanceCode['direction'];

                                //Check for last record on same day
                                try {
                                    if (!empty($session)) {
                                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'session_no' => $session, 'date' => $currentDate . '%');
                                        $sql = 'SELECT * FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND session_no=:session_no AND date LIKE :date ORDER BY pupilsightAttendanceLogPersonID DESC';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } else {
                                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'date' => $currentDate . '%');
                                        $sql = 'SELECT * FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND date LIKE :date ORDER BY pupilsightAttendanceLogPersonID DESC';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    }
                                } catch (PDOException $e) {
                                    $URL .= '&return=error2';
                                    //header("Location: {$URL}");
                                    // echo "someError";
                                    $response_status['status'] = "Error";
                                    $response_status['msg'] = "Your request failed due to a database error.";
                                    echo json_encode($response_status);
                                    exit();
                                }

                                //Check context and type, updating only if not a match
                                $existing = false;
                                $pupilsightAttendanceLogPersonID = '';
                                if ($result->rowCount() > 0) {
                                    $row = $result->fetch();
                                    if (!empty($session)) {
                                        if ($row['context'] == 'Roll Group' && $row['type'] == $type && $row['direction'] == $direction && $row['session_no'] == $session) {
                                            $existing = true;
                                            $pupilsightAttendanceLogPersonID = $row['pupilsightAttendanceLogPersonID'];
                                        }
                                    } else {
                                        if ($row['context'] == 'Roll Group' && $row['type'] == $type && $row['direction'] == $direction) {
                                            $existing = true;
                                            $pupilsightAttendanceLogPersonID = $row['pupilsightAttendanceLogPersonID'];
                                        }
                                    }
                                }

                                if (!$existing) {
                                    //If no records then create one
                                    try {
                                        $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type, 'pupilsightAttendanceLogID' => $pupilsightAttendanceLogID, 'session_no' => $session, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                                        $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Roll Group\', pupilsightAttendanceLogID=:pupilsightAttendanceLogID, session_no=:session_no, reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken';
                                        $resultUpdate = $connection2->prepare($sqlUpdate);
                                        $resultUpdate->execute($dataUpdate);
                                        if ($i == 0) {
                                            if ($smsenable == 1 && $type == 'Absent') {
                                                if (in_array($pupilsightPersonID, $sms_usrs)) {
                                                    //sms templ value
                                                    if (!empty($entities)) {
                                                        foreach ($entities as $val) {
                                                            $val = trim($val);
                                                            if ("Student_name" == $val) {
                                                                $val = trim($val);
                                                                $entities_d["@" . $val] = $studentName;
                                                            } else 
                                                    if ("Attendance_date" == $val) {
                                                                $entities_d["@" . $val] = $currentDate;
                                                            } else if ("Session_name" == $val) {
                                                                $entities_d["@" . $val] = $session;
                                                            } else if ("Absent_reason" == $val) {
                                                                $entities_d["@" . $val] = $reason;
                                                            } else if ("Student_info" == "") {
                                                                $entities_d["@" . $val] = $pupilsightPersonID;
                                                            } else if ("Student_id" == $val) {
                                                                $entities_d["@" . $val] = $pupilsightPersonID;
                                                            } else if ("Class" == $val) {
                                                                $entities_d["@" . $val] = $std_class;
                                                            } else if ("Section_name" == $val) {
                                                                $entities_d["@" . $val] = $std_section;
                                                            } else {
                                                                $entities_d["@" . $val] = "";
                                                            }
                                                        }
                                                    }
                                                    $sms_sent_tmp = str_replace(array_keys($entities_d), $entities_d, $sms_tmp);
                                                    $msg = $sms_sent_tmp;
                                                    // ends here values
                                                    //sends sms 
                                                    if (!empty($sms_recipients) && !empty($msg)) {
                                                        foreach ($sms_recipients as $susr) {
                                                            if ("Student_mobile" == $susr) {
                                                                $sms_s = send_sms($container, $phone1_std, $msg);
                                                            } else if ("Father" == $susr || "Mother" == $susr || "Other" == $susr) {
                                                                $sqle = "SELECT a.*, b.officialName, b.email, b.phone1  FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE pupilsightPersonID2 = '" . $pupilsightPersonID . "' AND relationship='" . $susr . "'";
                                                                $resulte = $connection2->query($sqle);
                                                                $rowdata = $resulte->fetch();
                                                                $number = $rowdata['phone1'];
                                                                //$number = "8777281040";
                                                                $sms_s = send_sms($container, $number, $msg);
                                                            }
                                                        }
                                                    }
                                                    // ends sms
                                                }
                                            }
                                        }
                                    } catch (PDOException $e) {
                                        $fail = true;
                                    }
                                } else {
                                    try {
                                        $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type, 'pupilsightAttendanceLogID' => $pupilsightAttendanceLogID, 'session_no' => $session, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'), 'pupilsightAttendanceLogPersonID' => $row['pupilsightAttendanceLogPersonID']);
                                        $sqlUpdate = 'UPDATE pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Roll Group\',  pupilsightAttendanceLogID=:pupilsightAttendanceLogID, session_no=:session_no, reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken WHERE pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID';
                                        $resultUpdate = $connection2->prepare($sqlUpdate);
                                        $resultUpdate->execute($dataUpdate);
                                        if ($i == 0) {
                                            if ($smsenable == 1 && $type == 'Absent') {
                                                if (in_array($pupilsightPersonID, $sms_usrs)) {
                                                    //sms templ value
                                                    if (!empty($entities)) {
                                                        foreach ($entities as $val) {
                                                            $val = trim($val);
                                                            if ("Student_name" == $val) {
                                                                $val = trim($val);
                                                                $entities_d["@" . $val] = $studentName;
                                                            } else 
                                                    if ("Attendance_date" == $val) {
                                                                $entities_d["@" . $val] = $currentDate;
                                                            } else if ("Session_name" == $val) {
                                                                $entities_d["@" . $val] = $session;
                                                            } else if ("Absent_reason" == $val) {
                                                                $entities_d["@" . $val] = $reason;
                                                            } else if ("Student_info" == "") {
                                                                $entities_d["@" . $val] = $pupilsightPersonID;
                                                            } else if ("Student_id" == $val) {
                                                                $entities_d["@" . $val] = $pupilsightPersonID;
                                                            } else if ("Class" == $val) {
                                                                $entities_d["@" . $val] = $std_class;
                                                            } else if ("Section_name" == $val) {
                                                                $entities_d["@" . $val] = $std_section;
                                                            } else {
                                                                $entities_d["@" . $val] = "";
                                                            }
                                                        }
                                                    }
                                                    $sms_sent_tmp = str_replace(array_keys($entities_d), $entities_d, $sms_tmp);
                                                    $msg = $sms_sent_tmp;
                                                    // ends here values
                                                    //sends sms 
                                                    if (!empty($sms_recipients) && !empty($msg)) {
                                                        foreach ($sms_recipients as $susr) {
                                                            if ("Student_mobile" == $susr) {
                                                                $sms_s = send_sms($container, $phone1_std, $msg);
                                                            } else if ("Father" == $susr || "Mother" == $susr || "Other" == $susr) {
                                                                $sqle = "SELECT a.*, b.officialName, b.email, b.phone1  FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE pupilsightPersonID2 = '" . $pupilsightPersonID . "' AND relationship='" . $susr . "'";
                                                                $resulte = $connection2->query($sqle);
                                                                $rowdata = $resulte->fetch();
                                                                $number = $rowdata['phone1'];
                                                                //$number = "8777281040";
                                                                $sms_s = send_sms($container, $number, $msg);
                                                            }
                                                        }
                                                    }
                                                    // ends sms
                                                }
                                            }
                                        }
                                    } catch (PDOException $e) {
                                        $fail = true;
                                    }
                                }
                            }
                            //ok
                            /*if ($partialFail == true) {
                            //$URL .= '&return=warning1';
                            //header("Location: {$URL}");
                            $response_status['status']="Error";
                            $response_status['msg']="Your request failed due to a database error.";
                            echo json_encode($response_status);
                            exit();
                            echo "Unknown Return";
                        } else {
                            echo  "Attendance added successfully";
                           // $URL .= '&return=success0&time='.date('H-i-s');
                            //header("Location: {$URL}");
                        }*/
                        }
                        $i++;
                    }
                } // ok last
                $response_status['status'] = "success";
                $response_status['msg'] = "Attendance added successfully.";
                echo json_encode($response_status);
                //echo "success";
            } //second ends
        } //ok final ends
    }
}


function send_sms($container, $number, $msg)
{
    if (!empty($msg) && !empty($number)) {
        $sms = $container->get(SMS::class);
        $res = $sms->sendSMSPro($number, $msg);
        /*
        $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
        $urls .= "&send_to=" . $number;
        $urls .= "&msg=" . rawurlencode($msg);
        $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
        $resms = file_get_contents($urls);*/
    }
}
