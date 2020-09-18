<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/feeCategories_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/feeCategories_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];
    $active = $_POST['active'];
    $description = $_POST['description'];

    if ($name == '' or $nameShort == '' or $active == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Write to database
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'description' => $description, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "INSERT INTO pupilsightFinanceFeeCategory SET name=:name, nameShort=:nameShort, active=:active, description=:description, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestampCreator='".date('Y-m-d H:i:s')."'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 4, '0', STR_PAD_LEFT);

        //Success 0
        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}
