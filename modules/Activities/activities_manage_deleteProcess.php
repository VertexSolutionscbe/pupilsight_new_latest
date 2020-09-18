<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightActivityID = $_POST['pupilsightActivityID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_manage_delete.php&pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID'];
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/activities_manage.php&search='.$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID'];

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($pupilsightActivityID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT * FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
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
                $data = array('pupilsightActivityID' => $pupilsightActivityID);
                $sql = 'DELETE FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            try {
                $data = array('pupilsightActivityID' => $pupilsightActivityID);
                $sql = 'DELETE FROM pupilsightActivitySlot WHERE pupilsightActivityID=:pupilsightActivityID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
