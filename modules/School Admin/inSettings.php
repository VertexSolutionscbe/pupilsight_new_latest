<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\IndividualNeeds\INGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/inSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Individual Needs Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Individual Needs Descriptors');
    echo '</h3>';


    $INGateway = $container->get(INGateway::class);

    // QUERY
    $criteria = $INGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber'])
        ->fromArray($_POST);

    $individualNeedsDescriptors = $INGateway->queryIndividualNeedsDescriptors($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('individualNeedsDescriptorsManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/School Admin/inSettings_add.php')
        ->displayLabel();

    
    $table->addColumn('sequenceNumber', __('Sequence'));
    $table->addColumn('name', __('Name').'<br/>'.Format::small(__('Short Name')))
        ->width('15%')
        ->format(function($values) {
            return '<strong>'.$values['name'].'</strong><br/>'.Format::small($values['nameShort']);
        });
    $table->addColumn('description', __('Description'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightINDescriptorID')
        ->format(function ($values, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/School Admin/inSettings_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/School Admin/inSettings_delete.php');
        });

    echo $table->render($individualNeedsDescriptors);

    echo '<h3>';
    echo __('Templates');
    echo '</h3>';

    $form = Form::create('inSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/inSettingsProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $setting = getSettingByScope($connection2, 'Individual Needs', 'targetsTemplate', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Individual Needs', 'teachingStrategiesTemplate', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Individual Needs', 'notesReviewTemplate', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
