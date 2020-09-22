<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_sync_run.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // Allows for a single value or a csv list of pupilsightYearGroupID
    $pupilsightYearGroupIDList = $_GET['pupilsightYearGroupIDList'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';

    $page->breadcrumbs
        ->add(__('Sync Course Enrolment'), 'courseEnrolment_sync.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Sync Now'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if (empty($pupilsightYearGroupIDList) || empty($pupilsightSchoolYearID)) {
        echo "<div class='alert alert-danger'>";
        echo __('Your request failed because your inputs were invalid.');
        echo '</div>';
        return;
    }

    if ($pupilsightYearGroupIDList == 'all') {
        // All class mappings
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightCourseClassMap.*, pupilsightYearGroup.name as pupilsightYearGroupName
                FROM pupilsightCourseClassMap
                JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightCourseClassMap.pupilsightRollGroupID)
                JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightYearGroupID=pupilsightCourseClassMap.pupilsightYearGroupID)
                WHERE pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
                GROUP BY pupilsightCourseClassMap.pupilsightYearGroupID";
    } else {
        // Pull up the class mapping for this year group
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightYearGroupID' => $pupilsightYearGroupIDList);
        $sql = "SELECT pupilsightCourseClassMap.*, pupilsightYearGroup.name as pupilsightYearGroupName
                FROM pupilsightCourseClassMap
                JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightCourseClassMap.pupilsightRollGroupID)
                JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightYearGroupID=pupilsightCourseClassMap.pupilsightYearGroupID)
                WHERE FIND_IN_SET(pupilsightCourseClassMap.pupilsightYearGroupID, :pupilsightYearGroupID)
                AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
                GROUP BY pupilsightCourseClassMap.pupilsightYearGroupID";
    }

    $result = $pdo->executeQuery($data, $sql);

    if ($result->rowCount() == 0) {
        echo "<div class='alert alert-danger'>";
        echo __('Your request failed because your inputs were invalid.');
        echo '</div>';
        return;
    }

    $form = Form::create('courseEnrolmentSyncRun', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_sync_runProcess.php');
    $form->setClass('w-full blank');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightYearGroupIDList', $pupilsightYearGroupIDList);
    $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

    // Checkall options
    $row = $form->addRow()->addContent('<h4>'.__('Options').'</h4>');
    $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth');

    $row = $table->addRow();
        $row->addLabel('includeStudents', __('Include Students'));
        $row->addCheckbox('includeStudents')->checked(true);
    $row = $table->addRow();
        $row->addLabel('includeTeachers', __('Include Teachers'));
        $row->addCheckbox('includeTeachers')->checked(true);

    $enrolableCount = 0;

    while ($classMap = $result->fetch()) {
        $form->addRow()->addHeading($classMap['pupilsightYearGroupName']);

        $data = array(
            'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
            'pupilsightYearGroupID' => $classMap['pupilsightYearGroupID'],
            'date' => date('Y-m-d'),
        );

        // Grab mapped classes for all teachers & students grouped by year group, excluding those already enrolled
        $sql = "(SELECT pupilsightPerson.pupilsightPersonID, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRollGroup.pupilsightRollGroupID, pupilsightRollGroup.name as pupilsightRollGroupName, GROUP_CONCAT(CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) ORDER BY pupilsightCourse.nameShort, pupilsightCourseClass.nameShort SEPARATOR ', ') AS courseList, 'Teacher' as role
                FROM pupilsightCourseClassMap
                JOIN pupilsightRollGroup ON (pupilsightCourseClassMap.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                JOIN pupilsightPerson ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightPerson.pupilsightPersonID || pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightPerson.pupilsightPersonID || pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassMap.pupilsightCourseClassID)
                JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                LEFT JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClassMap.pupilsightCourseClassID AND pupilsightCourseClassPerson.role = 'Teacher')
                WHERE pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
                AND pupilsightCourseClassMap.pupilsightYearGroupID=:pupilsightYearGroupID
                AND pupilsightPerson.status='Full'
                AND (pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<=:date)
                AND (pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd>=:date)
                AND pupilsightCourseClassPerson.pupilsightCourseClassPersonID IS NULL
                GROUP BY pupilsightPerson.pupilsightPersonID
            ) UNION ALL (
                SELECT pupilsightPerson.pupilsightPersonID, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRollGroup.pupilsightRollGroupID, pupilsightRollGroup.name as pupilsightRollGroupName, GROUP_CONCAT(CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) ORDER BY pupilsightCourse.nameShort, pupilsightCourseClass.nameShort SEPARATOR ', ') AS courseList, 'Student' as role
                FROM pupilsightCourseClassMap
                JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightCourseClassMap.pupilsightYearGroupID AND pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightCourseClassMap.pupilsightRollGroupID)
                JOIN pupilsightPerson ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRollGroup ON (pupilsightCourseClassMap.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassMap.pupilsightCourseClassID)
                JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                LEFT JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClassMap.pupilsightCourseClassID  AND pupilsightCourseClassPerson.role = 'Student')
                WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                AND pupilsightCourseClassMap.pupilsightYearGroupID=:pupilsightYearGroupID
                AND pupilsightPerson.status='Full'
                AND (pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<=:date)
                AND (pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd>=:date)
                AND pupilsightCourseClassPerson.pupilsightCourseClassPersonID IS NULL
                GROUP BY pupilsightPerson.pupilsightPersonID
            ) ORDER BY role DESC, surname, preferredName";

        $enrolmentResult = $pdo->executeQuery($data, $sql);

        if ($enrolmentResult->rowCount() == 0) {
            $form->addRow()->addAlert(__('Course enrolments are already synced. No changes will be made.'), 'success');
        } else {
            $enrolableCount += $enrolmentResult->rowCount();

            $table = $form->addRow()->addTable()->setClass('smallIntBorder colorOddEven fullWidth standardForm');
            $header = $table->addHeaderRow();
                $header->addCheckbox('checkall'.$classMap['pupilsightYearGroupID'])->checked(true);
                $header->addContent(__('Name'));
                $header->addContent(__('Role'));
                $header->addContent(__('Roll Group'));
                $header->addContent(__('Enrolment by Class'));

            while ($person = $enrolmentResult->fetch()) {
                $row = $table->addRow();
                    $row->addCheckbox('syncData['.$person['pupilsightRollGroupID'].']['.$person['pupilsightPersonID'].']')
                        ->setValue($person['role'])
                        ->checked($person['role'])
                        ->setClass($classMap['pupilsightYearGroupID'])
                        ->addClass(strtolower($person['role']))
                        ->description('&nbsp;&nbsp;');
                    $row->addLabel('syncData['.$person['pupilsightRollGroupID'].']['.$person['pupilsightPersonID'].']', Format::name('', $person['preferredName'], $person['surname'], 'Student', true))->addClass('mediumWidth');
                    $row->addContent($person['role']);
                    $row->addContent($person['pupilsightRollGroupName']);
                    $row->addContent($person['courseList']);
            }

            // Checkall by Year Group
            echo '<script type="text/javascript">';
            echo '$(function () {';
                echo "$('#checkall".$classMap['pupilsightYearGroupID']."').click(function () {";
                echo "$('.".$classMap['pupilsightYearGroupID']."').find(':checkbox').attr('checked', this.checked);";
                echo '});';
            echo '});';
            echo '</script>';
        }
    }

    // Only display a submit button if a sync is required
    if ($enrolableCount > 0) {
        $table = $form->addRow()->addTable()->setClass('smallIntBorder colorOddEven fullWidth standardForm');
        $table->addRow()->addSubmit(__('Proceed'));
    }

    echo $form->getOutput();

    // Checkall by Student/Teacher
    echo '<script type="text/javascript">';
    echo '$(function () {';
        echo "$('#includeStudents').click(function () {";
        echo "$('.student').find(':checkbox').attr('checked', this.checked);";
        echo '});';

        echo "$('#includeTeachers').click(function () {";
        echo "$('.teacher').find(':checkbox').attr('checked', this.checked);";
        echo '});';
    echo '});';
    echo '</script>';
}
