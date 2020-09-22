<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/behaviourSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/behaviourSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $enableDescriptors = $_POST['enableDescriptors'];
    $enableLevels = $_POST['enableLevels'];
    $enableBehaviourLetters = $_POST['enableBehaviourLetters'];
    $positiveDescriptors = '';
    $negativeDescriptors = '';
    if ($enableDescriptors == 'Y') {
        foreach (explode(',', $_POST['positiveDescriptors']) as $descriptor) {
            $positiveDescriptors .= trim($descriptor).',';
        }
        $positiveDescriptors = substr($positiveDescriptors, 0, -1);

        foreach (explode(',', $_POST['negativeDescriptors']) as $descriptor) {
            $negativeDescriptors .= trim($descriptor).',';
        }
        $negativeDescriptors = substr($negativeDescriptors, 0, -1);
    }
    $levels = '';
    if ($enableLevels == 'Y') {
        foreach (explode(',', $_POST['levels']) as $level) {
            $levels .= trim($level).',';
        }
        $levels = substr($levels, 0, -1);
    }

    $behaviourLettersLetter1Count = (isset($_POST['behaviourLettersLetter1Count']))? $_POST['behaviourLettersLetter1Count'] : '';
    $behaviourLettersLetter1Text = (isset($_POST['behaviourLettersLetter1Text']))? $_POST['behaviourLettersLetter1Text'] : '';
    $behaviourLettersLetter2Count = (isset($_POST['behaviourLettersLetter2Count']))? $_POST['behaviourLettersLetter2Count'] : '';
    $behaviourLettersLetter2Text = (isset($_POST['behaviourLettersLetter2Text']))? $_POST['behaviourLettersLetter2Text'] : '';
    $behaviourLettersLetter3Count = (isset($_POST['behaviourLettersLetter3Count']))? $_POST['behaviourLettersLetter3Count'] : '';
    $behaviourLettersLetter3Text = (isset($_POST['behaviourLettersLetter3Text']))? $_POST['behaviourLettersLetter3Text'] : '';
    
    $policyLink = $_POST['policyLink'];

    //Validate Inputs
    if ($enableDescriptors == '' or $enableLevels == '' or ($positiveDescriptors == '' and $enableDescriptors == 'Y') or ($negativeDescriptors == '' and $enableDescriptors == 'Y') or ($levels == '' and $enableLevels == 'Y') or (($behaviourLettersLetter1Count == '' or $behaviourLettersLetter1Text == '' or $behaviourLettersLetter2Count == '' or $behaviourLettersLetter2Text == '' or $behaviourLettersLetter3Count == '' or $behaviourLettersLetter3Text == '') and $enableBehaviourLetters == 'Y')) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

        try {
            $data = array('value' => $enableDescriptors);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='enableDescriptors'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        if ($enableDescriptors == 'Y') {
            try {
                $data = array('value' => $positiveDescriptors);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='positiveDescriptors'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $negativeDescriptors);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='negativeDescriptors'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }
        }
        try {
            $data = array('value' => $enableLevels);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='enableLevels'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if ($enableLevels == 'Y') {
            try {
                $data = array('value' => $levels);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='levels'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }
        }

        try {
            $data = array('value' => $enableBehaviourLetters);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='enableBehaviourLetters'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('value' => $behaviourLettersLetter1Count);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='behaviourLettersLetter1Count'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('value' => $behaviourLettersLetter1Text);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='behaviourLettersLetter1Text'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('value' => $behaviourLettersLetter2Count);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='behaviourLettersLetter2Count'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('value' => $behaviourLettersLetter2Text);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='behaviourLettersLetter2Text'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('value' => $behaviourLettersLetter3Count);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='behaviourLettersLetter3Count'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('value' => $behaviourLettersLetter3Text);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='behaviourLettersLetter3Text'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $policyLink);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Behaviour' AND name='policyLink'";
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
