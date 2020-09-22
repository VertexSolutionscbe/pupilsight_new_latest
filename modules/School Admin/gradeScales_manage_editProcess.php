<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$name = $_POST['name'];
$nameShort = $_POST['nameShort'];
$pupilsightScaleID = $_POST['pupilsightScaleID'];
$usage = $_POST['usage'];
$active = $_POST['active'];
$numeric = $_POST['numeric'];
$lowestAcceptable = $_POST['lowestAcceptable'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/gradeScales_manage_edit.php&pupilsightScaleID='.$pupilsightScaleID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/gradeScales_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if special day specified
    if ($pupilsightScaleID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightScaleID' => $pupilsightScaleID);
            $sql = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
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
            if ($name == '' or $nameShort == '' or $usage == '' or $active == '' or $numeric == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightScaleID' => $pupilsightScaleID);
                    $sql = 'SELECT * FROM pupilsightScale WHERE (name=:name OR nameShort=:nameShort) AND NOT pupilsightScaleID=:pupilsightScaleID';
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
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'usage' => $usage, 'active' => $active, 'numeric' => $numeric, 'lowestAcceptable' => $lowestAcceptable, 'pupilsightScaleID' => $pupilsightScaleID);
                        $sql = 'UPDATE pupilsightScale SET name=:name, nameShort=:nameShort, `usage`=:usage, active=:active, `numeric`=:numeric, lowestAcceptable=:lowestAcceptable WHERE pupilsightScaleID=:pupilsightScaleID';
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
