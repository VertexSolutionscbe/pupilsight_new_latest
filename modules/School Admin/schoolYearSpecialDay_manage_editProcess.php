<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearSpecialDayID = $_GET['pupilsightSchoolYearSpecialDayID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/schoolYearSpecialDay_manage_edit.php&pupilsightSchoolYearSpecialDayID='.$pupilsightSchoolYearSpecialDayID.'&pupilsightSchoolYearID='.$_POST['pupilsightSchoolYearID'];

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearSpecialDay_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if special day specified
    if ($pupilsightSchoolYearSpecialDayID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightSchoolYearSpecialDayID' => $pupilsightSchoolYearSpecialDayID);
            $sql = 'SELECT pupilsightSchoolYearTermID FROM pupilsightSchoolYearSpecialDay WHERE pupilsightSchoolYearSpecialDayID=:pupilsightSchoolYearSpecialDayID';
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
            $type = $_POST['type'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $schoolOpen = null;
            if (!empty($_POST['schoolOpenH']) && is_numeric($_POST['schoolOpenH']) && is_numeric($_POST['schoolOpenM'])) {
                $schoolOpen = $_POST['schoolOpenH'].':'.$_POST['schoolOpenM'].':00';
            }
            $schoolStart = null;
            if (!empty($_POST['schoolStartH']) && is_numeric($_POST['schoolStartH']) && is_numeric($_POST['schoolStartM'])) {
                $schoolStart = $_POST['schoolStartH'].':'.$_POST['schoolStartM'].':00';
            }
            $schoolEnd = null;
            if (!empty($_POST['schoolEndH']) && is_numeric($_POST['schoolEndH']) && is_numeric($_POST['schoolEndM'])) {
                $schoolEnd = $_POST['schoolEndH'].':'.$_POST['schoolEndM'].':00';
            }
            $schoolClose = null;
            if (!empty($_POST['schoolCloseH']) && is_numeric($_POST['schoolCloseH']) && is_numeric($_POST['schoolCloseM'])) {
                $schoolClose = $_POST['schoolCloseH'].':'.$_POST['schoolCloseM'].':00';
            }

            // Update the term ID, or fallback to the previous one
            $pupilsightSchoolYearTermID = (isset($_POST['pupilsightSchoolYearTermID']))? $_POST['pupilsightSchoolYearTermID'] : $result->fetchColumn(0);

            if ($type == '' or $name == '' or $pupilsightSchoolYearID == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('type' => $type, 'name' => $name, 'description' => $description, 'schoolOpen' => $schoolOpen, 'schoolStart' => $schoolStart, 'schoolEnd' => $schoolEnd, 'schoolClose' => $schoolClose, 'pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID, 'pupilsightSchoolYearSpecialDayID' => $pupilsightSchoolYearSpecialDayID);
                    $sql = 'UPDATE pupilsightSchoolYearSpecialDay SET type=:type, name=:name, description=:description,schoolOpen=:schoolOpen, schoolStart=:schoolStart, schoolEnd=:schoolEnd, schoolClose=:schoolClose, pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID WHERE pupilsightSchoolYearSpecialDayID=:pupilsightSchoolYearSpecialDayID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
