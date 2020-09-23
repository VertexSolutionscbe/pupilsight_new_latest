<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$enableDescriptors = getSettingByScope($connection2, 'Behaviour', 'enableDescriptors');
$enableLevels = getSettingByScope($connection2, 'Behaviour', 'enableLevels');

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $page->breadcrumbs
        ->add(__('Manage Behaviour Records'), 'behaviour_manage.php')
        ->add(__('Add Multiple'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo "<div class='linkTop'>";
    $policyLink = getSettingByScope($connection2, 'Behaviour', 'policyLink');
    if ($policyLink != '') {
        echo "<a target='_blank' href='$policyLink'>".__('View Behaviour Policy').'</a>';
    }
    if ($_GET['pupilsightPersonID'] != '' or $_GET['pupilsightRollGroupID'] != '' or $_GET['pupilsightYearGroupID'] != '' or $_GET['type'] != '') {
        if ($policyLink != '') {
            echo ' | ';
        }
        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Behaviour/behaviour_manage.php&pupilsightPersonID='.$_GET['pupilsightPersonID'].'&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'].'&type='.$_GET['type']."'>".__('Back to Search Results').'</a>';
    }
    echo '</div>';


    $form = Form::create('addform', $_SESSION[$guid]['absoluteURL'].'/modules/Behaviour/behaviour_manage_addMultiProcess.php?pupilsightPersonID='.$_GET['pupilsightPersonID'].'&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'].'&type='.$_GET['type']);
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', "/modules/Behaviour/behaviour_manage_addMulti.php");
    $form->addRow()->addHeading(__('Step 1'));

    //Student
    $row = $form->addRow();
        $row->addLabel('pupilsightPersonIDMulti', __('Students'));
        $row->addSelectStudent('pupilsightPersonIDMulti', $_SESSION[$guid]['pupilsightSchoolYearID'], array('byName' => true, 'byRoll' => true))->selectMultiple()->required();

    //Date
    $row = $form->addRow();
        $row->addLabel('date', __('Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
        $row->addDate('date')->setValue(date($_SESSION[$guid]['i18n']['dateFormatPHP']))->required();

    //Type
    $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')->fromArray(array('Positive' => __('Positive'), 'Negative' => __('Negative')))->required();

    //Descriptor
    if ($enableDescriptors == 'Y') {
        $negativeDescriptors = getSettingByScope($connection2, 'Behaviour', 'negativeDescriptors');
        $negativeDescriptors = (!empty($negativeDescriptors))? explode(',', $negativeDescriptors) : array();
        $positiveDescriptors = getSettingByScope($connection2, 'Behaviour', 'positiveDescriptors');
        $positiveDescriptors = (!empty($positiveDescriptors))? explode(',', $positiveDescriptors) : array();

        $chainedToNegative = array_combine($negativeDescriptors, array_fill(0, count($negativeDescriptors), 'Negative'));
        $chainedToPositive = array_combine($positiveDescriptors, array_fill(0, count($positiveDescriptors), 'Positive'));
        $chainedTo = array_merge($chainedToNegative, $chainedToPositive);

        $row = $form->addRow();
            $row->addLabel('descriptor', __('Descriptor'));
            $row->addSelect('descriptor')
                ->fromArray($positiveDescriptors)
                ->fromArray($negativeDescriptors)
                ->chainedTo('type', $chainedTo)
                ->required()
                ->placeholder();
    }

    //Level
    if ($enableLevels == 'Y') {
        $optionsLevels = getSettingByScope($connection2, 'Behaviour', 'levels');
        if ($optionsLevels != '') {
            $optionsLevels = explode(',', $optionsLevels);
        }
        $row = $form->addRow();
            $row->addLabel('level', __('Level'));
            $row->addSelect('level')->fromArray($optionsLevels)->placeholder();
    }

    //Incident
    $row = $form->addRow();
        $column = $row->addColumn();
        $column->addLabel('comment', __('Incident'));
        $column->addTextArea('comment')->setRows(5)->setClass('fullWidth');

    //Follow Up
    $row = $form->addRow();
        $column = $row->addColumn();
        $column->addLabel('followup', __('Follow Up'));
        $column->addTextArea('followup')->setRows(5)->setClass('fullWidth');

    //Copy to Notes
    $row = $form->addRow();
        $row->addLabel('copyToNotes', __('Copy To Notes'));
        $row->addCheckbox('copyToNotes');

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
?>
