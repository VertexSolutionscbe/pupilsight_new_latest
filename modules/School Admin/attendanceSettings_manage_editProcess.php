<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightAttendanceCodeID = (isset($_GET['pupilsightAttendanceCodeID']))? $_GET['pupilsightAttendanceCodeID'] : NULL;
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/attendanceSettings.php";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/attendanceSettings_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($pupilsightAttendanceCodeID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID);
            $sql = 'SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
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
            //Validate Inputs
            $name = (isset($_POST['name']))? $_POST['name'] : NULL;
            $nameShort = (isset($_POST['nameShort']))? $_POST['nameShort'] : NULL;
            $direction = (isset($_POST['direction']))? $_POST['direction'] : NULL;
            $scope = (isset($_POST['scope']))? $_POST['scope'] : NULL;
            $sequenceNumber = (isset($_POST['sequenceNumber']))? $_POST['sequenceNumber'] : NULL;
            $active = (isset($_POST['active']))? $_POST['active'] : NULL;
            $reportable = (isset($_POST['reportable']))? $_POST['reportable'] : NULL;
            $future = (isset($_POST['future']))? $_POST['future'] : NULL;

            $pupilsightRoleIDArray = (isset($_POST['pupilsightRoleIDAll']))? $_POST['pupilsightRoleIDAll'] : NULL;
            $pupilsightRoleIDAll = (is_array($pupilsightRoleIDArray))? implode(',', $pupilsightRoleIDArray) : $pupilsightRoleIDArray;

            if ($pupilsightRoleIDAll == '' or $name == '' or $nameShort == '' or $direction == '' or $scope == '' or $sequenceNumber == '' or $active == '' or $reportable == '' or $future == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness in current school year
                try {
                    $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID);
                    $sql = 'SELECT name, nameShort FROM pupilsightAttendanceCode WHERE (name=:name OR nameShort=:nameShort) AND NOT pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array( 'pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID, 'name' => $name, 'nameShort' => $nameShort, 'direction' => $direction, 'scope' => $scope, 'sequenceNumber' => $sequenceNumber, 'active' => $active, 'reportable' => $reportable, 'future' => $future, 'pupilsightRoleIDAll' => $pupilsightRoleIDAll );

                        $sql = 'UPDATE pupilsightAttendanceCode SET name=:name, nameShort=:nameShort, direction=:direction, scope=:scope, sequenceNumber=:sequenceNumber, active=:active, reportable=:reportable, future=:future, pupilsightRoleIDAll=:pupilsightRoleIDAll WHERE pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= "&return=success0";
                    header("Location: {$URL}");
                }
            }
        }
    }
}
