<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/alertLevelSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/alertLevelSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $count = $_POST['count'];
    $partialFail = false;
    //Proceed!
    if ($count < 1) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
    } else {
        for ($i = 0; $i < $count; ++$i) {
            $pupilsightAlertLevelID = $_POST['pupilsightAlertLevelID'.$i];
            $name = $_POST['name'.$i];
            $nameShort = $_POST['nameShort'.$i];
            $color = $_POST['color'.$i];
            $colorBG = $_POST['colorBG'.$i];
            $description = $_POST['description'.$i];

            //Validate Inputs
            if ($pupilsightAlertLevelID == '' or $name == '' or $nameShort == '' or $color == '' or $colorBG == '') {
                $partialFail = true;
            } else {
                try {
                    $dataUpdate = array('name' => $name, 'nameShort' => $nameShort, 'color' => $color, 'colorBG' => $colorBG, 'description' => $description, 'pupilsightAlertLevelID' => $pupilsightAlertLevelID);
                    $sqlUpdate = 'UPDATE pupilsightAlertLevel SET name=:name, nameShort=:nameShort, color=:color, colorBG=:colorBG, description=:description WHERE pupilsightAlertLevelID=:pupilsightAlertLevelID';
                    $resultUpdate = $connection2->prepare($sqlUpdate);
                    $resultUpdate->execute($dataUpdate);
                } catch (PDOException $e) {
                    $partialFail = false;
                }
            }
        }

        //Deal with failed update
        if ($partialFail == true) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
