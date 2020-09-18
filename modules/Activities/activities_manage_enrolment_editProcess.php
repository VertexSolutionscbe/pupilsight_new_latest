<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightActivityID = $_GET['pupilsightActivityID'];
$pupilsightPersonID = $_GET['pupilsightPersonID'];

if ($pupilsightActivityID == '' or $pupilsightPersonID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_manage_enrolment_edit.php&pupilsightPersonID=$pupilsightPersonID&pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID'];

    if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_enrolment_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        $status = $_POST['status'];
        if ($status == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'SELECT pupilsightActivity.*, pupilsightActivityStudent.*, surname, preferredName FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID) JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightActivityStudent.pupilsightActivityID=:pupilsightActivityID AND pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID';
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
                $row = $result->fetch();
                $statusOld = $row['status'];

                //Write to database
                try {
                    $data = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $pupilsightPersonID, 'status' => $status);
                    $sql = 'UPDATE pupilsightActivityStudent SET status=:status WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Set log
                if ($statusOld != $status) {
                    $pupilsightModuleID = getModuleIDFromName($connection2, 'Activities') ;
                    setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], $pupilsightModuleID, $_SESSION[$guid]['pupilsightPersonID'], 'Activities - Student Status Changed', array('pupilsightPersonIDStudent' => $pupilsightPersonID, 'statusOld' => $statusOld, 'statusNew' => $status));
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
