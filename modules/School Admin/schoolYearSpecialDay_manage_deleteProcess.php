<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearSpecialDayID = $_GET['pupilsightSchoolYearSpecialDayID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/schoolYearSpecialDay_manage_delete.php&pupilsightSchoolYearSpecialDayID='.$pupilsightSchoolYearSpecialDayID.'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/schoolYearSpecialDay_manage.php&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearSpecialDay_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightSchoolYearSpecialDayID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightSchoolYearSpecialDayID' => $pupilsightSchoolYearSpecialDayID);
            $sql = 'SELECT * FROM pupilsightSchoolYearSpecialDay WHERE pupilsightSchoolYearSpecialDayID=:pupilsightSchoolYearSpecialDayID';
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
            try {
                $data = array('pupilsightSchoolYearSpecialDayID' => $pupilsightSchoolYearSpecialDayID);
                $sql = 'DELETE FROM pupilsightSchoolYearSpecialDay WHERE pupilsightSchoolYearSpecialDayID=:pupilsightSchoolYearSpecialDayID';
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
