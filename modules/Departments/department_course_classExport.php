<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/department_course_class.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Departments/department_course_class.php') == false or getHighestGroupedAction($guid, '/modules/Students/student_view_details.php', $connection2) != 'Student Profile_full') {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if ($pupilsightCourseClassID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'SELECT pupilsightCourseClassID, pupilsightCourse.nameShort AS courseName, pupilsightCourseClass.nameShort AS className FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY pupilsightCourse.name, pupilsightCourseClass.name';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        if ($result->rowCount() < 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Proceed!

            $data = ['pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'today' => date('Y-m-d')];
            $sql = "SELECT role, surname, preferredName, email, studentID, pupilsightRollGroup.nameShort as rollGroup
                    FROM pupilsightCourseClassPerson 
                    JOIN pupilsightPerson ON pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID 
                    JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND status='Full' 
                    AND (dateStart IS NULL OR dateStart<=:today) 
                    AND (dateEnd IS NULL  OR dateEnd>=:today) 
                    AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                    ORDER BY role DESC, surname, preferredName";

            $result = $pdo->select($sql, $data);

            $exp = new Pupilsight\Excel();
            $exp->exportWithQuery($result, 'classList.xls');
        }
    }
}
