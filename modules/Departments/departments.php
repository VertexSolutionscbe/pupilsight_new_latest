<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\View\GridView;
use Pupilsight\Domain\DataSet;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$makeDepartmentsPublic = getSettingByScope($connection2, 'Departments', 'makeDepartmentsPublic');
if (isActionAccessible($guid, $connection2, '/modules/Departments/departments.php') == false and $makeDepartmentsPublic != 'Y') {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('View All'));

    // Data Table
    $gridRenderer = new GridView($container->get('twig'));
    $table = $container->get(DataTable::class)->setRenderer($gridRenderer);
    $table->setTitle(__('Departments'));

    $table->addColumn('logo')
        ->format(function ($department) {
            return Format::userPhoto($department['logo'], 125, 'w-20 h-20 sm:w-32 sm:h-32 p-1');
        });

    $table->addColumn('name')
        ->setClass('text-xs font-bold mt-1 mb-4')
        ->format(function ($department) {
            $url = "./index.php?q=/modules/Departments/department.php&pupilsightDepartmentID=".$department['pupilsightDepartmentID'];
            return Format::link($url, $department['name']);
        });

    // Learning Areas
    $sql = "SELECT * FROM pupilsightDepartment WHERE type='Learning Area' ORDER BY name";
    $learningAreas = $pdo->select($sql)->toDataSet();

    if (count($learningAreas) > 0) {
        $tableLA = clone $table;
        $tableLA->setTitle(__('Learning Areas'));
        
        echo $tableLA->render($learningAreas);
    }
    
    // Administration
    $sql = "SELECT * FROM pupilsightDepartment WHERE type='Administration' ORDER BY name";
    $administration = $pdo->select($sql)->toDataSet();

    if (count($administration) > 0) {
        $tableAdmin = clone $table;
        $tableAdmin->setTitle(__('Administration'));

        echo $tableAdmin->render($administration);
    }

    if (count($learningAreas) == 0 && count($administration) == 0) {
        echo $table->render(new DataSet([]));
    }

    if (isset($_SESSION[$guid]['username'])) {
        //Print sidebar
        $sidebarExtra = '';

        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE \'% - Left%\' ORDER BY course, class';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() > 0) {
            $sidebarExtra .= '<div class="column-no-break">';
            $sidebarExtra .= "<h2 class='sidebar'>";
            $sidebarExtra .= __('My Classes');
            $sidebarExtra .= '</h2>';

            $sidebarExtra .= '<ul>';
            while ($row = $result->fetch()) {
                $sidebarExtra .= "<li><a href='index.php?q=/modules/Departments/department_course_class.php&pupilsightCourseClassID=".$row['pupilsightCourseClassID']."'>".$row['course'].'.'.$row['class'].'</a></li>';
            }
            $sidebarExtra .= '</ul>';
            $sidebarExtra .= '</div>';

            $_SESSION[$guid]['sidebarExtra'] = $sidebarExtra;
        }
    }
}
