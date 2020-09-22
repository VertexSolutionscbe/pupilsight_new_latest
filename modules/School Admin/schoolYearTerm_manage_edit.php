<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearTerm_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs
        ->add(__('Manage Terms'), 'schoolYearTerm_manage.php')
        ->add(__('Edit Term'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightSchoolYearTermID = $_GET['pupilsightSchoolYearTermID'];
    if ($pupilsightSchoolYearTermID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID);
            $sql = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID';
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
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('schoolYearTerm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/schoolYearTerm_manage_editProcess.php?pupilsightSchoolYearTermID='.$pupilsightSchoolYearTermID);
		    $form->setFactory(DatabaseFormFactory::create($pdo));

		    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

		    $row = $form->addRow();
		        $row->addLabel('pupilsightSchoolYearID', __('School Year'));
		        $row->addSelectSchoolYear('pupilsightSchoolYearID')->required()->selected($values['pupilsightSchoolYearID']);

		    $row = $form->addRow();
		        $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
		        $row->addSequenceNumber('sequenceNumber', 'pupilsightSchoolYearTerm', $values['sequenceNumber'])
		        	->required()
		        	->maxLength(3)
		        	->setValue($values['sequenceNumber']);

		    $row = $form->addRow();
		        $row->addLabel('name', __('Name'));
		        $row->addTextField('name')->required()->maxLength(20)->setValue($values['name']);

		    $row = $form->addRow();
		        $row->addLabel('nameShort', __('Short Name'));
		        $row->addTextField('nameShort')->required()->maxLength(4)->setValue($values['nameShort']);

		    $row = $form->addRow();
		        $row->addLabel('firstDay', __('First Day'));
		        $row->addDate('firstDay')->required()->setValue(dateConvertBack($guid, $values['firstDay']));

		    $row = $form->addRow();
		        $row->addLabel('lastDay', __('Last Day'));
		        $row->addDate('lastDay')->required()->setValue(dateConvertBack($guid, $values['lastDay']));

		    $row = $form->addRow();
		        $row->addFooter();
		        $row->addSubmit();

		    echo $form->getOutput();
        }
    }
}
