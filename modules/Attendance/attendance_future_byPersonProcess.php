<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Module\Attendance\AttendanceView;

//Pupilsight system-wide includes
include __DIR__ . '/../../pupilsight.php';

//Module includes
include __DIR__ . '/moduleFunctions.php';

$pupilsightPersonID = $_POST['pupilsightPersonID'];
$scope = $_POST['scope'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/attendance_future_byPerson.php&pupilsightPersonID=$pupilsightPersonID&scope=$scope";

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_future_byPerson.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if person specified
    if ($pupilsightPersonID == '' & $scope == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $pupilsightPersonID = explode(',', $pupilsightPersonID);

        $personCheck = true ;
        foreach ($pupilsightPersonID as $pupilsightPersonIDCurrent) {
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonIDCurrent);
                $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $personCheck = false;
            }
            if ($result->rowCount() != 1) {
                $personCheck = false;
            }
        }

        if (!$personCheck) {
            $URL .= '&return=error2';
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

            $absenceType = (isset($_POST['absenceType']))? $_POST['absenceType'] : 'full';

            $dateStart = '';
            if ($_POST['dateStart'] != '') {
                $dateStart = dateConvert($guid, $_POST['dateStart']);
            }
            $dateEnd = $dateStart;
            if ($_POST['dateEnd'] != '') {
                $dateEnd = dateConvert($guid, $_POST['dateEnd']);
            }
            $today = date('Y-m-d');

            //Check to see if date is in the future and is a school day.
            if ($dateStart == '' or ($dateEnd != '' and $dateEnd < $dateStart) or $dateStart < $today ) {
                $URL .= '&return=error8';
                header("Location: {$URL}");
            } else {
                //Scroll through days
                $partialFail = false;
                $partialFailSchoolClosed = false;

                $dateStartStamp = dateConvertToTimestamp($dateStart);
                $dateEndStamp = dateConvertToTimestamp($dateEnd);
                for ($i = $dateStartStamp; $i <= $dateEndStamp; $i = ($i + 86400)) {
                    $date = date('Y-m-d', $i);

                    if (isSchoolOpen($guid, $date, $connection2)) { //Only add if school is open on this day
                        foreach ($pupilsightPersonID as $pupilsightPersonIDCurrent) {
                            //Check for record on same day
                            try {
                                $data = array('pupilsightPersonID' => $pupilsightPersonIDCurrent, 'date' => "$date%");
                                $sql = 'SELECT * FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassID IS NULL AND date LIKE :date ORDER BY date DESC';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }

                            if ($result->rowCount() > 0 AND $absenceType == 'full') {
                                $partialFail = true;
                            } else {
                                // Handle full-day absenses normally
                                if ($absenceType == 'full') {
                                    try {
                                        $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonIDCurrent, 'direction' => $direction, 'type' => $type, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $date, 'timestampTaken' => date('Y-m-d H:i:s'));
                                        $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Future\', reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken';
                                        $resultUpdate = $connection2->prepare($sqlUpdate);
                                        $resultUpdate->execute($dataUpdate);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }

                                // Handle partial absenses per-class
                                } else if ($absenceType == 'partial') {

                                    // Return error if full-day absense already recorded
                                    if ($result->rowCount() > 0) {
                                        $URL .= '&return=error7';
                                        header("Location: {$URL}");
                                        exit();
                                    } else {
                                        $courses = (isset($_POST['courses']))? $_POST['courses'] : null;
                                        if (!empty($courses) && is_array($courses)) {
                                            foreach ($courses as $course) {
                                                try {
                                                    $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonIDCurrent, 'direction' => $direction, 'type' => $type, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $date, 'pupilsightCourseClassID' => $course, 'timestampTaken' => date('Y-m-d H:i:s'));
                                                    $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Class\', reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, pupilsightCourseClassID=:pupilsightCourseClassID, timestampTaken=:timestampTaken';
                                                    $resultUpdate = $connection2->prepare($sqlUpdate);
                                                    $resultUpdate->execute($dataUpdate);
                                                } catch (PDOException $e) {
                                                    $partialFail = true;
                                                }
                                            }
                                            $URL .= '&absenceType=partial&date=' . $_POST['dateStart']; //Redirect to exact state of submit form
                                        } else {
                                            // Return error if no courses selected for partial absence
                                            $URL .= '&return=error1';
                                            header("Location: {$URL}");
                                            exit();
                                        }
                                    }
                                } else {
                                    $URL .= '&return=error1';
                                    header("Location: {$URL}");
                                    exit();
                                }
                            }
                        }

                    } else {
                        $partialFailSchoolClosed = true;
                    }
                }
            }

            if ($partialFailSchoolClosed == true) {
                $URL .= '&return=warning2';
                header("Location: {$URL}");
            }
            else if ($partialFail == true) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
