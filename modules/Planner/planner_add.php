<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Module\Planner\Forms\PlannerFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_add.php') == false) {
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
        //Set variables
        $today = date('Y-m-d');

        //Proceed!
        //Get viewBy, date and class variables
        $params = [];
        $viewBy = null;
        if (isset($_GET['viewBy'])) {
            $viewBy = $_GET['viewBy'];
        }
        $subView = null;
        if (isset($_GET['subView'])) {
            $subView = $_GET['subView'];
        }
        if ($viewBy != 'date' and $viewBy != 'class') {
            $viewBy = 'date';
        }
        $pupilsightCourseClassID = null;
        $date = null;
        $dateStamp = null;
        if ($viewBy == 'date') {
            $date = $_GET['date'] ?? '';
            if (isset($_GET['dateHuman']) == true) {
                $date = dateConvert($guid, $_GET['dateHuman']);
            }
            if ($date == '') {
                $date = date('Y-m-d');
            }
            list($dateYear, $dateMonth, $dateDay) = explode('-', $date);
            $dateStamp = mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear);
            $params += [
                'viewBy' => 'date',
                'date' => $date,
            ];
        } elseif ($viewBy == 'class') {
            $class = null;
            if (isset($_GET['class'])) {
                $class = $_GET['class'];
            }
            $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
            $pupilsightProgramID = $_GET['pupilsightProgramID'];
            $params += [
                'viewBy' => 'class',
                'date' => $class,
                'pupilsightCourseClassID' => $pupilsightCourseClassID,
                'subView' => $subView,
            ];
        }

        list($todayYear, $todayMonth, $todayDay) = explode('-', $today);
        $todayStamp = mktime(0, 0, 0, $todayMonth, $todayDay, $todayYear);

        $proceed = true;
        $extra = '';
        if ($viewBy == 'class') {
            if ($pupilsightCourseClassID == '') {
                $proceed = false;
            } else {
                try {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        /* closed by bikash */
                        // $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                        // $sql = 'SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightDepartmentID, pupilsightCourse.pupilsightYearGroupIDList FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';

                        $data = array('pupilsightProgramID' => $pupilsightProgramID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);

                        $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightProgram.name AS progName, pupilsightYearGroup.name AS className , pupilsightRollGroup.name AS sectionName, pupilsightDepartment.name AS subjectName, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, 'Teacher' AS role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightProgram ON (pupilsightPlannerEntry.pupilsightProgramID=pupilsightProgram.pupilsightProgramID) JOIN pupilsightYearGroup ON (pupilsightPlannerEntry.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightPlannerEntry.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightDepartment ON (pupilsightPlannerEntry.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightPlannerEntry.pupilsightProgramID=:pupilsightProgramID AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY date, timeStart";
                    } else {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightDepartmentID, pupilsightCourse.pupilsightYearGroupIDList FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND role='Teacher' ORDER BY course, class";
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() != 1) {
                    $proceed = false;
                } else {
                    $values = $result->fetch();
                    $extra = $values['course'].'.'.$values['class'];
                    $pupilsightDepartmentID = $values['pupilsightDepartmentID'];
                    $pupilsightYearGroupIDList = $values['pupilsightYearGroupIDList'];
                }
            }
        } else {
            $extra = dateConvertBack($guid, $date);
        }
       
        if ($proceed == false) {
            echo "<div class='alert alert-danger'>";
            echo __('Your request failed because you do not have access to this action.');
            echo '</div>';
        } else {
            $page->breadcrumbs
                ->add(
                    empty($extra) ?
                        __('Planner') :
                        __('Planner for {classDesc}', ['classDesc' => $extra]),
                    'planner.php',
                    $params
                )
                ->add(__('Add Lesson Plan'));

            $editLink = '';
            if (isset($_GET['editID'])) {
                $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?' . http_build_query($params + [
                    'q' => '/modules/Planner/planner_edit.php',
                    'pupilsightPlannerEntryID' => $_GET['editID'] ?? '',
                ]);
            }
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], $editLink, null);
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/planner_addProcess.php?viewBy=$viewBy&subView=$subView&address=".$_SESSION[$guid]['address']);
            $form->setFactory(PlannerFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            //BASIC INFORMATION
            $form->addRow()->addHeading(__('Basic Information'));

            // if ($viewBy == 'class') {
            //     $form->addHiddenValue('pupilsightCourseClassID', $values['pupilsightCourseClassID']);
            //     $row = $form->addRow();
            //         $row->addLabel('schoolYearName', __('Class'));
            //         $row->addTextField('schoolYearName')->setValue($values['course'].'.'.$values['class'])->required()->readonly();
            // } else {
            //     if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
            //         $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            //         $sql = 'SELECT pupilsightCourseClass.pupilsightCourseClassID AS value, CONCAT(pupilsightCourse.nameShort,".", pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name';
            //     } else {
            //         $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            //         $sql = 'SELECT pupilsightCourseClass.pupilsightCourseClassID AS value, CONCAT(pupilsightCourse.nameShort,".", pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID ORDER BY name';
            //     }
            //     $row = $form->addRow();
            //         $row->addLabel('pupilsightCourseClassID', __('Class'));
            //         $row->addSelect('pupilsightCourseClassID')->fromQuery($pdo, $sql, $data)->required()->placeholder();
            // }

            $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
            $resultp = $connection2->query($sqlp);
            $rowdataprog = $resultp->fetchAll();

            $program = array();
            $program2 = array();
            $program1 = array('' => 'Select Program');
            foreach ($rowdataprog as $dt) {
                $program2[$dt['pupilsightProgramID']] = $dt['name'];
            }
            $program = $program1 + $program2;

            $row = $form->addRow();
                $row->addLabel('pupilsightProgramID', __('Program'));
                $row->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPP')->fromArray($program)->placeholder('Select Program')->required();
        
        
            $row = $form->addRow();
                $row->addLabel('pupilsightYearGroupID', __('Class'));
                $row->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPP')->placeholder('Select Class')->required();
        
                
            $row = $form->addRow();
                $row->addLabel('pupilsightRollGroupID', __('Section'));
                $row->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDbyPP')->placeholder('Select Section')->required(); 

            $row = $form->addRow();
                $row->addLabel('pupilsightDepartmentID', __('Subject'));
                $row->addSelect('pupilsightDepartmentID')->setId('pupilsightDepartmentIDbyPP')->placeholder('Select Subject')->required(); 

            if ($viewBy == 'class') {
                $data = array('pupilsightCourseClassID' => $values['pupilsightCourseClassID']);
                $sql = "SELECT pupilsightCourseClassID AS chainedTo, pupilsightUnit.pupilsightUnitID as value, name FROM pupilsightUnit JOIN pupilsightUnitClass ON (pupilsightUnit.pupilsightUnitID=pupilsightUnitClass.pupilsightUnitID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND active='Y' AND running='Y' ORDER BY name";
                $row = $form->addRow();
                    $row->addLabel('pupilsightUnitID', __('Unit'));
                    $row->addSelect('pupilsightUnitID')->fromQuery($pdo, $sql, $data)->placeholder();
            }
            else {
                $sql = "SELECT GROUP_CONCAT(pupilsightCourseClassID SEPARATOR ' ') AS chainedTo, pupilsightUnit.pupilsightUnitID as value, name FROM pupilsightUnit JOIN pupilsightUnitClass ON (pupilsightUnit.pupilsightUnitID=pupilsightUnitClass.pupilsightUnitID) WHERE active='Y' AND running='Y'  GROUP BY pupilsightUnit.pupilsightUnitID ORDER BY name";
                $row = $form->addRow();
                    $row->addLabel('pupilsightUnitID', __('Unit'));
                    $row->addSelect('pupilsightUnitID')->fromQueryChained($pdo, $sql, [], 'pupilsightCourseClassID')->placeholder();
            }

            

            $row = $form->addRow();
                $row->addLabel('name', __('Lesson Name'));
                $row->addTextField('name')->setValue()->maxLength(50)->required();

            $row = $form->addRow();
                $row->addLabel('summary', __('Summary'));
                $row->addTextField('summary')->setValue()->maxLength(255);

            //Try and find the next unplanned slot for this class.
            if ($viewBy == 'class') {
                //Get $_GET values
                $nextDate = null;
                if (isset($_GET['date'])) {
                    $nextDate = $_GET['date'];
                }
                $nextTimeStart = null;
                if (isset($_GET['timeStart'])) {
                    $nextTimeStart = $_GET['timeStart'];
                }
                $nextTimeEnd = null;
                if (isset($_GET['timeEnd'])) {
                    $nextTimeEnd = $_GET['timeEnd'];
                }

                if ($nextDate == '') {
                    try {
                        $dataNext = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => date('Y-m-d'));
                        $sqlNext = 'SELECT timeStart, timeEnd, date FROM pupilsightTTDayRowClass JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTColumn ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND date>=:date ORDER BY date, timestart LIMIT 0, 10';
                        $resultNext = $connection2->prepare($sqlNext);
                        $resultNext->execute($dataNext);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    $nextDate = '';
                    $nextTimeStart = '';
                    $nextTimeEnd = '';
                    while ($rowNext = $resultNext->fetch()) {
                        try {
                            $dataPlanner = array('date' => $rowNext['date'], 'timeStart' => $rowNext['timeStart'], 'timeEnd' => $rowNext['timeEnd'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                            $sqlPlanner = 'SELECT * FROM pupilsightPlannerEntry WHERE date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd AND pupilsightCourseClassID=:pupilsightCourseClassID';
                            $resultPlanner = $connection2->prepare($sqlPlanner);
                            $resultPlanner->execute($dataPlanner);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultPlanner->rowCount() == 0) {
                            $nextDate = $rowNext['date'];
                            $nextTimeStart = $rowNext['timeStart'];
                            $nextTimeEnd = $rowNext['timeEnd'];
                            break;
                        }
                    }
                }
            }

            if ($viewBy == 'date') {
                $row = $form->addRow();
                    $row->addLabel('date', __('Date'));
                    $row->addDate('date')->setValue(dateConvertBack($guid, $date))->required()->readonly();
            }
            else {
                $row = $form->addRow();
                    $row->addLabel('date', __('Date'));
                    $row->addDate('date')->setValue(dateConvertBack($guid, $nextDate))->required();
            }

            $nextTimeStart = (isset($nextTimeStart)) ? substr($nextTimeStart, 0, 5) : null;
            $row = $form->addRow();
                $row->addLabel('timeStart', __('Start Time'))->description("Format: hh:mm (24hr)");
                $row->addTime('timeStart')->setValue($nextTimeStart)->required();

            $nextTimeEnd = (isset($nextTimeEnd)) ? substr($nextTimeEnd, 0, 5) : null;
            $row = $form->addRow();
                $row->addLabel('timeEnd', __('End Time'))->description("Format: hh:mm (24hr)");
                $row->addTime('timeEnd')->setValue($nextTimeEnd)->required();

            $form->addRow()->addHeading(__('Lesson Content'));

            $description = getSettingByScope($connection2, 'Planner', 'lessonDetailsTemplate') ;
            $row = $form->addRow();
                $column = $row->addColumn();
                $column->addLabel('description', __('Lesson Details'));
                $column->addEditor('description', $guid)->setRows(25)->showMedia()->setValue($description);

            $teachersNotes = getSettingByScope($connection2, 'Planner', 'teachersNotesTemplate');
            $row = $form->addRow();
                $column = $row->addColumn();
                $column->addLabel('teachersNotes', __('Teacher\'s Notes'));
                $column->addEditor('teachersNotes', $guid)->setRows(25)->showMedia()->setValue($teachersNotes);

            //HOMEWORK
            $form->addRow()->addHeading(__('Homework'));

            $form->toggleVisibilityByClass('homework')->onRadio('homework')->when('Y');
            $row = $form->addRow();
                $row->addLabel('homework', __('Homework?'));
                $row->addRadio('homework')->fromArray(array('Y' => __('Yes'), 'N' => __('No')))->required()->checked('N')->inline(true);

            $row = $form->addRow()->addClass('homework');
                $row->addLabel('homeworkDueDate', __('Homework Due Date'));
                $row->addDate('homeworkDueDate')->required();

            $row = $form->addRow()->addClass('homework');
                $row->addLabel('homeworkDueDateTime', __('Homework Due Date Time'))->description("Format: hh:mm (24hr)");
                $row->addTime('homeworkDueDateTime');

            $row = $form->addRow()->addClass('homework');
                $column = $row->addColumn();
                $column->addLabel('homeworkDetails', __('Homework Details'));
                $column->addEditor('homeworkDetails', $guid)->setRows(15)->showMedia()->setValue($description)->required();

            $form->toggleVisibilityByClass('homeworkSubmission')->onRadio('homeworkSubmission')->when('Y');
            $row = $form->addRow()->addClass('homework');
                $row->addLabel('homeworkSubmission', __('Online Submission?'));
                $row->addRadio('homeworkSubmission')->fromArray(array('Y' => __('Yes'), 'N' => __('No')))->required()->checked('N')->inline(true);

            $row = $form->addRow()->setClass('homeworkSubmission');
                $row->addLabel('homeworkSubmissionDateOpen', __('Submission Open Date'));
                $row->addDate('homeworkSubmissionDateOpen')->required();

            $row = $form->addRow()->setClass('homeworkSubmission');
                $row->addLabel('homeworkSubmissionDrafts', __('Drafts'));
                $row->addSelect('homeworkSubmissionDrafts')->fromArray(array('0' => __('None'), '1' => __('1'), '2' => __('2'), '3' => __('3')))->required();

            $row = $form->addRow()->setClass('homeworkSubmission');
                $row->addLabel('homeworkSubmissionType', __('Submission Type'));
                $row->addSelect('homeworkSubmissionType')->fromArray(array('Link' => __('Link'), 'File' => __('File'), 'Link/File' => __('Link/File')))->required();

            $row = $form->addRow()->setClass('homeworkSubmission');
                $row->addLabel('homeworkSubmissionRequired', __('Submission Required'));
                $row->addSelect('homeworkSubmissionRequired')->fromArray(array('Optional' => __('Optional'), 'Compulsory' => __('Compulsory')))->required();

            if (isActionAccessible($guid, $connection2, '/modules/Crowd Assessment/crowdAssess.php')) {
                $form->toggleVisibilityByClass('homeworkCrowdAssess')->onRadio('homeworkCrowdAssess')->when('Y');
                $row = $form->addRow()->addClass('homeworkSubmission');
                    $row->addLabel('homeworkCrowdAssess', __('Crowd Assessment?'));
                    $row->addRadio('homeworkCrowdAssess')->fromArray(array('Y' => __('Yes'), 'N' => __('No')))->required()->checked('N')->inline(true);

                $row = $form->addRow()->addClass('homeworkCrowdAssess');
                    $row->addLabel('homeworkCrowdAssessControl', __('Access Controls?'))->description(__('Decide who can see this homework.'));
                    $column = $row->addColumn()->setClass('flex-col items-end');
                        $column->addCheckbox('homeworkCrowdAssessClassTeacher')->checked(true)->description(__('Class Teacher'))->disabled();
                        $column->addCheckbox('homeworkCrowdAssessClassSubmitter')->checked(true)->description(__('Submitter'))->disabled();
                        $column->addCheckbox('homeworkCrowdAssessClassmatesRead')->description(__('Classmates'));
                        $column->addCheckbox('homeworkCrowdAssessOtherStudentsRead')->description(__('Other Students'));
                        $column->addCheckbox('homeworkCrowdAssessOtherTeachersRead')->description(__('Other Teachers'));
                        $column->addCheckbox('homeworkCrowdAssessSubmitterParentsRead')->description(__('Submitter\'s Parents'));
                        $column->addCheckbox('homeworkCrowdAssessClassmatesParentsRead')->description(__('Classmates\'s Parents'));
                        $column->addCheckbox('homeworkCrowdAssessOtherParentsRead')->description(__('Other Parents'));
            }

            // OUTCOMES
            if ($viewBy == 'date') {
                $form->addRow()->addHeading(__('Outcomes'));
                $form->addRow()->addAlert(__('Outcomes cannot be set when viewing the Planner by date. Use the "Choose A Class" dropdown in the sidebar to switch to a class. Make sure to save your changes first.'), 'warning');
            } else {
                $form->addRow()->addHeading(__('Outcomes'));
                $form->addRow()->addContent(__('Link this lesson to outcomes (defined in the Manage Outcomes section of the Planner), and track which outcomes are being met in which lessons.'));

                $allowOutcomeEditing = getSettingByScope($connection2, 'Planner', 'allowOutcomeEditing');

                $row = $form->addRow();
                    $row->addPlannerOutcomeBlocks('outcome', $pupilsight->session, $pupilsightYearGroupIDList, $pupilsightDepartmentID, $allowOutcomeEditing);
            }

            //MARKBOOK
            $form->addRow()->addHeading(__('Markbook'));

            $form->toggleVisibilityByClass('homework')->onRadio('homework')->when('Y');
            $row = $form->addRow();
                $row->addLabel('markbook', __('Create Markbook Column?'))->description('Linked to this lesson by default.');
                $row->addRadio('markbook')->fromArray(array('Y' => __('Yes'), 'N' => __('No')))->required()->checked('N')->inline(true);

            //ADVANCED
            $form->addRow()->addHeading(__('Advanced Options'));

            $form->toggleVisibilityByClass('advanced')->onCheckbox('advanced')->when('Y');
            $row = $form->addRow();
                $row->addCheckbox('advanced')->setValue('Y')->description('Show Advanced Options');

            //Access
            $form->addRow()->addHeading(__('Access'))->addClass('advanced');

            $sharingDefaultStudents = getSettingByScope($connection2, 'Planner', 'sharingDefaultStudents');
            $row = $form->addRow()->addClass('advanced');
                $row->addLabel('viewableStudents', __('Viewable to Students'));
                $row->addYesNo('viewableStudents')->required()->selected($sharingDefaultStudents);

            $sharingDefaultParents = getSettingByScope($connection2, 'Planner', 'sharingDefaultParents');
            $row = $form->addRow()->addClass('advanced');
                $row->addLabel('viewableParents', __('Viewable to Parents'));
                $row->addYesNo('viewableParents')->required()->selected($sharingDefaultParents);

            //Guests
            $form->addRow()->addHeading(__('Guests'))->addClass('advanced');

            $row = $form->addRow()->addClass('advanced');
                $row->addLabel('guests', __('Guest List'));
                $row->addSelectUsers('guests')->selectMultiple();

            $roles = array(
                'Guest Student' => __('Guest Student'),
                'Guest Teacher' => __('Guest Teacher'),
                'Guest Assistant' => __('Guest Assistant'),
                'Guest Technician' => __('Guest Technician'),
                'Guest Parent' => __('Guest Parent'),
                'Other Guest' => __('Other Guest'),
            );
            $row = $form->addRow()->addClass('advanced');
                $row->addLabel('role', __('Role'));
                $row->addSelect('role')->fromArray($roles);

            $row = $form->addRow();
                $row->addFooter();
                $row->addCheckbox('notify')->description('Notify all class participants');
                $row->addSubmit();
                
            echo $form->getOutput();
        }

        //Print sidebar
        $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $todayStamp, $_SESSION[$guid]['pupilsightPersonID'], $dateStamp, $pupilsightCourseClassID);
    }
}

?>
<style>
    #guestsPhoto {
        margin: 0 0 0 -60px;
    }
</style>
<script>

$(document).ready(function(){
    $("#guests").select2();
});

$(document).on('change', '#pupilsightYearGroupIDbyPP', function () {
    var id = $(this).val();
    var pid = $('#pupilsightProgramIDbyPP').val();
    var type = 'getSubjectbasedonclassNew';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pupilsightProgramID: pid },
        async: true,
        success: function (response) {
            $("#pupilsightDepartmentIDbyPP").html();
            $("#pupilsightDepartmentIDbyPP").html(response);
        }
    });
});

</script>