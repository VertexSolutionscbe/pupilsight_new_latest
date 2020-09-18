<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseApprovers_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Expense Approvers'),'expenseApprovers_manage.php')
        ->add(__('Add Expense Approver'));
    
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/expenseApprovers_manage_edit.php&pupilsightFinanceExpenseApproverID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink);
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/expenseApprovers_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Staff'));
        $row->addSelectStaff('pupilsightPersonID')->required()->placeholder();

    $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
    if ($expenseApprovalType == 'Chain Of All') {
        $row = $form->addRow();
            $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique.'));
            $row->addSequenceNumber('sequenceNumber', 'pupilsightFinanceExpenseApprover')->required()->maxLength(3);
    }

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
?>
