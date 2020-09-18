<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_attendance.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightActivityID = $_GET['pupilsightActivityID'];
    try {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID);
        $sql = "SELECT name, programStart, programEnd, pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightRollGroupID, pupilsightActivityStudent.status FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID ORDER BY pupilsightActivityStudent.status, surname, preferredName";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    $row = $result->fetch();

    if ($pupilsightActivityID != '') {
        $output = '';

        $date = '';
        if (substr($row['programStart'], 0, 4) == substr($row['programEnd'], 0, 4)) {
            if (substr($row['programStart'], 5, 2) == substr($row['programEnd'], 5, 2)) {
                $date = ' ('.date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))).' '.substr($row['programStart'], 0, 4).')';
            } else {
                $date = ' ('.date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))).' - '.date('F', mktime(0, 0, 0, substr($row['programEnd'], 5, 2))).' '.substr($row['programStart'], 0, 4).')';
            }
        } else {
            $date = ' ('.date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))).' '.substr($row['programStart'], 0, 4).' - '.date('F', mktime(0, 0, 0, substr($row['programEnd'], 5, 2))).' '.substr($row['programEnd'], 0, 4).')';
        }

        echo '<h2>';
        echo __('Participants for').' '.$row['name'].$date;
        echo '</h2>';

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            echo "<div class='linkTop'>";
            echo "<a href='javascript:window.print()'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
            echo '</div>';

            $lastPerson = '';

            echo "<table class='mini' cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo __('Student');
            echo '</th>';
            echo '<th colspan=15>';
            echo __('Attendance');
            echo '</th>';
            echo '</tr>';
            echo "<tr style='height: 75px' class='odd'>";
            echo "<td style='vertical-align:top; width: 120px'>Date</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>1</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>2</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>3</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>4</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>5</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>6</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>7</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>8</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>9</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>10</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>11</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>12</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>13</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>14</td>";
            echo "<td style='color: #bbb; vertical-align:top; width: 15px'>15</td>";
            echo '</tr>';

            $count = 0;
            $rowNum = 'odd';
            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID);
                $sql = "SELECT name, programStart, programEnd, pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightRollGroupID, pupilsightActivityStudent.status FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID ORDER BY pupilsightActivityStudent.status, surname, preferredName";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
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
                echo $count.'. '.formatName('', $row['preferredName'], $row['surname'], 'Student', true);
                echo '</td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '</tr>';

                $lastPerson = $row['pupilsightPersonID'];
            }
            if ($count == 0) {
                echo "<tr class=$rowNum>";
                echo '<td colspan=16>';
                echo __('There are no records to display.');
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}
