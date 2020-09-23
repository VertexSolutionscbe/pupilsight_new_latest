<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/space_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/spaceSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $facilityTypes = '';
    foreach (explode(',', $_POST['facilityTypes']) as $type) {
        $facilityTypes .= trim($type).',';
    }
    $facilityTypes = substr($facilityTypes, 0, -1);

    //Validate Inputs
    if ($facilityTypes == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

        //Update internal assessment fields
        try {
            $data = array('value' => $facilityTypes);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='School Admin' AND name='facilityTypes'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if ($fail == true) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            getSystemSettings($guid, $connection2);
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
