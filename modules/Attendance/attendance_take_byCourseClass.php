<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Module\Attendance\AttendanceView;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
require_once __DIR__ . '/src/AttendanceView.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Take Attendance by Class'));

if (isActionAccessible($guid, $connection2, "/modules/Attendance/attendance_take_byCourseClass.php") == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __("You do not have access to this action.");
    echo "</div>";
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('error3' => __('Your request failed because the specified date is in the future, or is not a school day.')));
    }

    $attendance = new AttendanceView($pupilsight, $pdo);

    $pupilsightCourseClassID = isset($_GET['pupilsightCourseClassID']) ? $_GET['pupilsightCourseClassID'] : '';
    if (empty($pupilsightCourseClassID)) {
        try {
            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightSchoolYear.firstDay, pupilsightSchoolYear.lastDay
                    FROM pupilsightCourse
                    JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                    JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                    JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                    WHERE pupilsightPersonID=:pupilsightPersonID
                    AND pupilsightCourseClass.attendance='Y'
                    AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
        }

        if ($result->rowCount() > 0) {
            $pupilsightCourseClassID = $result->fetchColumn(0);
        }
    }

    echo '<h2>' . __('Choose Class') . "</h2>";

    $today = date('Y-m-d');
    $currentDate = isset($_GET['currentDate']) ? dateConvert($guid, $_GET['currentDate']) : $today;

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byCourseClass.php');

    $row = $form->addRow();
    $row->addLabel('pupilsightCourseClassID', __('Class'));
    $row->addSelectClass('pupilsightCourseClassID', $_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID'], array('attendance' => 'Y'))
        ->required()
        ->selected($pupilsightCourseClassID)
        ->placeholder();

    $row = $form->addRow();
    $row->addLabel('currentDate', __('Date'));
    $row->addDate('currentDate')->required()->setValue(dateConvertBack($guid, $currentDate));

    $row = $form->addRow();
    $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if (!empty($pupilsightCourseClassID)) {
        if ($currentDate > $today) {
            echo "<div class='alert alert-danger'>";
            echo __("The specified date is in the future: it must be today or earlier.");
            echo "</div>";
        } else {
            if (isSchoolOpen($guid, $currentDate, $connection2) == false) {
                echo "<div class='alert alert-danger'>";
                echo __("School is closed on the specified date, and so attendance information cannot be recorded.");
                echo "</div>";
            } else {
                $defaultAttendanceType = getSettingByScope($connection2, 'Attendance', 'defaultClassAttendanceType');
                $crossFillClasses = getSettingByScope($connection2, 'Attendance', 'crossFillClasses');

                // Check class
                try {
                    $data = array("pupilsightCourseClassID" => $pupilsightCourseClassID, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"]);
                    $sql = "SELECT pupilsightCourseClass.*, pupilsightCourse.pupilsightSchoolYearID,firstDay, lastDay,
                    pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourse
                    JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                    JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                    WHERE pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID";

                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
                }

                if ($result->rowCount() == 0) {
                    echo '<div class="alert alert-danger">';
                    echo __('There are no records to display.');
                    echo '</div>';
                    return;
                }

                $class = $result->fetch();

                if ($class["attendance"] == 'N') {
                    echo '<div class="alert alert-danger">';
                    echo __('Attendance taking has been disabled for this class.');
                    echo '</div>';
                } else {
                    // Check if the class is a timetabled course AND if it's timetabled on the current day
                    try {
                        $dataTT = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => $currentDate);
                        $sqlTT = "SELECT MIN(pupilsightTTDayDateID) as currentlyTimetabled, COUNT(*) AS totalTimetableCount
                        FROM pupilsightTTDayRowClass
                        LEFT JOIN pupilsightTTDayDate ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID AND pupilsightTTDayDate.date=:date)
                        WHERE pupilsightTTDayRowClass.pupilsightCourseClassID=:pupilsightCourseClassID
                        GROUP BY pupilsightTTDayRowClass.pupilsightCourseClassID";
                        $resultTT = $connection2->prepare($sqlTT);
                        $resultTT->execute($dataTT);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
                    }

                    if ($resultTT && $resultTT->rowCount() > 0) {
                        $ttCheck = $resultTT->fetch();
                        if ($ttCheck['totalTimetableCount'] > 0 && empty($ttCheck['currentlyTimetabled'])) {
                            echo "<div class='alert alert-warning'>";
                            echo __('This class is not timetabled to run on the specified date. Attendance may still be taken for this group however it currently falls outside the regular schedule for this class.');
                            echo "</div>";
                        }
                    }

                    //Show attendance log for the current day
                    try {
                        $dataLog = array("pupilsightCourseClassID" => $pupilsightCourseClassID, "date" => $currentDate . "%");
                        $sqlLog = "SELECT * FROM pupilsightAttendanceLogCourseClass, pupilsightPerson WHERE pupilsightAttendanceLogCourseClass.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID AND date LIKE :date ORDER BY timestampTaken";
                        $resultLog = $connection2->prepare($sqlLog);
                        $resultLog->execute($dataLog);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
                    }
                    if ($resultLog->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __("Attendance has not been taken for this group yet for the specified date. The entries below are a best-guess based on defaults and information put into the system in advance, not actual data.");
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-sucess'>";
                        echo __("Attendance has been taken at the following times for the specified date for this group:");
                        echo "<ul>";
                        while ($rowLog = $resultLog->fetch()) {
                            echo "<li>" . sprintf(__('Recorded at %1$s on %2$s by %3$s.'), substr($rowLog["timestampTaken"], 11), dateConvertBack($guid, substr($rowLog["timestampTaken"], 0, 10)), formatName("", $rowLog["preferredName"], $rowLog["surname"], "Staff", false, true)) . "</li>";
                        }
                        echo "</ul>";
                        echo "</div>";
                    }

                    //Show roll group grid
                    try {
                        $dataCourseClass = array("pupilsightCourseClassID" => $pupilsightCourseClassID, 'date' => $currentDate);
                        $sqlCourseClass = "SELECT pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightPerson.pupilsightPersonID, pupilsightPerson.image_240 FROM pupilsightCourseClassPerson
                            INNER JOIN pupilsightPerson ON pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID
                            LEFT JOIN (SELECT pupilsightTTDayRowClass.pupilsightCourseClassID, pupilsightTTDayRowClass.pupilsightTTDayRowClassID FROM pupilsightTTDayDate JOIN pupilsightTTDayRowClass ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID) WHERE pupilsightTTDayDate.date=:date) AS pupilsightTTDayRowClassSubset ON (pupilsightTTDayRowClassSubset.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                            LEFT JOIN pupilsightTTDayRowClassException ON (pupilsightTTDayRowClassException.pupilsightTTDayRowClassID=pupilsightTTDayRowClassSubset.pupilsightTTDayRowClassID AND pupilsightTTDayRowClassException.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID)
                            WHERE pupilsightCourseClassPerson.pupilsightCourseClassID=:pupilsightCourseClassID
                            AND status='Full' AND role='Student'
                            AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL OR dateEnd>=:date)
                            GROUP BY pupilsightCourseClassPerson.pupilsightPersonID
                            HAVING COUNT(pupilsightTTDayRowClassExceptionID) = 0
                            ORDER BY surname, preferredName";
                        $resultCourseClass = $connection2->prepare($sqlCourseClass);
                        $resultCourseClass->execute($dataCourseClass);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
                    }

                    if ($resultCourseClass->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __("There are no records to display.");
                        echo "</div>";
                    } else {
                        $count = 0;
                        $countPresent = 0;
                        $columns = 4;

                        $defaults = array('type' => $defaultAttendanceType, 'reason' => '', 'comment' => '', 'context' => '');
                        $students = $resultCourseClass->fetchAll();

                        // Build the attendance log data per student
                        foreach ($students as $key => $student) {
                            $data = array('pupilsightPersonID' => $student['pupilsightPersonID'], 'date' => $currentDate . '%', 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                            $sql = "SELECT type, reason, comment, context, timestampTaken FROM pupilsightAttendanceLogPerson
                                    JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                    WHERE pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID
                                    AND date LIKE :date
                                    AND context='Class' AND pupilsightCourseClassID=:pupilsightCourseClassID
                                    ORDER BY timestampTaken DESC";
                            $result = $pdo->executeQuery($data, $sql);

                            $log = ($result->rowCount() > 0) ? $result->fetch() : $defaults;

                            //Check for school prefill if attendance not taken in this class
                            if ($result->rowCount() == 0 ) {
                                $data = array('pupilsightPersonID' => $student['pupilsightPersonID'], 'date' => $currentDate . '%');
                                $sql = "SELECT type, reason, comment, context, timestampTaken FROM pupilsightAttendanceLogPerson
                                        JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                        WHERE pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID
                                        AND date LIKE :date";
                                if ($crossFillClasses == "N") {
                                    $sql .= " AND NOT context='Class'";
                                }
                                $sql .= " ORDER BY timestampTaken DESC";
                                $result = $pdo->executeQuery($data, $sql);

                                $log = ($result->rowCount() > 0) ? $result->fetch() : $log;
                            }

                            $students[$key]['cellHighlight'] = '';
                            if ($attendance->isTypeAbsent($log['type'])) {
                                $students[$key]['cellHighlight'] = 'dayAbsent';
                            } elseif ($attendance->isTypeOffsite($log['type'])) {
                                $students[$key]['cellHighlight'] = 'dayMessage';
                            }

                            $students[$key]['absenceCount'] = '';
                            $absenceCount = getAbsenceCount($guid, $student['pupilsightPersonID'], $connection2, $class['firstDay'], $class['lastDay'], $pupilsightCourseClassID);
                            if ($absenceCount !== false) {
                                $absenceText = ($absenceCount == 1) ? __('%1$s Class Absent') : __('%1$s Classes Absent');
                                $students[$key]['absenceCount'] = sprintf($absenceText, $absenceCount);
                            }

                            if ($attendance->isTypePresent($log['type']) && $attendance->isTypeOnsite($log['type'])) {
                                $countPresent++;
                            }

                            $students[$key]['log'] = $log;
                        }

                        $form = Form::create('attendanceByClass', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byCourseClassProcess.php');
                        $form->setAutocomplete('off');

                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                        $form->addHiddenValue('pupilsightCourseClassID', $pupilsightCourseClassID);
                        $form->addHiddenValue('currentDate', $currentDate);
                        $form->addHiddenValue('count', count($students));

                        $form->addRow()->addHeading(__('Take Attendance') . ': ' . htmlPrep($class['course']) . '.' . htmlPrep($class['class']));

                        $grid = $form->addRow()->addGrid('attendance')->setBreakpoints('w-1/2 sm:w-1/4 md:w-1/5 lg:w-1/4');

                        foreach ($students as $student) {
                            $form->addHiddenValue($count . '-pupilsightPersonID', $student['pupilsightPersonID']);

                            $cell = $grid->addCell()
                                ->setClass('text-center py-2 px-1 -mr-px -mb-px flex flex-col justify-between')
                                ->addClass($student['cellHighlight']);

                            $cell->addContent(getUserPhoto($guid, $student['image_240'], 75));
                            $cell->addWebLink(formatName('', htmlPrep($student['preferredName']), htmlPrep($student['surname']), 'Student', false))
                                ->setURL('index.php?q=/modules/Students/student_view_details.php')
                                ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                                ->addParam('subpage', 'Attendance')
                                ->setClass('pt-2 font-bold underline');
                            $cell->addContent($student['absenceCount'])->wrap('<div class="text-xxs italic py-2">', '</div>');
                            $cell->addSelect($count . '-type')
                                ->fromArray(array_keys($attendance->getAttendanceTypes()))
                                ->selected($student['log']['type'])
                                ->setClass('mx-auto float-none w-32 m-0 mb-px');
                            $cell->addSelect($count . '-reason')
                                ->fromArray($attendance->getAttendanceReasons())
                                ->selected($student['log']['reason'])
                                ->setClass('mx-auto float-none w-32 m-0 mb-px');
                            $cell->addTextField($count . '-comment')
                                ->maxLength(255)
                                ->setValue($student['log']['comment'])
                                ->setClass('mx-auto float-none w-32 m-0 mb-2');
                            $cell->addContent($attendance->renderMiniHistory($student['pupilsightPersonID'], 'Class', $pupilsightCourseClassID));

                            $count++;
                        }

                        $form->addRow()->addAlert(__('Total students:') . ' ' . $count, 'success')->setClass('right')
                            ->append('<br/><span title="' . __('e.g. Present or Present - Late') . '">' . __('Total students present in room:') . ' ' . $countPresent . '</span>')
                            ->append('<br/><span title="' . __('e.g. not Present and not Present - Late') . '">' . __('Total students absent from room:') . ' ' . ($count - $countPresent) . '</span>')
                            ->wrap('<b>', '</b>');

                        $row = $form->addRow();

                        // Drop-downs to change the whole group at once
                        $row->addButton(__('Change All').'?')->addData('toggle', '.change-all')->addClass('w-32 m-px sm:self-center');

                        $col = $row->addColumn()->setClass('change-all hidden flex flex-col sm:flex-row items-stretch sm:items-center');
                            $col->addSelect('set-all-type')->fromArray(array_keys($attendance->getAttendanceTypes()))->addClass('m-px');
                            $col->addSelect('set-all-reason')->fromArray($attendance->getAttendanceReasons())->addClass('m-px');
                            $col->addTextField('set-all-comment')->maxLength(255)->addClass('m-px');
                        $col->addButton(__('Apply'))->setID('set-all');

                        $row->addSubmit();

                        echo $form->getOutput();
                    }
                }
            }
        }
    }
}
