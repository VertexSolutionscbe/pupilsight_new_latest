<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearTermID = $_GET['pupilsightSchoolYearTermID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/schoolYearTerm_manage_edit.php&pupilsightSchoolYearTermID='.$pupilsightSchoolYearTermID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearTerm_manage_edit.php') == false) {
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
            //Validate Inputs
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $sequenceNumber = $_POST['sequenceNumber'];
            $name = $_POST['name'];
            $nameShort = $_POST['nameShort'];
            $firstDay = dateConvert($guid, $_POST['firstDay']);
            $lastDay = dateConvert($guid, $_POST['lastDay']);

            if ($pupilsightSchoolYearID == '' or $name == '' or $nameShort == '' or $sequenceNumber == '' or is_numeric($sequenceNumber) == false or $firstDay == '' or $lastDay == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('sequenceNumber' => $sequenceNumber, 'pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID);
                    $sql = 'SELECT * FROM pupilsightSchoolYearTerm WHERE sequenceNumber=:sequenceNumber AND NOT pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID';
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
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber, 'firstDay' => $firstDay, 'lastDay' => $lastDay, 'pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID);
                        $sql = 'UPDATE pupilsightSchoolYearTerm SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, sequenceNumber=:sequenceNumber, firstDay=:firstDay, lastDay=:lastDay WHERE pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
