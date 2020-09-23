<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightActivityID = $_GET['pupilsightActivityID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_attendance.php&pupilsightActivityID=$pupilsightActivityID";

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_attendanceProcess.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!

    $pupilsightPersonID = $_POST['pupilsightPersonID'];

    $highestAction = ($guid, '/modules/Activities/activities_attendance.php', $connection2);

    if($highestAction == "Enter Activity Attendance_leader") {
        try {
            $dataCheck = array("pupilsightPersonID" => $pupilsightPersonID, "pupilsightActivityID" => $pupilsightActivityID);
            $sqlCheck = "SELECT role FROM pupilsightActivityStaff WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPersonID=:pupilsightPersonID";
            $resultCheck = $connection2->prepare($sqlCheck);
            $resultCheck->execute($dataCheck);

            if ($resultCheck->rowCount() > 0) {
                $row = $resultCheck->fetch();
                if ($row["role"] != "Organiser" && $row["role"] != "Assistant" && $row["role"] != "Coach") {
                    $URL .= '&return=error0';
                    header("Location: {$URL}");
                    exit();
                }
            } else {
                $URL .= '&return=error0';
                header("Location: {$URL}");
                exit();
            }
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
    }

    $sessions = (isset($_POST['sessions'])) ? $_POST['sessions'] : null;
    $attendance = (isset($_POST['attendance'])) ? $_POST['attendance'] : null;

    if ($pupilsightActivityID == '' || $pupilsightPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } elseif (empty($sessions)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $partialFail = false;

        // Iterate through the session columns
        foreach ($sessions as $i => $session) {
            $sessionTimestamp = $session;
            $sessionDate = date('Y-m-d', intval($sessionTimestamp));

            if (empty($sessionTimestamp) || empty($sessionDate)) {
                $URL .= '&return=error1';
                header("Location: {$URL}");

                return;
            }

            $sessionAttendance = (isset($attendance[$i])) ? serialize($attendance[$i]) : '';

            try {
                $data = array('pupilsightActivityID' => $pupilsightActivityID, 'date' => $sessionDate);
                $sql = 'SELECT pupilsightActivityAttendanceID FROM pupilsightActivityAttendance WHERE pupilsightActivityID=:pupilsightActivityID AND date=:date';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            // INSERT
            if ($result->rowCount() <= 0) {

                // Skip sessions we're not recording attendance for
                if (!isset($attendance[$i]) || empty($attendance[$i]) || !is_array($attendance[$i])) {
                    continue;
                }

                try {
                    $data = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonIDTaker' => $pupilsightPersonID, 'attendance' => $sessionAttendance, 'date' => $sessionDate);
                    $sql = 'INSERT INTO pupilsightActivityAttendance SET pupilsightActivityID=:pupilsightActivityID, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, attendance=:attendance, date=:date ';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
            }
            // UPDATE
            else {
                $pupilsightActivityAttendanceID = $result->fetchColumn(0);

                try {
                    $data = array('pupilsightActivityAttendanceID' => $pupilsightActivityAttendanceID, 'pupilsightPersonIDTaker' => $pupilsightPersonID, 'attendance' => $sessionAttendance, 'date' => $sessionDate);
                    $sql = 'UPDATE pupilsightActivityAttendance SET pupilsightPersonIDTaker=:pupilsightPersonIDTaker, attendance=:attendance, date=:date WHERE pupilsightActivityAttendanceID=:pupilsightActivityAttendanceID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
            }
        }

        if ($partialFail == true) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
