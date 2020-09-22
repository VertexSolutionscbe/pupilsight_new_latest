<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_item_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_item_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['name'];
    $code = $_POST['code'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $fn_fee_item_type_id = $_POST['fn_fee_item_type_id'];
    
    if ($name == ''  or $code == '' or $pupilsightSchoolYearID == '' or $fn_fee_item_type_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'code' => $code, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fee_item_type_id' => $fn_fee_item_type_id);
            $sql = 'SELECT * FROM fn_fee_items WHERE name=:name AND code=:code AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND fn_fee_item_type_id=:fn_fee_item_type_id';
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
                $data = array('name' => $name, 'code' => $code, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fee_item_type_id' => $fn_fee_item_type_id);
                $sql = 'INSERT INTO fn_fee_items SET name=:name, code=:code, pupilsightSchoolYearID=:pupilsightSchoolYearID, fn_fee_item_type_id=:fn_fee_item_type_id';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
