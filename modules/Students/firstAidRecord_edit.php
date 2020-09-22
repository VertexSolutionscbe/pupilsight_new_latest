<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/firstAidRecord_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    //Proceed!
    $page->breadcrumbs
        ->add(__('First Aid Records'), 'firstAidRecord.php')
        ->add(__('Edit'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightFirstAidID = $_GET['pupilsightFirstAidID'] ?? '';
    $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'] ?? '';
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'] ?? '';

    if ($pupilsightFirstAidID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightFirstAidID' => $pupilsightFirstAidID);
            $sql = "SELECT pupilsightFirstAid.*, patient.pupilsightPersonID AS pupilsightPersonIDPatient, patient.surname AS surnamePatient, patient.preferredName AS preferredNamePatient, firstAider.title, firstAider.surname AS surnameFirstAider, firstAider.preferredName AS preferredNameFirstAider
                FROM pupilsightFirstAid
                    JOIN pupilsightPerson AS patient ON (pupilsightFirstAid.pupilsightPersonIDPatient=patient.pupilsightPersonID)
                    JOIN pupilsightPerson AS firstAider ON (pupilsightFirstAid.pupilsightPersonIDFirstAider=firstAider.pupilsightPersonID)
                    JOIN pupilsightStudentEnrolment ON (patient.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFirstAidID=:pupilsightFirstAidID";
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
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/firstAidRecord_editProcess.php?pupilsightFirstAidID=$pupilsightFirstAidID&pupilsightRollGroupID=".$pupilsightRollGroupID.'&pupilsightYearGroupID='.$pupilsightYearGroupID);

            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addHiddenValue('pupilsightPersonID', $values['pupilsightPersonIDPatient']);
            $row = $form->addRow();
                $row->addLabel('patient', __('Patient'));
                $row->addTextField('patient')->setValue(Format::name('', $values['preferredNamePatient'], $values['surnamePatient'], 'Student'))->required()->readonly();

            $row = $form->addRow();
                $row->addLabel('name', __('First Aider'));
                $row->addTextField('name')->setValue(Format::name('', $values['preferredNameFirstAider'], $values['surnameFirstAider'], 'Staff', false, true))->required()->readonly();

            $row = $form->addRow();
                $row->addLabel('date', __('Date'));
                $row->addDate('date')->setValue(dateConvertBack($guid, $values['date']))->required()->readonly();

            $row = $form->addRow();
                $row->addLabel('timeIn', __('Time In'));
                $row->addTime('timeIn')->setValue(substr($values['timeIn'], 0, 5))->required()->readonly();

            $row = $form->addRow();
                $row->addLabel('timeOut', __('Time Out'));
                $row->addTime('timeOut')->setValue(substr($values['timeOut'], 0, 5))->chainedTo('timeIn');

            $row = $form->addRow();
                $column = $row->addColumn();
                $column->addLabel('description', __('Description'));
                $column->addTextArea('description')->setValue($values['description'])->setRows(8)->setClass('fullWidth')->readonly();

            $row = $form->addRow();
                $column = $row->addColumn();
                $column->addLabel('actionTaken', __('Action Taken'));
                $column->addTextArea('actionTaken')->setValue($values['actionTaken'])->setRows(8)->setClass('fullWidth')->readonly();

            $row = $form->addRow();
                $column = $row->addColumn();
                $column->addLabel('followUp', __('Follow Up'));
                $column->addTextArea('followUp')->setValue($values['followUp'])->setRows(8)->setClass('fullWidth');

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
