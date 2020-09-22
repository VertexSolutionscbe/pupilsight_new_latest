<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\MedicalGateway;

if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Medical Forms'), 'medicalForm_manage.php')
        ->add(__('Edit Medical Form'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if person medical specified
    $pupilsightPersonMedicalID = isset($_GET['pupilsightPersonMedicalID'])? $_GET['pupilsightPersonMedicalID'] : '';
    $search = isset($_GET['search'])? $_GET['search'] : '';

    if ($pupilsightPersonMedicalID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {

        $medicalGateway = $container->get(MedicalGateway::class);
        $criteria = $medicalGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();
        $values = $medicalGateway->getMedicalFormByID($pupilsightPersonMedicalID);

        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/medicalForm_manage.php&search=$search'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/medicalForm_manage_editProcess.php?pupilsightPersonMedicalID='.$pupilsightPersonMedicalID."&search=$search");

            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('General Information'));

            $row = $form->addRow();
                $row->addLabel('name', __('Student'));
                $row->addTextField('name')->setValue(Format::name('', htmlPrep($values['preferredName']), htmlPrep($values['surname']), 'Student'))->required()->readonly();

            $row = $form->addRow();
                $row->addLabel('bloodType', __('Blood Type'));
                $row->addSelectBloodType('bloodType')->placeholder();

            $row = $form->addRow();
                $row->addLabel('longTermMedication', __('Long-Term Medication?'));
                $row->addYesNo('longTermMedication')->placeholder();

            $form->toggleVisibilityByClass('longTermMedicationDetails')->onSelect('longTermMedication')->when('Y');

            $row = $form->addRow()->addClass('longTermMedicationDetails');
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

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();

            echo '<h2>';
            echo __('Medical Conditions');
            echo '</h2>';

            $conditions = $medicalGateway->selectMedicalConditionsByID($pupilsightPersonMedicalID);

            $table = DataTable::createPaginated('medicalConditions', $criteria);

            $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/Students/medicalForm_manage_condition_add.php')
                ->addParam('pupilsightPersonMedicalID', $pupilsightPersonMedicalID)
                ->addParam('search', $search)
                ->displayLabel();

            $table->addColumn('name', __('Name'));
            $table->addColumn('risk', __('Risk'));
            $table->addColumn('details', __('Details'))->format(function($condition){
                $output = '';
                if (!empty($condition['triggers'])) $output .= '<b>'.__('Triggers').':</b> '.$condition['triggers'].'<br/>';
                if (!empty($condition['reaction'])) $output .= '<b>'.__('Reaction').':</b> '.$condition['reaction'].'<br/>';
                if (!empty($condition['response'])) $output .= '<b>'.__('Response').':</b> '.$condition['response'].'<br/>';
                if (!empty($condition['lastEpisode'])) $output .= '<b>'.__('Last Episode').':</b> '.Format::date($condition['lastEpisode']).'<br/>';
                if (!empty($condition['lastEpisodeTreatment'])) $output .= '<b>'.__('Last Episode Treatment').':</b> '.$condition['lastEpisodeTreatment'].'<br/>';
                return $output;
            });
            $table->addColumn('medication', __('Medication'));
            $table->addColumn('comment', __('Comment'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightPersonMedicalID', $pupilsightPersonMedicalID)
                ->addParam('pupilsightPersonMedicalConditionID')
                ->addParam('search', $search)
                ->format(function ($person, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Students/medicalForm_manage_condition_edit.php');

                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Students/medicalForm_manage_condition_delete.php');
                });

            echo $table->render($conditions->toDataSet());
        }
    }
}
