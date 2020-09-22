<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightStudentEnrolmentID = $_POST['pupilsightStudentEnrolmentID'];
$search = $_GET['search'];

if ($pupilsightStudentEnrolmentID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/studentEnrolment_manage_delete.php&pupilsightStudentEnrolmentID=$pupilsightStudentEnrolmentID&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/studentEnrolment_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Students/studentEnrolment_manage_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightStudentEnrolmentID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightStudentEnrolmentID' => $pupilsightStudentEnrolmentID);
                // $sql = 'SELECT pupilsightRollGroup.pupilsightRollGroupID, pupilsightYearGroup.pupilsightYearGroupID,pupilsightStudentEnrolmentID, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup, pupilsightRollGroup WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) AND (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID ORDER BY surname, preferredName';
                $sql = 'SELECT pupilsightRollGroup.pupilsightRollGroupID, pupilsightYearGroup.pupilsightYearGroupID,pupilsightStudentEnrolmentID, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, dateStart, dateEnd, pupilsightPerson.pupilsightPersonID, rollOrder, pupilsightProgramID FROM pupilsightPerson LEFT JOIN pupilsightStudentEnrolment ON pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID LEFT JOIN pupilsightYearGroup ON pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID LEFT JOIN  pupilsightRollGroup ON pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID ORDER BY surname, preferredName';
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
                //Write to database
                try {
                    $data = array('pupilsightStudentEnrolmentID' => $pupilsightStudentEnrolmentID);
                    $sql = 'DELETE FROM pupilsightStudentEnrolment WHERE pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $URLDelete = $URLDelete.'&return=success0';
                header("Location: {$URLDelete}");
            }
        }
    }
}
