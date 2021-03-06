<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/attendanceSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/attendanceSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $fail = false;

    $attendanceReasons = (isset($_POST['attendanceReasons'])) ? $_POST['attendanceReasons'] : NULL;
    try {
        $data = array('value' => $attendanceReasons);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='attendanceReasons'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $countClassAsSchool = (isset($_POST['countClassAsSchool'])) ? $_POST['countClassAsSchool'] : NULL;
    try {
        $data = array('value' => $countClassAsSchool);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='countClassAsSchool'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $crossFillClasses = (isset($_POST['crossFillClasses'])) ? $_POST['crossFillClasses'] : NULL;
    try {
        $data = array('value' => $crossFillClasses);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='crossFillClasses'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $defaultRollGroupAttendanceType = (isset($_POST['defaultRollGroupAttendanceType'])) ? $_POST['defaultRollGroupAttendanceType'] : NULL;
    try {
        $data = array('value' => $defaultRollGroupAttendanceType);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='defaultRollGroupAttendanceType'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $defaultClassAttendanceType = (isset($_POST['defaultClassAttendanceType'])) ? $_POST['defaultClassAttendanceType'] : NULL;
    try {
        $data = array('value' => $defaultClassAttendanceType);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='defaultClassAttendanceType'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $studentSelfRegistrationIPAddresses = '';
    foreach (explode(',', $_POST['studentSelfRegistrationIPAddresses']) as $ipAddress) {
        $studentSelfRegistrationIPAddresses .= trim($ipAddress).',';
    }
    $studentSelfRegistrationIPAddresses = substr($studentSelfRegistrationIPAddresses, 0, -1);
    try {
        $data = array('value' => $studentSelfRegistrationIPAddresses);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='studentSelfRegistrationIPAddresses'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $selfRegistrationRedirect = (isset($_POST['selfRegistrationRedirect'])) ? $_POST['selfRegistrationRedirect'] : NULL;
    try {
        $data = array('value' => $selfRegistrationRedirect);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='selfRegistrationRedirect'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $attendanceCLINotifyByRollGroup = (isset($_POST['attendanceCLINotifyByRollGroup'])) ? $_POST['attendanceCLINotifyByRollGroup'] : NULL;
    try {
        $data = array('value' => $attendanceCLINotifyByRollGroup);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='attendanceCLINotifyByRollGroup'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $attendanceCLINotifyByClass = (isset($_POST['attendanceCLINotifyByClass'])) ? $_POST['attendanceCLINotifyByClass'] : NULL;
    try {
        $data = array('value' => $attendanceCLINotifyByClass);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='attendanceCLINotifyByClass'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $attendanceCLIAdditionalUsers = (isset($_POST['attendanceCLIAdditionalUsers'])) ? $_POST['attendanceCLIAdditionalUsers'] : NULL;
    try {
        $attendanceCLIAdditionalUserList = (is_array($attendanceCLIAdditionalUsers))? implode(',', $attendanceCLIAdditionalUsers) : '';
        $data = array('value' => $attendanceCLIAdditionalUserList);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='attendanceCLIAdditionalUsers'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    /*
    $attendanceMedicalReasons = (isset($_POST['attendanceMedicalReasons'])) ? $_POST['attendanceMedicalReasons'] : NULL;
    try {
        $data = array('value' => $attendanceMedicalReasons);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='attendanceMedicalReasons'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $attendanceEnableMedicalTracking = (isset($_POST['attendanceEnableMedicalTracking'])) ? $_POST['attendanceEnableMedicalTracking'] : NULL;
    try {
        $data = array('value' => $attendanceEnableMedicalTracking);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Attendance' AND name='attendanceEnableMedicalTracking'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    // Move this to a Medical Settings page, eventually
    $medicalIllnessSymptoms = (isset($_POST['medicalIllnessSymptoms'])) ? $_POST['medicalIllnessSymptoms'] : NULL;
    try {
        $data = array('value' => $medicalIllnessSymptoms);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='medicalIllnessSymptoms'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    */


   //RETURN RESULTS
   if ($fail == true) {
       $URL .= '&return=error2';
       header("Location: {$URL}");
   } else {
       //Success 0
        $URL .= '&return=success0';
       header("Location: {$URL}");
   }
}
