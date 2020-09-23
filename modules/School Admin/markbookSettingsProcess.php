<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/markbookSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/markbookSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $markbookType = '';
    foreach (explode(',', $_POST['markbookType']) as $type) {
        $markbookType .= trim($type).',';
    }
    $markbookType = substr($markbookType, 0, -1);
    $enableEffort = $_POST['enableEffort'];
    $enableRubrics = $_POST['enableRubrics'];
    $enableColumnWeighting = $_POST['enableColumnWeighting'];
    $enableDisplayCumulativeMarks = (isset($_POST['enableDisplayCumulativeMarks']))? $_POST['enableDisplayCumulativeMarks'] : 'N';
    $enableRawAttainment = $_POST['enableRawAttainment'];
    $enableModifiedAssessment = $_POST['enableModifiedAssessment'];
    $enableGroupByTerm = $_POST['enableGroupByTerm'];
    $attainmentAlternativeName = $_POST['attainmentAlternativeName'];
    $attainmentAlternativeNameAbrev = $_POST['attainmentAlternativeNameAbrev'];
    $effortAlternativeName = $_POST['effortAlternativeName'];
    $effortAlternativeNameAbrev = $_POST['effortAlternativeNameAbrev'];
    $showStudentAttainmentWarning = $_POST['showStudentAttainmentWarning'];
    $showStudentEffortWarning = $_POST['showStudentEffortWarning'];
    $showParentAttainmentWarning = $_POST['showParentAttainmentWarning'];
    $showParentEffortWarning = $_POST['showParentEffortWarning'];
    $personalisedWarnings = $_POST['personalisedWarnings'];

    //Validate Inputs
    if ($markbookType == '' or $enableRubrics == '' or $enableRubrics == '' or $enableColumnWeighting == '' or $enableRawAttainment == '' or $enableModifiedAssessment == '' or $enableGroupByTerm == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

        try {
            $data = array('value' => $markbookType);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='markbookType'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $enableEffort);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='enableEffort'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $enableRubrics);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='enableRubrics'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $enableColumnWeighting);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='enableColumnWeighting'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $enableDisplayCumulativeMarks);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='enableDisplayCumulativeMarks'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $enableRawAttainment);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='enableRawAttainment'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $enableModifiedAssessment);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='enableModifiedAssessment'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }


        try {
            $data = array('value' => $enableGroupByTerm);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='enableGroupByTerm'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $attainmentAlternativeName);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='attainmentAlternativeName'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $attainmentAlternativeNameAbrev);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='attainmentAlternativeNameAbrev'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $effortAlternativeName);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='effortAlternativeName'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $effortAlternativeNameAbrev);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='effortAlternativeNameAbrev'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $showStudentAttainmentWarning);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='showStudentAttainmentWarning'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $showStudentEffortWarning);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='showStudentEffortWarning'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $showParentAttainmentWarning);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='showParentAttainmentWarning'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $showParentEffortWarning);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='showParentEffortWarning'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $personalisedWarnings);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Markbook' AND name='personalisedWarnings'";
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
