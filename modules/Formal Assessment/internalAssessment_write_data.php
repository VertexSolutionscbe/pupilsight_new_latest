<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

//Get alternative header names
$attainmentAlternativeName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
$attainmentAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev');
$hasAttainmentName = ($attainmentAlternativeName != '' && $attainmentAlternativeNameAbrev != '');

$effortAlternativeName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');
$effortAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev');
$hasEffortName = ($effortAlternativeName != '' && $effortAlternativeNameAbrev != '');

echo "<script type='text/javascript'>";
    echo '$(document).ready(function(){';
        echo "autosize($('textarea'));";
    echo '});';
echo '</script>';

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_write_data.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Check if school year specified
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
        $pupilsightInternalAssessmentColumnID = $_GET['pupilsightInternalAssessmentColumnID'] ?? '';
        if ($pupilsightCourseClassID == '' or $pupilsightInternalAssessmentColumnID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Write Internal Assessments_all') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourse.name AS courseName, pupilsightCourseClass.nameShort AS class, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
                } else {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourse.name AS courseName, pupilsightCourseClass.nameShort AS class, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID AND role='Teacher'";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                try {
                    $data2 = array('pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID);
                    $sql2 = "SELECT pupilsightInternalAssessmentColumn.*, attainmentScale.name as scaleNameAttainment, attainmentScale.usage as usageAttainment, attainmentScale.lowestAcceptable as lowestAcceptableAttainment, effortScale.name as scaleNameEffort, effortScale.usage as usageEffort, effortScale.lowestAcceptable as lowestAcceptableEffort
                        FROM pupilsightInternalAssessmentColumn 
                        LEFT JOIN pupilsightScale as attainmentScale ON (attainmentScale.pupilsightScaleID=pupilsightInternalAssessmentColumn.pupilsightScaleIDAttainment)
                        LEFT JOIN pupilsightScale as effortScale ON (effortScale.pupilsightScaleID=pupilsightInternalAssessmentColumn.pupilsightScaleIDEffort)
                        WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID";
                    $result2 = $connection2->prepare($sql2);
                    $result2->execute($data2);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result2->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo 'The selected column does not exist, or you do not have access to it.';
                    echo '</div>';
                } else {
                    //Let's go!
                    $class = $result->fetch();
                    $values = $result2->fetch();

                    $page->breadcrumbs
                        ->add(__('Write {courseClass} Internal Assessments', ['courseClass' => $class['course'].'.'.$class['class']]), 'internalAssessment_write.php', ['pupilsightCourseClassID' => $pupilsightCourseClassID])
                        ->add(__('Enter Internal Assessment Results'));

                    if (isset($_GET['return'])) {
                        returnProcess($guid, $_GET['return'], null, array('error3' => 'Your request failed due to an attachment error.', 'success0' => 'Your request was completed successfully.'));
                    }

                    $hasAttainment = $values['attainment'] == 'Y';
                    $hasEffort = $values['effort'] == 'Y';
                    $hasComment = $values['comment'] == 'Y';
                    $hasUpload = $values['uploadedResponse'] == 'Y';

                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID, 'today' => date('Y-m-d'));
                    $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightPerson.dateStart, pupilsightInternalAssessmentEntry.*
                        FROM pupilsightCourseClassPerson 
                        JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
                        LEFT JOIN pupilsightInternalAssessmentEntry ON (pupilsightInternalAssessmentEntry.pupilsightPersonIDStudent=pupilsightPerson.pupilsightPersonID AND pupilsightInternalAssessmentEntry.pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID)
                        WHERE pupilsightCourseClassPerson.pupilsightCourseClassID=:pupilsightCourseClassID 
                        AND pupilsightCourseClassPerson.reportable='Y' AND pupilsightCourseClassPerson.role='Student' 
                        AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today) 
                        ORDER BY pupilsightPerson.surname, pupilsightPerson.preferredName";
                    $result = $pdo->executeQuery($data, $sql);
                    $students = ($result->rowCount() > 0)? $result->fetchAll() : array();

                    $form = Form::create('internalAssessment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/internalAssessment_write_dataProcess.php?pupilsightCourseClassID='.$pupilsightCourseClassID.'&pupilsightInternalAssessmentColumnID='.$pupilsightInternalAssessmentColumnID.'&address='.$_SESSION[$guid]['address']);
                    $form->setFactory(DatabaseFormFactory::create($pdo));
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    $form->addRow()->addHeading(__('Assessment Details'));

                    $row = $form->addRow();
                        $row->addLabel('description', __('Description'));
                        $row->addTextField('description')->required()->maxLength(1000);

                    $row = $form->addRow();
                        $row->addLabel('file', __('Attachment'));
                        $row->addFileUpload('file')->setAttachment('attachment', $_SESSION[$guid]['absoluteURL'], $values['attachment']);


                    if (count($students) == 0) {
                        $form->addRow()->addHeading(__('Students'));
                        $form->addRow()->addAlert(__('There are no records to display.'), 'error');
                    } else {
                        $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth colorOddEven noMargin noPadding noBorder');

                        $completeText = !empty($values['completeDate'])? __('Marked on').' '.dateConvertBack($guid, $values['completeDate']) : __('Unmarked');
                        $detailsText = $values['type'];
                        if ($values['attachment'] != '' and file_exists($_SESSION[$guid]['absolutePath'].'/'.$values['attachment'])) {
                            $detailsText .= " | <a title='".__('Download more information')."' href='".$_SESSION[$guid]['absoluteURL'].'/'.$values['attachment']."'>".__('More info').'</a>';
                        }

                        $header = $table->addHeaderRow();
                            $header->addTableCell(__('Student'))->rowSpan(2);
                            $header->addTableCell($values['name'])
                                ->setTitle($values['description'])
                                ->append('<br><span class="small emphasis" style="font-weight:normal;">'.$completeText.'</span>')
                                ->append('<br><span class="small emphasis" style="font-weight:normal;">'.$detailsText.'</span>')
                                ->setClass('textCenter')
                                ->colSpan(3);

                        $header = $table->addHeaderRow();
                            if ($hasAttainment) {
                                $scale = '';
                                if (!empty($values['pupilsightScaleIDAttainment'])) {
                                    $form->addHiddenValue('scaleAttainment', $values['pupilsightScaleIDAttainment']);
                                    $form->addHiddenValue('lowestAcceptableAttainment', $values['lowestAcceptableAttainment']);
                                    $scale = ' - '.$values['scaleNameAttainment'];
                                    $scale .= $values['usageAttainment']? ': '.$values['usageAttainment'] : '';
                                }
                                $header->addContent($hasAttainmentName? $attainmentAlternativeNameAbrev : __('Att'))
                                    ->setTitle(($hasAttainmentName? $attainmentAlternativeName : __('Attainment')).$scale)
                                    ->setClass('textCenter');
                            }
        
                            if ($hasEffort) {
                                $scale = '';
                                if (!empty($values['pupilsightScaleIDEffort'])) {
                                    $form->addHiddenValue('scaleEffort', $values['pupilsightScaleIDEffort']);
                                    $form->addHiddenValue('lowestAcceptableEffort', $values['lowestAcceptableEffort']);
                                    $scale = ' - '.$values['scaleNameEffort'];
                                    $scale .= $values['usageEffort']? ': '.$values['usageEffort'] : '';
                                }
                                $header->addContent($hasEffortName? $effortAlternativeNameAbrev : __('Eff'))
                                    ->setTitle(($hasEffortName? $effortAlternativeName : __('Effort')).$scale)
                                    ->setClass('textCenter');
                            }
        
                            if ($hasComment || $hasUpload) {
                                $header->addContent(__('Com'))->setTitle(__('Comment'))->setClass('textCenter');
                            }
                    }

                    foreach ($students as $index => $student) {
                        $count = $index+1;
                        $row = $table->addRow();
            
                        $row->addWebLink(Format::name('', $student['preferredName'], $student['surname'], 'Student', true))
                            ->setURL($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php')
                            ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                            ->addParam('subpage', 'Internal Assessment')
                            ->wrap('<strong>', '</strong>')
                            ->prepend($count.') ');

                        if ($hasAttainment) {
                            $attainment = $row->addSelectGradeScaleGrade($count.'-attainmentValue', $values['pupilsightScaleIDAttainment'])->setClass('textCenter gradeSelect');
                            if (!empty($student['attainmentValue'])) $attainment->selected($student['attainmentValue']);
                        }
    
                        if ($hasEffort) {
                            $effort = $row->addSelectGradeScaleGrade($count.'-effortValue', $values['pupilsightScaleIDEffort'])->setClass('textCenter gradeSelect');
                            if (!empty($student['effortValue'])) $effort->selected($student['effortValue']);
                        }
    
                        if ($hasComment || $hasUpload) {
                            $col = $row->addColumn()->addClass('stacked');

                            if ($hasComment) {
                                $col->addTextArea('comment'.$count)->setRows(6)->setValue($student['comment']);
                            }

                            if ($hasUpload) {
                                $col->addFileUpload('response'.$count)->setAttachment('attachment'.$count, $_SESSION[$guid]['absoluteURL'], $student['response'])->setMaxUpload(false);
                            }
                        }
                        $form->addHiddenValue($count.'-pupilsightPersonID', $student['pupilsightPersonID']);
                    }

                    $form->addHiddenValue('count', $count);

                    $form->addRow()->addHeading(__('Assessment Complete?'));

                    $row = $form->addRow();
                        $row->addLabel('completeDate', __('Go Live Date'))->prepend('1. ')->append('<br/>'.__('2. Column is hidden until date is reached.'));
                        $row->addDate('completeDate');

                    $row = $form->addRow();
                        $row->addContent(getMaxUpload($guid, true));
                        $row->addSubmit();

                    $form->loadAllValuesFrom($values);
        
                    echo $form->getOutput();
                }
            }
        }

        //Print sidebar
        $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $pupilsightCourseClassID, 'write');
    }
}
