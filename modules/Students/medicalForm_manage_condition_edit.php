<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Students\MedicalGateway;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_condition_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightPersonMedicalID = $_GET['pupilsightPersonMedicalID'] ?? '';
    $pupilsightPersonMedicalConditionID = $_GET['pupilsightPersonMedicalConditionID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Medical Forms'), 'medicalForm_manage.php')
        ->add(__('Manage Medical Forms'), 'medicalForm_manage.php')
        ->add(__('Edit Medical Form'), 'medicalForm_manage_edit.php', ['pupilsightPersonMedicalID' => $pupilsightPersonMedicalID])
        ->add(__('Edit Condition'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if ($pupilsightPersonMedicalID == '' or $pupilsightPersonMedicalConditionID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $medicalGateway = $container->get(MedicalGateway::class);
        $values = $medicalGateway->getMedicalConditionByID($pupilsightPersonMedicalConditionID);

        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/medicalForm_manage_edit.php&search=$search&pupilsightPersonMedicalID=$pupilsightPersonMedicalID'>".__('Back').'</a>';
                echo '</div>';
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/medicalForm_manage_condition_editProcess.php?pupilsightPersonMedicalID=$pupilsightPersonMedicalID&search=$search&pupilsightPersonMedicalConditionID=$pupilsightPersonMedicalConditionID");

            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightPersonMedicalID', $pupilsightPersonMedicalID);

            $form->addRow()->addHeading(__('General Information'));

            $row = $form->addRow();
                $row->addLabel('personName', __('Student'));
                $row->addTextField('personName')->setValue(Format::name('', htmlPrep($values['preferredName']), htmlPrep($values['surname']), 'Student'))->required()->readonly();

            $sql = "SELECT name AS value, name FROM pupilsightMedicalCondition ORDER BY name";
            $row = $form->addRow();
                $row->addLabel('name', __('Condition Name'));
                $row->addSelect('name')->fromQuery($pdo, $sql)->required()->placeholder();

            $row = $form->addRow();
                $row->addLabel('pupilsightAlertLevelID', __('Risk'));
                $row->addSelectAlert('pupilsightAlertLevelID')->required();

            $row = $form->addRow();
                $row->addLabel('triggers', __('Triggers'));
                $row->addTextField('triggers')->maxLength(255);

            $row = $form->addRow();
                $row->addLabel('reaction', __('Reaction'));
                $row->addTextField('reaction')->maxLength(255);

            $row = $form->addRow();
                $row->addLabel('response', __('Response'));
                $row->addTextField('response')->maxLength(255);

            $row = $form->addRow();
                $row->addLabel('medication', __('Medication'));
                $row->addTextField('medication')->maxLength(255);

            $row = $form->addRow();
                $row->addLabel('lastEpisode', __('Last Episode Date'));
                $row->addDate('lastEpisode');

            $row = $form->addRow();
                $row->addLabel('lastEpisodeTreatment', __('Last Episode Treatment'));
                $row->addTextField('lastEpisodeTreatment')->maxLength(255);

            $row = $form->addRow();
                $row->addLabel('comment', __('Comment'));
                $row->addTextArea('comment');

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
