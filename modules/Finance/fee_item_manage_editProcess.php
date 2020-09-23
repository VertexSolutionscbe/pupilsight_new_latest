<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_item_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_item_manage_edit.php') == false) {
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
            $sql = 'SELECT * FROM fn_fee_items WHERE id=:id';
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
            $code = $_POST['code'];
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $fn_fee_item_type_id = $_POST['fn_fee_item_type_id'];
            

            if ($name == '' or $code == '' or $pupilsightSchoolYearID == '' or $fn_fee_item_type_id == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'code' => $code, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fee_item_type_id' => $fn_fee_item_type_id, 'id' => $id);
                    $sql = 'SELECT * FROM fn_fee_items WHERE (name=:name AND code=:code AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND fn_fee_item_type_id=:fn_fee_item_type_id) AND NOT id=:id';
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
                        $data = array('name' => $name, 'code' => $code, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fee_item_type_id' => $fn_fee_item_type_id, 'id' => $id);
                        $sql = 'UPDATE fn_fee_items SET name=:name, code=:code, pupilsightSchoolYearID=:pupilsightSchoolYearID, fn_fee_item_type_id=:fn_fee_item_type_id WHERE id=:id';
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
