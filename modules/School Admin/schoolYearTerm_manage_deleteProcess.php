<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearTermID = $_GET['pupilsightSchoolYearTermID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/schoolYearTerm_manage_delete.php&pupilsightSchoolYearTermID='.$pupilsightSchoolYearTermID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/schoolYearTerm_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearTerm_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightSchoolYearTermID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID);
            $sql = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID';
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
                $data = array('pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID);
                $sql = 'DELETE FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID';
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
