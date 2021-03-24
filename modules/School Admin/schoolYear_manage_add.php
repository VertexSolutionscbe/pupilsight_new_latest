<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYear_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Academic Year'), 'schoolYear_manage.php')
        ->add(__('Add Academic Year'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/schoolYear_manage_edit.php&pupilsightSchoolYearID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    echo '<h2>';
    echo __('Add Academic Year');
    echo '</h2>';

    $form = Form::create('schoolYear', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/schoolYear_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $statuses = array(
        'Past'     => __('Past'),
        'Current'  => __('Current'),
        'Upcoming' => __('Upcoming'),
    );

    $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->required();

    $row = $form->addRow();
        $row->addLabel('status', __('Status'));
        $row->addSelect('status')->fromArray($statuses)->required()->selected('Upcoming');

    $form->toggleVisibilityByClass('statusChange')->onSelect('status')->when('Current');
    $direction = __('Past');

    // Display an alert to warn users that changing this will have an impact on their system.
    $row = $form->addRow()->setClass('statusChange');
    $row->addAlert(sprintf(__('Setting the status of this school year to Current will change the current school year %1$s to %2$s. Adjustments to the Academic Year can affect the visibility of vital data in your system. It\'s recommended to use the Rollover tool in User Admin to advance school years rather than changing them here. PROCEED WITH CAUTION!'), $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'], $direction) );

    $row = $form->addRow();
        $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
        $row->addSequenceNumber('sequenceNumber', 'pupilsightSchoolYear')->required()->maxLength(3);

    $row = $form->addRow();
        $row->addLabel('firstDay', __('Start Day'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
        $row->addDate('firstDay')->required()->readonly();

    $row = $form->addRow();
        $row->addLabel('lastDay', __('End Day'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
        $row->addDate('lastDay')->required()->readonly();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}

?>

<script>
    $("#firstDay").datepicker({
        //minDate: 0,
        onClose: function (selectedDate) {
            $("#lastDay").datepicker("option", "minDate", selectedDate);
        }
    });
</script>
