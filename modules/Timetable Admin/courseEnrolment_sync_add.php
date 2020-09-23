<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_sync_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightSchoolYearID = $_REQUEST['pupilsightSchoolYearID'] ?? '';

    $page->breadcrumbs
        ->add(__('Sync Course Enrolment'), 'courseEnrolment_sync.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Map Classes'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if (empty($pupilsightSchoolYearID)) {
        echo '<div class="alert alert-danger">';
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
        return;
    }

    $form = Form::create('courseEnrolmentSyncAdd', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_sync_edit.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

    $row = $form->addRow();
        $row->addLabel('pupilsightYearGroupID', __('Year Group'))->description(__('Determines the available courses and classes to map.'));
        $row->addSelectYearGroup('pupilsightYearGroupID')->required();

    $row = $form->addRow();
        $column = $row->addColumn();
        $column->addLabel('courseClassMapping', __('Compare to Pattern'))->description(sprintf(__('Classes will be matched to Roll Groups that fit the specified pattern. Choose from %1$s. Must contain %2$s'), '[courseShortName] [yearGroupShortName] [rollGroupShortName]', '[classShortName]'));

        $row->addTextField('pattern')
            ->required()
            ->setValue('[yearGroupShortName].[classShortName]')
            ->addValidation('Validate.Format', 'pattern: /(\[classShortName\])/');

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
