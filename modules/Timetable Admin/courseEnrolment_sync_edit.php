<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_sync_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightYearGroupID = $_REQUEST['pupilsightYearGroupID'] ?? '';
    $pupilsightSchoolYearID = $_REQUEST['pupilsightSchoolYearID'] ?? '';
    $pattern = $_POST['pattern'] ?? '';

    $page->breadcrumbs
        ->add(__('Sync Course Enrolment'), 'courseEnrolment_sync.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Map Classes'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if (empty($pupilsightYearGroupID) || empty($pupilsightSchoolYearID)) {
        echo "<div class='alert alert-danger'>";
        echo __('Your request failed because your inputs were invalid.');
        echo '</div>';
        return;
    }

    $form = Form::create('courseEnrolmentSyncEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_sync_addEditProcess.php');
    $form->setClass('w-full blank');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);
    $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

    if (!empty($pattern)) {
        // Allows for Roll Group naming patterns with different formats
        $subQuery = "(SELECT syncBy.pupilsightRollGroupID FROM pupilsightRollGroup AS syncBy WHERE REPLACE(REPLACE(REPLACE(REPLACE(:pattern, '[courseShortName]', pupilsightCourse.nameShort), '[classShortName]', pupilsightCourseClass.nameShort), '[yearGroupShortName]', pupilsightYearGroup.nameShort), '[rollGroupShortName]', nameShort) LIKE CONCAT('%', syncBy.nameShort) AND syncBy.pupilsightSchoolYearID=:pupilsightSchoolYearID LIMIT 1)";

        // Grab courses by year group, optionally match to a pattern
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pattern' => $pattern);
        $sql = "SELECT pupilsightCourse.name as courseName, pupilsightCourse.pupilsightCourseID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.name as courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourseClass.nameShort as classShortName, pupilsightYearGroup.nameShort as yearGroupShortName,
                $subQuery as syncTo
                FROM pupilsightCourse
                JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightYearGroupID=:pupilsightYearGroupID)
                WHERE FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightCourse.pupilsightYearGroupIDList)
                AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID
                GROUP BY pupilsightCourseClass.pupilsightCourseClassID
                ORDER BY pupilsightCourse.nameShort, pupilsightCourseClass.nameShort
                ";
        $result = $pdo->executeQuery($data, $sql);
    } else {
        // Grab courses by year group, pull in existing mapped classes
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
        $sql = "SELECT pupilsightCourse.name as courseName, pupilsightCourseClassMap.pupilsightRollGroupID as syncTo,  pupilsightCourse.pupilsightCourseID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.name as courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourseClass.nameShort as classShortName, pupilsightYearGroup.nameShort as yearGroupShortName
                FROM pupilsightCourseClass
                JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                JOIN pupilsightYearGroup ON (FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightCourse.pupilsightYearGroupIDList))
                LEFT JOIN pupilsightCourseClassMap ON (pupilsightCourseClassMap.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND pupilsightCourseClassMap.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                WHERE pupilsightYearGroup.pupilsightYearGroupID=:pupilsightYearGroupID
                AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID
                GROUP BY pupilsightCourseClass.pupilsightCourseClassID
                ORDER BY pupilsightCourse.name, pupilsightCourseClass.nameShort
                ";
        $result = $pdo->executeQuery($data, $sql);
    }

    if ($result->rowCount() > 0) {
        $classesGroupedByCourse = $result->fetchAll(PDO::FETCH_GROUP);

        foreach ($classesGroupedByCourse as $courseName => $classes) {
            $course = current($classes);
            $optionsSelected = array_filter($classes, function ($item) {
                return !empty($item['syncTo']);
            });

            $form->addRow()->addHeading($courseName);
            $table = $form->addRow()->addTable()->setClass('smallIntBorder colorOddEven fullWidth standardForm');

            $header = $table->addHeaderRow();
                $header->addCheckbox('checkall'.$course['pupilsightCourseID'])->checked(!empty($optionsSelected))->setClass();
                $header->addContent(__('Class'));
                $header->addContent('');
                $header->addContent(__('Roll Group'));

            foreach ($classes as $class) {
                $row = $table->addRow();
                    $row->addCheckbox('syncEnabled['.$class['pupilsightCourseClassID'].']')
                        ->checked(!empty($class['syncTo']))
                        ->setClass($course['pupilsightCourseID'])
                        ->description('&nbsp;&nbsp;');
                    $row->addLabel('syncEnabled['.$class['pupilsightCourseClassID'].']', $class['courseNameShort'].'.'.$class['classShortName'])
                        ->setTitle($class['courseNameShort'])
                        ->setClass('mediumWidth');
                    $row->addContent((empty($class['syncTo'])? '<em>'.__('No match found').'</em>' : '') )
                        ->setClass('shortWidth right');
                    $row->addSelectRollGroup('syncTo['.$class['pupilsightCourseClassID'].']', $pupilsightSchoolYearID)
                        ->selected($class['syncTo'])
                        ->setClass('mediumWidth');
            }

            // Checkall by course
            echo '<script type="text/javascript">';
            echo '$(function () {';
                echo "$('#checkall".$course['pupilsightCourseID']."').click(function () {";
                echo "$('.".$course['pupilsightCourseID']."').find(':checkbox').attr('checked', this.checked);";
                echo '});';
            echo '});';
            echo '</script>';
        }
    }

    $table = $form->addRow()->addTable()->setClass('smallIntBorder colorOddEven fullWidth standardForm');

    $row = $table->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
