<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/displaySettings.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/displaySettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $mainMenuCategoryOrder = '';
    foreach (explode(',', $_POST['mainMenuCategoryOrder']) as $category) {
        $mainMenuCategoryOrder .= trim($category).',';
    }
    $mainMenuCategoryOrder = substr($mainMenuCategoryOrder, 0, -1);

    //Validate Inputs
    if ($mainMenuCategoryOrder == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

        //Update internal assessment fields
        try {
            $data = array('value' => $mainMenuCategoryOrder);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='mainMenuCategoryOrder'";
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
            $_SESSION[$guid]['pageLoads'] = null;
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
