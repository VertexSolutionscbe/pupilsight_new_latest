<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Classes Not Registered'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/report_courseClassesNotRegistered_byDate.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h2>';
    echo __('Choose Date');
    echo '</h2>';

    $today = date('Y-m-d');

    $dateEnd = (isset($_GET['dateEnd']))? dateConvert($guid, $_GET['dateEnd']) : date('Y-m-d');
    $dateStart = (isset($_GET['dateStart']))? dateConvert($guid, $_GET['dateStart']) : date('Y-m-d', strtotime( $dateEnd.' -4 days') );

    // Correct inverse date ranges rather than generating an error
    if ($dateStart > $dateEnd) {
        $swapDates = $dateStart;
        $dateStart = $dateEnd;
        $dateEnd = $swapDates;
    }

    // Limit date range to the current school year
    if ($dateStart < $_SESSION[$guid]['pupilsightSchoolYearFirstDay']) {
        $dateStart = $_SESSION[$guid]['pupilsightSchoolYearFirstDay'];
    }

    if ($dateEnd > $_SESSION[$guid]['pupilsightSchoolYearLastDay']) {
        $dateEnd = $_SESSION[$guid]['pupilsightSchoolYearLastDay'];
    }

    $datediff = strtotime($dateEnd) - strtotime($dateStart);
    $daysBetweenDates = floor($datediff / (60 * 60 * 24)) + 1;

    $lastSetOfSchoolDays = getLastNSchoolDays($guid, $connection2, $dateEnd, $daysBetweenDates, true);

    $lastNSchoolDays = array();
    for($i = 0; $i < count($lastSetOfSchoolDays); $i++) {
        if ( $lastSetOfSchoolDays[$i] >= $dateStart  ) $lastNSchoolDays[] = $lastSetOfSchoolDays[$i];
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_courseClassesNotRegistered_byDate.php");

    $row = $form->addRow();
        $row->addLabel('dateStart', __('Start Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
        $row->addDate('dateStart')->setValue(dateConvertBack($guid, $dateStart))->required();

    $row = $form->addRow();
        $row->addLabel('dateEnd', __('End Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
        $row->addDate('dateEnd')->setValue(dateConvertBack($guid, $dateEnd))->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if ( count($lastNSchoolDays) == 0 ) {
        echo "<div class='alert alert-danger'>";
        echo __('School is closed on the specified date, and so attendance information cannot be recorded.');
        echo '</div>';
    }
    else if ($dateStart != '') {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        //Produce array of attendance data
        try {
            $data = array('dateStart' => $lastNSchoolDays[count($lastNSchoolDays)-1], 'dateEnd' => $lastNSchoolDays[0]);
            $sql = "SELECT date, pupilsightCourseClassID FROM pupilsightAttendanceLogCourseClass WHERE date>=:dateStart AND date<=:dateEnd ORDER BY date";

            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        $log = array();
        while ($row = $result->fetch()) {
            $log[$row['pupilsightCourseClassID']][$row['date']] = true;
        }

        // Produce an array of scheduled classes
        try {
            $data = array('dateStart' => $lastNSchoolDays[count($lastNSchoolDays)-1], 'dateEnd' => $lastNSchoolDays[0] );
            $sql = "SELECT pupilsightTTDayRowClass.pupilsightCourseClassID, pupilsightTTDayDate.date FROM pupilsightTTDayRowClass JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID) JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightTTDayRowClass.pupilsightCourseClassID) WHERE pupilsightCourseClass.attendance = 'Y' AND pupilsightTTDayDate.date>=:dateStart AND pupilsightTTDayDate.date<=:dateEnd ORDER BY pupilsightTTDayDate.date";

            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        $tt = array();
        while ($row = $result->fetch()) {
            $tt[$row['pupilsightCourseClassID']][$row['date']] = true;
        }


        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'] );
            $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClass.name as class, pupilsightCourse.name as course, pupilsightCourse.nameShort as courseShort, (SELECT count(*) FROM pupilsightCourseClassPerson WHERE role='Student' AND pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) as studentCount FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.attendance = 'Y' ORDER BY pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        }
        else if ($dateStart > $today || $dateEnd > $today) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified date is in the future: it must be today or earlier.');
            echo '</div>';
        } else {
            //Produce array of roll groups
            $classes = $result->fetchAll();

            echo "<div class='linkTop'>";
            echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/report_courseClassesNotRegistered_byDate_print.php&dateStart='.dateConvertBack($guid, $dateStart).'&dateEnd='.dateConvertBack($guid, $dateEnd)."'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
            echo '</div>';

            echo "<table cellspacing='0' class='fullWidth colorOddEven'>";
            echo "<tr class='head'>";
            echo '<th width="140px">';
            echo __('Class');
            echo '</th>';
            echo '<th>';
            echo __('Date');
            echo '</th>';
            echo '<th width="164px">';
            echo __('History');
            echo '</th>';
            echo '<th>';
            echo __('Tutor');
            echo '</th>';
            echo '</tr>';

            $count = 0;

            $timestampStart = dateConvertToTimestamp($dateStart);
            $timestampEnd = dateConvertToTimestamp($dateEnd);

            //Loop through each roll group
            foreach ($classes as $row) {

                // Skip classes with no students
                if ($row['studentCount'] <= 0) continue;

                //Output row only if not registered on specified date, and timetabled for that day
                if (isset($tt[$row['pupilsightCourseClassID']]) == true && (isset($log[$row['pupilsightCourseClassID']]) == false ||
                    count($log[$row['pupilsightCourseClassID']]) < min(count($lastNSchoolDays), count($tt[$row['pupilsightCourseClassID']])) ) ) {
                    ++$count;

                    //COLOR ROW BY STATUS!
                    echo "<tr>";
                    echo '<td>';
                    echo $row['courseShort'].'.'.$row['class'];
                    echo '</td>';
                    echo '<td>';
                    echo Format::dateRangeReadable($dateStart, $dateEnd);
                    echo '</td>';
                    echo '<td style="padding: 0;">';

                        echo "<table cellspacing='0' class='historyCalendarMini' style='width:160px;margin:0;' >";
                        echo '<tr>';

                        $historyCount = 0;
                        for ($i = count($lastNSchoolDays)-1; $i >= 0; --$i) {
                            $link = '';
                            if ($i > ( count($lastNSchoolDays) - 1)) {
                                echo "<td class='highlightNoData'>";
                                echo '<i>'.__('NA').'</i>';
                                echo '</td>';
                            } else {
                                $link = './index.php?q=/modules/Attendance/attendance_take_byCourseClass.php&pupilsightCourseClassID='.$row['pupilsightCourseClassID'].'&currentDate='.$lastNSchoolDays[$i];

                                if ( isset($log[$row['pupilsightCourseClassID']][$lastNSchoolDays[$i]]) == true ) {
                                    $class = 'highlightPresent';
                                } else {
                                    if (isset($tt[$row['pupilsightCourseClassID']][$lastNSchoolDays[$i]]) == true) {
                                        $class = 'highlightAbsent';

                                    } else {
                                        $class = 'highlightNoData';
                                        $link = '';
                                    }
                                }

                                echo "<td class='$class' style='padding: 12px !important;'>";
                                if ($link != '') {
                                    echo "<a href='$link'>";
                                    echo Format::dateReadable($lastNSchoolDays[$i], '%d').'<br/>';
                                    echo "<span>".Format::dateReadable($lastNSchoolDays[$i], '%b').'</span>';
                                    echo '</a>';
                                } else {
                                    echo Format::dateReadable($lastNSchoolDays[$i], '%d').'<br/>';
                                    echo "<span>".Format::dateReadable($lastNSchoolDays[$i], '%b').'</span>';
                                }
                                echo '</td>';  

                                // Wrap to a new line every 10 dates
                                if (  ($historyCount+1) % 10 == 0 ) {
                                    echo '</tr><tr>';
                                }

                                $historyCount++;
                            }
                        }

                        echo '</tr>';
                        echo '</table>';

                    echo '</td>';
                    echo '<td>';

                    try {
                        $dataTutor = array('pupilsightCourseClassID' => $row['pupilsightCourseClassID'] );
                        $sqlTutor = 'SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightCourseClassPerson.role = "Teacher"';
                        $resultTutor = $connection2->prepare($sqlTutor);
                        $resultTutor->execute($dataTutor);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultTutor->rowCount() > 0) {
                        while ($rowTutor = $resultTutor->fetch()) {
                            echo formatName('', $rowTutor['preferredName'], $rowTutor['surname'], 'Staff', true, true).'<br/>';
                        }
                    }

                    echo '</td>';
                    echo '</tr>';
                }
            }

            if ($count == 0) {
                echo "<tr";
                echo '<td colspan=3>';
                echo __('All classes have been registered.');
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';

            if ($count > 0) {
                echo "<div class='alert alert-sucess'>";
                    echo '<b>'.__('Total:')." $count</b><br/>";
                echo "</div>";
            }
        }
    }
}
?>
