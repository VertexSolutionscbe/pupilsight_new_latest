<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

//Get alternative header names
$attainmentAlternativeName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
$effortAlternativeName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
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
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
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
                $sql2 = 'SELECT * FROM pupilsightInternalAssessmentColumn WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID';
                $result2 = $connection2->prepare($sql2);
                $result2->execute($data2);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result2->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                //Let's go!
                $class = $result->fetch();
                $values = $result2->fetch();

                $page->breadcrumbs
                    ->add(__('Manage {courseClass} Internal Assessments', ['courseClass' => $class['course'].'.'.$class['class']]), 'internalAssessment_manage.php', ['pupilsightCourseClassID' => $pupilsightCourseClassID])
                    ->add(__('Edit Column'));

                if ($values['groupingID'] != '' and $values['pupilsightPersonIDCreator'] != $_SESSION[$guid]['pupilsightPersonID']) {
                    echo "<div class='alert alert-danger'>";
                    echo __('This column is part of a set of columns, which you did not create, and so cannot be individually edited.');
                    echo '</div>';
                } else {
                    if (isset($_GET['return'])) {
                        returnProcess($guid, $_GET['return'], null, array('error3' => __('Your request failed due to an attachment error.')));
                    }

                    $form = Form::create('internalAssessment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/internalAssessment_manage_editProcess.php?pupilsightInternalAssessmentColumnID='.$pupilsightInternalAssessmentColumnID.'&pupilsightCourseClassID='.$pupilsightCourseClassID.'&address='.$_SESSION[$guid]['address']);
                    $form->setFactory(DatabaseFormFactory::create($pdo));
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        
                    $form->addRow()->addHeading(__('Basic Information'));
        
                    $row = $form->addRow();
                        $row->addLabel('className', __('Class'));
                        $row->addTextField('className')->required()->readonly()->setValue(htmlPrep($class['course'].'.'.$class['class']));
        
                    $row = $form->addRow();
                        $row->addLabel('name', __('Name'));
                        $row->addTextField('name')->required()->maxLength(20);
        
                    $row = $form->addRow();
                        $row->addLabel('description', __('Description'));
                        $row->addTextField('description')->required()->maxLength(1000);
        
                    $types = getSettingByScope($connection2, 'Formal Assessment', 'internalAssessmentTypes');
                    if (!empty($types)) {
                        $row = $form->addRow();
                            $row->addLabel('type', __('Type'));
                            $row->addSelect('type')->fromString($types)->required()->placeholder();
                    }
        
                    $row = $form->addRow();
                        $row->addLabel('file', __('Attachment'));
                        $row->addFileUpload('file')->setAttachment('attachment', $_SESSION[$guid]['absoluteURL'], $values['attachment']);
        
                    $form->addRow()->addHeading(__('Assessment'));
        
                    $attainmentLabel = !empty($attainmentAlternativeName)? sprintf(__('Assess %1$s?'), $attainmentAlternativeName) : __('Assess Attainment?');
                    $row = $form->addRow();
                        $row->addLabel('attainment', $attainmentLabel);
                        $row->addYesNoRadio('attainment')->required();
        
                    $form->toggleVisibilityByClass('attainmentRow')->onRadio('attainment')->when('Y');
        
                    $attainmentScaleLabel = !empty($attainmentAlternativeName)? $attainmentAlternativeName.' '.__('Scale') : __('Attainment Scale');
                    $row = $form->addRow()->addClass('attainmentRow');
                        $row->addLabel('pupilsightScaleIDAttainment', $attainmentScaleLabel);
                        $row->addSelectGradeScale('pupilsightScaleIDAttainment')->required()->selected($_SESSION[$guid]['defaultAssessmentScale']);
        
                    $effortLabel = !empty($effortAlternativeName)? sprintf(__('Assess %1$s?'), $effortAlternativeName) : __('Assess Effort?');
                    $row = $form->addRow();
                        $row->addLabel('effort', $effortLabel);
                        $row->addYesNoRadio('effort')->required();
        
                    $form->toggleVisibilityByClass('effortRow')->onRadio('effort')->when('Y');
        
                    $effortScaleLabel = !empty($effortAlternativeName)? $effortAlternativeName.' '.__('Scale') : __('Effort Scale');
                    $row = $form->addRow()->addClass('effortRow');
                        $row->addLabel('pupilsightScaleIDEffort', $effortScaleLabel);
                        $row->addSelectGradeScale('pupilsightScaleIDEffort')->required()->selected($_SESSION[$guid]['defaultAssessmentScale']);
        
                    $row = $form->addRow();
                        $row->addLabel('comment', __('Include Comment?'));
                        $row->addYesNoRadio('comment')->required();
        
                    $row = $form->addRow();
                        $row->addLabel('uploadedResponse', __('Include Uploaded Response?'));
                        $row->addYesNoRadio('uploadedResponse')->required();
        
                    $form->addRow()->addHeading(__('Access'));
        
                    $row = $form->addRow();
                        $row->addLabel('viewableStudents', __('Viewable to Students'));
                        $row->addYesNo('viewableStudents')->required();
        
                    $row = $form->addRow();
                        $row->addLabel('viewableParents', __('Viewable to Parents'));
                        $row->addYesNo('viewableParents')->required();
        
                    $row = $form->addRow();
                        $row->addLabel('completeDate', __('Go Live Date'))->prepend('1. ')->append('<br/>'.__('2. Column is hidden until date is reached.'));
                        $row->addDate('completeDate');
        
                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit();

                    $form->loadAllValuesFrom($values);
        
                    echo $form->getOutput();
                }
            }
        }

        //Print sidebar
        $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $pupilsightCourseClassID);
    }
}
