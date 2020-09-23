<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Services\Format;

include '../../pupilsight.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$subpage = $_GET['subpage'];
$allStudents = '';
if (isset($_GET['allStudents'])) {
    $allStudents = $_GET['allStudents'];
}
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/student_view_details_notes_add.php&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search']."&subpage=$subpage&category=".$_GET['category']."&allStudents=$allStudents";

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details_notes_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $enableStudentNotes = getSettingByScope($connection2, 'Students', 'enableStudentNotes');
    $noteCreationNotification = getSettingByScope($connection2, 'Students', 'noteCreationNotification');

    if ($enableStudentNotes != 'Y') {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        if ($pupilsightPersonID == '' or $subpage == '') {
            echo 'Fatal error loading this page!';
        } else {
            //Check for existence of student
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = 'SELECT pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightPerson.status, pupilsightStudentEnrolment.pupilsightYearGroupID
                    FROM pupilsightPerson
                    LEFT JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID)
                    WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
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
                exit();
            } else {
                $row = $result->fetch();
                $status = $row['status'];

                //Proceed!
                //Validate Inputs
                $title = $_POST['title'];
                $pupilsightStudentNoteCategoryID = $_POST['pupilsightStudentNoteCategoryID'];
                if ($pupilsightStudentNoteCategoryID == '') {
                    $pupilsightStudentNoteCategoryID = null;
                }
                $note = $_POST['note'];

                if ($note == '' or $title == '') {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightStudentNoteCategoryID' => $pupilsightStudentNoteCategoryID, 'title' => $title, 'note' => $note, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'timestamp' => date('Y-m-d H:i:s', time()));
                        $sql = 'INSERT INTO pupilsightStudentNote SET pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID, title=:title, note=:note, pupilsightPersonID=:pupilsightPersonID, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestamp=:timestamp';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Attempt to issue alerts form tutor(s) and teacher(s) according to settings
                    if ($status == 'Full') {

                        // Raise a new notification event
                        $event = new NotificationEvent('Students', 'New Student Note');

                        $staffName = Format::name('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', false, true);
                        $studentName = Format::name('', $row['preferredName'], $row['surname'], 'Student', false);

                        $event->setNotificationText(sprintf(__('%1$s has added a student note ("%2$s") about %3$s.'), $staffName, $title, $studentName));
                        $event->setActionLink("/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search']."&subpage=$subpage&category=".$_GET['category']);

                        $event->addScope('pupilsightPersonIDStudent', $pupilsightPersonID);
                        $event->addScope('pupilsightYearGroupID', $row['pupilsightYearGroupID']);

                        if ($noteCreationNotification == 'Tutors' or $noteCreationNotification == 'Tutors & Teachers') {
                            try {
                                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                $sql = "SELECT pupilsightPerson.pupilsightPersonID
    								FROM pupilsightStudentEnrolment
    								JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
    								LEFT JOIN pupilsightPerson ON ((pupilsightPerson.pupilsightPersonID=pupilsightRollGroup.pupilsightPersonIDTutor AND pupilsightPerson.status='Full') OR (pupilsightPerson.pupilsightPersonID=pupilsightRollGroup.pupilsightPersonIDTutor2 AND pupilsightPerson.status='Full') OR (pupilsightPerson.pupilsightPersonID=pupilsightRollGroup.pupilsightPersonIDTutor3 AND pupilsightPerson.status='Full'))
    								WHERE pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) { print $e->getMessage(); }
                            while ($row = $result->fetch()) {
                                $event->addRecipient($row['pupilsightPersonID']);
                            }

                        }
                        if ($noteCreationNotification == 'Tutors & Teachers') {
                            try {
                                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                $sql = "SELECT DISTINCT teacher.pupilsightPersonID FROM pupilsightPerson AS teacher JOIN pupilsightCourseClassPerson AS teacherClass ON (teacherClass.pupilsightPersonID=teacher.pupilsightPersonID)  JOIN pupilsightCourseClassPerson AS studentClass ON (studentClass.pupilsightCourseClassID=teacherClass.pupilsightCourseClassID) JOIN pupilsightPerson AS student ON (studentClass.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightCourseClass ON (studentClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE teacher.status='Full' AND teacherClass.role='Teacher' AND studentClass.role='Student' AND student.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY teacher.preferredName, teacher.surname, teacher.email ;";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) { }
                            while ($row = $result->fetch()) {
                                $event->addRecipient($row['pupilsightPersonID']);
                            }
                        }

                        // Send notifications
                        $event->sendNotifications($pdo, $pupilsight->session);
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
