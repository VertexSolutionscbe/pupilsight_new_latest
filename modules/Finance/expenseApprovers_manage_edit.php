<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseApprovers_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Expense Approvers'),'expenseApprovers_manage.php')
        ->add(__('Edit Expense Approver'));    

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return']);
    }

    //Check if school year specified
    $pupilsightFinanceExpenseApproverID = $_GET['pupilsightFinanceExpenseApproverID'];
    if ($pupilsightFinanceExpenseApproverID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFinanceExpenseApproverID' => $pupilsightFinanceExpenseApproverID);
            $sql = 'SELECT * FROM pupilsightFinanceExpenseApprover WHERE pupilsightFinanceExpenseApproverID=:pupilsightFinanceExpenseApproverID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/expenseApprovers_manage_editProcess.php?pupilsightFinanceExpenseApproverID=$pupilsightFinanceExpenseApproverID");
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('pupilsightPersonID', __('Staff'));
                $row->addSelectStaff('pupilsightPersonID')->required()->placeholder();

            $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
            if ($expenseApprovalType == 'Chain Of All') {
                $row = $form->addRow();
                    $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique.'));
                    $row->addSequenceNumber('sequenceNumber', 'pupilsightFinanceExpenseApprover', $values['sequenceNumber'])->required()->maxLength(3);
            }

            $form->loadAllValuesFrom($values);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
?>
