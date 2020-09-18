<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Module\Attendance\AttendanceView;

//Pupilsight system-wide includes
require __DIR__ . '/../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$currentDate = $_POST['currentDate'];
//  echo '<pre>';
//         print_r($_POST);
//         echo '</pre>';
        
$session_no = $_POST['session1'];
$today = date('Y-m-d');
$copy_this_too='';
if(isset($_POST['capy_to'])){
  $copy_this_too=$_POST['capy_to'];
}
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/attendance_take_byPerson.php&pupilsightPersonID=$pupilsightPersonID&currentDate=".dateConvertBack($guid, $currentDate);

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightPersonID == '' and $currentDate == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Check that date is not in the future
            if ($currentDate > $today) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check that date is a school day
                if (isSchoolOpen($guid, $currentDate, $connection2) == false) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    require_once __DIR__ . '/src/AttendanceView.php';
                    $attendance = new AttendanceView($pupilsight, $pdo);

                    $fail = false;
                    $type = $_POST['type'];
                    $reason = $_POST['reason'];
                    $comment = $_POST['comment'];

                    $attendanceCode = $attendance->getAttendanceCodeByType($type);
                    $direction = $attendanceCode['direction'];

                    //Check for last record on same day
                    try {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'date' => $currentDate.'%');
                        $sql = 'SELECT * FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND date LIKE :date ORDER BY pupilsightAttendanceLogPersonID DESC';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Check context and type, updating only if not a match
                    $existing = false ;
                    $pupilsightAttendanceLogPersonID = '';
                    if ($result->rowCount()>0) {
                        $row=$result->fetch() ;
                        if ($row['context'] == 'Person' && $row['type'] == $type) {
                            $existing = true ;
                            $pupilsightAttendanceLogPersonID = $row['pupilsightAttendanceLogPersonID'];
                        }
                    }

                    if (!$existing) {
                        //If no records then create one
                        try {

                            $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'session_no'=>$session_no,'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                            $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID,session_no=:session_no, direction=:direction, type=:type, context=\'Person\', reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken';
                            $resultUpdate = $connection2->prepare($sqlUpdate);
                            $resultUpdate->execute($dataUpdate);
                            if(!empty($copy_this_too)){
                                foreach ($copy_this_too as $val) {
                                    $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'session_no'=>$val,'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                            $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID,session_no=:session_no, direction=:direction, type=:type, context=\'Person\', reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken';
                            $resultUpdate = $connection2->prepare($sqlUpdate);
                            $resultUpdate->execute($dataUpdate);
                                }
                            }
                        } catch (PDOException $e) {
                            $fail = true;
                        }
                    } else {
                        //If direction same then update
                        if ($row['direction'] == $direction && $row['pupilsightCourseClassID'] == 0) {
                            try {
                                $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type,'session_no'=>$session_no, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'), 'pupilsightAttendanceLogPersonID' => $row['pupilsightAttendanceLogPersonID']);
                                $sqlUpdate = 'UPDATE pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Person\', reason=:reason, session_no=:session_no,comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken WHERE pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID';
                                $resultUpdate = $connection2->prepare($sqlUpdate);
                                $resultUpdate->execute($dataUpdate);
                            } catch (PDOException $e) {
                                $fail = true;
                            }
                        }
                        //Else create a new record
                        else {
                            try {
                                    $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type,'session_no'=>$session_no, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                                $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Person\', reason=:reason, session_no=:session_no,comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken';
                                $resultUpdate = $connection2->prepare($sqlUpdate);
                                $resultUpdate->execute($dataUpdate);
                                if(!empty($copy_this_too)){
                                  foreach ($copy_this_too as  $val) {
                                        $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type,'session_no'=>$val, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                                $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Person\', reason=:reason, session_no=:session_no,comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken';
                                $resultUpdate = $connection2->prepare($sqlUpdate);
                                $resultUpdate->execute($dataUpdate);
                                  }
                                }
                            } catch (PDOException $e) {
                                $fail = true;
                            }
                        }
                    }
                }
            }

            if ($fail == true) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
