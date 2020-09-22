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

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage_edit.php') == false) {
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
        //Proceed!
        $page->breadcrumbs
            ->add(__('Manage Behaviour Records'), 'behaviour_manage.php')
            ->add(__('Edit'));
        
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $pupilsightBehaviourID = $_GET['pupilsightBehaviourID'];
        if ($pupilsightBehaviourID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Manage Behaviour Records_all') {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightBehaviourID' => $pupilsightBehaviourID);
                    $sql = 'SELECT pupilsightBehaviour.*, student.surname AS surnameStudent, student.preferredName AS preferredNameStudent, creator.surname AS surnameCreator, creator.preferredName AS preferredNameCreator, creator.title FROM pupilsightBehaviour JOIN pupilsightPerson AS student ON (pupilsightBehaviour.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightPerson AS creator ON (pupilsightBehaviour.pupilsightPersonIDCreator=creator.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightBehaviourID=:pupilsightBehaviourID ORDER BY date DESC';
                } elseif ($highestAction == 'Manage Behaviour Records_my') {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightBehaviourID' => $pupilsightBehaviourID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = 'SELECT pupilsightBehaviour.*, student.surname AS surnameStudent, student.preferredName AS preferredNameStudent, creator.surname AS surnameCreator, creator.preferredName AS preferredNameCreator, creator.title FROM pupilsightBehaviour JOIN pupilsightPerson AS student ON (pupilsightBehaviour.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightPerson AS creator ON (pupilsightBehaviour.pupilsightPersonIDCreator=creator.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightBehaviourID=:pupilsightBehaviourID AND pupilsightPersonIDCreator=:pupilsightPersonID ORDER BY date DESC';
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
                echo "<div class='linkTop'>";
                $policyLink = getSettingByScope($connection2, 'Behaviour', 'policyLink');
                if ($policyLink != '') {
                    echo "<a target='_blank' href='$policyLink'>".__('View Behaviour Policy').'</a>';
                }
                if ($_GET['pupilsightPersonID'] != '' or $_GET['pupilsightRollGroupID'] != '' or $_GET['pupilsightYearGroupID'] != '' or $_GET['type'] != '') {
                    if ($policyLink != '') {
                        echo ' | ';
                    }
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Behaviour/behaviour_manage.php&pupilsightPersonID='.$_GET['pupilsightPersonID'].'&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'].'&type='.$_GET['type']."'>".__('Back to Search Results').'</a>';
                }
                echo '</div>';

                //Let's go!
                $values = $result->fetch();

                $form = Form::create('addform', $_SESSION[$guid]['absoluteURL'].'/modules/Behaviour/behaviour_manage_editProcess.php?pupilsightBehaviourID='.$pupilsightBehaviourID.'&pupilsightPersonID='.$_GET['pupilsightPersonID'].'&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'].'&type='.$_GET['type']);
                $form->setFactory(DatabaseFormFactory::create($pdo));
                $form->addHiddenValue('address', "/modules/Behaviour/behaviour_manage_add.php");

                //Student
                $row = $form->addRow();
                    $row->addLabel('students', __('Student'));
                    $row->addTextField('students')->setValue(formatName('', $values['preferredNameStudent'], $values['surnameStudent'], 'Student'))->readonly();
                    $form->addHiddenValue('pupilsightPersonID', $values['pupilsightPersonID']);

                //Date
                $row = $form->addRow();
                	$row->addLabel('date', __('Date'));
                	$row->addDate('date')->setValue(dateConvertBack($guid, $values['date']))->required()->readonly();

                //Date
                $row = $form->addRow();
                    $row->addLabel('type', __('Type'));
                    $row->addTextField('type')->setValue($values['type'])->required()->readonly();

                //Descriptor
                if ($enableDescriptors == 'Y') {
                    if ($values['type'] == 'Negative') {
                        $descriptors = getSettingByScope($connection2, 'Behaviour', 'negativeDescriptors');
                    }
                    else {
                        $descriptors = getSettingByScope($connection2, 'Behaviour', 'positiveDescriptors');
                    }
                    $descriptors = (!empty($descriptors))? explode(',', $descriptors) : array();

                    $row = $form->addRow();
                		$row->addLabel('descriptor', __('Descriptor'));
                        $row->addSelect('descriptor')
                            ->fromArray($descriptors)
                            ->selected($values['descriptor'])
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
                    	$row->addSelect('level')->fromArray($optionsLevels)->selected($values['level'])->placeholder();
                }

                //Incident
                $row = $form->addRow();
                    $column = $row->addColumn();
                    $column->addLabel('comment', __('Incident'));
                    $column->addTextArea('comment')->setRows(5)->setClass('fullWidth')->setValue($values['comment']);

                //Follow Up
                $row = $form->addRow();
                    $column = $row->addColumn();
                    $column->addLabel('followup', __('Follow Up'));
                    $column->addTextArea('followup')->setRows(5)->setClass('fullWidth')->setValue($values['followup']);

                //Lesson link
                $lessons = array();
                $minDate = date('Y-m-d', (strtotime($values['date']) - (24 * 60 * 60 * 30)));
                try {
                    $dataSelect = array('date' => date('Y-m-d', strtotime($values['date'])), 'minDate' => $minDate, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $values['pupilsightPersonID']);
                    $sqlSelect = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightPlannerEntry.name AS lesson, pupilsightPlannerEntryID, date, homework, homeworkSubmission FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightPlannerEntry ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightPlannerEntry.pupilsightCourseClassID) WHERE (date<=:date AND date>=:minDate) AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Student' ORDER BY course, class, date, timeStart";
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
                        } catch (PDOException $e) {
                        }
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
                        $selected = '';
                        if ($rowSelect['pupilsightPlannerEntryID'] == $values['pupilsightPlannerEntryID']) {
                            $selected = 'selected';
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
                        $row->addSelect('pupilsightPlannerEntryID')->fromArray($lessons)->placeholder()->selected($values['pupilsightPlannerEntryID']);
                    }

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit()->setClass('submit_align submt ');

                echo $form->getOutput();
            }
        }
    }
}
?>
