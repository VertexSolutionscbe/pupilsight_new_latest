<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\UI\Dashboard;

use Pupilsight\Contracts\Services\Session;
use Pupilsight\Contracts\Database\Connection;
use Pupilsight\Forms\OutputableInterface;

/**
 * Student Dashboard View Composer
 *
 * @version  v18
 * @since    v18
 */
class StudentDashboard implements OutputableInterface
{
    protected $db;
    protected $session;

    public function __construct(Connection $db, Session $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    public function getOutput()
    {
        $output = '<h2>'.
            __('Student Dashboard').
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
        if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt.php') and $_SESSION[$guid]['username'] != '' and getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2) == 'Student') {

            $timetable .= '
            <script type="text/javascript">
                $(document).ready(function(){
                    $("#tt").load("'.$_SESSION[$guid]['absoluteURL'].'/index_tt_ajax.php",{"pupilsightTTID": "'.@$_GET['pupilsightTTID'].'", "ttDate": "'.@$_POST['ttDate'].'", "fromTT": "'.@$_POST['fromTT'].'", "personalCalendar": "'.@$_POST['personalCalendar'].'", "schoolCalendar": "'.@$_POST['schoolCalendar'].'", "spaceBookingCalendar": "'.@$_POST['spaceBookingCalendar'].'"});
                });
            </script>';

            $timetable .= '<h2>'.__('My Timetable').'</h2>';
            $timetable .= "<div id='tt' name='tt' style='width: 100%; min-height: 40px; text-align: center'>";
            $timetable .= "<img style='margin: 10px 0 5px 0' src='".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/loading.gif' alt='".__('Loading')."' onclick='return false;' /><br/><p style='text-align: center'>".__('Loading').'</p>';
            $timetable .= '</div>';
        }

        //GET HOOKS INTO DASHBOARD
        $hooks = array();
        try {
            $dataHooks = array();
            $sqlHooks = "SELECT * FROM pupilsightHook WHERE type='Student Dashboard'";
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
                    $sqlHook = "SELECT pupilsightHook.name, pupilsightModule.name AS module, pupilsightAction.name AS action FROM pupilsightHook JOIN pupilsightModule ON (pupilsightHook.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightAction ON (pupilsightAction.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightPermission ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID) WHERE pupilsightAction.pupilsightModuleID=(SELECT pupilsightModuleID FROM pupilsightModule WHERE name=:sourceModuleName) AND pupilsightPermission.pupilsightRoleID=:pupilsightRoleIDCurrent AND pupilsightHook.type='Student Dashboard' AND pupilsightAction.name='".$options['sourceModuleAction']."' AND pupilsightModule.name='".$options['sourceModuleName']."' ORDER BY name";
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
            $studentDashboardDefaultTab = getSettingByScope($connection2, 'School Admin', 'studentDashboardDefaultTab');
            $studentDashboardDefaultTabCount = null;

            $return .= "<div id='".$pupilsightPersonID."tabs' style='margin: 0 0'>";
            $return .= '<ul>';
            $tabCount = 1;
            if ($planner != false or $timetable != false) {
                $return .= "<li><a href='#tabs".$tabCount."'>".__('Planner').'</a></li>';
                if ($studentDashboardDefaultTab == 'Planner')
                    $studentDashboardDefaultTabCount = $tabCount;
                ++$tabCount;
            }
            foreach ($hooks as $hook) {
                $return .= "<li><a href='#tabs".$tabCount."'>".__($hook['name']).'</a></li>';
                if ($studentDashboardDefaultTab == $hook['name'])
                    $studentDashboardDefaultTabCount = $tabCount;
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
        else if (!is_null($studentDashboardDefaultTabCount)) {
            $defaultTab = $studentDashboardDefaultTabCount-1;
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
