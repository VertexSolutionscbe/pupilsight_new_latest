<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/department_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/department_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $makeDepartmentsPublic = $_POST['makeDepartmentsPublic'];

    //Write to database
    $fail = false;

    try {
        $data = array('value' => $makeDepartmentsPublic);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Departments' AND name='makeDepartmentsPublic'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    if ($fail == true) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
    } else {
        //Success 0
        getSystemSettings($guid, $connection2);
        $URL .= '&return=success0';
        header("Location: {$URL}");
    }
}
