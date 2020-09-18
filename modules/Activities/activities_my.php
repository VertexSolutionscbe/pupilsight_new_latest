<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_my.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('My Activities')); 

    try {
        $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sql = "(SELECT pupilsightActivity.*, pupilsightActivityStudent.status, NULL AS role FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID) WHERE pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y') UNION (SELECT pupilsightActivity.*, NULL as status, pupilsightActivityStaff.role FROM pupilsightActivity JOIN pupilsightActivityStaff ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStaff.pupilsightActivityID) WHERE pupilsightActivityStaff.pupilsightPersonID=:pupilsightPersonID2 AND pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND active='Y') ORDER BY name";
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
        $highestAction = getHighestGroupedAction($guid, '/modules/Activities/activities_attendance.php', $connection2);
        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Activity');
        echo '</th>';
        $options = getSettingByScope($connection2, 'Activities', 'activityTypes');
        if ($options != '') {
            echo '<th>';
            echo __('Type');
            echo '</th>';
        }
        echo '<th>';
        echo __('Role');
        echo '</th>';
        echo '<th>';
        echo __('Status');
        echo '</th>';
        echo '<th>';
        echo __('Actions');
        echo '</th>';
        echo '</tr>';

        $count = 0;
        $rowNum = 'odd';
        while ($row = $result->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }

            ++$count;

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo $row['name'];
            echo '</td>';
            if ($options != '') {
                echo '<td>';
                echo trim($row['type']);
                echo '</td>';
            }
            echo '<td>';
            if ($row['role'] == '') {
                echo 'Student';
            } else {
                echo __($row['role']);
            }
            echo '</td>';
            echo '<td>';
            if ($row['status'] != '') {
                echo $row['status'];
            } else {
                echo '<i>'.__('NA').'</i>';
            }
            echo '</td>';
            echo '<td>';
            if ($row['role'] == 'Organiser' && isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_enrolment.php')) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/activities_manage_enrolment.php&pupilsightActivityID='.$row['pupilsightActivityID']."&search=&pupilsightSchoolYearTermID='><img title='".__('Enrolment')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
            }

            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/activities_my_full.php&pupilsightActivityID='.$row['pupilsightActivityID']."&width=1000&height=550'><img title='".__('View Details')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";

            if ($highestAction == "Enter Activity Attendance" || ($highestAction == "Enter Activity Attendance_leader" && ($row['role'] == 'Organiser' || $row['role'] == 'Assistant' || $row['role'] == 'Coach'))) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/activities_attendance.php&pupilsightActivityID='.$row['pupilsightActivityID']."'><img title='".__('Attendance')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/attendance.png'/></a> ";
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
