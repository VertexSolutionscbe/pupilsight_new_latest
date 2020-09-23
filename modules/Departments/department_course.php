<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$makeDepartmentsPublic = getSettingByScope($connection2, 'Departments', 'makeDepartmentsPublic');
if (isActionAccessible($guid, $connection2, '/modules/Departments/department_course.php') == false and $makeDepartmentsPublic != 'Y') {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
    $pupilsightCourseID = $_GET['pupilsightCourseID'];
    if ($pupilsightDepartmentID == '' or $pupilsightCourseID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightCourseID' => $pupilsightCourseID);
            $sql = 'SELECT pupilsightDepartment.name AS department, pupilsightCourse.name, pupilsightCourse.description, pupilsightSchoolYear.name AS year, pupilsightCourse.pupilsightSchoolYearID FROM pupilsightDepartment JOIN pupilsightCourse ON (pupilsightDepartment.pupilsightDepartmentID=pupilsightCourse.pupilsightDepartmentID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightDepartment.pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightCourseID=:pupilsightCourseID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();

            //Get role within learning area
            $role = null;
            if (isset($_SESSION[$guid]['username'])) {
                $role = getRole($_SESSION[$guid]['pupilsightPersonID'], $pupilsightDepartmentID, $connection2);
            }

            $extra = '';
            if (($role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)' or $role == 'Teacher') and $row['pupilsightSchoolYearID'] != $_SESSION[$guid]['pupilsightSchoolYearID']) {
                $extra = ' '.$row['year'];
            }

            $urlParams = ['pupilsightDepartmentID' => $pupilsightDepartmentID];

            $page->breadcrumbs
                ->add(__('View All'), 'departments.php')
                ->add($row['department'], 'department.php', $urlParams)
                ->add($row['name'].$extra);

            //Print overview
            if ($row['description'] != '' or $role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)') {
                echo '<h2>';
                echo __('Overview');
                if ($role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)') {
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/department_course_edit.php&pupilsightCourseID=$pupilsightCourseID&pupilsightDepartmentID=$pupilsightDepartmentID'><img style='margin-left: 5px' title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                }
                echo '</h2>';
                echo '<p>';
                echo $row['description'];
                echo '</p>';
            }

            //Print Units
            echo '<h2>';
            echo __('Units');
            echo '</h2>';

            try {
                $dataUnit = array('pupilsightCourseID' => $pupilsightCourseID);
                $sqlUnit = 'SELECT pupilsightUnitID, pupilsightUnit.name, pupilsightUnit.description, attachment FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightUnit.pupilsightCourseID=:pupilsightCourseID AND active=\'Y\' ORDER BY ordering, name';
                $resultUnit = $connection2->prepare($sqlUnit);
                $resultUnit->execute($dataUnit);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            while ($rowUnit = $resultUnit->fetch()) {
                echo '<h4>';
                echo $rowUnit['name'];
                echo '</h4>';
                echo '<p>';
                echo $rowUnit['description'];
                if ($rowUnit['attachment'] != '') {
                    echo "<br/><br/><a href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowUnit['attachment']."'>".__('Download Unit Outline').'</a></li>';
                }
                echo '</p>';
            }

            //Print sidebar
            $sidebarExtra = '';

            if (isActionAccessible($guid, $connection2, '/modules/Departments/department_course_class.php')) {
                //Print class list
                try {
                    $dataCourse = array('pupilsightCourseID' => $pupilsightCourseID);
                    $sqlCourse = 'SELECT pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourse.pupilsightCourseID=:pupilsightCourseID ORDER BY class';
                    $resultCourse = $connection2->prepare($sqlCourse);
                    $resultCourse->execute($dataCourse);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultCourse->rowCount() > 0) {
                    $sidebarExtra .= '<div class="column-no-break">';
                    $sidebarExtra .= '<h2>';
                    $sidebarExtra .= __('Class List');
                    $sidebarExtra .= '</h2>';

                    $sidebarExtra .= '<ul>';
                    while ($rowCourse = $resultCourse->fetch()) {
                        $sidebarExtra .= "<li><a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Departments/department_course_class.php&pupilsightDepartmentID=$pupilsightDepartmentID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=".$rowCourse['pupilsightCourseClassID']."'>".$rowCourse['course'].'.'.$rowCourse['class'].'</a></li>';
                    }
                    $sidebarExtra .= '</ul>';
                    $sidebarExtra .= '</div>';

                    $_SESSION[$guid]['sidebarExtra'] = $sidebarExtra;
                }
            }
        }
    }
}
