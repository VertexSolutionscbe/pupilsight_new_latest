<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTColumnRowID = $_POST['pupilsightTTColumnRowID'];
$pupilsightTTColumnID = $_POST['pupilsightTTColumnID'];

if ($pupilsightTTColumnID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/ttColumn_edit_row_edit.php&pupilsightTTColumnID=$pupilsightTTColumnID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttColumn_edit_row_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if tt specified
        if ($pupilsightTTColumnRowID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
                $sql = 'SELECT * FROM pupilsightTTColumnRow WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID';
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
                $name = $_POST['name'];
                $nameShort = $_POST['nameShort'];
                $timeStart = $_POST['timeStart'];
                $timeEnd = $_POST['timeEnd'];
                $type = $_POST['type'];

                if ($name == '' or $nameShort == '' or $timeStart == '' or $timeEnd == '' or $type == '') {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Check unique inputs for uniquness
                    try {
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightTTColumnID' => $pupilsightTTColumnID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
                        $sql = 'SELECT * FROM pupilsightTTColumnRow WHERE (name=:name OR nameShort=:nameShort) AND pupilsightTTColumnID=:pupilsightTTColumnID AND NOT pupilsightTTColumnRowID=:pupilsightTTColumnRowID';
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
                            $data = array('name' => $name, 'nameShort' => $nameShort, 'timeStart' => $timeStart, 'timeEnd' => $timeEnd, 'type' => $type, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
                            $sql = 'UPDATE pupilsightTTColumnRow SET name=:name, nameShort=:nameShort, timeStart=:timeStart, timeEnd=:timeEnd, type=:type WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID';
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
}
