<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

//Get settings
$enableEffort = getSettingByScope($connection2, 'Markbook', 'enableEffort');
$enableRubrics = getSettingByScope($connection2, 'Markbook', 'enableRubrics');
$enableColumnWeighting = getSettingByScope($connection2, 'Markbook', 'enableColumnWeighting');
$enableRawAttainment = getSettingByScope($connection2, 'Markbook', 'enableRawAttainment');
$enableGroupByTerm = getSettingByScope($connection2, 'Markbook', 'enableGroupByTerm');
$attainmentAlternativeName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
$attainmentAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev');
$effortAlternativeName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');
$effortAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev');

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_edit.php') == false) {
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
        $pupilsightMarkbookColumnID = $_GET['pupilsightMarkbookColumnID'] ?? '';
        if ($pupilsightCourseClassID == '' or $pupilsightMarkbookColumnID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Edit Markbook_everything') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                } else {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID2' => $pupilsightCourseClassID, 'pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID);
                    $sql = "(SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)
					UNION
					(SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightMarkbookColumn.pupilsightPersonIDCreator=:pupilsightPersonID2 AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID2 AND pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID)
					ORDER BY course, class";
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
                    $data2 = array('pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID);
                    $sql2 = 'SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID';
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
                    $course = $result->fetch();
                    $values = $result2->fetch();

                    $page->breadcrumbs
                        ->add(
                            __('View {courseClass} Markbook', [
                                'courseClass' => Format::courseClassName($course['course'], $course['class']),
                            ]),
                            'markbook_view.php',
                            [
                                'pupilsightCourseClassID' => $pupilsightCourseClassID,
                            ]
                        )
                        ->add(__('Edit Column'));

                    if ($values['groupingID'] != '' and $values['pupilsightPersonIDCreator'] != $_SESSION[$guid]['pupilsightPersonID']) {
                        echo "<div class='alert alert-danger'>";
                        echo __('This column is part of a set of columns, which you did not create, and so cannot be individually edited.');
                        echo '</div>';
                    } else {
                        $returns = array();
                        $returns['error6'] = __('Your request failed because you already have one "End of Year" column for this class.');
                        $returns['success1'] = __('Planner was successfully added: you opted to add a linked Markbook column, and you can now do so below.');
                        if (isset($_GET['return'])) {
                            returnProcess($guid, $_GET['return'], null, $returns);
                        }

                        echo "<div class='linkTop'>";
                        if ($values['pupilsightPlannerEntryID'] != '') {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&viewBy=class&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightPlannerEntryID=".$values['pupilsightPlannerEntryID']."'>".__('View Linked Lesson')."<img style='margin: 0 0 -4px 5px' title='".__('View Linked Lesson')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/planner.png'/></a> | ";
                        }
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/markbook_edit_data.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightMarkbookColumnID=$pupilsightMarkbookColumnID'>".__('Enter Data')."<img style='margin: 0 0 0px 5px' title='".__('Enter Data')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/markbook.png'/></a> ";
                        echo '</div>';

                        $form = Form::create('markbook', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/markbook_edit_editProcess.php?pupilsightMarkbookColumnID='.$pupilsightMarkbookColumnID.'&pupilsightCourseClassID='.$pupilsightCourseClassID.'&address='.$_SESSION[$guid]['address']);
                        $form->setFactory(DatabaseFormFactory::create($pdo));
                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                        $form->addRow()->addHeading(__('Basic Information'));

                        $row = $form->addRow();
                            $row->addLabel('courseName', __('Class'));
                            $row->addTextField('courseName')->required()->readOnly()->setValue($course['course'].'.'.$course['class']);

                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                        $sql = "SELECT pupilsightUnit.pupilsightUnitID as value, pupilsightUnit.name FROM pupilsightUnit JOIN pupilsightUnitClass ON (pupilsightUnit.pupilsightUnitID=pupilsightUnitClass.pupilsightUnitID) WHERE running='Y' AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY name";

                        $row = $form->addRow();
                            $row->addLabel('pupilsightUnitID', __('Unit'));
                            $units = $row->addSelect('pupilsightUnitID')->fromQuery($pdo, $sql, $data)->placeholder();

                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                        $sql = "SELECT pupilsightUnitID as chainedTo, pupilsightPlannerEntryID as value, name FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY name";
                        $row = $form->addRow();
                            $row->addLabel('pupilsightPlannerEntryID', __('Lesson'));
                            $row->addSelect('pupilsightPlannerEntryID')->fromQueryChained($pdo, $sql, $data, 'pupilsightUnitID')->placeholder();

                        $row = $form->addRow();
                            $row->addLabel('name', __('Name'));
                            $row->addTextField('name')->required()->maxLength(20);

                        $row = $form->addRow();
                            $row->addLabel('description', __('Description'));
                            $row->addTextField('description')->required()->maxLength(1000);

                        // TYPE
                        $types = getSettingByScope($connection2, 'Markbook', 'markbookType');
                        if (!empty($types)) {
                            $row = $form->addRow();
                                $row->addLabel('type', __('Type'));
                                $typesSelect = $row->addSelect('type')->required()->placeholder();

                            if ($enableColumnWeighting == 'Y') {
                                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'perTerm' => __('Per Term'), 'wholeYear' => __('Whole Year'));
                                $sql = "SELECT (CASE WHEN calculate='term' THEN :perTerm ELSE :wholeYear END) as groupBy, type as value, description as name FROM pupilsightMarkbookWeight WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY calculate, type";
                                $typesSelect->fromQuery($pdo, $sql, $data, 'groupBy');
                            }

                            if ($typesSelect->getOptionCount() == 0) {
                                $typesSelect->fromString($types);
                            }
                        }

                        $row = $form->addRow();
                            $row->addLabel('file', __('Attachment'));
                            $row->addFileUpload('file')->setAttachment('attachment', $_SESSION[$guid]['absoluteURL'], $values['attachment']);

                        // DATE
                        if ($enableGroupByTerm == 'Y') {
                            $form->addRow()->addHeading(__('Term Date'));

                            $row = $form->addRow();
                                $row->addLabel('pupilsightSchoolYearTermID', __('Term'));
                                $row->addSelectSchoolYearTerm('pupilsightSchoolYearTermID', $_SESSION[$guid]['pupilsightSchoolYearID']);

                            $row = $form->addRow();
                                $row->addLabel('date', __('Date'));
                                $row->addDate('date')->setValue(dateConvertBack($guid, $values['date']))->required();
                        } else {
                            $form->addHiddenValue('pupilsightSchoolYearTermID',$values['pupilsightSchoolYearTermID']);
                            $form->addHiddenValue('date', dateConvertBack($guid, $values['date']));
                        }

                        $form->addRow()->addHeading(__('Assessment'));

                        // ATTAINMENT
                        $attainmentLabel = !empty($attainmentAltName)? sprintf(__('Assess %1$s?'), $attainmentAltName) : __('Assess Attainment?');
                        $attainmentScaleLabel = !empty($attainmentAltName)? $attainmentAltName.' '.__('Scale') : __('Attainment Scale');
                        $attainmentRawMaxLabel = !empty($attainmentAltName)? $attainmentAltName.' '.__('Total Mark') : __('Attainment Total Mark');
                        $attainmentWeightingLabel = !empty($attainmentAltName)? $attainmentAltName.' '.__('Weighting') : __('Attainment Weighting');
                        $attainmentRubricLabel = !empty($attainmentAltName)? $attainmentAltName.' '.__('Rubric') : __('Attainment Rubric');

                        $row = $form->addRow();
                            $row->addLabel('attainment', $attainmentLabel);
                            $row->addYesNoRadio('attainment')->required();

                        $form->toggleVisibilityByClass('attainmentRow')->onRadio('attainment')->when('Y');

                        $row = $form->addRow()->addClass('attainmentRow');
                            $row->addLabel('pupilsightScaleIDAttainment', $attainmentScaleLabel);
                            $row->addSelectGradeScale('pupilsightScaleIDAttainment')->required();

                        if ($enableRawAttainment == 'Y') {
                            $row = $form->addRow()->addClass('attainmentRow');
                                $row->addLabel('attainmentRawMax', $attainmentRawMaxLabel)->description(__('Leave blank to omit raw marks.'));
                                $row->addNumber('attainmentRawMax')->maxLength(8)->onlyInteger(false);
                        }

                        if ($enableColumnWeighting == 'Y') {
                            $row = $form->addRow()->addClass('attainmentRow');
                                $row->addLabel('attainmentWeighting', $attainmentWeightingLabel);
                                $row->addNumber('attainmentWeighting')->maxLength(5)->onlyInteger(false)->setValue(1);
                        }

                        if ($enableRubrics == 'Y') {
                            $row = $form->addRow()->addClass('attainmentRow');
                                $row->addLabel('pupilsightRubricIDAttainment', $attainmentRubricLabel)->description(__('Choose predefined rubric, if desired.'));
                                $row->addSelectRubric('pupilsightRubricIDAttainment', $course['pupilsightYearGroupIDList'], $course['pupilsightDepartmentID'])->placeholder();
                        }

                        // EFFORT
                        if ($enableEffort == 'Y') {
                            $effortLabel = !empty($effortAltName)? sprintf(__('Assess %1$s?'), $effortAltName) : __('Assess Effort?');
                            $effortScaleLabel = !empty($effortAltName)? $effortAltName.' '.__('Scale') : __('Effort Scale');
                            $effortRubricLabel = !empty($effortAltName)? $effortAltName.' '.__('Rubric') : __('Effort Rubric');

                            $row = $form->addRow();
                                $row->addLabel('effort', $effortLabel);
                                $row->addYesNoRadio('effort')->required();

                            $form->toggleVisibilityByClass('effortRow')->onRadio('effort')->when('Y');

                            $row = $form->addRow()->addClass('effortRow');
                                $row->addLabel('pupilsightScaleIDEffort', $effortScaleLabel);
                                $row->addSelectGradeScale('pupilsightScaleIDEffort')->required();

                            if ($enableRubrics == 'Y') {
                                $row = $form->addRow()->addClass('effortRow');
                                    $row->addLabel('pupilsightRubricIDEffort', $effortRubricLabel)->description(__('Choose predefined rubric, if desired.'));
                                    $row->addSelectRubric('pupilsightRubricIDEffort', $course['pupilsightYearGroupIDList'], $course['pupilsightDepartmentID'])->placeholder();
                            }
                        }

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
        }
    }

    // Print the sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $pdo, $_SESSION[$guid]['pupilsightPersonID'], $pupilsightCourseClassID, 'markbook_view.php');
}
