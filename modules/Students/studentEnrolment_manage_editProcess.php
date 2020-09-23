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
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/studentEnrolment_manage_edit.php&pupilsightStudentEnrolmentID=$pupilsightStudentEnrolmentID&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Students/studentEnrolment_manage_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit;
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightStudentEnrolmentID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
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
                exit;
            }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit;
            } else {
                $pupilsightProgramID = $_POST['pupilsightProgramID'];
                $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
                $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];

                $rollOrder = $_POST['rollOrder'];
                if ($rollOrder == '') {
                    $rollOrder = null;
                }

                //Check unique inputs for uniquness
                // try {
                //     $data = array('pupilsightStudentEnrolmentID' => $pupilsightStudentEnrolmentID, 'rollOrder' => $rollOrder, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                //     $sql = "SELECT * FROM pupilsightStudentEnrolment WHERE rollOrder=:rollOrder AND pupilsightRollGroupID=:pupilsightRollGroupID AND NOT pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID AND NOT rollOrder=''";
                //     $result = $connection2->prepare($sql);
                //     $result->execute($data);
                // } catch (PDOException $e) {
                //     $URL .= '&return=error2';
                //     header("Location: {$URL}");
                //     exit;
                // }

                // if ($result->rowCount() > 0) {
                //     $URL .= '&return=error3';
                //     header("Location: {$URL}");
                //     exit;
                // } else {
                    //Write to database
                    try {
                        $data = array('pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'rollOrder' => $rollOrder, 'pupilsightStudentEnrolmentID' => $pupilsightStudentEnrolmentID);
                        $sql = 'UPDATE pupilsightStudentEnrolment SET pupilsightProgramID=:pupilsightProgramID,pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID, rollOrder=:rollOrder WHERE pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit;
                    }

                    // Handle automatic course enrolment if enabled
                    $autoEnrolStudent = (isset($_POST['autoEnrolStudent']))? $_POST['autoEnrolStudent'] : 'N';
                    if ($autoEnrolStudent == 'Y') {

                        // Remove existing auto-enrolment: moving a student from one Roll Group to another
                        $pupilsightRollGroupIDOriginal = (isset($_POST['pupilsightRollGroupIDOriginal']))? $_POST['pupilsightRollGroupIDOriginal'] : 'N';

                        $data = array('pupilsightRollGroupIDOriginal' => $pupilsightRollGroupIDOriginal, 'pupilsightStudentEnrolmentID' => $pupilsightStudentEnrolmentID);
                        $sql = "UPDATE pupilsightCourseClassPerson
                                JOIN pupilsightStudentEnrolment ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                                JOIN pupilsightCourseClassMap ON (pupilsightCourseClassMap.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                                SET role='Student - Left'
                                WHERE pupilsightStudentEnrolment.pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID
                                AND pupilsightCourseClassMap.pupilsightRollGroupID=:pupilsightRollGroupIDOriginal";
                        $pdo->executeQuery($data, $sql);

                        if ($pdo->getQuerySuccess() == false) {
                            $URL .= "&return=warning3";
                            header("Location: {$URL}");
                            exit;
                        }

                        // Add course enrolments for new Roll Group
                        $data = array('pupilsightStudentEnrolmentID' => $pupilsightStudentEnrolmentID);
                        $sql = "INSERT INTO pupilsightCourseClassPerson (`pupilsightCourseClassID`, `pupilsightPersonID`, `role`, `reportable`)
                                SELECT pupilsightCourseClassMap.pupilsightCourseClassID, pupilsightStudentEnrolment.pupilsightPersonID, 'Student', 'Y'
                                FROM pupilsightStudentEnrolment
                                JOIN pupilsightCourseClassMap ON (pupilsightCourseClassMap.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID)
                                LEFT JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClassMap.pupilsightCourseClassID AND pupilsightCourseClassPerson.role='Student')
                                WHERE pupilsightStudentEnrolment.pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID
                                AND pupilsightCourseClassPerson.pupilsightCourseClassPersonID IS NULL";
                        $pdo->executeQuery($data, $sql);

                        if ($pdo->getQuerySuccess() == false) {
                            $URL .= "&return=warning3";
                            header("Location: {$URL}");
                            exit;
                        }
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                    exit;
                //}
            }
        }
    }
}
