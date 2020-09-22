<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFirstAidID = $_GET['pupilsightFirstAidID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/firstAidRecord_edit.php&pupilsightFirstAidID=$pupilsightFirstAidID&pupilsightRollGroupID=".$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'];

if (isActionAccessible($guid, $connection2, '/modules/Students/firstAidRecord_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFirstAidID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightFirstAidID' => $pupilsightFirstAidID);
            $sql = "SELECT pupilsightFirstAid.*, patient.surname AS surnamePatient, patient.preferredName AS preferredNamePatient, firstAider.title, firstAider.surname AS surnameFirstAider, firstAider.preferredName AS preferredNameFirstAider
                FROM pupilsightFirstAid
                    JOIN pupilsightPerson AS patient ON (pupilsightFirstAid.pupilsightPersonIDPatient=patient.pupilsightPersonID)
                    JOIN pupilsightPerson AS firstAider ON (pupilsightFirstAid.pupilsightPersonIDFirstAider=firstAider.pupilsightPersonID)
                    JOIN pupilsightStudentEnrolment ON (patient.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFirstAidID=:pupilsightFirstAidID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            $timeOut = null;
            if ($_POST['timeOut'] != '')
                $timeOut = $_POST['timeOut'];
            $followUp = $_POST['followUp'];

            try {
                $data = array('timeOut' => $timeOut, 'followUp' => $followUp, 'pupilsightFirstAidID' => $pupilsightFirstAidID);
                $sql = 'UPDATE pupilsightFirstAid SET timeOut=:timeOut, followUp=:followUp WHERE pupilsightFirstAidID=:pupilsightFirstAidID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
