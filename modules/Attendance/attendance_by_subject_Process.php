<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Module\Attendance\AttendanceView;

//Pupilsight system-wide includes
require __DIR__ . '/../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

/*
echo "<pre>";
print_r($_POST);
exit; */
$pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
$currentDate = $_POST['currentDate'];
$today = date('Y-m-d');
$pupilsightYearGroupID =$_POST['pupilsightYearGroupID'];
$pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
$pupilsightProgramID =  $_POST['pupilsightProgramID'];
$time_slot = $_POST['time_slot'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/attendance_take_bysubject.php&pupilsightRollGroupID=$pupilsightRollGroupID&pupilsightDepartmentID=$pupilsightDepartmentID& time_slot=$time_slot & currentDate=".dateConvertBack($guid, $currentDate);

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_bysubject.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, '/modules/Attendance/attendance_take_bysubject.php', $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightRollGroupID == '' and $currentDate == '' and $pupilsightDepartmentID  == '' and $time_slot=='' ) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                if ($highestAction == 'Attendance By Subject') {

                    // $sqldp = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment WHERE pupilsightDepartmentID="'.$pupilsightDepartmentID.'" ';
                    $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
                    $sql = 'SELECT * FROM pupilsightDepartment WHERE pupilsightDepartmentID=:pupilsightDepartmentID';
                }
                else {
                    $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightPersonIDTutor1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = "SELECT * FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND (pupilsightPersonIDTutor=:pupilsightPersonIDTutor1 OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3) AND pupilsightRollGroup.attendance = 'Y' AND pupilsightRollGroupID=:pupilsightRollGroupID ORDER BY LENGTH(name), name";
                }
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
                    // check special day
                    $SpecialDays = array('date' => $currentDate);
                    $sqlSpecialDays = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date";
                    $resultSpecialDays = $connection2->prepare($sqlSpecialDays);
                    $resultSpecialDays->execute($SpecialDays);
                    $specialDaysCounts = $resultSpecialDays->fetch();
                    //check special day ends 
                    if (isSchoolOpen($guid, $currentDate, $connection2) == false AND empty($specialDaysCounts)) {
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } else {
                        //Write to database
                        require_once __DIR__ . '/src/AttendanceView.php';
                        $attendance = new AttendanceView($pupilsight, $pdo);


                        try {
          
                                
                            $dataLog = array('pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $pupilsightDepartmentID ,'pupilsightTTColumnRowID' => $time_slot,'date' => $currentDate.'%');
                            $sqlLog = 'SELECT * FROM pupilsightAttendanceLogDepartment, pupilsightPerson WHERE pupilsightAttendanceLogDepartment.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND   date LIKE :date ORDER BY timestampTaken';


                            $resultlog = $connection2->prepare($sqlLog);
                            $resultlog->execute($dataLog);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }


                        if ($resultlog->rowCount()>0) {

                            $URL .= '&return=error11';
                          //  echo  $URL;
                          header("Location: {$URL}");
                        }
                        else
                        {

                        try {
                                //SELECT * FROM `pupilsightAttendanceLogDepartment` WHERE 1,`pupilsightAttendanceLogDepartmentID`,`pupilsightRollGroupID`,`pupilsightDepartmentID`,`pupilsightTTColumnRowID`,`pupilsightPersonIDTaker`,`date`,`timestampTaken`

                            $data = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $pupilsightDepartmentID,'pupilsightTTColumnRowID' => $time_slot, 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                            $sql = 'INSERT INTO pupilsightAttendanceLogDepartment SET pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightRollGroupID=:pupilsightRollGroupID,pupilsightDepartmentID=:pupilsightDepartmentID,pupilsightTTColumnRowID=:pupilsightTTColumnRowID, date=:date, timestampTaken=:timestampTaken';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                    }
                        $count = $_POST['count'];
                        $partialFail = false;

                        for ($i = 0; $i < $count; ++$i) {
                            $pupilsightPersonID = $_POST[$i.'-pupilsightPersonID'];
                            $type = $_POST[$i.'-type'];
                            $reason = $_POST[$i.'-reason'];
                            $comment = $_POST[$i.'-comment'];

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
                                if ($row['context'] == 'Subject' && $row['type'] == $type && $row['direction'] == $direction ) {
                                    $existing = true ;
                                    $pupilsightAttendanceLogPersonID = $row['pupilsightAttendanceLogPersonID'];
                                }
                            }

                            if (!$existing) {
                                //If no records then create one
                                try {
                                    $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                                    $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Subject\', reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken';
                                    $resultUpdate = $connection2->prepare($sqlUpdate);
                                    $resultUpdate->execute($dataUpdate);
                                } catch (PDOException $e) {
                                    $fail = true;
                                }
                            } else {
                                try {
                                    $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'), 'pupilsightAttendanceLogPersonID' => $row['pupilsightAttendanceLogPersonID']);
                                    $sqlUpdate = 'UPDATE pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Subject\', reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken WHERE pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID';
                                    $resultUpdate = $connection2->prepare($sqlUpdate);
                                    $resultUpdate->execute($dataUpdate);
                                } catch (PDOException $e) {
                                    $fail = true;
                                }
                            }
                        }

                        if ($partialFail == true) {
                            $URL .= '&return=warning1';
                            header("Location: {$URL}");
                        } else {
                            $URL .= '&return=success0&time='.date('H-i-s');
                            //echo $URL;
                            header("Location: {$URL}");
                        }
                    }
                }
            }
        }
    }
}
