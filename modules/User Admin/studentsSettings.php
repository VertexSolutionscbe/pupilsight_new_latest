<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentNoteGateway;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/studentsSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Students Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Student Note Categories');
    echo '</h3>';
    echo '<p>';
    echo __('This section allows you to manage the categories which can be associated with student notes. Categories can be given templates, which will pre-populate the student note on selection.');
    echo '</p>';


    $studentNoteGateway = $container->get(StudentNoteGateway::class);

    // QUERY
    $criteria = $studentNoteGateway->newQueryCriteria()
        ->sortBy(['name'])
        ->fromArray($_POST);

    $studentNoteCategories = $studentNoteGateway->queryStudentNoteCategories($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('studentNoteCategoriesManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/User Admin/studentsSettings_noteCategory_add.php')
        ->displayLabel();

    $table->modifyRows(function ($values, $row) {
        if ($values['active'] == 'N') $row->addClass('error');
        return $row;
    });
    
    $table->addColumn('name', __('Name'));
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightStudentNoteCategoryID')
        ->format(function ($values, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/User Admin/studentsSettings_noteCategory_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/User Admin/studentsSettings_noteCategory_delete.php');
        });

    echo $table->render($studentNoteCategories);

    echo '<h3>';
    echo __('Settings');
    echo '</h3>';

    $form = Form::create('studentsSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/studentsSettingsProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addRow()->addHeading(__('Student Notes'));

    $setting = getSettingByScope($connection2, 'Students', 'enableStudentNotes', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Students', 'noteCreationNotification', true);
    $noteCreationNotificationRoles = array(
        'Tutors' => __('Tutors'), 
        'Tutors & Teachers' => __('Tutors & Teachers')
    );
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($noteCreationNotificationRoles)->selected($setting['value'])->required();

    $form->addRow()->addHeading(__('Alerts'));

    $setting = getSettingByScope($connection2, 'Students', 'academicAlertLowThreshold', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))
            ->description(__($setting['description']));
        $row->addNumber($setting['name'])
            ->setValue($setting['value'])
            ->decimalPlaces(0)
            ->minimum(0)
            ->maximum(50)
            ->required();

    $setting = getSettingByScope($connection2, 'Students', 'academicAlertMediumThreshold', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))
            ->description(__($setting['description']));
        $row->addNumber($setting['name'])
            ->setValue($setting['value'])
            ->decimalPlaces(0)
            ->minimum(0)
            ->maximum(50)
            ->required();

    $setting = getSettingByScope($connection2, 'Students', 'academicAlertHighThreshold', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))
            ->description(__($setting['description']));
        $row->addNumber($setting['name'])
            ->setValue($setting['value'])
            ->decimalPlaces(0)
            ->minimum(0)
            ->maximum(50)
            ->required();

        $setting = getSettingByScope($connection2, 'Students', 'behaviourAlertLowThreshold', true);
        $row = $form->addRow();
            $row->addLabel($setting['name'], __($setting['nameDisplay']))
                ->description(__($setting['description']));
            $row->addNumber($setting['name'])
                ->setValue($setting['value'])
                ->decimalPlaces(0)
                ->minimum(0)
                ->maximum(50)
                ->required();

        $setting = getSettingByScope($connection2, 'Students', 'behaviourAlertMediumThreshold', true);
        $row = $form->addRow();
            $row->addLabel($setting['name'], __($setting['nameDisplay']))
                ->description(__($setting['description']));
            $row->addNumber($setting['name'])
                ->setValue($setting['value'])
                ->decimalPlaces(0)
                ->minimum(0)
                ->maximum(50)
                ->required();

        $setting = getSettingByScope($connection2, 'Students', 'behaviourAlertHighThreshold', true);
        $row = $form->addRow();
            $row->addLabel($setting['name'], __($setting['nameDisplay']))
                ->description(__($setting['description']));
            $row->addNumber($setting['name'])
                ->setValue($setting['value'])
                ->decimalPlaces(0)
                ->minimum(0)
                ->maximum(50)
                ->required();

    $form->addRow()->addHeading(__('Miscellaneous'));

    $setting = getSettingByScope($connection2, 'Students', 'extendedBriefProfile', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'School Admin', 'studentAgreementOptions', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
