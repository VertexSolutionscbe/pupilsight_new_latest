<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/firstAidRecord_add.php&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'];

if (isActionAccessible($guid, $connection2, '/modules/Students/firstAidRecord_add.php') == false) {
    $URL .= '&return=error0&step=1';
    header("Location: {$URL}");
} else {
    $pupilsightFirstAidID = null;
    if (isset($_POST['pupilsightFirstAidID'])) {
        $pupilsightFirstAidID = $_POST['pupilsightFirstAidID'];
    }

    //Proceed!
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
    $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
    $pupilsightPersonID = $_POST['pupilsightPersonID'];
    $pupilsightPersonIDFirstAider = $_SESSION[$guid]['pupilsightPersonID'];
    $date = $_POST['date'];
    $timeIn = $_POST['timeIn'];
    $description = $_POST['description'];
    $actionTaken = $_POST['actionTaken'];
    $followUp = $_POST['followUp'];

    if ($pupilsightPersonID == '' or $pupilsightPersonIDFirstAider == '' or $date == '' or $timeIn == '') {
        $URL .= '&return=error1&step=1';
        header("Location: {$URL}");
    } else {
        //Write to database
        try {
            $data = array('pupilsightPersonIDPatient' => $pupilsightPersonID, 'pupilsightPersonIDFirstAider' => $pupilsightPersonIDFirstAider, 'date' => dateConvert($guid, $date), 'timeIn' => $timeIn, 'description' => $description, 'actionTaken' => $actionTaken, 'followUp' => $followUp, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
            $sql = 'INSERT INTO pupilsightFirstAid SET pupilsightPersonIDPatient=:pupilsightPersonIDPatient, pupilsightPersonIDFirstAider=:pupilsightPersonIDFirstAider, date=:date, timeIn=:timeIn, description=:description, actionTaken=:actionTaken, followUp=:followUp, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=erorr2&step=1';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 12, '0', STR_PAD_LEFT);

        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}
