<?php
/*
Pupilsight, Flexible & Open School System
*/

if (isActionAccessible($guid, $connection2, '/modules/Timetable/studentEnrolment_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Student Enrolment'));

    echo '<p>';
    echo __('This page allows departmental Coordinators and Assistant Coordinators to manage student enolment within their department.');
    echo '</p>';

    try {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        $sql = "SELECT pupilsightCourse.* FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE (role='Coordinator' OR role='Assistant Coordinator') AND pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY pupilsightCourse.nameShort";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        while ($row = $result->fetch()) {
            echo '<h3>';
            echo $row['nameShort'].' ('.$row['name'].')';
            echo '</h3>';

            try {
                $dataClass = array('pupilsightCourseID' => $row['pupilsightCourseID']);
                $sqlClass = 'SELECT * FROM pupilsightCourseClass WHERE pupilsightCourseID=:pupilsightCourseID ORDER BY name';
                $resultClass = $connection2->prepare($sqlClass);
                $resultClass->execute($dataClass);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultClass->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                echo "<table cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo __('Name');
                echo '</th>';
                echo '<th>';
                echo __('Short Name');
                echo '</th>';
                echo '<th>';
                echo __('Participants').'<br/>';
                echo "<span style='font-size: 85%; font-style: italic'>".__('Active').'</span>';
                echo '</th>';
                echo '<th>';
                echo 'Participants<br/>';
                echo "<span style='font-size: 85%; font-style: italic'>".__('Expected').'</span>';
                echo '</th>';
                echo '<th>';
                echo 'Participants<br/>';
                echo "<span style='font-size: 85%; font-style: italic'>".__('Total').'</span>';
                echo '</th>';
                echo "<th style='width: 55px'>";
                echo __('Actions');
                echo '</th>';
                echo '</tr>';

                $count = 0;
                $rowNum = 'odd';
                while ($rowClass = $resultClass->fetch()) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                    echo '<td>';
                    echo $rowClass['name'];
                    echo '</td>';
                    echo '<td>';
                    echo $rowClass['nameShort'];
                    echo '</td>';
                    echo '<td>';
                    $total = 0;
                    $active = 0;
                    $expected = 0;
                    try {
                        $dataClasses = array('pupilsightCourseClassID' => $rowClass['pupilsightCourseClassID']);
                        $sqlClasses = "SELECT pupilsightCourseClassPerson.* FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND pupilsightCourseClassID=:pupilsightCourseClassID AND (NOT role='Student - Left') AND (NOT role='Teacher - Left')";
                        $resultClasses = $connection2->prepare($sqlClasses);
                        $resultClasses->execute($dataClasses);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultClasses->rowCount() >= 0) {
                        $active = $resultClasses->rowCount();
                    }

                    try {
                        $dataClasses = array('pupilsightCourseClassID' => $rowClass['pupilsightCourseClassID']);
                        $sqlClasses = "SELECT pupilsightCourseClassPerson.* FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Expected' AND pupilsightCourseClassID=:pupilsightCourseClassID AND (NOT role='Student - Left') AND (NOT role='Teacher - Left')";
                        $resultClasses = $connection2->prepare($sqlClasses);
                        $resultClasses->execute($dataClasses);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultClasses->rowCount() >= 0) {
                        $expected = $resultClasses->rowCount();
                    }
                    echo $active;
                    echo '</td>';
                    echo '<td>';
                    echo $expected;
                    echo '</td>';
                    echo '<td>';
                    echo '<b>'.($active + $expected).'<b/> ';
                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/studentEnrolment_manage_edit.php&pupilsightCourseClassID='.$rowClass['pupilsightCourseClassID'].'&pupilsightCourseID='.$row['pupilsightCourseID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                    echo '</td>';
                    echo '</tr>';

                    ++$count;
                }
                echo '</table>';
            }
        }
    }
}
