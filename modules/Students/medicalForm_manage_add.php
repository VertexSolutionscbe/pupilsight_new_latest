<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Medical Forms'), 'medicalForm_manage.php')
        ->add(__('Add Medical Form'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/medicalForm_manage_edit.php&pupilsightPersonMedicalID='.$_GET['editID'].'&search='.$_GET['search'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $pupilsightPersonID = (isset($_GET['pupilsightPersonID']))? $_GET['pupilsightPersonID'] : '';
    $search = $_GET['search'];
    if ($search != '') {
        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/medicalForm_manage.php&search=$search'>".__('Back to Search Results').'</a>';
        echo '</div>';
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/medicalForm_manage_addProcess.php?search=$search");

    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addRow()->addHeading(__('General Information'));

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Student'));
        $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->required()->placeholder()->selected($pupilsightPersonID);

    $row = $form->addRow();
        $row->addLabel('bloodType', __('Blood Type'));
        $row->addSelectBloodType('bloodType')->placeholder();

    $row = $form->addRow();
        $row->addLabel('longTermMedication', __('Long-Term Medication?'));
        $row->addYesNo('longTermMedication')->placeholder();

    $form->toggleVisibilityByClass('longTermMedicationDetails')->onSelect('longTermMedication')->when('Y');

    $row = $form->addRow()->addClass('longTermMedicationDetails');;
        $row->addLabel('longTermMedicationDetails', __('Medication Details'));
        $row->addTextArea('longTermMedicationDetails')->setRows(5);

    $row = $form->addRow();
        $row->addLabel('tetanusWithin10Years', __('Tetanus Within Last 10 Years?'));
        $row->addYesNo('tetanusWithin10Years')->placeholder();

    $row = $form->addRow();
        $row->addLabel('comment', __('Comment'));
        $row->addTextArea('comment')->setRows(6);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
