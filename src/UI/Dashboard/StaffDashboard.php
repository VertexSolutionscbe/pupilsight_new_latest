<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\UI\Dashboard;

use Pupilsight\Forms\OutputableInterface;
use Pupilsight\Contracts\Services\Session;
use Pupilsight\Tables\Prefab\RollGroupTable;
use Pupilsight\Contracts\Database\Connection;

/**
 * Staff Dashboard View Composer
 *
 * @version  v18
 * @since    v18
 */
class StaffDashboard implements OutputableInterface
{
    protected $db;
    protected $session;
    protected $rollGroupTable;

    public function __construct(Connection $db, Session $session, RollGroupTable $rollGroupTable)
    {
        $this->db = $db;
        $this->session = $session;
        $this->rollGroupTable = $rollGroupTable;
    }

    public function getOutput()
    {
        $guid = $this->session->get('guid');
        $connection2 = $this->db->getConnection();
        $roleid = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
        if($roleid=='001'){
            $output = "<style>.card-body {padding: 0px !important;}</style><div style='background-image:url(assets/img/dashboard/dashboard_admin.jpg);background-size: cover;height:1400px;'>";
        }else{
            $output = "<style>.card-body {padding: 0px !important;}</style><div style='background-image:url(assets/img/dashboard/dashboard_teacher.jpg);background-size: cover;height:1400px;'>";
        }
        //return $output;
        $output = '';

        $smartWorkflowHelp = getSmartWorkflowHelp($connection2, $guid);
        if ($smartWorkflowHelp != false) {
            $output .= $smartWorkflowHelp;
        }

        // if($_SESSION[$guid]['pupilsightRoleIDPrimary'] == '001'){
        //     $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

        //     $sqlterm = 'SELECT * FROM pupilsightSchoolYear ORDER BY pupilsightSchoolYearID ASC';
        //     $resultterm = $connection2->query($sqlterm);
        //     $yeardata = $resultterm->fetchAll();
            
        //     $output .= '<form action="yearSwitcherProcess.php" method="post"><div style="float:right;margin-bottom:10px;"><span style="font-size:18px;float:left">Change Academic Year : </span> &nbsp;&nbsp;&nbsp;&nbsp; <select name="pupilsightSchoolYearID" style="float:left" id="academicYearChange">';

        //     $output .= '<option value="">Select Academic Year</option>';
        //     foreach ($yeardata as $row) {
        //         if($row['pupilsightSchoolYearID'] == $pupilsightSchoolYearID){
        //             $selected = 'selected';
        //         } else {
        //             $selected = '';
        //         }
        //         $output .= '<option value=' . $row['pupilsightSchoolYearID'] . ' '.$selected.'>' . $row['name'] . '</option>';
        //     }
        //     $output .= '</select>  <button type="submit" style="float:right" id="" class="btn btn-primary">Change Year</a></div></form>';
        // }

        
       $output .= '<h2 style="margin-top: 50px;border-top: 1px solid rgba(0, 0, 0, 0.5);padding-top: 5px;">'.
            __('Staff Dashboard').
            '</h2>'.
            "<div style='margin-bottom: 30px; margin-left: 1%; float: left; width: 100%'>";

        $dashboardContents = $this->renderDashboard();

        if ($dashboardContents == false) {
            $output .= "<div class='alert alert-danger'>".
                __('There are no records to display.').
                '</div>';
        } else {
            $output .= $dashboardContents;
        }
        $output .= '</div>';
        
        return $output;
    }

    protected function renderDashboard()
    {
        $guid = $this->session->get('guid');
        $connection2 = $this->db->getConnection();
        $pupilsightPersonID = $this->session->get('pupilsightPersonID');

        $return = false;

        //GET PLANNER
        $planner = false;
        $date = date('Y-m-d');
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => $date, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date2' => $date, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "(SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkSubmission, homeworkCrowdAssess, role, date, summary, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) LEFT JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND date=:date AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left') UNION (SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkSubmission, homeworkCrowdAssess,  role, date, summary, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND date=:date2 AND pupilsightPlannerEntryGuest.pupilsightPersonID=:pupilsightPersonID2) ORDER BY date, timeStart, course, class";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $planner .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        $planner .= '<h2>';
        $planner .= __("Today's Lessons");
        $planner .= '</h2>';
        if ($result->rowCount() < 1) {
            $planner .= "<div class='alert alert-warning'>";
            $planner .= __('There are no records to display.');
            $planner .= '</div>';
        } else {
            $planner .= "<div class='linkTop'>";
            $planner .= "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner.php'>".__('View Planner').'</a>';
            $planner .= '</div>';

            $planner .= "<table class='table'>";
            $planner .= "<tr class='head'>";
            $planner .= '<th>';
            $planner .= __('Class').'<br/>';
            $planner .= '</th>';
            $planner .= '<th>';
            $planner .= __('Lesson').'</br>';
            $planner .= "<span style='font-size: 85%; font-style: italic'>".__('Unit').'</span>';
            $planner .= '</th>';
            $planner .= '<th>';
            $planner .= __('Homework');
            $planner .= '</th>';
            $planner .= '<th>';
            $planner .= __('Summary');
            $planner .= '</th>';
            $planner .= '<th>';
            $planner .= __('Action');
            $planner .= '</th>';
            $planner .= '</tr>';

            $count = 0;
            $rowNum = 'odd';
            while ($row = $result->fetch()) {
                if (!($row['role'] == 'Student' and $row['viewableStudents'] == 'N')) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;

                    //Highlight class in progress
                    if ((date('H:i:s') > $row['timeStart']) and (date('H:i:s') < $row['timeEnd']) and ($date) == date('Y-m-d')) {
                        $rowNum = 'current';
                    }

                    //COLOR ROW BY STATUS!
                    $planner .= "<tr class=$rowNum>";
                    $planner .= '<td>';
                    $planner .= $row['course'].'.'.$row['class'].'<br/>';
                    $planner .= "<span style='font-style: italic; font-size: 75%'>".substr($row['timeStart'], 0, 5).'-'.substr($row['timeEnd'], 0, 5).'</span>';
                    $planner .= '</td>';
                    $planner .= '<td>';
                    $planner .= '<b>'.$row['name'].'</b><br/>';
                    $planner .= "<div style='font-size: 85%; font-style: italic'>";
                    $unit = getUnit($connection2, $row['pupilsightUnitID'], $row['pupilsightCourseClassID']);
                    if (isset($unit[0])) {
                        $planner .= $unit[0];
                        if ($unit[1] != '') {
                            $planner .= '<br/><i>'.$unit[1].' '.__('Unit').'</i>';
                        }
                    }
                    $planner .= '</div>';
                    $planner .= '</td>';
                    $planner .= '<td>';
                    if ($row['homework'] == 'N' and $row['myHomeworkDueDateTime'] == '') {
                        $planner .= __('No');
                    } else {
                        if ($row['homework'] == 'Y') {
                            $planner .= __('Yes').': '.__('Teacher Recorded').'<br/>';
                            if ($row['homeworkSubmission'] == 'Y') {
                                $planner .= "<span style='font-size: 85%; font-style: italic'>+".__('Submission').'</span><br/>';
                                if ($row['homeworkCrowdAssess'] == 'Y') {
                                    $planner .= "<span style='font-size: 85%; font-style: italic'>+".__('Crowd Assessment').'</span><br/>';
                                }
                            }
                        }
                        if ($row['myHomeworkDueDateTime'] != '') {
                            $planner .= __('Yes').': '.__('Student Recorded').'</br>';
                        }
                    }
                    $planner .= '</td>';
                    $planner .= '<td id="wordWrap">';
                    $planner .= $row['summary'];
                    $planner .= '</td>';
                    $planner .= '<td>';
                    $planner .= "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_view_full.php&viewBy=class&pupilsightCourseClassID='.$row['pupilsightCourseClassID'].'&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a>";
                    $planner .= '</td>';
                    $planner .= '</tr>';
                }
            }
            $planner .= '</table>';
        }

        //GET TIMETABLE
        $timetable = false;
        if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt.php') and $_SESSION[$guid]['username'] != '' and getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2) == 'Staff') {

            $timetable .= '
            <script type="text/javascript">
                $(document).ready(function(){
                    $("#tt").load("'.$_SESSION[$guid]['absoluteURL'].'/index_tt_ajax.php",{"pupilsightTTID": "'.@$_GET['pupilsightTTID'].'", "ttDate": "'.@$_POST['ttDate'].'", "fromTT": "'.@$_POST['fromTT'].'", "personalCalendar": "'.@$_POST['personalCalendar'].'", "schoolCalendar": "'.@$_POST['schoolCalendar'].'", "spaceBookingCalendar": "'.@$_POST['spaceBookingCalendar'].'"});
                });
            </script>   ';

            $timetable .= '<h2>'.__('My Timetable').'</h2>';
            $timetable .= "<div id='tt' name='tt' style='width: 100%; min-height: 40px; text-align: center'>";
            $timetable .= "<img style='margin: 10px 0 5px 0' src='".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/loading.gif' alt='".__('Loading')."' onclick='return false;' /><br/><p style='text-align: center'>".__('Loading').'</p>';
            $timetable .= '</div>';
        }

        //GET ROLL GROUPS
        $rollGroups = array();
        $rollGroupCount = 0;
        $count = 0;
        try {
            $dataRollGroups = array('pupilsightPersonIDTutor' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlRollGroups = 'SELECT * FROM pupilsightRollGroup WHERE (pupilsightPersonIDTutor=:pupilsightPersonIDTutor OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3) AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $resultRollGroups = $connection2->prepare($sqlRollGroups);
            $resultRollGroups->execute($dataRollGroups);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        $attendanceAccess = isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byRollGroup.php');

        while ($rowRollGroups = $resultRollGroups->fetch()) {
            $rollGroups[$count][0] = $rowRollGroups['pupilsightRollGroupID'];
            $rollGroups[$count][1] = $rowRollGroups['nameShort'];

            //Roll group table
            $this->rollGroupTable->build($rowRollGroups['pupilsightRollGroupID'], true, false, 'rollOrder, surname, preferredName');
            $this->rollGroupTable->setTitle('');
            
            if ($rowRollGroups['attendance'] == 'Y' AND $attendanceAccess) {
                $this->rollGroupTable->addHeaderAction('attendance', __('Take Attendance'))
                    ->setURL('/modules/Attendance/attendance_take_byRollGroup.php')
                    ->addParam('pupilsightRollGroupID', $rowRollGroups['pupilsightRollGroupID'])
                    ->setIcon('attendance')
                    ->displayLabel()
                    ->append(' | ');
            }

            $this->rollGroupTable->addHeaderAction('export', __('Export to Excel'))
                ->setURL('/indexExport.php')
                ->addParam('pupilsightRollGroupID', $rowRollGroups['pupilsightRollGroupID'])
                ->directLink()
                ->displayLabel();
            
            $rollGroups[$count][2] = $this->rollGroupTable->getOutput();

            $behaviourView = isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_view.php');
            if ($behaviourView) {
                //Behaviour
                $rollGroups[$count][3] = '';
                $plural = 's';
                if ($resultRollGroups->rowCount() == 1) {
                    $plural = '';
                }
                try {
                    $dataBehaviour = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightRollGroupID' => $rollGroups[$count][0]);
                    $sqlBehaviour = 'SELECT pupilsightBehaviour.*, student.surname AS surnameStudent, student.preferredName AS preferredNameStudent, creator.surname AS surnameCreator, creator.preferredName AS preferredNameCreator, creator.title FROM pupilsightBehaviour JOIN pupilsightPerson AS student ON (pupilsightBehaviour.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightPerson AS creator ON (pupilsightBehaviour.pupilsightPersonIDCreator=creator.pupilsightPersonID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightBehaviour.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightRollGroupID=:pupilsightRollGroupID ORDER BY timestamp DESC';
                    $resultBehaviour = $connection2->prepare($sqlBehaviour);
                    $resultBehaviour->execute($dataBehaviour);
                } catch (PDOException $e) {
                    $rollGroups[$count][3] .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
 
                if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage_add.php')) {
                    $rollGroups[$count][3] .= "<div class='linkTop'>";
                    $rollGroups[$count][3] .= "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Behaviour/behaviour_manage_add.php&pupilsightPersonID=&pupilsightRollGroupID=&pupilsightYearGroupID=&type='>".__('Add')."<i title='Add' class='mdi mdi-plus-circle-outline'></i></a>";
                    $policyLink = getSettingByScope($connection2, 'Behaviour', 'policyLink');
                    if ($policyLink != '') {
                        $rollGroups[$count][3] .= " | <a target='_blank' href='$policyLink'>".__('View Behaviour Policy').'</a>';
                    }
                    $rollGroups[$count][3] .= '</div>';
                }

                if ($resultBehaviour->rowCount() < 1) {
                    $rollGroups[$count][3] .= "<div class='alert alert-danger'>";
                    $rollGroups[$count][3] .= __('There are no records to display.');
                    $rollGroups[$count][3] .= '</div>';
                } else {
                    $rollGroups[$count][3] .= "<table class='table'>";
                    $rollGroups[$count][3] .= "<tr class='head'>";
                    $rollGroups[$count][3] .= '<th>';
                    $rollGroups[$count][3] .= __('Student & Date');
                    $rollGroups[$count][3] .= '</th>';
                    $rollGroups[$count][3] .= '<th>';
                    $rollGroups[$count][3] .= __('Type');
                    $rollGroups[$count][3] .= '</th>';
                    $rollGroups[$count][3] .= '<th>';
                    $rollGroups[$count][3] .= __('Descriptor');
                    $rollGroups[$count][3] .= '</th>';
                    $rollGroups[$count][3] .= '<th>';
                    $rollGroups[$count][3] .= __('Level');
                    $rollGroups[$count][3] .= '</th>';
                    $rollGroups[$count][3] .= '<th>';
                    $rollGroups[$count][3] .= __('Teacher');
                    $rollGroups[$count][3] .= '</th>';
                    $rollGroups[$count][3] .= '<th>';
                    $rollGroups[$count][3] .= __('Action');
                    $rollGroups[$count][3] .= '</th>';
                    $rollGroups[$count][3] .= '</tr>';

                    $count2 = 0;
                    $rowNum = 'odd';
                    while ($rowBehaviour = $resultBehaviour->fetch()) {
                        if ($count2 % 2 == 0) {
                            $rowNum = 'even';
                        } else {
                            $rowNum = 'odd';
                        }
                        ++$count2;

                        //COLOR ROW BY STATUS!
                        $rollGroups[$count][3] .= "<tr class=$rowNum>";
                        $rollGroups[$count][3] .= '<td>';
                        $rollGroups[$count][3] .= '<b>'.formatName('', $rowBehaviour['preferredNameStudent'], $rowBehaviour['surnameStudent'], 'Student', false).'</b><br/>';
                        if (substr($rowBehaviour['timestamp'], 0, 10) > $rowBehaviour['date']) {
                            $rollGroups[$count][3] .= __('Date Updated').': '.dateConvertBack($guid, substr($rowBehaviour['timestamp'], 0, 10)).'<br/>';
                            $rollGroups[$count][3] .= __('Incident Date').': '.dateConvertBack($guid, $rowBehaviour['date']).'<br/>';
                        } else {
                            $rollGroups[$count][3] .= dateConvertBack($guid, $rowBehaviour['date']).'<br/>';
                        }
                        $rollGroups[$count][3] .= '</td>';
                        $rollGroups[$count][3] .= "<td style='text-align: center'>";
                        if ($rowBehaviour['type'] == 'Negative') {
                            $rollGroups[$count][3] .= "<img title='".__('Negative')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/> ";
                        } elseif ($rowBehaviour['type'] == 'Positive') {
                            $rollGroups[$count][3] .= "<img title='".__('Positive')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/> ";
                        }
                        $rollGroups[$count][3] .= '</td>';
                        $rollGroups[$count][3] .= '<td>';
                        $rollGroups[$count][3] .= trim($rowBehaviour['descriptor']);
                        $rollGroups[$count][3] .= '</td>';
                        $rollGroups[$count][3] .= '<td>';
                        $rollGroups[$count][3] .= trim($rowBehaviour['level']);
                        $rollGroups[$count][3] .= '</td>';
                        $rollGroups[$count][3] .= '<td>';
                        $rollGroups[$count][3] .= formatName($rowBehaviour['title'], $rowBehaviour['preferredNameCreator'], $rowBehaviour['surnameCreator'], 'Staff', false).'<br/>';
                        $rollGroups[$count][3] .= '</td>';
                        $rollGroups[$count][3] .= '<td>';
                        $rollGroups[$count][3] .= "<script type='text/javascript'>";
                        $rollGroups[$count][3] .= '$(document).ready(function(){';
                        $rollGroups[$count][3] .= "\$(\".comment-$count2\").hide();";
                        $rollGroups[$count][3] .= "\$(\".show_hide-$count2\").fadeIn(1000);";
                        $rollGroups[$count][3] .= "\$(\".show_hide-$count2\").click(function(){";
                        $rollGroups[$count][3] .= "\$(\".comment-$count2\").fadeToggle(1000);";
                        $rollGroups[$count][3] .= '});';
                        $rollGroups[$count][3] .= '});';
                        $rollGroups[$count][3] .= '</script>';
                        if ($rowBehaviour['comment'] != '') {
                            $rollGroups[$count][3] .= "<a title='".__('View Description')."' class='show_hide-$count2' onclick='false' href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/page_down.png' alt='".__('Show Comment')."' onclick='return false;' /></a>";
                        }
                        $rollGroups[$count][3] .= '</td>';
                        $rollGroups[$count][3] .= '</tr>';
                        if ($rowBehaviour['comment'] != '') {
                            if ($rowBehaviour['type'] == 'Positive') {
                                $bg = 'background-color: #D4F6DC;';
                            } else {
                                $bg = 'background-color: #F6CECB;';
                            }
                            $rollGroups[$count][3] .= "<tr class='comment-$count2' id='comment-$count2'>";
                            $rollGroups[$count][3] .= "<td style='$bg' colspan=6>";
                            $rollGroups[$count][3] .= $rowBehaviour['comment'];
                            $rollGroups[$count][3] .= '</td>';
                            $rollGroups[$count][3] .= '</tr>';
                        }
                        $rollGroups[$count][3] .= '</tr>';
                        $rollGroups[$count][3] .= '</tr>';
                    }
                    $rollGroups[$count][3] .= '</table>';
                }
            }

            ++$count;
            ++$rollGroupCount;
        }

        //GET HOOKS INTO DASHBOARD
        $hooks = array();
        try {
            $dataHooks = array();
            $sqlHooks = "SELECT * FROM pupilsightHook WHERE type='Staff Dashboard'";
            $resultHooks = $connection2->prepare($sqlHooks);
            $resultHooks->execute($dataHooks);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        if ($resultHooks->rowCount() > 0) {
            $count = 0;
            while ($rowHooks = $resultHooks->fetch()) {
                $options = unserialize($rowHooks['options']);
                //Check for permission to hook
                try {
                    $dataHook = array('pupilsightRoleIDCurrent' => $_SESSION[$guid]['pupilsightRoleIDCurrent'], 'sourceModuleName' => $options['sourceModuleName']);
                    $sqlHook = "SELECT pupilsightHook.name, pupilsightModule.name AS module, pupilsightAction.name AS action FROM pupilsightHook JOIN pupilsightModule ON (pupilsightHook.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightAction ON (pupilsightAction.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightPermission ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID) WHERE pupilsightAction.pupilsightModuleID=(SELECT pupilsightModuleID FROM pupilsightModule WHERE pupilsightPermission.pupilsightRoleID=:pupilsightRoleIDCurrent AND name=:sourceModuleName) AND pupilsightHook.type='Staff Dashboard'  AND pupilsightAction.name='".$options['sourceModuleAction']."' AND pupilsightModule.name='".$options['sourceModuleName']."' ORDER BY name";
                    $resultHook = $connection2->prepare($sqlHook);
                    $resultHook->execute($dataHook);
                } catch (PDOException $e) {
                }
                if ($resultHook->rowCount() == 1) {
                    $rowHook = $resultHook->fetch();
                    $hooks[$count]['name'] = $rowHooks['name'];
                    $hooks[$count]['sourceModuleName'] = $rowHook['module'];
                    $hooks[$count]['sourceModuleInclude'] = $options['sourceModuleInclude'];
                    ++$count;
                }
            }
        }

        if ($planner == false and $timetable == false and count($hooks) < 1) {
            $return .= "<div class='alert alert-warning'>";
            $return .= __('There are no records to display.');
            $return .= '</div>';
        } else {
            $staffDashboardDefaultTab = getSettingByScope($connection2, 'School Admin', 'staffDashboardDefaultTab');
            $staffDashboardDefaultTabCount = null;

            $return .= "<div id='".$pupilsightPersonID."tabs' style='margin: 0 0'>";
            $return .= '<ul>';
            $tabCount = 1;
            if ($planner != false or $timetable != false) {
                $return .= "<li><a href='#tabs".$tabCount."'>".__('Planner').'</a></li>';
                if ($staffDashboardDefaultTab == 'Planner')
                    $staffDashboardDefaultTabCount = $tabCount;
                ++$tabCount;
            }
            if (count($rollGroups) > 0) {
                foreach ($rollGroups as $rollGroup) {
                    $return .= "<li><a href='#tabs".$tabCount."'>".$rollGroup[1].'</a></li>';
                    ++$tabCount;
                    if ($behaviourView) {
                        $return .= "<li><a href='#tabs".$tabCount."'>".$rollGroup[1].' '.__('Behaviour').'</a></li>';
                        ++$tabCount;
                    }
                }
            }

            foreach ($hooks as $hook) {
                $return .= "<li><a href='#tabs".$tabCount."'>".__($hook['name']).'</a></li>';
                if ($staffDashboardDefaultTab == $hook['name'])
                    $staffDashboardDefaultTabCount = $tabCount;
                ++$tabCount;
            }
            $return .= '</ul>';

            $tabCount = 1;
            if ($planner != false or $timetable != false) {
                $return .= "<div id='tabs".$tabCount."'>";
                $return .= $planner;
                $return .= $timetable;
                $return .= '</div>';
                ++$tabCount;
            }
            if (count($rollGroups) > 0) {
                foreach ($rollGroups as $rollGroup) {
                    $return .= "<div id='tabs".$tabCount."'>";
                    $return .= $rollGroup[2];
                    $return .= '</div>';
                    ++$tabCount;

                    if ($behaviourView) {
                        $return .= "<div id='tabs".$tabCount."'>";
                        $return .= $rollGroup[3];
                        $return .= '</div>';
                        ++$tabCount;
                    }
                }
            }
            foreach ($hooks as $hook) {
                $return .= "<div style='min-height: 100px' id='tabs".$tabCount."'>";
                $include = $_SESSION[$guid]['absolutePath'].'/modules/'.$hook['sourceModuleName'].'/'.$hook['sourceModuleInclude'];
                if (!file_exists($include)) {
                    $return .= "<div class='alert alert-danger'>";
                    $return .= __('The selected page cannot be displayed due to a hook error.');
                    $return .= '</div>';
                } else {
                    $return .= include $include;
                }
                ++$tabCount;
                $return .= '</div>';
            }
            $return .= '</div>';
        }

        $defaultTab = 0;
        if (isset($_GET['tab'])) {
            $defaultTab = $_GET['tab'];
        }
        else if (!empty($staffDashboardDefaultTabCount)) {
            $defaultTab = $staffDashboardDefaultTabCount-1;
        }

        $return .= "<script type='text/javascript'>";
        $return .= '$( "#'.$pupilsightPersonID.'tabs" ).tabs({';
        $return .= 'active: '.$defaultTab.',';
        $return .= 'ajaxOptions: {';
        $return .= 'error: function( xhr, status, index, anchor ) {';
        $return .= '$( anchor.hash ).html(';
        $return .= "\"Couldn't load this tab.\" );";
        $return .= '}';
        $return .= '}';
        $return .= '});';
        $return .= '</script>';

        return $return;
    }
}
