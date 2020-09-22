<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\View\GridView;
use Pupilsight\Tables\Prefab\ClassGroupTable;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$makeDepartmentsPublic = getSettingByScope($connection2, 'Departments', 'makeDepartmentsPublic');
if (isActionAccessible($guid, $connection2, '/modules/Departments/department_course_class.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
    $pupilsightCourseID = $_GET['pupilsightCourseID'] ?? '';
    $pupilsightDepartmentID = $_GET['pupilsightDepartmentID'] ?? '';

    if (empty($pupilsightCourseClassID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
    } else {
        if (!empty($pupilsightDepartmentID)) {
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = "SELECT pupilsightCourse.pupilsightSchoolYearID,pupilsightDepartment.name AS department, pupilsightCourse.name AS courseLong, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourse.pupilsightCourseID, pupilsightSchoolYear.name AS year, pupilsightCourseClass.attendance 
                    FROM pupilsightCourse 
                    JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) 
                    JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) 
                    JOIN pupilsightDepartment ON (pupilsightDepartment.pupilsightDepartmentID=pupilsightCourse.pupilsightDepartmentID) 
                    WHERE pupilsightCourseClassID=:pupilsightCourseClassID";
        } else {
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = "SELECT pupilsightCourse.pupilsightSchoolYearID, pupilsightCourse.name AS courseLong, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourse.pupilsightCourseID, pupilsightSchoolYear.name AS year, pupilsightCourseClass.attendance 
                    FROM pupilsightCourse 
                    JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) 
                    JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) 
                    WHERE pupilsightCourseClassID=:pupilsightCourseClassID";
        }

        $row = $pdo->selectOne($sql, $data);

        if (empty($row)) {
            $page->addError(__('The specified record does not exist.'));
        } else {
            //Get role within learning area
            $role = null;
            if ($pupilsightDepartmentID != '' and isset($_SESSION[$guid]['username'])) {
                $role = getRole($_SESSION[$guid]['pupilsightPersonID'], $pupilsightDepartmentID, $connection2);
            }

            $extra = '';
            if (($role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)' or $role == 'Teacher') and $row['pupilsightSchoolYearID'] != $_SESSION[$guid]['pupilsightSchoolYearID']) {
                $extra = ' '.$row['year'];
            }
            if ($pupilsightDepartmentID != '') {
                
                $urlParams = ['pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightCourseID' => $pupilsightCourseID];
                $page->breadcrumbs
                    ->add(__('View All'), 'departments.php')
                    ->add($row['department'], 'department.php', $urlParams)
                    ->add($row['courseLong'].$extra, 'department_course.php', $urlParams)
                    ->add(Format::courseClassName($row['course'], $row['class']));
            } else {
                $page->breadcrumbs
                    ->add(__('View All'), 'departments.php')
                    ->add(Format::courseClassName($row['course'], $row['class']));
            }

            // CHECK & STORE WHAT TO DISPLAY
            $menuItems = [];

            // Attendance
            if ($row['attendance'] == 'Y' && isActionAccessible($guid, $connection2, "/modules/Attendance/attendance_take_byCourseClass.php")) {
                $menuItems[] = [
                    'name' => __('Attendance'),
                    'url'  => './index.php?q=/modules/Attendance/attendance_take_byCourseClass.php&pupilsightCourseClassID='.$pupilsightCourseClassID,
                    'icon' => 'attendance_large.png',
                ];
            }
            // Planner
            if (isActionAccessible($guid, $connection2, '/modules/Planner/planner.php')) {
                $menuItems[] = [
                    'name' => __('Planner'),
                    'url'  => './index.php?q=/modules/Planner/planner.php&pupilsightCourseClassID='.$pupilsightCourseClassID.'&viewBy=class',
                    'icon' => 'planner_large.png',
                ];
            }
            // Markbook
            if (getHighestGroupedAction($guid, '/modules/Markbook/markbook_view.php', $connection2) == 'View Markbook_allClassesAllData') {
                $menuItems[] = [
                    'name' => __('Markbook'),
                    'url'  => './index.php?q=/modules/Markbook/markbook_view.php&pupilsightCourseClassID='.$pupilsightCourseClassID,
                    'icon' => 'markbook_large.png',
                ];
            }
            // Homework
            if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_deadlines.php')) {
                $menuItems[] = [
                    'name' => __('Homework'),
                    'url'  => './index.php?q=/modules/Planner/planner_deadlines.php&pupilsightCourseClassIDFilter='.$pupilsightCourseClassID,
                    'icon' => 'homework_large.png',
                ];
            }
            // Internal Assessment
            if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_write.php')) {
                $menuItems[] = [
                    'name' => __('Internal Assessment'),
                    'url'  => './index.php?q=/modules/Formal Assessment/internalAssessment_write.php&pupilsightCourseClassID='.$pupilsightCourseClassID,
                    'icon' => 'internalAssessment_large.png',
                ];
            }

            // Menu Items Table
            $gridRenderer = new GridView($container->get('twig'));
            $table = $container->get(DataTable::class)->setRenderer($gridRenderer);
            $table->setTitle(Format::courseClassName($row['course'], $row['class']));
            $table->setDescription(__('Course').': '.$row['courseLong']);

            $table->addMetaData('gridClass', 'rounded-sm bg-gray border py-2');
            $table->addMetaData('gridItemClass', 'w-1/2 sm:w-1/3 p-4 text-center');

            $iconPath = $_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/';
            $table->addColumn('icon')
                ->format(function ($menu) use ($iconPath) {
                    $img = sprintf('<img src="%1$s" title="%2$s" class="w-24 sm:w-32 px-4 pb-2">', $iconPath.$menu['icon'], $menu['name']);
                    return Format::link($menu['url'], $img);
                });

            $table->addColumn('name')
                ->setClass('font-bold text-xs')
                ->format(function ($menu) {
                    return Format::link($menu['url'], $menu['name']);
                });

            echo $table->render(new DataSet($menuItems));

            // Participants
            if (!empty($menuItems)) {
                $table = $container->get(ClassGroupTable::class);
                $table->build($pupilsight->session->get('pupilsightSchoolYearID'), $pupilsightCourseClassID);

                echo $table->getOutput();
            }

            //Print sidebar
            if (isset($_SESSION[$guid]['username'])) {
                $sidebarExtra = '';

                //Print related class list
                try {
                    $dataCourse = array('pupilsightCourseID' => $row['pupilsightCourseID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sqlCourse = 'SELECT pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourse.pupilsightCourseID=:pupilsightCourseID AND pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY class';
                    $resultCourse = $connection2->prepare($sqlCourse);
                    $resultCourse->execute($dataCourse);
                } catch (PDOException $e) {
                    $sidebarExtra .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultCourse->rowCount() > 0) {
                    $sidebarExtra .= '<div class="column-no-break">';
                    $sidebarExtra .= '<h2>';
                    $sidebarExtra .= __('Related Classes');
                    $sidebarExtra .= '</h2>';

                    $sidebarExtra .= '<ul>';
                    while ($rowCourse = $resultCourse->fetch()) {
                        $sidebarExtra .= "<li><a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Departments/department_course_class.php&pupilsightDepartmentID=$pupilsightDepartmentID&pupilsightCourseID=".$row['pupilsightCourseID'].'&pupilsightCourseClassID='.$rowCourse['pupilsightCourseClassID']."'>".$rowCourse['course'].'.'.$rowCourse['class'].'</a></li>';
                    }
                    $sidebarExtra .= '</ul>';
                    $sidebarExtra .= '</div>';
                }

                //Print list of all classes
                $sidebarExtra .= '<div class="column-no-break">';

                $form = Form::create('classSelect', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
                $form->setTitle(__('Current Classes'));
                $form->setClass('smallIntBorder w-full');

                $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/department_course_class.php');
                
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name 
                        FROM pupilsightCourse 
                        JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) 
                        WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID 
                        ORDER BY pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";

                $row = $form->addRow();
                    $row->addSelect('pupilsightCourseClassID')
                        ->fromQuery($pdo, $sql, $data)
                        ->selected($pupilsightCourseClassID)
                        ->placeholder()
                        ->setClass('fullWidth');
                    $row->addSubmit(__('Go'));
                
                $sidebarExtra .= $form->getOutput();
                $sidebarExtra .= '</div>';

                $_SESSION[$guid]['sidebarExtra'] .= $sidebarExtra;
            }
        }
    }
}
