<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/librarySettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/librarySettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $defaultLoanLength = $_POST['defaultLoanLength'];
    $browseBGColor = $_POST['browseBGColor'];
    $browseBGImage = $_POST['browseBGImage'];

    //Validate Inputs
    if ($defaultLoanLength == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

        try {
            $data = array('value' => $defaultLoanLength);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Library' AND name='defaultLoanLength'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $browseBGColor);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Library' AND name='browseBGColor'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $browseBGImage);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Library' AND name='browseBGImage'";
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
