<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightPersonID = $_POST['pupilsightPersonID'];
$search = $_GET['search'];

if ($pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/studentEnrolment_manage_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Students/studentEnrolment_manage_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit;
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightPersonID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        } else {
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = "SELECT pupilsightPersonID FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightPerson.status='Full'";
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
                //Check for existing enrolment
                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                    $sql = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit;
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                    exit;
                } else {
                    $pupilsightProgramID = $_POST['pupilsightProgramID'];
                    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
                    $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
                    $rollOrder = $_POST['rollOrder'];
                    if ($_POST['rollOrder'] == '') {
                        $rollOrder = null;
                    }

                    //Check unique inputs for uniquness
                    // try {
                    //     $data = array('rollOrder' => $rollOrder, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                    //     $sql = "SELECT * FROM pupilsightStudentEnrolment WHERE rollOrder=:rollOrder AND pupilsightRollGroupID=:pupilsightRollGroupID AND NOT rollOrder=''";
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
                            $data = array('pupilsightProgramID'=>$pupilsightProgramID,'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'rollOrder' => $rollOrder);
                            $sql = 'INSERT INTO pupilsightStudentEnrolment SET pupilsightPersonID=:pupilsightPersonID, pupilsightProgramID=:pupilsightProgramID, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID, rollOrder=:rollOrder';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit;
                        }

                        //Last insert ID
                        $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

                        // Handle automatic course enrolment if enabled
                        $autoEnrolStudent = (isset($_POST['autoEnrolStudent']))? $_POST['autoEnrolStudent'] : 'N';
                        if ($autoEnrolStudent == 'Y') {
                            $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightPersonID' => $pupilsightPersonID);
                            $sql = "INSERT INTO pupilsightCourseClassPerson (`pupilsightCourseClassID`, `pupilsightPersonID`, `role`, `reportable`)
                                    SELECT pupilsightCourseClassMap.pupilsightCourseClassID, :pupilsightPersonID, 'Student', 'Y'
                                    FROM pupilsightCourseClassMap
                                    LEFT JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClassMap.pupilsightCourseClassID AND pupilsightCourseClassPerson.role='Student')
                                    WHERE pupilsightCourseClassMap.pupilsightRollGroupID=:pupilsightRollGroupID
                                    AND pupilsightCourseClassPerson.pupilsightCourseClassPersonID IS NULL";
                            $pdo->executeQuery($data, $sql);

                            if ($pdo->getQuerySuccess() == false) {
                                $URL .= "&return=warning3&editID=$AI";
                                header("Location: {$URL}");
                                exit;
                            }
                        }

                        $URL .= "&return=success0&editID=$AI";
                        header("Location: {$URL}");
                        exit;
                    // }
                }
            }
        }
    }
}
