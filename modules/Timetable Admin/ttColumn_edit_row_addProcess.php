<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$name = $_POST['name'];
$nameShort = $_POST['nameShort'];
$timeStart = $_POST['timeStart'];
$timeEnd = $_POST['timeEnd'];
$type = $_POST['type'];

$pupilsightTTColumnID = $_POST['pupilsightTTColumnID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/ttColumn_edit_row_add.php&pupilsightTTColumnID=$pupilsightTTColumnID";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttColumn_edit_row_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightTTColumnID == '' or $name == '' or $nameShort == '' or $timeStart == '' or $timeEnd == '' or $type == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightTTColumnID' => $pupilsightTTColumnID);
            $sql = 'SELECT * FROM pupilsightTTColumnRow WHERE ((name=:name) OR (nameShort=:nameShort)) AND pupilsightTTColumnID=:pupilsightTTColumnID';
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
                $data = array('pupilsightTTColumnID' => $pupilsightTTColumnID, 'name' => $name, 'nameShort' => $nameShort, 'timeStart' => $timeStart, 'timeEnd' => $timeEnd, 'type' => $type);
                $sql = 'INSERT INTO pupilsightTTColumnRow SET pupilsightTTColumnID=:pupilsightTTColumnID, name=:name, nameShort=:nameShort, timeStart=:timeStart, timeEnd=:timeEnd, type=:type';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
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
