<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//$role can be teacher, student or parent. If no role is specified, the default is teacher.
function getInternalAssessmentRecord($guid, $connection2, $pupilsightPersonID, $role = 'teacher')
{
    $output = '';

    //Get alternative header names
    $attainmentAlternativeName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
    $attainmentAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev');
    $effortAlternativeName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');
    $effortAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev');
    $alert = getAlert($guid, $connection2, 002);

    //Get school years in reverse order
    try {
        $dataYears = array('pupilsightPersonID' => $pupilsightPersonID);
        $sqlYears = "SELECT * FROM pupilsightSchoolYear JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE (status='Current' OR status='Past') AND pupilsightPersonID=:pupilsightPersonID ORDER BY sequenceNumber DESC";
        $resultYears = $connection2->prepare($sqlYears);
        $resultYears->execute($dataYears);
    } catch (PDOException $e) {
        $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($resultYears->rowCount() < 1) {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('There are no records to display.');
        $output .= '</div>';
    } else {
        $results = false;
        while ($rowYears = $resultYears->fetch()) {
            //Get and output Internal Assessments
            try {
                $dataInternalAssessment = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightPersonID2' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $rowYears['pupilsightSchoolYearID']);
                if ($role == 'teacher') {
                    $sqlInternalAssessment = "SELECT pupilsightInternalAssessmentColumn.*, pupilsightInternalAssessmentEntry.*, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourse.name AS courseFull FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightInternalAssessmentColumn ON (pupilsightInternalAssessmentColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightInternalAssessmentEntry ON (pupilsightInternalAssessmentEntry.pupilsightInternalAssessmentColumnID=pupilsightInternalAssessmentColumn.pupilsightInternalAssessmentColumnID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightInternalAssessmentEntry.pupilsightPersonIDStudent=:pupilsightPersonID2 AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND completeDate<='".date('Y-m-d')."' ORDER BY completeDate DESC, pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";
                } elseif ($role == 'student') {
                    $sqlInternalAssessment = "SELECT pupilsightInternalAssessmentColumn.*, pupilsightInternalAssessmentEntry.*, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourse.name AS courseFull FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightInternalAssessmentColumn ON (pupilsightInternalAssessmentColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightInternalAssessmentEntry ON (pupilsightInternalAssessmentEntry.pupilsightInternalAssessmentColumnID=pupilsightInternalAssessmentColumn.pupilsightInternalAssessmentColumnID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightInternalAssessmentEntry.pupilsightPersonIDStudent=:pupilsightPersonID2 AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND completeDate<='".date('Y-m-d')."' AND viewableStudents='Y' ORDER BY completeDate DESC, pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";
                } elseif ($role == 'parent') {
                    $sqlInternalAssessment = "SELECT pupilsightInternalAssessmentColumn.*, pupilsightInternalAssessmentEntry.*, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourse.name AS courseFull FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightInternalAssessmentColumn ON (pupilsightInternalAssessmentColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightInternalAssessmentEntry ON (pupilsightInternalAssessmentEntry.pupilsightInternalAssessmentColumnID=pupilsightInternalAssessmentColumn.pupilsightInternalAssessmentColumnID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightInternalAssessmentEntry.pupilsightPersonIDStudent=:pupilsightPersonID2 AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND completeDate<='".date('Y-m-d')."' AND viewableParents='Y'  ORDER BY completeDate DESC, pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";
                }
                $resultInternalAssessment = $connection2->prepare($sqlInternalAssessment);
                $resultInternalAssessment->execute($dataInternalAssessment);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultInternalAssessment->rowCount() > 0) {
                $results = true;
                $output .= '<h4>';
                $output .= $rowYears['name'];
                $output .= '</h4>';
                $output .= "<table cellspacing='0' style='width: 100%'>";
                $output .= "<tr class='head'>";
                $output .= "<th style='width: 160px'>";
                $output .= __('Assessment');
                $output .= '</th>';
                $output .= "<th style='width: 180px'>";
                $output .= __('Course');
                $output .= '</th>';
                $output .= "<th style='width: 75px; text-align: center'>";
                if ($attainmentAlternativeName != '') {
                    $output .= $attainmentAlternativeName;
                } else {
                    $output .= __('Attainment');
                }
                $output .= '</th>';
                $output .= "<th style='width: 75px; text-align: center'>";
                if ($effortAlternativeName != '') {
                    $output .= $effortAlternativeName;
                } else {
                    $output .= __('Effort');
                }
                $output .= '</th>';
                $output .= '<th>';
                $output .= __('Comment');
                $output .= '</th>';

                $output .= '</tr>';

                $count = 0;
                while ($rowInternalAssessment = $resultInternalAssessment->fetch()) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;

                    $output .= "<tr class=$rowNum>";
                    $output .= '<td>';
                    $output .= "<span title='".htmlPrep($rowInternalAssessment['description'])."'><b><u>".$rowInternalAssessment['name'].'</u></b></span><br/>';
                    $output .= "<span style='font-size: 90%; font-style: italic; font-weight: normal'>";
                    if ($rowInternalAssessment['completeDate'] != '') {
                        $output .= __('Marked on').' '.dateConvertBack($guid, $rowInternalAssessment['completeDate']).'<br/>';
                    } else {
                        $output .= __('Unmarked').'<br/>';
                    }
                    if ($rowInternalAssessment['attachment'] != '' and file_exists($_SESSION[$guid]['absolutePath'].'/'.$rowInternalAssessment['attachment'])) {
                        $output .= " | <a title='".__('Download more information')."' href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowInternalAssessment['attachment']."'>".__('More info')."</a>";
                    }
                    $output .= '</span>';
                    $output .= '</td>';
                    $output .= "<td>";
                    $output .= $rowInternalAssessment['courseFull'];
                    $output .= '</td>';
                    if ($rowInternalAssessment['attainment'] == 'N' or $rowInternalAssessment['pupilsightScaleIDAttainment'] == '') {
                        $output .= "<td class='dull' style='color: #bbb; text-align: center'>";
                        $output .= __('N/A');
                        $output .= '</td>';
                    } else {
                        $output .= "<td style='text-align: center'>";
                        $attainmentExtra = '';
                        try {
                            $dataAttainment = array('pupilsightScaleID' => $rowInternalAssessment['pupilsightScaleIDAttainment']);
                            $sqlAttainment = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
                            $resultAttainment = $connection2->prepare($sqlAttainment);
                            $resultAttainment->execute($dataAttainment);
                        } catch (PDOException $e) {
                            $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultAttainment->rowCount() == 1) {
                            $rowAttainment = $resultAttainment->fetch();
                            $attainmentExtra = __($rowAttainment['usage']);
                        }
                        $styleAttainment = "style='font-weight: bold'";
                        $output .= "<div $styleAttainment>".$rowInternalAssessment['attainmentValue'].'</div>';
                        if ($rowInternalAssessment['attainmentValue'] != '') {
                            $output .= "<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'>".__($attainmentExtra).'</div>';
                        }
                        $output .= '</td>';
                    }
                    if ($rowInternalAssessment['effort'] == 'N' or $rowInternalAssessment['pupilsightScaleIDEffort'] == '') {
                        $output .= "<td class='dull' style='color: #bbb; text-align: center'>";
                        $output .= __('N/A');
                        $output .= '</td>';
                    } else {
                        $output .= "<td style='text-align: center'>";
                        $effortExtra = '';
                        try {
                            $dataEffort = array('pupilsightScaleID' => $rowInternalAssessment['pupilsightScaleIDEffort']);
                            $sqlEffort = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
                            $resultEffort = $connection2->prepare($sqlEffort);
                            $resultEffort->execute($dataEffort);
                        } catch (PDOException $e) {
                            $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultEffort->rowCount() == 1) {
                            $rowEffort = $resultEffort->fetch();
                            $effortExtra = __($rowEffort['usage']);
                        }
                        $styleEffort = "style='font-weight: bold'";
                        $output .= "<div $styleEffort>".$rowInternalAssessment['effortValue'];
                        $output .= '</div>';
                        if ($rowInternalAssessment['effortValue'] != '') {
                            $output .= "<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'>";
                            if ($effortExtra != '') {
                                $output .= __($effortExtra);
                            }
                            $output .= '</div>';
                        }
                        $output .= '</td>';
                    }
                    if ($rowInternalAssessment['comment'] == 'N' and $rowInternalAssessment['uploadedResponse'] == 'N') {
                        echo "<td class='dull' style='color: #bbb; text-align: left'>";
                        echo __('N/A');
                        echo '</td>';
                    } else {
                        $output .= '<td>';
                        if ($rowInternalAssessment['comment'] != '') {
                            $output .= $rowInternalAssessment['comment'].'<br/>';
                        }
                        if ($rowInternalAssessment['response'] != '') {
                            $output .= "<a title='".__('Uploaded Response')."' href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowInternalAssessment['response']."'>".__('Uploaded Response').'</a><br/>';
                        }
                        $output .= '</td>';
                    }
                    $output .= '</tr>';
                }

                $output .= '</table>';
            }
        }
        if ($results == false) {
            $output .= "<div class='alert alert-danger'>";
            $output .= __('There are no records to display.');
            $output .= '</div>';
        }
    }

    return $output;
}

function sidebarExtra($guid, $connection2, $pupilsightCourseClassID, $mode = 'manage')
{
    $output = '';

    $output .= '<div class="column-no-break">';
    $output .= '<h2>';
    $output .= __('Select Class');
    $output .= '</h2>';

    $classes = array();
    
    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
    $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClass.reportable='Y' ORDER BY course, class";
    $result = $connection2->prepare($sql);
    $result->execute($data);

    if ($result->rowCount() > 0) {
        $group = '--'.__('My Classes').'--';
        while ($class = $result->fetch()) {
            $classes[$group][$class['pupilsightCourseClassID']] = $class['course'].'.'.$class['class'];
        }
    }

    if ($mode == 'manage' or ($mode == 'write' and getHighestGroupedAction($guid, '/modules/Formal Assessment/internalAssessment_write_data.php', $connection2) == 'Write Internal Assessments_all')) {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.reportable='Y' ORDER BY course, class";
        $result = $connection2->prepare($sql);
        $result->execute($data);

        if ($result->rowCount() > 0) {
            $group = '--'.__('All Classes').'--';
            while ($class = $result->fetch()) {
                $classes[$group][$class['pupilsightCourseClassID']] = $class['course'].'.'.$class['class'];
            }
        }
    }

    $form = Form::create('classSelect', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->addHiddenValue('q', '/modules/Formal Assessment/internalAssessment_'.$mode.'.php');

    $row = $form->addRow();
        $row->addSelect('pupilsightCourseClassID')
            ->fromArray($classes)
            ->selected($pupilsightCourseClassID)
            ->placeholder()
            ->setClass('float-none w-48');
        $row->addSubmit(__('Go'));

    $output .= $form->getOutput();

    $output .= '</div>';

    return $output;
}

function externalAssessmentDetails($guid, $pupilsightPersonID, $connection2, $pupilsightYearGroupID = null, $manage = false, $search = '', $allStudents = '')
{
    try {
        $dataAssessments = array('pupilsightPersonID' => $pupilsightPersonID);
        $sqlAssessments = 'SELECT * FROM pupilsightExternalAssessmentStudent JOIN pupilsightExternalAssessment ON (pupilsightExternalAssessmentStudent.pupilsightExternalAssessmentID=pupilsightExternalAssessment.pupilsightExternalAssessmentID) WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY date';
        $resultAssessments = $connection2->prepare($sqlAssessments);
        $resultAssessments->execute($dataAssessments);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($resultAssessments->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        while ($rowAssessments = $resultAssessments->fetch()) {
            echo '<h2>';
            echo __($rowAssessments['name'])." <span style='font-size: 75%; font-style: italic'>(".substr(strftime('%B', mktime(0, 0, 0, substr($rowAssessments['date'], 5, 2))), 0, 3).' '.substr($rowAssessments['date'], 0, 4).')</span>';
            if ($manage == true) {
                echo "<a class='icon_color' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/externalAssessment_manage_details_edit.php&pupilsightPersonID=$pupilsightPersonID&pupilsightExternalAssessmentStudentID=".$rowAssessments['pupilsightExternalAssessmentStudentID']."&search=$search&allStudents=$allStudents'><i style='margin-left: 5px' title='".__('Edit')."' class='mdi mdi-lead-pencil'></i></a> ";
                echo "<a class='icon_color' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/externalAssessment_manage_details_delete.php&pupilsightPersonID=$pupilsightPersonID&pupilsightExternalAssessmentStudentID=".$rowAssessments['pupilsightExternalAssessmentStudentID']."&search=$search&allStudents=$allStudents'><i title='".__('Delete')."' class='far fa-trssash-alt'> </i></a>";
            }
            echo '</h2>';
            echo '<p>';
            echo __($rowAssessments['description']);
            echo '</p>';

            if ($rowAssessments['attachment'] != '') {
                echo "<div class='linkTop'>";
                echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowAssessments['attachment']."'>".__('Uploaded File').'</a>';
                echo '</div>';
            }

            //Get results
            try {
                $dataResults = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightExternalAssessmentStudentID' => $rowAssessments['pupilsightExternalAssessmentStudentID']);
                $sqlResults = "SELECT pupilsightExternalAssessmentField.name, pupilsightExternalAssessmentField.category, resultGrade.value, resultGrade.descriptor, result.usage, result.lowestAcceptable, resultGrade.sequenceNumber
                    FROM pupilsightExternalAssessmentStudentEntry
                        JOIN pupilsightExternalAssessmentStudent ON (pupilsightExternalAssessmentStudentEntry.pupilsightExternalAssessmentStudentID=pupilsightExternalAssessmentStudent.pupilsightExternalAssessmentStudentID)
                        JOIN pupilsightExternalAssessmentField ON (pupilsightExternalAssessmentStudentEntry.pupilsightExternalAssessmentFieldID=pupilsightExternalAssessmentField.pupilsightExternalAssessmentFieldID)
                        JOIN pupilsightExternalAssessment ON (pupilsightExternalAssessment.pupilsightExternalAssessmentID=pupilsightExternalAssessmentField.pupilsightExternalAssessmentID)
                        JOIN pupilsightScaleGrade AS resultGrade ON (pupilsightExternalAssessmentStudentEntry.pupilsightScaleGradeID=resultGrade.pupilsightScaleGradeID)
                        JOIN pupilsightScale AS result ON (result.pupilsightScaleID=resultGrade.pupilsightScaleID)
                    WHERE pupilsightPersonID=:pupilsightPersonID
                        AND result.active='Y'
                        AND pupilsightExternalAssessment.active='Y'
                        AND pupilsightExternalAssessmentStudentEntry.pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID
                    ORDER BY category, pupilsightExternalAssessmentField.order";
                $resultResults = $connection2->prepare($sqlResults);
                $resultResults->execute($dataResults);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultResults->rowCount() < 1) {
                echo "<div class='alert alert-warning'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                $lastCategory = '';
                $count = 0;
                $rowNum = 'odd';
                while ($rowResults = $resultResults->fetch()) {
                    if ($rowResults['category'] != $lastCategory) {
                        if ($count != 0) {
                            echo '</table>';
                        }
                        echo "<p style='font-weight: bold; margin-bottom: 0px'>";
                        if (strpos($rowResults['category'], '_') === false) {
                            echo $rowResults['category'];
                        } else {
                            echo substr($rowResults['category'], (strpos($rowResults['category'], '_') + 1));
                        }
                        echo '</p>';

                        echo "<table cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo "<th style='width:40%'>";
                        echo __('Item');
                        echo '</th>';
                        echo "<th style='width:15%'>";
                        echo __('Result');
                        echo '</th>';
                        echo '</tr>';
                    }

                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                    echo '<td>';
                    echo __($rowResults['name']);
                    echo '</td>';
                    echo '<td>';
                    $style = '';
                    if ($rowResults['lowestAcceptable'] != '' and $rowResults['sequenceNumber'] > $rowResults['lowestAcceptable']) {
                        $style = "style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'";
                    }
                    echo "<span $style title='".__($rowResults['usage'])."'>".__($rowResults['value']).'</span>';
                    echo '</td>';
                    echo '</tr>';

                    $lastCategory = $rowResults['category'];
                    ++$count;
                }
                echo '</table>';
            }
        }
    }
}
