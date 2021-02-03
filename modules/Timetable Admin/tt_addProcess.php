<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// die();

$name = $_POST['name'];
echo $nameShort = $_POST['nameShort'];exit;
$nameShortDisplay = $_POST['nameShortDisplay'];
$active = $_POST['active'];
$count = $_POST['count'];
// $pupilsightYearGroupIDList = (isset($_POST["pupilsightYearGroupID"]) ? implode(',', $_POST["pupilsightYearGroupID"]) : '');
$pupilsightYearGroupIDList = $_POST['pupilsightYearGroupID'];
$pupilsightRollGroupIDList = $_POST["pupilsightRollGroupID"];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightProgramID = $_POST['pupilsightProgramID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt.php&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightSchoolYearID == '' or $name == '' or $nameShort == '' or $nameShortDisplay == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID,'pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupIDList'=>$pupilsightYearGroupIDList,'pupilsightRollGroupIDList'=>$pupilsightRollGroupIDList);          
            $sql = 'SELECT * FROM pupilsightTT WHERE (name=:name OR (pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupIDList=:pupilsightYearGroupIDList AND pupilsightRollGroupIDList=:pupilsightRollGroupIDList))';
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
                $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'nameShort' => $nameShort, 'nameShortDisplay' => $nameShortDisplay, 'active' => $active, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightRollGroupIDList' => $pupilsightRollGroupIDList,'pupilsightProgramID'=>$pupilsightProgramID);
                $sql = 'INSERT INTO pupilsightTT SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, nameShortDisplay=:nameShortDisplay, active=:active, pupilsightProgramID=:pupilsightProgramID,pupilsightYearGroupIDList=:pupilsightYearGroupIDList, pupilsightRollGroupIDList=:pupilsightRollGroupIDList';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error9';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
