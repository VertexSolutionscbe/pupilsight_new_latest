<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_series_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_series_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fee_series WHERE id=:id';
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
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $series_name = $_POST['series_name'];
            $description = $_POST['description'];
            $format = $_POST['format'];
            $start_number = $_POST['start_number'];
            $no_of_digit = $_POST['no_of_digit'];
            $start_char = $_POST['start_char'];
            

            if ($series_name == ''  or $format == '' ) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('series_name' => $series_name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'format' => $format, 'id' => $id);
                    $sql = 'SELECT * FROM fn_fee_series WHERE (series_name=:series_name AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND format=:format) AND NOT id=:id';
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
                        $data = array('series_name' => $series_name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'format' => $format, 'description' => $description, 'start_number' => $start_number, 'no_of_digit' => $no_of_digit, 'start_char' => $start_char, 'id' => $id);
                        $sql = 'UPDATE fn_fee_series SET series_name=:series_name, format=:format, pupilsightSchoolYearID=:pupilsightSchoolYearID, description=:description, start_number=:start_number, no_of_digit=:no_of_digit, start_char=:start_char WHERE id=:id';
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
