<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/resourceSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/resourceSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $categories = '';
    foreach (explode(',', $_POST['categories']) as $category) {
        $categories .= trim($category).',';
    }
    $categories = substr($categories, 0, -1);
    $purposesGeneral = '';
    foreach (explode(',', $_POST['purposesGeneral']) as $purpose) {
        $purposesGeneral .= trim($purpose).',';
    }
    $purposesGeneral = substr($purposesGeneral, 0, -1);
    $purposesRestricted = '';
    foreach (explode(',', $_POST['purposesRestricted']) as $purpose) {
        $purposesRestricted .= trim($purpose).',';
    }
    $purposesRestricted = substr($purposesRestricted, 0, -1);

    //Validate Inputs
    if ($categories == '' or $purposesGeneral == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

        try {
            $data = array('value' => $categories);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Resources' AND name='categories'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $purposesGeneral);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Resources' AND name='purposesGeneral'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $purposesRestricted);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Resources' AND name='purposesRestricted'";
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
