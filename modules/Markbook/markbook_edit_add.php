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
$attainmentAltName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
$attainmentAltNameAbrev = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev');
$effortAltName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');
$effortAltNameAbrev = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev');

//Get variables from Planner
$pupilsightUnitID = isset($_GET['pupilsightUnitID'])? $_GET['pupilsightUnitID'] : null;
$pupilsightPlannerEntryID = isset($_GET['pupilsightPlannerEntryID'])? $_GET['pupilsightPlannerEntryID'] : null;
$name = isset($_GET['name'])? $_GET['name'] : null;
$summary = isset($_GET['summary'])? $_GET['summary'] : null;
$date = isset($_GET['date'])? $_GET['date'] : date('Y-m-d');

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_add.php') == false) {
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
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
        if ($pupilsightCourseClassID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Edit Markbook_everything') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                } else {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class";
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
                $course = $result->fetch();

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
                    ->add(__('Add Column'));

                $returns = array();
                $returns['error6'] = __('Your request failed because you already have one "End of Year" column for this class.');
                $returns['success1'] = __('Planner was successfully added: you opted to add a linked Markbook column, and you can now do so below.');
                $editLink = '';
                if (isset($_GET['editID'])) {
                    $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Markbook/markbook_edit_edit.php&pupilsightMarkbookColumnID='.$_GET['editID'].'&pupilsightCourseClassID='.$pupilsightCourseClassID;
                }
                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], $editLink, $returns);
                }

                $form = Form::create('markbook', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/markbook_edit_addProcess.php?pupilsightCourseClassID='.$pupilsightCourseClassID.'&address='.$_SESSION[$guid]['address']);
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
                    $units = $row->addSelect('pupilsightUnitID')->fromQuery($pdo, $sql, $data)->placeholder()->selected($pupilsightUnitID);

                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = "SELECT pupilsightUnitID as chainedTo, pupilsightPlannerEntryID as value, name FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY name";
                $row = $form->addRow();
                    $row->addLabel('pupilsightPlannerEntryID', __('Lesson'));
                    $row->addSelect('pupilsightPlannerEntryID')->fromQueryChained($pdo, $sql, $data, 'pupilsightUnitID')->placeholder()->selected($pupilsightPlannerEntryID);

                $row = $form->addRow();
                    $row->addLabel('name', __('Name'));
                    $row->addTextField('name')->required()->maxLength(20)->setValue($name);

                $row = $form->addRow();
                    $row->addLabel('description', __('Description'));
                    $row->addTextField('description')->required()->maxLength(1000)->setValue($summary);

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
                    $row->addFileUpload('file');

                // DATE
                if ($enableGroupByTerm == 'Y') {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => $date);
                    $sql = "SELECT pupilsightSchoolYearTermID FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND :date BETWEEN firstDay AND lastDay ORDER BY sequenceNumber";
                    $result = $pdo->executeQuery($data, $sql);
                    $currentTerm = ($result->rowCount() > 0)? $result->fetchColumn(0) : '';

                    $form->addRow()->addHeading(__('Term Date'));

                    $row = $form->addRow();
                        $row->addLabel('pupilsightSchoolYearTermID', __('Term'));
                        $row->addSelectSchoolYearTerm('pupilsightSchoolYearTermID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($currentTerm);

                    $row = $form->addRow();
                        $row->addLabel('date', __('Date'));
                        $row->addDate('date')->setValue(dateConvertBack($guid, $date))->required();
                } else {
                    $form->addHiddenValue('date', dateConvertBack($guid, $date));
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
                    $row->addSelectGradeScale('pupilsightScaleIDAttainment')->required()->selected($_SESSION[$guid]['defaultAssessmentScale']);

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
                        $row->addSelectGradeScale('pupilsightScaleIDEffort')->required()->selected($_SESSION[$guid]['defaultAssessmentScale']);

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

                echo $form->getOutput();
            }
        }
    }

    // Print the sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $pdo, $_SESSION[$guid]['pupilsightPersonID'], $pupilsightCourseClassID, 'markbook_edit_add.php');
}
