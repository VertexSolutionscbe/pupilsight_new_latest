<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/attendance_studentSelfRegister.php";

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_studentSelfRegister.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $studentSelfRegistrationIPAddresses = getSettingByScope($connection2, 'Attendance', 'studentSelfRegistrationIPAddresses');
    $realIP = getIPAddress();
    if ($studentSelfRegistrationIPAddresses == '' || is_null($studentSelfRegistrationIPAddresses)) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Check if school day
        $currentDate = date('Y-m-d');
        if (isSchoolOpen($guid, $currentDate, $connection2, true) == false) {
            $URL .= '&return=error0';
            header("Location: {$URL}");
        }
        else {
            //Check for existence of records today
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate);
                $sql = "SELECT type FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND date=:date ORDER BY timestampTaken DESC";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() > 0) { //Records! Return error
                $URL .= '&return=error1';
                header("Location: {$URL}");
            }
            else { //If no records, set status to Present
                $inRange = false ;
                foreach (explode(',', $studentSelfRegistrationIPAddresses) as $ipAddress) {
                    if (trim($ipAddress) == $realIP)
                        $inRange = true ;
                }

                $status = (isset($_POST['status']))? $_POST['status'] : null;

                if (!$inRange && $status == 'Absent') {
                    try {
                        $dataUpdate = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                        $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=\'Absent\'), pupilsightPersonID=:pupilsightPersonID, direction=\'Out\', type=\'Absent\', context=\'Self Registration\', reason=\'\', comment=\'\', pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightCourseClassID=NULL, date=:date, timestampTaken=:timestampTaken';
                        $resultUpdate = $connection2->prepare($sqlUpdate);
                        $resultUpdate->execute($dataUpdate);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                }
                else if ($inRange && $status == 'Present') {
                    try {
                        $dataUpdate = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                        $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=\'Present\'), pupilsightPersonID=:pupilsightPersonID, direction=\'In\', type=\'Present\', context=\'Self Registration\', reason=\'\', comment=\'\', pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightCourseClassID=NULL, date=:date, timestampTaken=:timestampTaken';
                        $resultUpdate = $connection2->prepare($sqlUpdate);
                        $resultUpdate->execute($dataUpdate);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                }
                else {
                    $URL .= '&return=error0';
                    header("Location: {$URL}");
                    exit();
                }

                $selfRegistrationRedirect = getSettingByScope($connection2, 'Attendance', 'selfRegistrationRedirect');
                if ($selfRegistrationRedirect == 'Y') {
                    $URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Messenger/messageWall_view.php&return=message0&status=$status";
                }
                else {
                    $URL .= '&return=success0';
                }
                header("Location: {$URL}");
                exit();
            }
        }
    }
}
