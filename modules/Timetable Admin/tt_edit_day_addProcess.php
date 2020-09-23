<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$name = $_POST['name'];
$nameShort = $_POST['nameShort'];
$color = $_POST['color'];
$fontColor = $_POST['fontColor'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightTTID = $_POST['pupilsightTTID'];
$pupilsightTTColumnID = $_POST['pupilsightTTColumnID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTID=$pupilsightTTID";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightSchoolYearID == '' or $pupilsightTTID == '' or $name == '' or $nameShort == '' or $pupilsightTTColumnID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightTTID' => $pupilsightTTID);
            $sql = 'SELECT * FROM pupilsightTTDay WHERE ((name=:name) OR (nameShort=:nameShort)) AND pupilsightTTID=:pupilsightTTID';
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
                $data = array('pupilsightTTID' => $pupilsightTTID, 'name' => $name, 'nameShort' => $nameShort, 'color' => $color, 'fontColor' => $fontColor, 'pupilsightTTColumnID' => $pupilsightTTColumnID);
                $sql = 'INSERT INTO pupilsightTTDay SET pupilsightTTID=:pupilsightTTID, name=:name, nameShort=:nameShort, color=:color, fontColor=:fontColor, pupilsightTTColumnID=:pupilsightTTColumnID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 6, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
