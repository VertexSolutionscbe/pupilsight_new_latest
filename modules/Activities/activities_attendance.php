<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_attendance.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Enter Activity Attendance'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h2>';
    echo __('Choose Activity');
    echo '</h2>';

    $highestAction = getHighestGroupedAction($guid, '/modules/Activities/activities_attendance.php', $connection2);
    $pupilsightActivityID = null;
    if (isset($_GET['pupilsightActivityID'])) {
        $pupilsightActivityID = $_GET['pupilsightActivityID'];
    }

    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);

    $sql = "";
    if($highestAction == "Enter Activity Attendance") {
        $sql = "SELECT pupilsightActivity.pupilsightActivityID AS value, name, programStart  FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name, programStart";
    } elseif($highestAction == "Enter Activity Attendance_leader") {
        $data["pupilsightPersonID"] = $_SESSION[$guid]["pupilsightPersonID"];
        $sql = "SELECT pupilsightActivity.pupilsightActivityID AS value, name, programStart FROM pupilsightActivityStaff JOIN pupilsightActivity ON (pupilsightActivityStaff.pupilsightActivityID = pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND pupilsightActivityStaff.pupilsightPersonID=:pupilsightPersonID AND (pupilsightActivityStaff.role='Organiser' OR pupilsightActivityStaff.role='Assistant' OR pupilsightActivityStaff.role='Coach') ORDER BY name, programStart";
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');
    $form->setClass('noIntBorder w-full');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/activities_attendance.php");

    $row = $form->addRow();
        $row->addLabel('pupilsightActivityID', __('Activity'));
        $row->addSelect('pupilsightActivityID')->fromQuery($pdo, $sql, $data)->selected($pupilsightActivityID)->required()->placeholder();

    $row = $form->addRow();
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


    $students = $studentResult->fetchAll();

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
    $activitySessions = getActivitySessions($activityWeekDays, $activityTimespan, $sessionAttendanceData);

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

    // Handle activities with no time slots or start/end, but don't return because there can still be previous records
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

        $form = Form::create('attendance', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/activities_attendanceProcess.php?pupilsightActivityID='.$pupilsightActivityID);
        $form->setClass('blank block max-w-full');

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('pupilsightPersonID', $_SESSION[$guid]['pupilsightPersonID']);

        $row = $form->addRow('doublescroll-wrapper')->setClass('block doublescroll-wrapper smallIntBorder w-full max-w-full')->addColumn()->setClass('pl-48');

        // Headings as a separate table
        $table = $row->addTable()->setClass('mini w-full m-0 border-0');
        $header = $table->addHeaderRow();
            $header->addContent(__('Student'))->addClass('w-48 py-8');
            $header->addContent(__('Attendance'));
            $header->addContent(sprintf(__('Sessions Recorded: %s of %s'), count($sessionAttendanceData), count($activitySessions)))
                ->addClass('emphasis subdued right');

        $table = $row->addClass('doublescroll-container block ')->addColumn()->setClass('ml-48 border-l-2 border-gray -mt-1')
            ->addTable()->setClass('mini colorOddEven w-full m-0 border-0 overflow-x-scroll rowHighlight');

        $row = $table->addRow();
            $row->addContent(__('Date'))->addClass('w-48 h-24 absolute left-0 ml-px flex items-center');

        $icon = '<img class="mt-1 inline" title="%1$s" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/%2$s"/>';

        // Display the date and action buttons for each session
        $i = 0;
        foreach ($activitySessions as $sessionDate => $sessionTimestamp) {
            $col = $row->addColumn()->addClass('h-24 px-2 text-center');
            $dateLabel = $col->addContent(Format::dateReadable($sessionDate, '%a<br>%b %e'))->addClass('w-10 mx-auto');

            if (isset($sessionAttendanceData[$sessionDate]['data'])) {
                $col->addWebLink(sprintf($icon, __('Edit'), 'config.png'))
                    ->setURL('')
                    ->addClass('editColumn')
                    ->addData('checked', '')
                    ->addData('column', strval($i))
                    ->addData('date', $sessionTimestamp);
            } else {
                $col->addWebLink(sprintf($icon, __('Add'), 'page_new.png'))
                    ->setURL('')
                    ->addClass('editColumn')
                    ->addData('checked', 'checked')
                    ->addData('column', strval($i))
                    ->addData('date', $sessionTimestamp);
                $dateLabel->addClass('subdued');
            }

            $col->addWebLink(sprintf($icon, __('Clear'), 'garbage.png'))
                ->setURL('')
                ->addClass('clearColumn hidden')
                ->addData('column', strval($i));

            $i++;
        }

        // Build an empty array of attendance count data for each session
        $attendanceCount = array_combine(array_keys($activitySessions), array_fill(0, count($activitySessions), 0));

        // Display student attendance data per session
        foreach ($students as $index => $student) {
            $row = $table->addRow()->addData('student', $student['pupilsightPersonID']);

            $col = $row->addColumn()->addClass('w-48 h-8 absolute left-0 ml-px text-left');

            $col->addWebLink(formatName('', $student['preferredName'], $student['surname'], 'Student', true))
                ->setURl($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php')
                ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                ->setClass('')
                ->prepend(($index+1).') ');

            $i = 0;
            foreach ($activitySessions as $sessionDate => $sessionTimestamp) {
                $content = '';
                if (isset($sessionAttendanceData[$sessionDate]['data'][$student['pupilsightPersonID']])) {
                    $content = '✓';
                    $attendanceCount[$sessionDate]++;
                }
                $row->addContent($content)->setClass("col$i h-8 text-center");
                ++$i;
            }
        }

        // Total students per date
        $row = $table->addRow();
        $row->addContent(__('Total students:'))->addClass('text-right w-48 h-8 absolute left-0 ml-px');

        foreach ($activitySessions as $sessionDate => $sessionTimestamp) {
            $row->setClass('h-8')->addContent(!empty($attendanceCount[$sessionDate])
                ? $attendanceCount[$sessionDate].' / '.$activity['participants']
                : '');
        }

        $row = $form->addRow()->addClass('flex w-full')->addTable()->setClass('smallIntBorder w-full')->addRow();
            $row->addContent(__('All highlighted columns will be updated when you press submit.'))
                ->wrap('<span class="small emphasis">', '</span>');
            $row->addSubmit();

        echo $form->getOutput();

        echo '<br/>';
    }
}
