<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$pupilsightActivityID = $_GET['pupilsightActivityID'];
$pupilsightPersonID = $_GET['pupilsightPersonID'];

if ($pupilsightActivityID == '' or $pupilsightPersonID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_manage_enrolment_delete.php&pupilsightPersonID=$pupilsightPersonID&pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID'];
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_manage_enrolment.php&pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID'];

    if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_enrolment_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
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
            //Write to database
            try {
                $data = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'DELETE FROM pupilsightActivityStudent WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPersonID=:pupilsightPersonID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Set log
            $pupilsightModuleID = getModuleIDFromName($connection2, 'Activities') ;
            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], $pupilsightModuleID, $_SESSION[$guid]['pupilsightPersonID'], 'Activities - Student Deleted', array('pupilsightPersonIDStudent' => $pupilsightPersonID));

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
