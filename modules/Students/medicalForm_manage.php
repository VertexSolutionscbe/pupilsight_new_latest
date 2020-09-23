<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\MedicalGateway;

if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Medical Forms'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = isset($_GET['search'])? $_GET['search'] : '';

    $medicalGateway = $container->get(MedicalGateway::class);

    // CRITERIA
    $criteria = $medicalGateway->newQueryCriteria()
        ->searchBy($medicalGateway->getSearchableColumns(), $search)
        ->sortBy(['surname', 'preferredName'])
        ->fromPOST();

    echo '<h2>';
    echo __('Search');
    echo '</h2>';

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/medicalForm_manage.php');

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
        $row->addTextField('search')->setValue($criteria->getSearchText());

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

    echo $form->getOutput();

    echo '<h2>';
    echo __('View');
    echo '</h2>';

    $medicalForms = $medicalGateway->queryMedicalFormsBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);

    // DATA TABLE
    $table = DataTable::createPaginated('medicalForms', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Students/medicalForm_manage_add.php')
        ->addParam('search', $criteria->getSearchText(true))
        ->displayLabel();

    // COLUMNS
    $table->addExpandableColumn('comment')->format(function($person) {
        return !empty($person['comment'])? '<b>'.__('Comment').'</b><br/>'.nl2brr($person['comment']) : '';
    });

    $table->addColumn('student', __('Student'))
        ->sortable(['surname', 'preferredName'])
        ->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', true]));

    $table->addColumn('rollGroup', __('Roll Group'));

    $table->addColumn('bloodType', __('Blood Type'));

    $table->addColumn('longTermMedication', __('Medication'))
        ->format(function($person) {
            return !empty($person['longTermMedicationDetails'])? $person['longTermMedicationDetails'] : Format::yesNo($person['longTermMedication']);
        });

    $table->addColumn('tetanusWithin10Years', __('Tetanus'))
        ->format(Format::using('yesNo', 'tetanusWithin10Years'));

    $table->addColumn('conditionCount', __('Conditions'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightPersonMedicalID')
        ->addParam('search', $criteria->getSearchText(true))
        ->format(function ($person, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Students/medicalForm_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Students/medicalForm_manage_delete.php');
        });

    echo $table->render($medicalForms);
}
