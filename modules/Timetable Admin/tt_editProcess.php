<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
// print_r("<pre>");
// print_r($_POST);dei();

$name = $_POST['name'];
$nameShort = $_POST['nameShort'];
$nameShortDisplay = $_POST['nameShortDisplay'];
$active = $_POST['active'];
$count = $_POST['count'];
$pupilsightProgramID = $_POST['pupilsightProgramID'];
// $pupilsightYearGroupIDList = (isset($_POST["pupilsightYearGroupID"]) ? implode(',', $_POST["pupilsightYearGroupID"]) : '');
$pupilsightYearGroupIDList = $_POST['pupilsightYearGroupID'];
$pupilsightRollGroupIDList = $_POST["pupilsightRollGroupID"];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightTTID = $_POST['pupilsightTTID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/tt_edit.php&pupilsightTTID='.$pupilsightTTID.'&pupilsightSchoolYearID='.$_POST['pupilsightSchoolYearID'];

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if special day specified
    if ($pupilsightTTID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightTTID' => $pupilsightTTID);
            $sql = 'SELECT * FROM pupilsightTT WHERE pupilsightTTID=:pupilsightTTID';
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
            if ($name == '' or $nameShort == '' or $nameShortDisplay == '' or $pupilsightSchoolYearID == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightTTID' => $pupilsightTTID);
                    $sql = 'SELECT * FROM pupilsightTT WHERE (name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID) AND NOT pupilsightTTID=:pupilsightTTID';
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
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'nameShortDisplay' => $nameShortDisplay, 'active' => $active, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightProgramID'=>$pupilsightProgramID,'pupilsightRollGroupIDList' => $pupilsightRollGroupIDList,  'pupilsightTTID' => $pupilsightTTID);
                        $sql = 'UPDATE pupilsightTT SET name=:name, nameShort=:nameShort, nameShortDisplay=:nameShortDisplay, active=:active,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, pupilsightRollGroupIDList=:pupilsightRollGroupIDList WHERE pupilsightTTID=:pupilsightTTID';
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
}
