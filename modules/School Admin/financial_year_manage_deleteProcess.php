<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolFinanceYearID = $_GET['pupilsightSchoolFinanceYearID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/financial_year_manage_delete.php&pupilsightSchoolFinanceYearID='.$pupilsightSchoolFinanceYearID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/financial_year_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/financial_year_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightSchoolFinanceYearID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID);
            $sql = "SELECT * FROM pupilsightSchoolFinanceYear WHERE pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID AND NOT status='Current'";
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
                $data = array('pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID);
                $sql = 'DELETE FROM pupilsightSchoolFinanceYear WHERE pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID';
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
