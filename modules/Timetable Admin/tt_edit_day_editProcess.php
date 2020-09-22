<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTDayID = $_GET['pupilsightTTDayID'];
$pupilsightTTID = $_GET['pupilsightTTID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];

if ($pupilsightTTID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_edit.php&pupilsightTTID=$pupilsightTTID&pupilsightTTDayID=$pupilsightTTDayID&pupilsightSchoolYearID=$pupilsightSchoolYearID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if tt specified
        if ($pupilsightTTDayID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightTTDayID' => $pupilsightTTDayID);
                $sql = 'SELECT * FROM pupilsightTTDay WHERE pupilsightTTDayID=:pupilsightTTDayID';
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
                $color = $_POST['color'];
                $fontColor = $_POST['fontColor'];
                $pupilsightTTColumnID = $_POST['pupilsightTTColumnID'];

                if ($name == '' or $nameShort == '' or $pupilsightTTColumnID == '') {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Check unique inputs for uniquness
                    try {
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightTTID' => $pupilsightTTID, 'pupilsightTTDayID' => $pupilsightTTDayID);
                        $sql = 'SELECT * FROM pupilsightTTDay WHERE ((name=:name) OR (nameShort=:nameShort)) AND pupilsightTTID=:pupilsightTTID AND NOT pupilsightTTDayID=:pupilsightTTDayID';
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
                            $data = array('name' => $name, 'nameShort' => $nameShort, 'color' => $color, 'fontColor' => $fontColor, 'pupilsightTTColumnID' => $pupilsightTTColumnID, 'pupilsightTTDayID' => $pupilsightTTDayID);
                            $sql = 'UPDATE pupilsightTTDay SET name=:name, nameShort=:nameShort, color=:color, fontColor=:fontColor, pupilsightTTColumnID=:pupilsightTTColumnID WHERE pupilsightTTDayID=:pupilsightTTDayID';
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
