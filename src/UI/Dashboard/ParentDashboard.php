<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\UI\Dashboard;

use Pupilsight\Contracts\Services\Session;
use Pupilsight\Contracts\Database\Connection;
use Pupilsight\Forms\OutputableInterface;

/**
 * Parent Dashboard View Composer
 *
 * @version  v18
 * @since    v18
 */
class ParentDashboard implements OutputableInterface
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
        $guid = $this->session->get('guid');
        $connection2 = $this->db->getConnection();

        $students = [];

        try {
            $data = ['pupilsightPersonID' => $this->session->get('pupilsightPersonID')];
            $sql = "SELECT * FROM pupilsightFamilyAdult WHERE
                pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {}

        if ($result->rowCount() > 0) {
            // Get child list
            while ($row = $result->fetch()) {
                try {
                    $dataChild = [
                        'pupilsightSchoolYearID' => $this->session->get('pupilsightSchoolYearID'),
                        'pupilsightFamilyID' => $row['pupilsightFamilyID'],
                        'today' => date('Y-m-d'),
                    ];
                    $sqlChild = "SELECT
                        pupilsightPerson.pupilsightPersonID,image_240, surname,
                        preferredName, dateStart,
                        pupilsightYearGroup.nameShort AS yearGroup,
                        pupilsightRollGroup.nameShort AS rollGroup,
                        pupilsightRollGroup.website AS rollGroupWebsite,
                        pupilsightRollGroup.pupilsightRollGroupID
                        FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                        JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                        JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                        JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                        WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                        AND pupilsightFamilyID=:pupilsightFamilyID
                        AND pupilsightPerson.status='Full'
                        AND (dateStart IS NULL OR dateStart<=:today)
                        AND (dateEnd IS NULL OR dateEnd>=:today)
                        ORDER BY surname, preferredName ";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {}

                while ($rowChild = $resultChild->fetch()) {
                    $students[] = $rowChild;
                }
            }
        }

        $output = '';

        if (count($students) > 0) {
            include_once $_SERVER["DOCUMENT_ROOT"].'/modules/Timetable/moduleFunctions.php';
            //include_once $_SERVER["DOCUMENT_ROOT"].'/pupilsight/modules/Timetable/moduleFunctions.php';
            
            $output .= '<h2>'.__('Parent Dashboard').'</h2>';

            foreach ($students as $student) {
                $output .= '<h4>'.
                    $student['preferredName'].' '.$student['surname'].
                    '</h4>';

                $output .= '<section class="flex flex-col sm:flex-row">';
                
                $output .= '<div class="w-24 text-center mx-auto mb-4 sm:ml-0 sm:mr-4">'.
                    getUserPhoto($guid, $student['image_240'], 75).
                    "<div style='height: 5px'></div>".
                    "<span style='font-size: 70%'>".
                    "<a href='".$this->session->get('absoluteURL').'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$student['pupilsightPersonID']."'>".__('Student Profile').'</a><br/>';

                if (isActionAccessible($guid, $connection2, '/modules/Roll Groups/rollGroups_details.php')) {
                    $output .= "<a href='".$this->session->get('absoluteURL').'/index.php?q=/modules/Roll Groups/rollGroups_details.php&pupilsightRollGroupID='.$student['pupilsightRollGroupID']."'>".__('Roll Group').' ('.$student['rollGroup'].')</a><br/>';
                }
                if ($student['rollGroupWebsite'] != '') {
                    $output .= "<a target='_blank' href='".$student['rollGroupWebsite']."'>".$student['rollGroup'].' '.__('Website').'</a>';
                }

                $output .= '</span>';
                $output .= '</div>';
                $output .= '<div class="flex-grow mb-6">';
                $dashboardContents = $this->renderChildDashboard($student['pupilsightPersonID'], $student['dateStart']);
                if ($dashboardContents == false) {
                    $output .= "<div class='alert alert-danger'>".__('There are no records to display.').'</div>';
                } else {
                    $output .= $dashboardContents;
                }
                $output .= '</div>';
                $output .= '</section>';
            }
        }

        return $output;
    }

    protected function renderChildDashboard($pupilsightPersonID, $dateStart)
    {
        $guid = $this->session->get('guid');
        $connection2 = $this->db->getConnection();

        $return = false;

        $alert = getAlert($guid, $connection2, 002);
        $entryCount = 0;

        //PREPARE PLANNER SUMMARY
        $plannerOutput = "<span style='font-size: 85%; font-weight: bold'>".__('Today\'s Classes')."</span> . <span style='font-size: 70%'><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner.php&search='.$pupilsightPersonID."'>".__('View Planner').'</a></span>';

        $classes = false;
        $date = date('Y-m-d');
        if (isSchoolOpen($guid, $date, $connection2) == true and isActionAccessible($guid, $connection2, '/modules/Planner/planner.php') and $_SESSION[$guid]['username'] != '') {
            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => $date, 'pupilsightPersonID' => $pupilsightPersonID, 'date2' => $date, 'pupilsightPersonID2' => $pupilsightPersonID);
                $sql = "(SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkSubmission, homeworkCrowdAssess, role, date, summary, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) LEFT JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND date=:date AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left') UNION (SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkSubmission, homeworkCrowdAssess, role, date, summary, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date2 AND pupilsightPlannerEntryGuest.pupilsightPersonID=:pupilsightPersonID2) ORDER BY date, timeStart";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $plannerOutput .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() > 0) {
                $classes = true;
                $plannerOutput .= "<table cellspacing='0' style='margin: 3px 0px; width: 100%'>";
                $plannerOutput .= "<tr class='head'>";
                $plannerOutput .= '<th>';
                $plannerOutput .= __('Class').'<br/>';
                $plannerOutput .= '</th>';
                $plannerOutput .= '<th>';
                $plannerOutput .= __('Lesson').'<br/>';
                $plannerOutput .= "<span style='font-size: 85%; font-weight: normal; font-style: italic'>".__('Summary').'</span>';
                $plannerOutput .= '</th>';
                $plannerOutput .= '<th>';
                $plannerOutput .= __('Homework');
                $plannerOutput .= '</th>';
                $plannerOutput .= '<th>';
                $plannerOutput .= __('Action');
                $plannerOutput .= '</th>';
                $plannerOutput .= '</tr>';

                $count2 = 0;
                $rowNum = 'odd';
                while ($row = $result->fetch()) {
                    if ($count2 % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count2;

                    //Highlight class in progress
                    if ((date('H:i:s') > $row['timeStart']) and (date('H:i:s') < $row['timeEnd']) and ($date) == date('Y-m-d')) {
                        $rowNum = 'current';
                    }

                    //COLOR ROW BY STATUS!
                    $plannerOutput .= "<tr class=$rowNum>";
                    $plannerOutput .= '<td>';
                    $plannerOutput .= '<b>'.$row['course'].'.'.$row['class'].'</b><br/>';
                    $plannerOutput .= '</td>';
                    $plannerOutput .= '<td id="wordWrap">';
                    $plannerOutput .= $row['name'].'<br/>';
                    $unit = getUnit($connection2, $row['pupilsightUnitID'], $row['pupilsightCourseClassID']);
                    if (isset($unit[0])) {
                        $plannerOutput .= $unit[0];
                        if ($unit[1] != '') {
                            $plannerOutput .= '<br/><i>'.$unit[1].' '.__('Unit').'</i><br/>';
                        }
                    }
                    $plannerOutput .= "<div style='font-size: 85%; font-weight: normal; font-style: italic'>";
                    $plannerOutput .= $row['summary'];
                    $plannerOutput .= '</div>';
                    $plannerOutput .= '</td>';
                    $plannerOutput .= '<td>';
                    if ($row['homework'] == 'N' and $row['myHomeworkDueDateTime'] == '') {
                        $plannerOutput .= __('No');
                    } else {
                        if ($row['homework'] == 'Y') {
                            $plannerOutput .= __('Yes').': '.__('Teacher Recorded').'<br/>';
                            if ($row['homeworkSubmission'] == 'Y') {
                                $plannerOutput .= "<span style='font-size: 85%; font-style: italic'>+".__('Submission').'</span><br/>';
                                if ($row['homeworkCrowdAssess'] == 'Y') {
                                    $plannerOutput .= "<span style='font-size: 85%; font-style: italic'>+".__('Crowd Assessment').'</span><br/>';
                                }
                            }
                        }
                        if ($row['myHomeworkDueDateTime'] != '') {
                            $plannerOutput .= __('Yes').': '.__('Student Recorded').'</br>';
                        }
                    }
                    $plannerOutput .= '</td>';
                    $plannerOutput .= '<td>';
                    $plannerOutput .= "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_view_full.php&search='.$pupilsightPersonID.'&viewBy=date&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&date=$date&width=1000&height=550'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
                    $plannerOutput .= '</td>';
                    $plannerOutput .= '</tr>';
                }
                $plannerOutput .= '</table>';
            }
        }
        if ($classes == false) {
            $plannerOutput .= "<div style='margin-top: 2px' class='warning'>";
            $plannerOutput .= __('There are no records to display.');
            $plannerOutput .= '</div>';
        }

        //PREPARE RECENT GRADES
        $gradesOutput = "<div style='margin-top: 20px'><span style='font-size: 85%; font-weight: bold'>".__('Recent Feedback')."</span> . <span style='font-size: 70%'><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Markbook/markbook_view.php&search='.$pupilsightPersonID."'>".__('View Markbook').'</a></span></div>';
        $grades = false;

        //Get settings
        $enableEffort = getSettingByScope($connection2, 'Markbook', 'enableEffort');
        $enableRubrics = getSettingByScope($connection2, 'Markbook', 'enableRubrics');
        $attainmentAlternativeName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
        $attainmentAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev');
        $effortAlternativeName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');
        $effortAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev');
        $enableModifiedAssessment = getSettingByScope($connection2, 'Markbook', 'enableModifiedAssessment');

        try {
            $dataEntry = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
            $sqlEntry = "SELECT *, pupilsightMarkbookColumn.comment AS commentOn, pupilsightMarkbookColumn.uploadedResponse AS uploadedResponseOn, pupilsightMarkbookEntry.comment AS comment FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonIDStudent=:pupilsightPersonID AND complete='Y' AND completeDate<='".date('Y-m-d')."' AND viewableParents='Y' ORDER BY completeDate DESC LIMIT 0, 3";
            $resultEntry = $connection2->prepare($sqlEntry);
            $resultEntry->execute($dataEntry);
        } catch (PDOException $e) {
            $gradesOutput .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        if ($resultEntry->rowCount() > 0) {
            $showParentAttainmentWarning = getSettingByScope($connection2, 'Markbook', 'showParentAttainmentWarning');
            $showParentEffortWarning = getSettingByScope($connection2, 'Markbook', 'showParentEffortWarning');
            $grades = true;
            $gradesOutput .= "<table cellspacing='0' style='margin: 3px 0px; width: 100%'>";
            $gradesOutput .= "<tr class='head'>";
            $gradesOutput .= "<th style='width: 120px'>";
            $gradesOutput .= __('Assessment');
            $gradesOutput .= '</th>';
            if ($enableModifiedAssessment == 'Y') {
                $gradesOutput .= "<th style='width: 75px'>";
                $gradesOutput .= __('Modified');
                $gradesOutput .= '</th>';
            }
            $gradesOutput .= "<th style='width: 75px'>";
            if ($attainmentAlternativeName != '') {
                $gradesOutput .= $attainmentAlternativeName;
            } else {
                $gradesOutput .= __('Attainment');
            }
            $gradesOutput .= '</th>';
            if ($enableEffort == 'Y') {
                $gradesOutput .= "<th style='width: 75px'>";
                if ($effortAlternativeName != '') {
                    $gradesOutput .= $effortAlternativeName;
                } else {
                    $gradesOutput .= __('Effort');
                }
            }
            $gradesOutput .= '</th>';
            $gradesOutput .= '<th>';
            $gradesOutput .= __('Comment');
            $gradesOutput .= '</th>';
            $gradesOutput .= "<th style='width: 75px'>";
            $gradesOutput .= __('Submission');
            $gradesOutput .= '</th>';
            $gradesOutput .= '</tr>';

            $count3 = 0;
            while ($rowEntry = $resultEntry->fetch()) {
                if ($count3 % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count3;

                $gradesOutput .= "<a name='".$rowEntry['pupilsightMarkbookEntryID']."'></a>";

                $gradesOutput .= "<tr class=$rowNum>";
                $gradesOutput .= '<td>';
                $gradesOutput .= "<span title='".htmlPrep($rowEntry['description'])."'>".$rowEntry['name'].'</span><br/>';
                $gradesOutput .= "<span style='font-size: 90%; font-style: italic; font-weight: normal'>";
                $gradesOutput .= __('Marked on').' '.dateConvertBack($guid, $rowEntry['completeDate']).'<br/>';
                $gradesOutput .= '</span>';
                $gradesOutput .= '</td>';
                if ($enableModifiedAssessment == 'Y') {
                    if (!is_null($rowEntry['modifiedAssessment'])) {
                        $gradesOutput .= "<td>";
                        $gradesOutput .= ynExpander($guid, $rowEntry['modifiedAssessment']);
                        $gradesOutput .= '</td>';
                    }
                    else {
                        $gradesOutput .= "<td class='dull' style='color: #bbb; text-align: center'>";
                        $gradesOutput .= __('N/A');
                        $gradesOutput .= '</td>';
                    }
                }
                if ($rowEntry['attainment'] == 'N' or ($rowEntry['pupilsightScaleIDAttainment'] == '' and $rowEntry['pupilsightRubricIDAttainment'] == '')) {
                    $gradesOutput .= "<td class='dull' style='color: #bbb; text-align: center'>";
                    $gradesOutput .= __('N/A');
                    $gradesOutput .= '</td>';
                } else {
                    $gradesOutput .= "<td style='text-align: center'>";
                    $attainmentExtra = '';
                    try {
                        $dataAttainment = array('pupilsightScaleID' => $rowEntry['pupilsightScaleIDAttainment']);
                        $sqlAttainment = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
                        $resultAttainment = $connection2->prepare($sqlAttainment);
                        $resultAttainment->execute($dataAttainment);
                    } catch (PDOException $e) {
                    }
                    if ($resultAttainment->rowCount() == 1) {
                        $rowAttainment = $resultAttainment->fetch();
                        $attainmentExtra = '<br/>'.__($rowAttainment['usage']);
                    }
                    $styleAttainment = "style='font-weight: bold'";
                    if ($rowEntry['attainmentConcern'] == 'Y' and $showParentAttainmentWarning == 'Y') {
                        $styleAttainment = "style='color: #".$alert['color'].'; font-weight: bold; border: 2px solid #'.$alert['color'].'; padding: 2px 4px; background-color: #'.$alert['colorBG']."'";
                    } elseif ($rowEntry['attainmentConcern'] == 'P' and $showParentAttainmentWarning == 'Y') {
                        $styleAttainment = "style='color: #390; font-weight: bold; border: 2px solid #390; padding: 2px 4px; background-color: #D4F6DC'";
                    }
                    $gradesOutput .= "<div $styleAttainment>".$rowEntry['attainmentValue'];
                    if ($rowEntry['pupilsightRubricIDAttainment'] != '' AND $enableRubrics =='Y') {
                        $gradesOutput .= "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Markbook/markbook_view_rubric.php&pupilsightRubricID='.$rowEntry['pupilsightRubricIDAttainment'].'&pupilsightCourseClassID='.$rowEntry['pupilsightCourseClassID'].'&pupilsightMarkbookColumnID='.$rowEntry['pupilsightMarkbookColumnID'].'&pupilsightPersonID='.$pupilsightPersonID."&mark=FALSE&type=attainment&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='View Rubric' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/rubric.png'/></a>";
                    }
                    $gradesOutput .= '</div>';
                    if ($rowEntry['attainmentValue'] != '') {
                        $gradesOutput .= "<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'><b>".htmlPrep(__($rowEntry['attainmentDescriptor'])).'</b>'.__($attainmentExtra).'</div>';
                    }
                    $gradesOutput .= '</td>';
                }
                if ($enableEffort == 'Y') {
                    if ($rowEntry['effort'] == 'N' or ($rowEntry['pupilsightScaleIDEffort'] == '' and $rowEntry['pupilsightRubricIDEffort'] == '')) {
                        $gradesOutput .= "<td class='dull' style='color: #bbb; text-align: center'>";
                        $gradesOutput .= __('N/A');
                        $gradesOutput .= '</td>';
                    } else {
                        $gradesOutput .= "<td style='text-align: center'>";
                        $effortExtra = '';
                        try {
                            $dataEffort = array('pupilsightScaleID' => $rowEntry['pupilsightScaleIDEffort']);
                            $sqlEffort = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
                            $resultEffort = $connection2->prepare($sqlEffort);
                            $resultEffort->execute($dataEffort);
                        } catch (PDOException $e) {
                        }
                        if ($resultEffort->rowCount() == 1) {
                            $rowEffort = $resultEffort->fetch();
                            $effortExtra = '<br/>'.__($rowEffort['usage']);
                        }
                        $styleEffort = "style='font-weight: bold'";
                        if ($rowEntry['effortConcern'] == 'Y' and $showParentEffortWarning == 'Y') {
                            $styleEffort = "style='color: #".$alert['color'].'; font-weight: bold; border: 2px solid #'.$alert['color'].'; padding: 2px 4px; background-color: #'.$alert['colorBG']."'";
                        }
                        $gradesOutput .= "<div $styleEffort>".$rowEntry['effortValue'];
                        if ($rowEntry['pupilsightRubricIDEffort'] != '' AND $enableRubrics =='Y') {
                            $gradesOutput .= "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Markbook/markbook_view_rubric.php&pupilsightRubricID='.$rowEntry['pupilsightRubricIDEffort'].'&pupilsightCourseClassID='.$rowEntry['pupilsightCourseClassID'].'&pupilsightMarkbookColumnID='.$rowEntry['pupilsightMarkbookColumnID'].'&pupilsightPersonID='.$pupilsightPersonID."&mark=FALSE&type=effort&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='View Rubric' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/rubric.png'/></a>";
                        }
                        $gradesOutput .= '</div>';
                        if ($rowEntry['effortValue'] != '') {
                            $gradesOutput .= "<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'><b>".htmlPrep(__($rowEntry['effortDescriptor'])).'</b>'.__($effortExtra).'</div>';
                        }
                        $gradesOutput .= '</td>';
                    }
                }
                if ($rowEntry['commentOn'] == 'N' and $rowEntry['uploadedResponseOn'] == 'N') {
                    $gradesOutput .= "<td class='dull' style='color: #bbb; text-align: left'>";
                    $gradesOutput .= __('N/A');
                    $gradesOutput .= '</td>';
                } else {
                    $gradesOutput .= '<td>';
                    if ($rowEntry['comment'] != '') {
                        if (mb_strlen($rowEntry['comment']) > 50) {
                            $gradesOutput .= "<script type='text/javascript'>";
                            $gradesOutput .= '$(document).ready(function(){';
                            $gradesOutput .= "\$(\".comment-$entryCount-$pupilsightPersonID\").hide();";
                            $gradesOutput .= "\$(\".show_hide-$entryCount-$pupilsightPersonID\").fadeIn(1000);";
                            $gradesOutput .= "\$(\".show_hide-$entryCount-$pupilsightPersonID\").click(function(){";
                            $gradesOutput .= "\$(\".comment-$entryCount-$pupilsightPersonID\").fadeToggle(1000);";
                            $gradesOutput .= '});';
                            $gradesOutput .= '});';
                            $gradesOutput .= '</script>';
                            $gradesOutput .= '<span>'.mb_substr($rowEntry['comment'], 0, 50).'...<br/>';
                            $gradesOutput .= "<a title='".__('View Description')."' class='show_hide-$entryCount-$pupilsightPersonID' onclick='return false;' href='#'>".__('Read more').'</a></span><br/>';
                        } else {
                            $gradesOutput .= nl2br($rowEntry['comment']);
                        }
                        $gradesOutput .= '<br/>';
                    }
                    if ($rowEntry['response'] != '') {
                        $gradesOutput .= "<a title='".__('Uploaded Response')."' href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowEntry['response']."'>".__('Uploaded Response').'</a><br/>';
                    }
                    $gradesOutput .= '</td>';
                }
                if ($rowEntry['pupilsightPlannerEntryID'] == 0) {
                    $gradesOutput .= "<td class='dull' style='color: #bbb; text-align: left'>";
                    $gradesOutput .= __('N/A');
                    $gradesOutput .= '</td>';
                } else {
                    try {
                        $dataSub = array('pupilsightPlannerEntryID' => $rowEntry['pupilsightPlannerEntryID']);
                        $sqlSub = "SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND homeworkSubmission='Y'";
                        $resultSub = $connection2->prepare($sqlSub);
                        $resultSub->execute($dataSub);
                    } catch (PDOException $e) {
                        $gradesOutput .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultSub->rowCount() != 1) {
                        $gradesOutput .= "<td class='dull' style='color: #bbb; text-align: left'>";
                        $gradesOutput .= __('N/A');
                        $gradesOutput .= '</td>';
                    } else {
                        $gradesOutput .= '<td>';
                        $rowSub = $resultSub->fetch();

                        try {
                            $dataWork = array('pupilsightPlannerEntryID' => $rowEntry['pupilsightPlannerEntryID'], 'pupilsightPersonID' => $pupilsightPersonID);
                            $sqlWork = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID ORDER BY count DESC';
                            $resultWork = $connection2->prepare($sqlWork);
                            $resultWork->execute($dataWork);
                        } catch (PDOException $e) {
                            $gradesOutput .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultWork->rowCount() > 0) {
                            $rowWork = $resultWork->fetch();

                            if ($rowWork['status'] == 'Exemption') {
                                $linkText = __('Exemption');
                            } elseif ($rowWork['version'] == 'Final') {
                                $linkText = __('Final');
                            } else {
                                $linkText = __('Draft').' '.$rowWork['count'];
                            }

                            $style = '';
                            $status = 'On Time';
                            if ($rowWork['status'] == 'Exemption') {
                                $status = __('Exemption');
                            } elseif ($rowWork['status'] == 'Late') {
                                $style = "style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'";
                                $status = __('Late');
                            }

                            if ($rowWork['type'] == 'File') {
                                $gradesOutput .= "<span title='".$rowWork['version'].". $status. ".sprintf(__('Submitted at %1$s on %2$s'), substr($rowWork['timestamp'], 11, 5), dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10)))."' $style><a href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowWork['location']."'>$linkText</a></span>";
                            } elseif ($rowWork['type'] == 'Link') {
                                $gradesOutput .= "<span title='".$rowWork['version'].". $status. ".sprintf(__('Submitted at %1$s on %2$s'), substr($rowWork['timestamp'], 11, 5), dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10)))."' $style><a target='_blank' href='".$rowWork['location']."'>$linkText</a></span>";
                            } else {
                                $gradesOutput .= "<span title='$status. ".sprintf(__('Recorded at %1$s on %2$s'), substr($rowWork['timestamp'], 11, 5), dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10)))."' $style>$linkText</span>";
                            }
                        } else {
                            if (date('Y-m-d H:i:s') < $rowSub['homeworkDueDateTime']) {
                                $gradesOutput .= "<span title='Pending'>".__('Pending').'</span>';
                            } else {
                                if (!empty($dateStart) && $dateStart > $rowSub['date']) {
                                    $gradesOutput .= "<span title='".__('Student joined school after assessment was given.')."' style='color: #000; font-weight: normal; border: 2px none #ff0000; padding: 2px 4px'>".__('NA').'</span>';
                                } else {
                                    if ($rowSub['homeworkSubmissionRequired'] == 'Compulsory') {
                                        $gradesOutput .= "<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>".__('Incomplete').'</div>';
                                    } else {
                                        $gradesOutput .= __('Not submitted online');
                                    }
                                }
                            }
                        }
                        $gradesOutput .= '</td>';
                    }
                }
                $gradesOutput .= '</tr>';
                if (strlen($rowEntry['comment']) > 50) {
                    $gradesOutput .= "<tr class='comment-$entryCount-$pupilsightPersonID' id='comment-$entryCount-$pupilsightPersonID'>";
                    $gradesOutput .= '<td colspan=6>';
                    $gradesOutput .= nl2br($rowEntry['comment']);
                    $gradesOutput .= '</td>';
                    $gradesOutput .= '</tr>';
                }
                ++$entryCount;
            }

            $gradesOutput .= '</table>';
        }
        if ($grades == false) {
            $gradesOutput .= "<div style='margin-top: 2px' class='warning'>";
            $gradesOutput .= __('There are no records to display.');
            $gradesOutput .= '</div>';
        }

        //PREPARE UPCOMING DEADLINES
        $deadlinesOutput = "<div style='margin-top: 20px'><span style='font-size: 85%; font-weight: bold'>".__('Upcoming Deadlines')."</span> . <span style='font-size: 70%'><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_deadlines.php&search='.$pupilsightPersonID."'>".__('View All Deadlines').'</a></span></div>';
        $deadlines = false;

        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = "
            (SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND (role='Teacher' OR (role='Student' AND viewableStudents='Y')) AND homeworkDueDateTime>'".date('Y-m-d H:i:s')."' AND ((date<'".date('Y-m-d')."') OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')))
            UNION
            (SELECT 'studentRecorded' AS type, pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, 'Y' AS viewableStudents, 'Y' AS viewableParents, 'Y' AS homework, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND (role='Teacher' OR (role='Student' AND viewableStudents='Y')) AND pupilsightPlannerEntryStudentHomework.homeworkDueDateTime>'".date('Y-m-d H:i:s')."' AND ((date<'".date('Y-m-d')."') OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')))
            ORDER BY homeworkDueDateTime, type";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $deadlinesOutput .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() > 0) {
            $deadlines = true;
            $deadlinesOutput .= "<ol style='margin-left: 15px'>";
            while ($row = $result->fetch()) {
                $diff = (strtotime(substr($row['homeworkDueDateTime'], 0, 10)) - strtotime(date('Y-m-d'))) / 86400;
                $style = "style='padding-right: 3px;'";
                if ($diff < 2) {
                    $style = "style='padding-right: 3px; border-right: 10px solid #cc0000'";
                } elseif ($diff < 4) {
                    $style = "style='padding-right: 3px; border-right: 10px solid #D87718'";
                }
                $deadlinesOutput .= "<li $style>";
                $deadlinesOutput .= "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_view_full.php&search='.$pupilsightPersonID.'&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=date&date=$date&width=1000&height=550'>".$row['course'].'.'.$row['class'].'</a> ';
                $deadlinesOutput .= "<span style='font-style: italic'>".sprintf(__('Due at %1$s on %2$s'), substr($row['homeworkDueDateTime'], 11, 5), dateConvertBack($guid, substr($row['homeworkDueDateTime'], 0, 10)));
                $deadlinesOutput .= '</li>';
            }
            $deadlinesOutput .= '</ol>';
        }

        if ($deadlines == false) {
            $deadlinesOutput .= "<div style='margin-top: 2px' class='warning'>";
            $deadlinesOutput .= __('There are no records to display.');
            $deadlinesOutput .= '</div>';
        }

        //PREPARE TIMETABLE
        $timetable = false;
        $timetableOutput = '';
        if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_view.php')) {
            $date = date('Y-m-d');
            if (isset($_POST['ttDate'])) {
                $date = dateConvert($guid, $_POST['ttDate']);
            }
            $params = '';
            if ($classes != false or $grades != false or $deadlines != false) {
                $params = '&tab=1';
            }
            //$timetableOutputTemp = renderTT($guid, $connection2, $pupilsightPersonID, null, null, dateConvertToTimestamp($date), '', $params, 'narrow');
            if ($timetableOutputTemp != false) {
                $timetable = true;
                $timetableOutput .= $timetableOutputTemp;
            }
        }

        //PREPARE ACTIVITIES
        $activities = false;
        $activitiesOutput = false;
        if (!(isActionAccessible($guid, $connection2, '/modules/Activities/activities_view.php'))) {
            $activitiesOutput .= "<div class='alert alert-danger'>";
            $activitiesOutput .= __('Your request failed because you do not have access to this action.');
            $activitiesOutput .= '</div>';
        } else {
            $activities = true;

            $activitiesOutput .= "<div class='linkTop'>";
            $activitiesOutput .= "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Activities/activities_view.php&pupilsightPersonID=".$pupilsightPersonID."'>".__('View Available Activities').'</a>';
            $activitiesOutput .= '</div>';

            $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
            if ($dateType == 'Term') {
                $maxPerTerm = getSettingByScope($connection2, 'Activities', 'maxPerTerm');
            }
            try {
                $dataYears = array('pupilsightPersonID' => $pupilsightPersonID);
                $sqlYears = "SELECT * FROM pupilsightStudentEnrolment JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightSchoolYear.status='Current' AND pupilsightPersonID=:pupilsightPersonID ORDER BY sequenceNumber DESC";
                $resultYears = $connection2->prepare($sqlYears);
                $resultYears->execute($dataYears);
            } catch (PDOException $e) {
                $activitiesOutput .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultYears->rowCount() < 1) {
                $activitiesOutput .= "<div class='alert alert-danger'>";
                $activitiesOutput .= __('There are no records to display.');
                $activitiesOutput .= '</div>';
            } else {
                $yearCount = 0;
                while ($rowYears = $resultYears->fetch()) {
                    ++$yearCount;
                    try {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $rowYears['pupilsightSchoolYearID']);
                        $sql = "SELECT pupilsightActivity.*, pupilsightActivityStudent.status, NULL AS role FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID) WHERE pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $activitiesOutput .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($result->rowCount() < 1) {
                        $activitiesOutput .= "<div class='alert alert-danger'>";
                        $activitiesOutput .= __('There are no records to display.');
                        $activitiesOutput .= '</div>';
                    } else {
                        $activitiesOutput .= "<table cellspacing='0' style='width: 100%'>";
                        $activitiesOutput .= "<tr class='head'>";
                        $activitiesOutput .= '<th>';
                        $activitiesOutput .= __('Activity');
                        $activitiesOutput .= '</th>';
                        $options = getSettingByScope($connection2, 'Activities', 'activityTypes');
                        if ($options != '') {
                            $activitiesOutput .= '<th>';
                            $activitiesOutput .= __('Type');
                            $activitiesOutput .= '</th>';
                        }
                        $activitiesOutput .= '<th>';
                        if ($dateType != 'Date') {
                            $activitiesOutput .= __('Term');
                        } else {
                            $activitiesOutput .= __('Dates');
                        }
                        $activitiesOutput .= '</th>';
                        $activitiesOutput .= '<th>';
                        $activitiesOutput .= __('Slots');
                        $activitiesOutput .= '</th>';
                        $activitiesOutput .= '<th>';
                        $activitiesOutput .= __('Status');
                        $activitiesOutput .= '</th>';
                        $activitiesOutput .= '</tr>';

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
                            $activitiesOutput .= "<tr class=$rowNum>";
                            $activitiesOutput .= '<td>';
                            $activitiesOutput .= $row['name'];
                            $activitiesOutput .= '</td>';
                            if ($options != '') {
                                $activitiesOutput .= '<td>';
                                $activitiesOutput .= trim($row['type']);
                                $activitiesOutput .= '</td>';
                            }
                            $activitiesOutput .= '<td>';
                            if ($dateType != 'Date') {
                                $terms = getTerms($connection2, $_SESSION[$guid]['pupilsightSchoolYearID'], true);
                                $termList = '';
                                for ($i = 0; $i < count($terms); $i = $i + 2) {
                                    if (is_numeric(strpos($row['pupilsightSchoolYearTermIDList'], $terms[$i]))) {
                                        $termList .= $terms[($i + 1)].'<br/>';
                                    }
                                }
                                $activitiesOutput .= $termList;
                            } else {
                                if (substr($row['programStart'], 0, 4) == substr($row['programEnd'], 0, 4)) {
                                    if (substr($row['programStart'], 5, 2) == substr($row['programEnd'], 5, 2)) {
                                        $activitiesOutput .= date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))).' '.substr($row['programStart'], 0, 4);
                                    } else {
                                        $activitiesOutput .= date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))).' - '.date('F', mktime(0, 0, 0, substr($row['programEnd'], 5, 2))).'<br/>'.substr($row['programStart'], 0, 4);
                                    }
                                } else {
                                    $activitiesOutput .= date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))).' '.substr($row['programStart'], 0, 4).' -<br/>'.date('F', mktime(0, 0, 0, substr($row['programEnd'], 5, 2))).' '.substr($row['programEnd'], 0, 4);
                                }
                            }
                            $activitiesOutput .= '</td>';
                            $activitiesOutput .= '<td>';
                                try {
                                    $dataSlots = array('pupilsightActivityID' => $row['pupilsightActivityID']);
                                    $sqlSlots = 'SELECT pupilsightActivitySlot.*, pupilsightDaysOfWeek.name AS dayOfWeek, pupilsightSpace.name AS facility FROM pupilsightActivitySlot JOIN pupilsightDaysOfWeek ON (pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID) LEFT JOIN pupilsightSpace ON (pupilsightActivitySlot.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightActivityID=:pupilsightActivityID ORDER BY sequenceNumber';
                                    $resultSlots = $connection2->prepare($sqlSlots);
                                    $resultSlots->execute($dataSlots);
                                } catch (PDOException $e) {
                                    $activitiesOutput .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }
                                $count = 0;
                                while ($rowSlots = $resultSlots->fetch()) {
                                    $activitiesOutput .= '<b>'.$rowSlots['dayOfWeek'].'</b><br/>';
                                    $activitiesOutput .= '<i>'.__('Time').'</i>: '.substr($rowSlots['timeStart'], 0, 5).' - '.substr($rowSlots['timeEnd'], 0, 5).'<br/>';
                                    if ($rowSlots['pupilsightSpaceID'] != '') {
                                        $activitiesOutput .= '<i>'.__('Location').'</i>: '.$rowSlots['facility'];
                                    } else {
                                        $activitiesOutput .= '<i>'.__('Location').'</i>: '.$rowSlots['locationExternal'];
                                    }
                                    ++$count;
                                }
                                if ($count == 0) {
                                    $activitiesOutput .= '<i>'.__('None').'</i>';
                                }
                            $activitiesOutput .= '</td>';
                            $activitiesOutput .= '<td>';
                            if ($row['status'] != '') {
                                $activitiesOutput .= $row['status'];
                            } else {
                                $activitiesOutput .= '<i>'.__('NA').'</i>';
                            }
                            $activitiesOutput .= '</td>';
                            $activitiesOutput .= '</tr>';
                        }
                        $activitiesOutput .= '</table>';
                    }
                }
            }
        }

        //GET HOOKS INTO DASHBOARD
        $hooks = array();
        try {
            $dataHooks = array();
            $sqlHooks = "SELECT * FROM pupilsightHook WHERE type='Parental Dashboard'";
            $resultHooks = $connection2->prepare($sqlHooks);
            $resultHooks->execute($dataHooks);
        } catch (PDOException $e) {
            $return .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        if ($resultHooks->rowCount() > 0) {
            $count = 0;
            while ($rowHooks = $resultHooks->fetch()) {
                $options = unserialize($rowHooks['options']);
                //Check for permission to hook
                try {
                    $dataHook = array('pupilsightRoleIDCurrent' => $_SESSION[$guid]['pupilsightRoleIDCurrent'], 'sourceModuleName' => $options['sourceModuleName']);
                    $sqlHook = "SELECT pupilsightHook.name, pupilsightModule.name AS module, pupilsightAction.name AS action FROM pupilsightHook JOIN pupilsightModule ON (pupilsightHook.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightAction ON (pupilsightAction.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightPermission ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID) WHERE pupilsightAction.pupilsightModuleID=(SELECT pupilsightModuleID FROM pupilsightModule WHERE pupilsightPermission.pupilsightRoleID=:pupilsightRoleIDCurrent AND name=:sourceModuleName) AND pupilsightHook.type='Parental Dashboard'  AND pupilsightAction.name='".$options['sourceModuleAction']."' AND pupilsightModule.name='".$options['sourceModuleName']."' ORDER BY name";
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

        if ($classes == false and $grades == false and $deadlines == false and $timetable == false and $activities == false and count($hooks) < 1) {
            $return .= "<div class='alert alert-warning'>";
            $return .= __('There are no records to display.');
            $return .= '</div>';
        } else {
            $parentDashboardDefaultTab = getSettingByScope($connection2, 'School Admin', 'parentDashboardDefaultTab');
            $parentDashboardDefaultTabCount = null;

            $return .= "<div id='".$pupilsightPersonID."tabs' style='margin: 0 0'>";
            $return .= '<ul>';
            $tabCountExtraReset = 0;
            if ($classes != false or $grades != false or $deadlines != false) {
                $return .= "<li><a href='#tabs".$tabCountExtraReset."'>".__('Learning').'</a></li>';
                $tabCountExtraReset++;
                if ($parentDashboardDefaultTab == 'Planner')
                    $parentDashboardDefaultTabCount = $tabCountExtraReset;
            }
            if ($timetable != false) {
                $return .= "<li><a href='#tabs".$tabCountExtraReset."'>".__('Timetable').'</a></li>';
                $tabCountExtraReset++;
                if ($parentDashboardDefaultTab == 'Timetable')
                    $parentDashboardDefaultTabCount = $tabCountExtraReset;
            }
            if ($activities != false) {
                $return .= "<li><a href='#tabs".$tabCountExtraReset."'>".__('Activities').'</a></li>';
                $tabCountExtraReset++;
                if ($parentDashboardDefaultTab == 'Activities')
                    $parentDashboardDefaultTabCount = $tabCountExtraReset;
            }
            $tabCountExtra = $tabCountExtraReset;
            foreach ($hooks as $hook) {
                ++$tabCountExtra;
                $return .= "<li><a href='#tabs".$tabCountExtra."'>".__($hook['name']).'</a></li>';
            }
            $return .= '</ul>';

            $tabCountExtraReset = 0;
            if ($classes != false or $grades != false or $deadlines != false) {
                $return .= "<div id='tabs".$tabCountExtraReset."' class='overflow-x-auto'>";
                $return .= $plannerOutput;
                $return .= $gradesOutput;
                $return .= $deadlinesOutput;
                $return .= '</div>';
                $tabCountExtraReset++;
            }
            if ($timetable != false) {
                $return .= "<div id='tabs".$tabCountExtraReset."' class='overflow-x-auto'>";
                $return .= $timetableOutput;
                $return .= '</div>';
                $tabCountExtraReset++;
            }
            if ($activities != false) {
                $return .= "<div id='tabs".$tabCountExtraReset."' class='overflow-x-auto'>";
                $return .= $activitiesOutput;
                $return .= '</div>';
                $tabCountExtraReset++;
            }
            $tabCountExtra = $tabCountExtraReset;
            foreach ($hooks as $hook) {
                if ($parentDashboardDefaultTab == $hook['name'])
                    $parentDashboardDefaultTabCount = $tabCountExtra+1;
                ++$tabCountExtra;
                $return .= "<div style='min-height: 100px' id='tabs".$tabCountExtra."'>";
                $include = $_SESSION[$guid]['absolutePath'].'/modules/'.$hook['sourceModuleName'].'/'.$hook['sourceModuleInclude'];
                if (!file_exists($include)) {
                    $return .= "<div class='alert alert-danger'>";
                    $return .= __('The selected page cannot be displayed due to a hook error.');
                    $return .= '</div>';
                } else {
                    $return .= include $include;
                }
                $return .= '</div>';
            }
            $return .= '</div>';
        }


        $defaultTab = 0;
        if (isset($_GET['tab'])) {
            $defaultTab = $_GET['tab'];
        }
        else if (!is_null($parentDashboardDefaultTabCount)) {
            $defaultTab = $parentDashboardDefaultTabCount-1;
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
