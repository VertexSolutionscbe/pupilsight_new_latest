<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$enableDescriptors = getSettingByScope($connection2, 'Behaviour', 'enableDescriptors');
$enableLevels = getSettingByScope($connection2, 'Behaviour', 'enableLevels');

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $page->breadcrumbs
            ->add(__('Manage Behaviour Records'), 'behaviour_manage.php')
            ->add(__('Add'));

        $pupilsightBehaviourID = isset($_GET['pupilsightBehaviourID'])? $_GET['pupilsightBehaviourID'] : null;
        $pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : '';
        $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : '';
        $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])? $_GET['pupilsightYearGroupID'] : '';
        $type = isset($_GET['type'])? $_GET['type'] : '';

        $editLink = '';
        $editID = '';
        if (isset($_GET['editID'])) {
            $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Behaviour/behaviour_manage_edit.php&pupilsightBehaviourID='.$_GET['editID'].'&pupilsightPersonID='.$pupilsightPersonID.'&pupilsightRollGroupID='.$pupilsightRollGroupID.'&pupilsightYearGroupID='.$pupilsightYearGroupID.'&type='.$type;
            $editID = $_GET['editID'];
        }
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], $editLink, array('warning1' => 'Your request was successful, but some data was not properly saved.', 'success1' => 'Your request was completed successfully. You can now add extra information below if you wish.'));
        }

        $step = null;
        if (isset($_GET['step'])) {
            $step = $_GET['step'];
        }
        if ($step != 1 and $step != 2) {
            $step = 1;
        }

        //Step 1
        if ($step == 1 or $pupilsightBehaviourID == null) {
            echo "<div class='linkTop'>";
            $policyLink = getSettingByScope($connection2, 'Behaviour', 'policyLink');
            if ($policyLink != '') {
                echo "<a target='_blank' href='$policyLink'>".__('View Behaviour Policy').'</a>';
            }
            if ($pupilsightPersonID != '' or $pupilsightRollGroupID != '' or $pupilsightYearGroupID != '' or $type != '') {
                if ($policyLink != '') {
                    echo ' | ';
                }
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Behaviour/behaviour_manage.php&pupilsightPersonID='.$pupilsightPersonID.'&pupilsightRollGroupID='.$pupilsightRollGroupID.'&pupilsightYearGroupID='.$pupilsightYearGroupID.'&type='.$type."'>".__('Back to Search Results').'</a>';
            }
            echo '</div>';

            $form = Form::create('addform', $_SESSION[$guid]['absoluteURL'].'/modules/Behaviour/behaviour_manage_addProcess.php?step=1&pupilsightPersonID='.$pupilsightPersonID.'&pupilsightRollGroupID='.$pupilsightRollGroupID.'&pupilsightYearGroupID='.$pupilsightYearGroupID.'&type='.$type);
            $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->addHiddenValue('address', "/modules/Behaviour/behaviour_manage_add.php");
            $form->addRow()->addHeading(__('Step 1'));

            //Student
            $row = $form->addRow();
            	$row->addLabel('pupilsightPersonID', __('Student'));
            	$row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->placeholder(__('Please select...'))->selected($pupilsightPersonID)->required();

            //Date
            $row = $form->addRow();
            	$row->addLabel('date', __('Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
            	$row->addDate('date')->setValue(date($_SESSION[$guid]['i18n']['dateFormatPHP']))->required();

            //Type
            $row = $form->addRow();
            	$row->addLabel('type', __('Type'));
            	$row->addSelect('type')->fromArray(array('Positive' => __('Positive'), 'Negative' => __('Negative')))->selected($type)->required();

            //Descriptor
            if ($enableDescriptors == 'Y') {
                $negativeDescriptors = getSettingByScope($connection2, 'Behaviour', 'negativeDescriptors');
                $negativeDescriptors = (!empty($negativeDescriptors))? explode(',', $negativeDescriptors) : array();
                $positiveDescriptors = getSettingByScope($connection2, 'Behaviour', 'positiveDescriptors');
                $positiveDescriptors = (!empty($positiveDescriptors))? explode(',', $positiveDescriptors) : array();

                $chainedToNegative = array_combine($negativeDescriptors, array_fill(0, count($negativeDescriptors), 'Negative'));
                $chainedToPositive = array_combine($positiveDescriptors, array_fill(0, count($positiveDescriptors), 'Positive'));
                $chainedTo = array_merge($chainedToNegative, $chainedToPositive);

                $row = $form->addRow();
            		$row->addLabel('descriptor', __('Descriptor'));
                    $row->addSelect('descriptor')
                        ->fromArray($positiveDescriptors)
                        ->fromArray($negativeDescriptors)
                        ->chainedTo('type', $chainedTo)
                        ->required()
                        ->placeholder();
            }

            //Level
            if ($enableLevels == 'Y') {
                $optionsLevels = getSettingByScope($connection2, 'Behaviour', 'levels');
                if ($optionsLevels != '') {
                    $optionsLevels = explode(',', $optionsLevels);
                }
                $row = $form->addRow();
                	$row->addLabel('level', __('Level'));
                	$row->addSelect('level')->fromArray($optionsLevels)->placeholder();
            }

			//Incident
            $row = $form->addRow();
                $column = $row->addColumn();
                $column->addLabel('comment', __('Incident'));
            	$column->addTextArea('comment')->setRows(5)->setClass('fullWidth');

            //Follow Up
            $row = $form->addRow();
            	$column = $row->addColumn();
            	$column->addLabel('followup', __('Follow Up'));
            	$column->addTextArea('followup')->setRows(5)->setClass('fullWidth');

            //Copy to Notes
            $row = $form->addRow();
                $row->addLabel('copyToNotes', __('Copy To Notes'));
                $row->addCheckbox('copyToNotes');

            $row = $form->addRow();
            	$row->addFooter();
            	$row->addSubmit();

            echo $form->getOutput();

        } elseif ($step == 2 and $pupilsightBehaviourID != null) {
            if ($pupilsightBehaviourID == '') {
                echo "<div class='alert alert-danger'>";
                echo __('You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                //Check for existence of behaviour record
                try {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightBehaviourID' => $pupilsightBehaviourID);
                    $sql = "SELECT * FROM pupilsightBehaviour JOIN pupilsightPerson ON (pupilsightBehaviour.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightBehaviourID=:pupilsightBehaviourID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($result->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The specified record cannot be found.');
                    echo '</div>';
                } else {
                    $values = $result->fetch();

                    $form = Form::create('addform', $_SESSION[$guid]['absoluteURL'].'/modules/Behaviour/behaviour_manage_addProcess.php?step=2&pupilsightPersonID='.$pupilsightPersonID.'&pupilsightRollGroupID='.$pupilsightRollGroupID.'&pupilsightYearGroupID='.$pupilsightYearGroupID.'&type='.$type);
                    $form->setFactory(DatabaseFormFactory::create($pdo));
                    $form->addHiddenValue('address', "/modules/Behaviour/behaviour_manage_add.php");
                    $form->addHiddenValue('pupilsightBehaviourID', $pupilsightBehaviourID);
                    $form->addRow()->addHeading(__('Step 2 (Optional)'));

                    //Student
                    $row = $form->addRow();
                    	$row->addLabel('students', __('Student'));
                    	$row->addTextField('students')->setValue(formatName('', $values['preferredName'], $values['surname'], 'Student'))->readonly();
                        $form->addHiddenValue('pupilsightPersonID', $values['pupilsightPersonID']);

                    //Lessons
                    $lessons = array();
                    $minDate = date('Y-m-d', (time() - (24 * 60 * 60 * 30)));
                    try {
                        $dataSelect = array('date1' => date('Y-m-d', time()), 'date2' => $minDate, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $values['pupilsightPersonID']);
                        $sqlSelect = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightPlannerEntry.name AS lesson, pupilsightPlannerEntryID, date, homework, homeworkSubmission FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightPlannerEntry ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightPlannerEntry.pupilsightCourseClassID) WHERE (date<=:date1 AND date>=:date2) AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Student' ORDER BY course, class, date DESC, timeStart";
                        $resultSelect = $connection2->prepare($sqlSelect);
                        $resultSelect->execute($dataSelect);
                    } catch (PDOException $e) {
                    }
                    while ($rowSelect = $resultSelect->fetch()) {
                        $show = true;
                        if ($highestAction == 'Manage Behaviour Records_my') {
                            try {
                                $dataShow = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $rowSelect['pupilsightCourseClassID']);
                                $sqlShow = "SELECT * FROM pupilsightCourseClassPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID AND role='Teacher'";
                                $resultShow = $connection2->prepare($sqlShow);
                                $resultShow->execute($dataShow);
                            } catch (PDOException $e) { }
                            if ($resultShow->rowCount() != 1) {
                                $show = false;
                            }
                        }
                        if ($show == true) {
                            $submission = '';
                            if ($rowSelect['homework'] == 'Y') {
                                $submission = 'HW';
                                if ($rowSelect['homeworkSubmission'] == 'Y') {
                                    $submission .= '+OS';
                                }
                            }
                            if ($submission != '') {
                                $submission = ' - '.$submission;
                            }
                            $lessons[$rowSelect['pupilsightPlannerEntryID']] = htmlPrep($rowSelect['course']).'.'.htmlPrep($rowSelect['class']).' '.htmlPrep($rowSelect['lesson']).' - '.substr(dateConvertBack($guid, $rowSelect['date']), 0, 5).$submission;
                        }
                    }

                    $row = $form->addRow();
                        $row->addLabel('pupilsightPlannerEntryID', __('Link To Lesson?'))->description(__('From last 30 days'));
                        if (count($lessons) < 1) {
                            $row->addSelect('pupilsightPlannerEntryID')->placeholder();
                        }
                        else {
                            $row->addSelect('pupilsightPlannerEntryID')->fromArray($lessons)->placeholder();
                        }

                    $row = $form->addRow();
                    	$row->addFooter();
                    	$row->addSubmit();

                    echo $form->getOutput();
                }
            }
        }
    }
}
?>
