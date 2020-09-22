<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/schoolYearTerm_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearTerm_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $sequenceNumber = $_POST['sequenceNumber'];
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];
    $firstDay = dateConvert($guid, $_POST['firstDay']);
    $lastDay = dateConvert($guid, $_POST['lastDay']);

    if ($pupilsightSchoolYearID == '' or $name == '' or $nameShort == '' or $sequenceNumber == '' or is_numeric($sequenceNumber) == false or $firstDay == '' or $lastDay == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('sequenceNumber' => $sequenceNumber);
            $sql = 'SELECT * FROM pupilsightSchoolYearTerm WHERE (sequenceNumber=:sequenceNumber)';
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
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber, 'firstDay' => $firstDay, 'lastDay' => $lastDay);
                $sql = 'INSERT INTO pupilsightSchoolYearTerm SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, sequenceNumber=:sequenceNumber, firstDay=:firstDay, lastDay=:lastDay';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 5, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
