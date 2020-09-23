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
$enableRawAttainment = getSettingByScope($connection2, 'Markbook', 'enableRawAttainment');
$enableModifiedAssessment = getSettingByScope($connection2, 'Markbook', 'enableModifiedAssessment');

//Get alternative header names
$attainmentAlternativeName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
$attainmentAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev');
$hasAttainmentName = ($attainmentAlternativeName != '' && $attainmentAlternativeNameAbrev != '');

$effortAlternativeName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');
$effortAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev');
$hasEffortName = ($effortAlternativeName != '' && $effortAlternativeNameAbrev != '');

// Get the sort order, if it exists
$studentOrderBy = (isset($_SESSION[$guid]['markbookOrderBy']))? $_SESSION[$guid]['markbookOrderBy'] : 'surname';
$studentOrderBy = (isset($_GET['markbookOrderBy']))? $_GET['markbookOrderBy'] : $studentOrderBy;

// Register scripts available to the core, but not included by default
$page->scripts->add('chart');

// This script makes entering raw marks easier, by capturing the enter key and moving to the next field insted of submitting
echo "<script type='text/javascript'>";
?>
    $(document).ready(function(){
        autosize($('textarea'));
    });

    // Map [Enter] key to work like the [Tab] key
    // Daniel P. Clark 2014
    // Modified for Pupilsight Markbook Edit Data

    $(window).keydown(function(e) {

        // Set self as the current item in focus
        var self = $(':focus'),
          // Set the form by the current item in focus
          form = self.parents('form:eq(0)'),
          focusable;

        // Sometimes :focus selector doesnt work (in Chrome specifically)
        if (self.length == false) {
            self = e.target.value;
        }

        function enterKey(){

            if (e.which === 13 && !self.is('textarea,div[contenteditable=true]')) { // [Enter] key

                var index = self.attr('name').substr(0, self.attr('name').indexOf('-'));
                var attainmentNext = $( '#' + (parseInt(index) + 1) + '-attainmentValueRaw');

                //If not a regular hyperlink/button/textarea
                if ($.inArray(self, focusable) && (!self.is('a,button'))){
                    // Then prevent the default [Enter] key behaviour from submitting the form
                    e.preventDefault();
                } // Otherwise follow the link/button as by design, or put new line in textarea

                self.change();

                if (attainmentNext.length) {

                    attainmentNext.focus();
                    attainmentNext.select();

                    // Scroll to the next raw score
                    $('html,body').animate( {
                        scrollTop: $(document).scrollTop() + ( attainmentNext.offset().top - self.offset().top ),
                    }, 250);
                }

                return false;
            }
        }

        // We need to capture the [Shift] key and check the [Enter] key either way.
        if (e.shiftKey) { enterKey() } else { enterKey() }
    });

    <?php
echo '</script>';

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_data.php') == false) {
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
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightCourse.pupilsightYearGroupIDList, pupilsightScale.name as targetGradeScale
                            FROM pupilsightCourse
                            JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                            LEFT JOIN pupilsightScale ON (pupilsightScale.pupilsightScaleID=pupilsightCourseClass.pupilsightScaleIDTarget)
                            WHERE pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID
                            ORDER BY course, class";
                } else {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightCourse.pupilsightYearGroupIDList, pupilsightScale.name as targetGradeScale
                            FROM pupilsightCourse
                            JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                            JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                            LEFT JOIN pupilsightScale ON (pupilsightScale.pupilsightScaleID=pupilsightCourseClass.pupilsightScaleIDTarget)
                            WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher'
                            AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID
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
                    $sql2 = "SELECT pupilsightMarkbookColumn.*, pupilsightUnit.name as unitName, attainmentScale.name as scaleNameAttainment, attainmentScale.usage as usageAttainment, attainmentScale.lowestAcceptable as lowestAcceptableAttainment, effortScale.name as scaleNameEffort, effortScale.usage as usageEffort, effortScale.lowestAcceptable as lowestAcceptableEffort
                            FROM pupilsightMarkbookColumn
                            LEFT JOIN pupilsightUnit ON (pupilsightMarkbookColumn.pupilsightUnitID=pupilsightUnit.pupilsightUnitID)
                            LEFT JOIN pupilsightScale as attainmentScale ON (attainmentScale.pupilsightScaleID=pupilsightMarkbookColumn.pupilsightScaleIDAttainment)
                            LEFT JOIN pupilsightScale as effortScale ON (effortScale.pupilsightScaleID=pupilsightMarkbookColumn.pupilsightScaleIDEffort)
                            WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID";
                    $result2 = $connection2->prepare($sql2);
                    $result2->execute($data2);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result2->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected column does not exist, or you do not have access to it.');
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
                        ->add(__('Enter Marks'));

                    if (isset($_GET['return'])) {
                        returnProcess($guid, $_GET['return'], null, null);
                    }

                    // Added an info message to let uers know about enter / automatic calculations
                    if ($values['attainment'] == 'Y' && $values['attainmentRaw'] == 'Y' && !empty($values['attainmentRawMax']) && $enableRawAttainment == 'Y') {
                        echo '<p>';
                        echo __('Press enter when recording marks to jump to the next student. Attainment values with a percentage grade scale will be calculated automatically. You can override the automatic value by selecting a different grade.');
                        echo '</p>';
                    }

                    echo "<div class='linkTop'>";
                    if ($values['pupilsightPlannerEntryID'] != '') {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&viewBy=class&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightPlannerEntryID=".$values['pupilsightPlannerEntryID']."'>".__('View Linked Lesson')."<img style='margin: 0 0 -4px 5px' title='".__('View Linked Lesson')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/planner.png'/></a> | ";
                    }
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/markbook_edit_edit.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightMarkbookColumnID=$pupilsightMarkbookColumnID'>".__('Edit')."<img style='margin: 0 0 -4px 5px' title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                    echo '</div>';

                    $columns = 1;

                    $hasTarget = !empty($course['targetGradeScale']);
                    $hasSubmission = false;
                    $hasAttainment = $values['attainment'] == 'Y';
                    $hasRawAttainment = $values['attainmentRaw'] == 'Y' && !empty($values['attainmentRawMax']) && $enableRawAttainment == 'Y';
                    $hasAttainmentRubric = $values['pupilsightRubricIDAttainment'] != '' && $enableRubrics =='Y';
                    $hasEffort = $values['effort'] == 'Y';
                    $hasEffortRubric = $values['pupilsightRubricIDEffort'] != '' && $enableRubrics =='Y';
                    $hasComment = $values['comment'] == 'Y';
                    $hasUpload = $values['uploadedResponse'] == 'Y';

                    $data = array(
                        'pupilsightCourseClassID' => $pupilsightCourseClassID,
                        'pupilsightMarkbookColumnID' => $values['pupilsightMarkbookColumnID'],
                        'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'],
                        'today' => date('Y-m-d'),
                    );
                    $sql = "SELECT pupilsightPerson.pupilsightPersonID as groupBy, title, surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightPerson.dateStart, pupilsightStudentEnrolment.rollOrder, pupilsightScaleGrade.value as targetScaleGrade, modifiedAssessment, pupilsightMarkbookEntry.attainmentValue, pupilsightMarkbookEntry.attainmentValueRaw, pupilsightMarkbookEntry.effortValue, pupilsightMarkbookEntry.comment, pupilsightMarkbookEntry.response
                            FROM pupilsightCourseClassPerson
                            JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                            JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                            JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID)
                            LEFT JOIN pupilsightMarkbookEntry ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID AND pupilsightMarkbookEntry.pupilsightPersonIDStudent=pupilsightCourseClassPerson.pupilsightPersonID)
                            LEFT JOIN pupilsightMarkbookTarget ON (pupilsightMarkbookTarget.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND pupilsightMarkbookTarget.pupilsightPersonIDStudent=pupilsightPerson.pupilsightPersonID)
                            LEFT JOIN pupilsightScaleGrade ON (pupilsightMarkbookTarget.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID)
                            WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightCourseClassID=:pupilsightCourseClassID
                            AND pupilsightPerson.status='Full' AND pupilsightCourseClassPerson.role='Student'
                            AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL OR dateEnd>=:today)";

                    if ($studentOrderBy == 'rollOrder') {
                        $sql .= " ORDER BY ISNULL(rollOrder), rollOrder, surname, preferredName";
                    } else if ($studentOrderBy == 'preferredName') {
                        $sql .= " ORDER BY preferredName, surname";
                    } else {
                        $sql .= " ORDER BY surname, preferredName";
                    }
                    $result = $pdo->executeQuery($data, $sql);
                    $students = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

                    // WORK OUT IF THERE IS SUBMISSION
                    if (is_null($values['pupilsightPlannerEntryID']) == false) {
                        try {
                            $dataSub = array('pupilsightPlannerEntryID' => $values['pupilsightPlannerEntryID']);
                            $sqlSub = "SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND homeworkSubmission='Y'";
                            $resultSub = $connection2->prepare($sqlSub);
                            $resultSub->execute($dataSub);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($resultSub->rowCount() == 1) {
                            $hasSubmission = true;
                            $rowSub = $resultSub->fetch();
                            $values['homeworkDueDateTime'] = $rowSub['homeworkDueDateTime'];
                            $values['homeworkSubmissionRequired'] = $rowSub['homeworkSubmissionRequired'];
                            $values['lessonDate'] = $rowSub['date'];
                        }
                    }

                    // Grab student submissions
                    foreach ($students as $pupilsightPersonID => $student) {
                        $students[$pupilsightPersonID]['submission'] = '';

                        if ($hasSubmission) {
                            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPlannerEntryID' => $values['pupilsightPlannerEntryID']);
                            $sql = "SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID ORDER BY count DESC LIMIT 1";
                            $result = $pdo->executeQuery($data, $sql);
                            $submission = ($result->rowCount() > 0)? $result->fetch() : '';

                            $students[$pupilsightPersonID]['submission'] = renderStudentSubmission($pupilsightPersonID, $submission, $values);
                        }
                    }

                    //Grab student individual needs flag
                    $data = array(
                        'pupilsightCourseClassID' => $pupilsightCourseClassID,
                        'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'],
                        'today' => date('Y-m-d')
                    );
                    $sql = "SELECT DISTINCT pupilsightPerson.pupilsightPersonID
                            FROM pupilsightCourseClassPerson
                            JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                            JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                            JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID)
                            JOIN pupilsightINPersonDescriptor ON (pupilsightPerson.pupilsightPersonID=pupilsightINPersonDescriptor.pupilsightPersonID)
                            WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightCourseClassID=:pupilsightCourseClassID
                            AND pupilsightPerson.status='Full' AND pupilsightCourseClassPerson.role='Student'
                            AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL OR dateEnd>=:today)";
                    $result = $pdo->executeQuery($data, $sql);
                    $individualNeeds = ($result->rowCount() > 0)? $result->fetchAll() : array();

                    $form = Form::create('markbookEditData', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/markbook_edit_dataProcess.php?pupilsightCourseClassID='.$pupilsightCourseClassID.'&pupilsightMarkbookColumnID='.$pupilsightMarkbookColumnID.'&address='.$_SESSION[$guid]['address']);
                    $form->setFactory(DatabaseFormFactory::create($pdo));
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    if (count($students) == 0) {
                        $form->addRow()->addHeading(__('Students'));
                        $form->addRow()->addAlert(__('There are no records to display.'), 'error');
                    } else {
                        $attainmentScale = '';
                        if ($hasAttainment) {
                            $form->addHiddenValue('scaleAttainment', $values['pupilsightScaleIDAttainment']);
                            $form->addHiddenValue('lowestAcceptableAttainment', $values['lowestAcceptableAttainment']);
                            $attainmentScale = ' - '.$values['scaleNameAttainment'];
                            $attainmentScale .= $values['usageAttainment']? ': '.$values['usageAttainment'] : '';
                        }

                        if ($hasAttainment && $hasRawAttainment) {
                            $form->addHiddenValue('attainmentRawMax', $values['attainmentRawMax']);

                            $scaleType = (strpos( strtolower($values['scaleNameAttainment']), 'percent') !== false)? '%' : '';
                            $form->addHiddenValue('attainmentScaleType', $scaleType);
                        }

                        $effortScale = '';
                        if ($hasEffort) {
                            $form->addHiddenValue('scaleEffort', $values['pupilsightScaleIDEffort']);
                            $form->addHiddenValue('lowestAcceptableEffort', $values['lowestAcceptableEffort']);
                            $effortScale = ' - '.$values['scaleNameEffort'];
                            $effortScale .= $values['usageEffort']? ': '.$values['usageEffort'] : '';
                        }

                        // Create a rubric link object (for reusabilty)
                        $rubricLink = $form->getFactory()
                            ->createWebLink('<img title="'.__('Mark Rubric').'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/rubric.png" style="margin-left:4px;"/>')
                            ->setURL($_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Markbook/markbook_view_rubric.php')
                            ->setClass('thickbox')
                            ->addParam('pupilsightCourseClassID', $pupilsightCourseClassID)
                            ->addParam('pupilsightMarkbookColumnID', $pupilsightMarkbookColumnID)
                            ->addParam('width', '1100')
                            ->addParam('height', '550');

                        $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth colorOddEven noMargin noPadding noBorder');

                        $detailsText = ($values['unitName'] != '')? $values['unitName'].'<br/>' : '';
                        $detailsText .= !empty($values['completeDate'])? __('Marked on').' '.dateConvertBack($guid, $values['completeDate']) : __('Unmarked');
                        $detailsText .= '<br/>'.$values['type'];

                        if ($values['attachment'] != '' and file_exists($_SESSION[$guid]['absolutePath'].'/'.$values['attachment'])) {
                            $detailsText .= " | <a title='".__('Download more information')."' href='".$_SESSION[$guid]['absoluteURL'].'/'.$values['attachment']."'>".__('More info').'</a>';
                        }

                        $header = $table->addHeaderRow();

                        $header->addTableCell(__('Student'))->rowSpan(2);

                        $header->onlyIf($hasTarget)
                            ->addTableCell(__('Target'))
                            ->setTitle(__('Personalised target grade').' | '.$course['targetGradeScale'].' '.__('Scale'))
                            ->rowSpan(2)
                            ->addClass('textCenter smallColumn dataColumn noPadding')
                            ->wrap('<div class="verticalText">', '</div>');

                        $header->addTableCell($values['name'])
                            ->setTitle($values['description'])
                            ->append('<br><span class="small emphasis" style="font-weight:normal;">'.$detailsText.'</span>')
                            ->setClass('textCenter')
                            ->colSpan(5);

                        $header = $table->addHeaderRow();

                        $header->onlyIf($enableModifiedAssessment == 'Y')
                            ->addContent(__('Mod'))
                            ->setTitle(__('Modified Assessment'))
                            ->setClass('textCenter');

                        $header->onlyIf($hasSubmission)
                            ->addContent(__('Sub'))
                            ->setTitle(__('Submitted Work'))
                            ->setClass('textCenter');

                        $header->onlyIf($hasAttainment && $hasRawAttainment)
                            ->addContent(__('Mark'))
                            ->setTitle(__('Raw Attainment Mark'))
                            ->setClass('textCenter');

                        $header->onlyIf($hasAttainment)
                            ->addContent($hasAttainmentName? $attainmentAlternativeNameAbrev : __('Att'))
                            ->setTitle(($hasAttainmentName? $attainmentAlternativeName : __('Attainment')).$attainmentScale)
                            ->setClass('textCenter');

                        $header->onlyIf($hasEffort)
                            ->addContent($hasEffortName? $effortAlternativeNameAbrev : __('Eff'))
                            ->setTitle(($hasEffortName? $effortAlternativeName : __('Effort')).$effortScale)
                            ->setClass('textCenter');

                        $header->onlyIf($hasComment || $hasUpload)
                            ->addContent(__('Com'))
                            ->setTitle(__('Comment'))
                            ->setClass('textCenter');
                    }

                    $count = 0;
                    foreach ($students as $pupilsightPersonID => $student) {
                        $count = $count+1;
                        $rollOrder = ($studentOrderBy == 'rollOrder')? $student['rollOrder'] : $count;

                        $form->addHiddenValue($count.'-pupilsightPersonID', $student['pupilsightPersonID']);

                        if (!$hasRawAttainment) {
                            $form->addHiddenValue($count.'-attainmentValueRaw', $student['attainmentValueRaw']);
                        }

                        $row = $table->addRow()->setID($student['pupilsightPersonID']);

                        $row->addWebLink(formatName('', $student['preferredName'], $student['surname'], 'Student', true))
                            ->setURL($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php')
                            ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                            ->addParam('subpage', 'Markbook')
                            ->wrap('<strong>', '</strong>')
                            ->prepend($rollOrder.') ');

                        $row->onlyIf($hasTarget)
                            ->addContent($student['targetScaleGrade']);


                        //Is modified assessment on?
                        if ($enableModifiedAssessment == 'Y') {
                            if(array_search($student['pupilsightPersonID'], array_column($individualNeeds, 'pupilsightPersonID')) !== false || !is_null($student['modifiedAssessment'])) { //Student has individual needs record now, or used to in the past (inferred by modifiedAssessment set to Y)
                                $form->addHiddenValue($count.'-modifiedAssessmentEligible', 'Y');
                                $row->addCheckbox($count.'-modifiedAssessment')
                                    ->setClass('textCenter')
                                    ->setValue('on')->checked($student['modifiedAssessment'] == 'Y');
                            }
                            else {
                                $row->addContent('');
                            }
                        }

                        $row->onlyIf($hasSubmission)
                            ->addContent($student['submission']);

                        $col = $row->onlyIf($hasAttainment && $hasRawAttainment)->addColumn();
                        $col->addNumber($count.'-attainmentValueRaw')
                            ->onlyInteger(false)
                            ->setClass('inline-block')
                            ->setValue($student['attainmentValueRaw']);
                        $col->addContent('/ '.floatval($values['attainmentRawMax']))->setClass('inline-block ml-1');

                        $col = $row->onlyIf($hasAttainment)->addColumn();
                        $col->addSelectGradeScaleGrade($count.'-attainmentValue', $values['pupilsightScaleIDAttainment'])
                            ->setClass('textCenter gradeSelect inline-block')
                            ->selected($student['attainmentValue']);

                        if ($hasAttainment && $hasAttainmentRubric) {
                            $rubricLink->addParam('pupilsightPersonID', $student['pupilsightPersonID']);
                            $rubricLink->addParam('pupilsightRubricID', $values['pupilsightRubricIDAttainment']);
                            $rubricLink->addParam('type', 'attainment');
                            $col->addContent($rubricLink->getOutput())->setClass('inline-block ml-1');
                        }

                        $effort = $row->onlyIf($hasEffort)
                            ->addSelectGradeScaleGrade($count.'-effortValue', $values['pupilsightScaleIDEffort'])
                            ->setClass('textCenter gradeSelect')
                            ->selected($student['effortValue']);

                        if ($hasEffort && $hasEffortRubric) {
                            $rubricLink->addParam('pupilsightPersonID', $student['pupilsightPersonID']);
                            $rubricLink->addParam('pupilsightRubricID', $values['pupilsightRubricIDEffort']);
                            $rubricLink->addParam('type', 'effort');
                            $effort->append($rubricLink->getOutput());
                        }

                        $col = $row->onlyIf($hasComment || $hasUpload)->addColumn()->addClass('stacked');

                            $col->onlyIf($hasComment)->addTextArea('comment'.$count)->setRows(6)->setValue($student['comment']);

                            $col->onlyIf($hasUpload)
                                ->addFileUpload('response'.$count)
                                ->setAttachment('attachment'.$count, $_SESSION[$guid]['absoluteURL'], $student['response'])
                                ->setMaxUpload(false);
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
    }

    // Print the sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $pdo, $_SESSION[$guid]['pupilsightPersonID'], $pupilsightCourseClassID, 'markbook_view.php');
}
?>
