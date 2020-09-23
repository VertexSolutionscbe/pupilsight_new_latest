<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\AttendanceView;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
require_once __DIR__ . '/src/AttendanceView.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Take Attendance by Roll Group'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_bySection.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    // if ($highestAction == false) {
    //     echo "<div class='alert alert-danger'>";
    //     echo __('The highest grouped action cannot be determined.');
    //     echo '</div>';
    // } else {
        $pupilsightYearGroupID = $_GET['classid'];
        $pupilsightRollGroupID = $_GET['sectionid'];
        $courseId = $_GET['courseid'];
        $pupilsightCourseClassID = $_GET['courseclsid'];
        $courseName = $_GET['coursename'];
        $periodId = $_GET['periodid'];
        $periodName = $_GET['periodname'];
        $timeperiod = $_GET['timeperiod'];
        $attendate = $_GET['attndate'];
        $selsession = '';
        $session = '';

        $sqlp = 'SELECT b.officialName FROM pupilsightCourseClassPerson AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightCourseClassID = "'.$pupilsightCourseClassID.'" AND role = "Teacher" ';
        $resultp = $connection2->query($sqlp);
        $teacherdata = $resultp->fetch();
        $teacherName = $teacherdata['officialName'];

        
        
        
        //Proceed!
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, array('error3' => __('Your request failed because the specified date is in the future, or is not a school day.')));
        }

        $attendance = new AttendanceView($pupilsight, $pdo);

        // $pupilsightYearGroupID = '';
        // $pupilsightRollGroupID = '';
        // $selsession = '';
        // if (isset($_GET['pupilsightRollGroupID']) == false) {
        //     try {
        //         $data = array('pupilsightPersonIDTutor1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        //         $sql = "SELECT pupilsightRollGroup.*, firstDay, lastDay, pupilsightProgramClassSectionMapping.pupilsightYearGroupID FROM pupilsightRollGroup JOIN pupilsightSchoolYear ON (pupilsightRollGroup.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) JOIN pupilsightProgramClassSectionMapping ON pupilsightRollGroup.pupilsightRollGroupID = pupilsightProgramClassSectionMapping.pupilsightRollGroupID WHERE (pupilsightPersonIDTutor=:pupilsightPersonIDTutor1 OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID";
        //         $result = $connection2->prepare($sql);
        //         $result->execute($data);
        //     } catch (PDOException $e) {
        //         echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        //     }
        //     if ($result->rowCount() > 0) {
        //         $row = $result->fetch();
        //         $pupilsightRollGroupID = $row['pupilsightRollGroupID'];
        //         $pupilsightYearGroupID = $row['pupilsightYearGroupID'];
        //         $selsession = $firstsession;
        //     }
        // } else {
        //     $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'];
        //     $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
        //     $selsession = $_GET['session'];
        // }

        $today = $attendate;
        $currentDate = isset($_GET['currentDate'])? dateConvert($guid, $_GET['currentDate']) : $today;

        echo '<h2>'.__('Take Attendance')."</h2>";
        
        echo '<div style="font-weight:bold; font-size:14px;"><span>Course Name : '.$courseName.'</span>  <span> Date : '.$attendate.'</span>  <span>  Time / Period : '.$timeperiod.' / '.$periodName.' </span>  <span> Teacher : '.$teacherName.'</span></div>';


        if ($pupilsightRollGroupID != '') {
            if ($currentDate > $today) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified date is in the future: it must be today or earlier.');
                echo '</div>';
            } else {
                if (isSchoolOpen($guid, $currentDate, $connection2) == false) {
                    echo "<div class='alert alert-danger'>";
                    echo __('School is closed on the specified date, and so attendance information cannot be recorded.');
                    echo '</div>';
                } else {
                    $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
                    $defaultAttendanceType = getSettingByScope($connection2, 'Attendance', 'defaultRollGroupAttendanceType');

                    //Check roll group
                    try {
                        $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                        $sql = 'SELECT pupilsightRollGroup.*, firstDay, lastDay FROM pupilsightRollGroup JOIN pupilsightSchoolYear ON (pupilsightRollGroup.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($result->rowCount() == 0) {
                        echo '<div class="alert alert-danger">';
                        echo __('There are no records to display.');
                        echo '</div>';
                        return;
                    }

                    $rollGroup = $result->fetch();

                    if ($rollGroup['attendance'] == 'N') {
                        print "<div class='alert alert-danger'>" ;
                            print __("Attendance taking has been disabled for this roll group.") ;
                        print "</div>" ;
                    } else {

                        //Show attendance log for the current day
                        try {
                            if(!empty($session)){
                                $dataLog = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'session_no' => $selsession, 'date' => $currentDate.'%');
                                $sqlLog = 'SELECT * FROM pupilsightAttendanceLogRollGroup, pupilsightPerson WHERE pupilsightAttendanceLogRollGroup.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightRollGroupID=:pupilsightRollGroupID AND session_no=:session_no AND date LIKE :date ORDER BY timestampTaken';
                            } else {
                                $dataLog = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'date' => $currentDate.'%');
                                $sqlLog = 'SELECT * FROM pupilsightAttendanceLogRollGroup, pupilsightPerson WHERE pupilsightAttendanceLogRollGroup.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightRollGroupID=:pupilsightRollGroupID AND date LIKE :date ORDER BY timestampTaken';
                            }
                            
                            $resultLog = $connection2->prepare($sqlLog);
                            $resultLog->execute($dataLog);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($resultLog->rowCount() < 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Attendance has not been taken for this group yet for the specified date. The entries below are a best-guess based on defaults and information put into the system in advance, not actual data.');
                            echo '</div>';
                        } else {
                            echo "<div class='alert alert-sucess'>";
                            echo __('Attendance has been taken at the following times for the specified date for this group:');
                            echo '<ul>';
                            while ($rowLog = $resultLog->fetch()) {
                                echo '<li>'.sprintf(__('Recorded at %1$s on %2$s by %3$s.'), substr($rowLog['timestampTaken'], 11), dateConvertBack($guid, substr($rowLog['timestampTaken'], 0, 10)), formatName('', $rowLog['preferredName'], $rowLog['surname'], 'Staff', false, true)).'</li>';
                            }
                            echo '</ul>';
                            echo '</div>';
                        }

                        //Show roll group grid
                        try {
                            $dataRollGroup = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'date' => $currentDate);
                            $sqlRollGroup = "SELECT pupilsightPerson.image_240, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightPerson.pupilsightPersonID, pupilsightYearGroup.name as classname FROM pupilsightStudentEnrolment INNER JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID LEFT JOIN pupilsightYearGroup ON pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND status='Full' AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL  OR dateEnd>=:date) ORDER BY rollOrder, surname, preferredName";
                            $resultRollGroup = $connection2->prepare($sqlRollGroup);
                            $resultRollGroup->execute($dataRollGroup);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($resultRollGroup->rowCount() < 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            $count = 0;
                            $countPresent = 0;
                            $columns = 4;

                            $defaults = array('type' => $defaultAttendanceType, 'reason' => '', 'comment' => '', 'context' => '');
                            $students = $resultRollGroup->fetchAll();
                            

                            // Build the attendance log data per student
                            foreach ($students as $key => $student) {
                                
                                if(!empty($session)){
                                    
                                    $data = array('pupilsightPersonID' => $student['pupilsightPersonID'],'session_no' => $selsession, 'date' => $currentDate.'%');
                                    $sql = "SELECT type, reason, comment, context, timestampTaken FROM pupilsightAttendanceLogPerson
                                        JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                        WHERE pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID AND session_no=:session_no
                                        AND date LIKE :date";
                                } else {
                                    $data = array('pupilsightPersonID' => $student['pupilsightPersonID'], 'date' => $currentDate.'%');
                                    $sql = "SELECT type, reason, comment, context, timestampTaken FROM pupilsightAttendanceLogPerson
                                        JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                        WHERE pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID
                                        AND date LIKE :date";
                                }
                                

                                if ($countClassAsSchool == 'N') {
                                    $sql .= " AND NOT context='Class'";
                                }
                                $sql .= " ORDER BY timestampTaken DESC";
                                $result = $pdo->executeQuery($data, $sql);

                                $log = ($result->rowCount() > 0)? $result->fetch() : $defaults;

                                $students[$key]['cellHighlight'] = '';
                                if ($attendance->isTypeAbsent($log['type'])) {
                                    $students[$key]['cellHighlight'] = 'dayAbsent';
                                } elseif ($attendance->isTypeOffsite($log['type'])) {
                                    $students[$key]['cellHighlight'] = 'dayMessage';
                                }

                                $students[$key]['absenceCount'] = '';
                                $absenceCount = getAbsenceCount($guid, $student['pupilsightPersonID'], $connection2, $rollGroup['firstDay'], $rollGroup['lastDay']);
                                if ($absenceCount !== false) {
                                    $absenceText = ($absenceCount == 1)? __('%1$s Day Absent') : __('%1$s Days Absent');
                                    $students[$key]['absenceCount'] = sprintf($absenceText, $absenceCount);
                                }

                                if ($attendance->isTypePresent($log['type']) && $attendance->isTypeOnsite($log['type'])) {
                                    $countPresent++;
                                }

                                $students[$key]['log'] = $log;
                            }

                            $form = Form::create('attendanceByClass', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']. '/attendance_take_byTimetableProcess.php');
                            $form->setAutocomplete('off');

                            $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byRollGroupListView.php');
                            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                            // $form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);
                            // $form->addHiddenValue('pupilsightRollGroupID', $pupilsightRollGroupID);
                            $form->addHiddenValue('periodID', $periodId);
                            $form->addHiddenValue('pupilsightCourseClassID', $pupilsightCourseClassID);
                            $form->addHiddenValue('currentDate', $currentDate);
                            $form->addHiddenValue('count', count($students));

                            $form->addRow()->addHeading(__('Take Attendance') . ': '. htmlPrep($rollGroup['name']));

                            $grid = $form->addRow()->addGrid('attendance')->setBreakpoints('w-1/2 sm:w-1/4 md:w-1/5 lg:w-1/4');
                            $sl = 1;
                                $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('sl_no', __('Sl No'))->addClass('dte'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('name', __('Name'))->addClass('dte'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('id', __('ID'))->addClass('dte'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('class', __('Class'))->addClass('dte'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('section', __('Section'))->addClass('dte'); 

                                    if(!empty($session)){
                                        $col = $row->addColumn()->setClass('newdes');
                                        $col->addLabel('sessionname', __('Session'))->addClass('dte');
                                    }

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('attendance', __('Attendance'))->addClass('dte'); 
                                    
                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('reason', __('Reason'))->addClass('dte'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('remark', __('Remark'))->addClass('dte'); 

                            foreach ($students as $student) {
                                $form->addHiddenValue($count . '-pupilsightPersonID', $student['pupilsightPersonID']);
                                
                                    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv'); 

                                    $col = $row->addColumn()->setClass('newdes customize_input');
                                    $col->addTextField('sl_No')->required()->readonly()->setValue($count+1);

                                    $col = $row->addColumn()->setClass('newdes customize_input');
                                    $col->addWebLink(formatName('', htmlPrep($student['preferredName']), htmlPrep($student['surname']), 'Student', false))
                                         ->setURL('index.php?q=/modules/Students/student_view_details.php')
                                         ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                                         ->addParam('subpage', 'Attendance')
                                         ->setClass('pt-2 font-bold underline');

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('id')->readonly()->setValue($student['pupilsightPersonID']);

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('class')->readonly()->setValue($student['classname']);

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('section')->readonly()->setValue($rollGroup['name']);

                                    if(!empty($session)){
                                        $sqlse = 'SELECT session_name FROM attn_session_settings WHERE session_no = '.$selsession.' ';
                                        $resultse = $connection2->query($sqlse);
                                        $sessname = $resultse->fetch();
                                        $col = $row->addColumn()->setClass('newdes');
                                        $col->addTextField('sessionname')->readonly()->setValue($sessname['session_name']);
                                    }

                                    $col = $row->addColumn()->setClass('newdes');
                                    
                                    $col->addSelect($count.'-type')->addClass('txtfield')->fromArray(array_keys($attendance->getAttendanceTypes()))->selected($student['log']['type']);
                                    
                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addSelect($count.'-reason')->fromArray($attendance->getAttendanceReasons())->selected($student['log']['reason']);

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField($count.'-comment')->maxLength(255)->setValue($student['log']['comment']);
                               

                                // $cell = $grid->addCell()
                                //     ->setClass('text-center py-2 px-1 -mr-px -mb-px flex flex-col justify-between')
                                //     ->addClass($student['cellHighlight']);

                                // $cell->addContent(getUserPhoto($guid, $student['image_240'], 75));
                                // $cell->addWebLink(formatName('', htmlPrep($student['preferredName']), htmlPrep($student['surname']), 'Student', false))
                                //      ->setURL('index.php?q=/modules/Students/student_view_details.php')
                                //      ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                                //      ->addParam('subpage', 'Attendance')
                                //      ->setClass('pt-2 font-bold underline');
                                // $cell->addContent($student['absenceCount'])->wrap('<div class="text-xxs italic py-2">', '</div>');
                                // $cell->addSelect($count.'-type')
                                //      ->fromArray(array_keys($attendance->getAttendanceTypes()))
                                //      ->selected($student['log']['type'])
                                //      ->setClass('mx-auto float-none w-32 m-0 mb-px');
                                // $cell->addSelect($count.'-reason')
                                //      ->fromArray($attendance->getAttendanceReasons())
                                //      ->selected($student['log']['reason'])
                                //      ->setClass('mx-auto float-none w-32 m-0 mb-px');
                                // $cell->addTextField($count.'-comment')
                                //      ->maxLength(255)
                                //      ->setValue($student['log']['comment'])
                                //      ->setClass('mx-auto float-none w-32 m-0 mb-2');
                                // $cell->addContent($attendance->renderMiniHistory($student['pupilsightPersonID'], 'Roll Group'));

                                $sl++;
                                $count++;
                            }

                            $form->addRow()->addAlert(__('Total students:').' '. $count, 'success')->setClass('right')
                                ->append('<br/><span title="'.__('e.g. Present or Present - Late').'">'.__('Total students present in room:').' '. $countPresent.'</span>')
                                ->append('<br/><span title="'.__('e.g. not Present and not Present - Late').'">'.__('Total students absent from room:').' '. ($count-$countPresent).'</span>')
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
    // }
}
