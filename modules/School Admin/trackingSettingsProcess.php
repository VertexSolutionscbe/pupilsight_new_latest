<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/trackingSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/trackingSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $fail = false;

   //DEAL WITH EXTERNAL ASSESSMENT DATA POINTS
   $externalAssessmentDataPoints = (isset($_POST['externalDP']))? $_POST['externalDP'] : null;

    if (!empty($externalAssessmentDataPoints) && is_array($externalAssessmentDataPoints)) {
      foreach ($externalAssessmentDataPoints as &$dp) {
        if (!empty($dp['pupilsightYearGroupIDList'])) {
          $dp['category'] = filter_var($dp['category'], FILTER_SANITIZE_SPECIAL_CHARS);
          $dp['pupilsightExternalAssessmentID'] = filter_var($dp['pupilsightExternalAssessmentID'], FILTER_SANITIZE_NUMBER_INT);
          $dp['pupilsightYearGroupIDList'] = implode(',', $dp['pupilsightYearGroupIDList']);
        }
      }
    }

   //Write setting to database
   try {
       $data = array('value' => serialize($externalAssessmentDataPoints));
       $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Tracking' AND name='externalAssessmentDataPoints'";
       $result = $connection2->prepare($sql);
       $result->execute($data);
   } catch (PDOException $e) {
       $fail = true;
   }

   //DEAL WITH INTERNAL ASSESSMENT DATA POINTS
   $internalAssessmentDataPoints = (isset($_POST['internalDP']))? $_POST['internalDP'] : null;

    if (!empty($internalAssessmentDataPoints) && is_array($internalAssessmentDataPoints)) {
      foreach ($internalAssessmentDataPoints as &$dp) {
        if (!empty($dp['pupilsightYearGroupIDList'])) {
          $dp['type'] = filter_var($dp['type'], FILTER_SANITIZE_SPECIAL_CHARS);
          $dp['pupilsightYearGroupIDList'] = implode(',', $dp['pupilsightYearGroupIDList']);
        }
      }
    }

   //Write setting to database
   try {
       $data = array('value' => serialize($internalAssessmentDataPoints));
       $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Tracking' AND name='internalAssessmentDataPoints'";
       $result = $connection2->prepare($sql);
       $result->execute($data);
   } catch (PDOException $e) {
       $fail = true;
   }

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
