<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_attendance.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Attendance History by Activity')); 

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h2>';
    echo __('Choose Activity');
    echo '</h2>';

    $pupilsightActivityID = null;
    if (isset($_GET['pupilsightActivityID'])) {
        $pupilsightActivityID = $_GET['pupilsightActivityID'];
    }
    $allColumns = (isset($_GET['allColumns'])) ? $_GET['allColumns'] : false;

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_attendance.php");

    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
    $sql = "SELECT pupilsightActivityID AS value, name FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name, programStart";
    $row = $form->addRow();
        $row->addLabel('pupilsightActivityID', __('Activity'));
        $row->addSelect('pupilsightActivityID')->fromQuery($pdo, $sql, $data)->selected($pupilsightActivityID)->required()->placeholder();

    $row = $form->addRow();
        $row->addLabel('allColumns', __('All Columns'))->description(__('Include empty columns with unrecorded attendance.'));
        $row->addCheckbox('allColumns')->checked($allColumns);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    // Cancel out early if we have no pupilsightActivityID
    if (empty($pupilsightActivityID)) {
        return;
    }

    try {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID);
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightRollGroupID, pupilsightActivityStudent.status FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivityID=:pupilsightActivityID ORDER BY pupilsightActivityStudent.status, surname, preferredName";
        $studentResult = $connection2->prepare($sql);
        $studentResult->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    try {
        $data = array('pupilsightActivityID' => $pupilsightActivityID);
        $sql = "SELECT pupilsightSchoolYearTermIDList, maxParticipants, programStart, programEnd, (SELECT COUNT(*) FROM pupilsightActivityStudent JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID AND pupilsightActivityStudent.status='Waiting List' AND pupilsightPerson.status='Full') AS waiting FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID";
        $activityResult = $connection2->prepare($sql);
        $activityResult->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($studentResult->rowCount() < 1 || $activityResult->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';

        return;
    }

    try {
        $data = array('pupilsightActivityID' => $pupilsightActivityID);
        $sql = 'SELECT pupilsightActivityAttendance.date, pupilsightActivityAttendance.timestampTaken, pupilsightActivityAttendance.attendance, pupilsightPerson.preferredName, pupilsightPerson.surname FROM pupilsightActivityAttendance, pupilsightPerson WHERE pupilsightActivityAttendance.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightActivityAttendance.pupilsightActivityID=:pupilsightActivityID';
        $attendanceResult = $connection2->prepare($sql);
        $attendanceResult->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    // Gather the existing attendance data (by date and not index, should the time slots change)
    $sessionAttendanceData = array();

    while ($attendance = $attendanceResult->fetch()) {
        $sessionAttendanceData[ $attendance['date'] ] = array(
            'data' => (!empty($attendance['attendance'])) ? unserialize($attendance['attendance']) : array(),
            'info' => sprintf(__('Recorded at %1$s on %2$s by %3$s.'), substr($attendance['timestampTaken'], 11), dateConvertBack($guid, substr($attendance['timestampTaken'], 0, 10)), formatName('', $attendance['preferredName'], $attendance['surname'], 'Staff', false, true)),
        );
    }

    $activity = $activityResult->fetch();
    $activity['participants'] = $studentResult->rowCount();

    // Get the week days that match time slots for this activity
    $activityWeekDays = getActivityWeekdays($connection2, $pupilsightActivityID);

    // Get the start and end date of the activity, depending on which dateType we're using
    $activityTimespan = getActivityTimespan($connection2, $pupilsightActivityID, $activity['pupilsightSchoolYearTermIDList']);

    // Use the start and end date of the activity, along with time slots, to get the activity sessions
    $activitySessions = getActivitySessions(($allColumns) ? $activityWeekDays : array(), $activityTimespan, $sessionAttendanceData);

    echo '<h2>';
    echo __('Activity');
    echo '</h2>';

    echo "<table class='table'><tbody>";
    echo '<tr>';
    echo "<td style='width: 33%; vertical-align: top'>";
    echo "<span class='infoTitle'>".__('Start Date').'</span><br>';
    if (!empty($activityTimespan['start'])) {
        echo date($_SESSION[$guid]['i18n']['dateFormatPHP'], $activityTimespan['start']);
    }
    echo '</td>';

    echo "<td style='width: 33%; vertical-align: top'>";
    echo "<span class='infoTitle'>".__('End Date').'</span><br>';
    if (!empty($activityTimespan['end'])) {
        echo date($_SESSION[$guid]['i18n']['dateFormatPHP'], $activityTimespan['end']);
    }
    echo '</td>';

    echo "<td style='width: 33%; vertical-align: top'>";
    printf("<span class='infoTitle' title=''>%s</span><br>%s", __('Number of Sessions'), count($activitySessions));
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo "<td style='width: 33%; vertical-align: top'>";
    printf("<span class='infoTitle'>%s</span><br>%s", __('Participants'), $activity['participants']);
    echo '</td>';

    echo "<td style='width: 33%; vertical-align: top'>";
    printf("<span class='infoTitle'>%s</span><br>%s", __('Maximum Participants'), $activity['maxParticipants']);
    echo '</td>';

    echo "<td style='width: 33%; vertical-align: top'>";
    printf("<span class='infoTitle' title=''>%s</span><br>%s", __('Waiting'), $activity['waiting']);
    echo '</td>';
    echo '</tr>';
    echo '</tbody></table>';

    echo '<h2>';
    echo __('Attendance');
    echo '</h2>';

    if ($allColumns == false && $attendanceResult->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';

        return;
    }

    if (empty($activityWeekDays) || empty($activityTimespan)) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no time slots assigned to this activity, or the start and end dates are invalid. New attendance values cannot be entered until the time slots and dates are added.');
        echo '</div>';
    }

    if (count($activitySessions) <= 0) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        if (isActionAccessible($guid, $connection2, '/modules/Activities/report_attendanceExport.php')) {
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/report_attendanceExport.php?pupilsightActivityID='.$pupilsightActivityID."'>".__('Export to Excel')."<img style='margin-left: 5px' title='".__('Export to Excel')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/download.png'/></a>";
            echo '</div>';
        }

        echo "<div class='doublescroll-wrapper'>";

        echo "<table class='mini' cellspacing='0' style='width:100%; border: 0; margin:0;'>";
        echo "<tr class='head' style='height:60px; '>";
        echo "<th style='width:175px;'>";
        echo __('Student');
        echo '</th>';
        echo '<th>';
        echo __('Attendance');
        echo '</th>';
        echo "<th class='emphasis subdued' style='text-align:right'>";
        printf(__('Sessions Recorded: %s of %s'), count($sessionAttendanceData), count($activitySessions));
        echo '</th>';
        echo '</tr>';
        echo '</table>';
        echo "<div class='doublescroll-top'><div class='doublescroll-top-tablewidth'></div></div>";

        $columnCount = ($allColumns) ? count($activitySessions) : count($sessionAttendanceData);

        echo "<div class='doublescroll-container'>";
        echo "<table class='mini colorOddEven' cellspacing='0' style='width: ".($columnCount * 56)."px'>";

        echo "<tr style='height: 55px'>";
        echo "<td style='vertical-align:top;height:55px;'>".__('Date').'</td>';

        foreach ($activitySessions as $sessionDate => $sessionTimestamp) {
            if (isset($sessionAttendanceData[$sessionDate]['data'])) {
                // Handle instances where the time slot has been deleted after creating an attendance record
                        if (!in_array(date('D', $sessionTimestamp), $activityWeekDays) || ($sessionTimestamp < $activityTimespan['start']) || ($sessionTimestamp > $activityTimespan['end'])) {
                            echo "<td style='vertical-align:top; width: 45px;' class='warning' title='".__('Does not match the time slots for this activity.')."'>";
                        } else {
                            echo "<td style='vertical-align:top; width: 45px;'>";
                        }

                printf("<span title='%s'>%s</span><br/>&nbsp;<br/>", $sessionAttendanceData[$sessionDate]['info'], Format::dateReadable($sessionDate, '%a <br /> %b %e'));
            } else {
                echo "<td style='color: #bbb; vertical-align:top; width: 45px;'>";
                echo Format::dateReadable($sessionDate, '%a <br /> %b %e').'<br/>&nbsp;<br/>';
            }
            echo '</td>';
        }

        echo '</tr>';

        $count = 0;
        // Build an empty array of attendance count data for each session
        $attendanceCount = array_combine(array_keys($activitySessions), array_fill(0, count($activitySessions), 0));

        while ($row = $studentResult->fetch()) {
            ++$count;
            $student = $row['pupilsightPersonID'];

            echo "<tr data-student='$student'>";
            echo '<td>';
            echo $count.'. '.formatName('', $row['preferredName'], $row['surname'], 'Student', true);
            echo '</td>';

            foreach ($activitySessions as $sessionDate => $sessionTimestamp) {
                echo "<td class='col'>";
                if (isset($sessionAttendanceData[$sessionDate]['data'])) {
                    if (isset($sessionAttendanceData[$sessionDate]['data'][$student])) {
                        echo '???';
                        $attendanceCount[$sessionDate]++;
                    }
                }
                echo '</td>';
            }

            echo '</tr>';

            $lastPerson = $row['pupilsightPersonID'];
        }

            // Output a total attendance per column
            echo '<tr>';
        echo "<td class='right'>";
        echo __('Total students:');
        echo '</td>';

        foreach ($activitySessions as $sessionDate => $sessionTimestamp) {
            echo '<td>';
            if (!empty($attendanceCount[$sessionDate])) {
                echo $attendanceCount[$sessionDate].' / '.$activity['participants'];
            }
            echo '</td>';
        }

        echo '</tr>';

        if ($count == 0) {
            echo "<tr class=$rowNum>";
            echo '<td colspan=16>';
            echo __('There are no records to display.');
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</div>';
        echo '</div><br/>';
    }
}

?>
