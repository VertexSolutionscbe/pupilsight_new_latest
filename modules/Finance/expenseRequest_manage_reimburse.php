<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseRequest_manage_reimburse.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightFinanceBudgetCycleID = $_GET['pupilsightFinanceBudgetCycleID'];
    
    $urlParams = compact('pupilsightFinanceBudgetCycleID');        
        
    $page->breadcrumbs
        ->add(__('My Expense Requests'), 'expenseRequest_manage.php',  $urlParams)
        ->add(__('Request Reimbursement'));    

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if params are specified
    $pupilsightFinanceExpenseID = $_GET['pupilsightFinanceExpenseID'];
    $status2 = $_GET['status2'];
    $pupilsightFinanceBudgetID2 = $_GET['pupilsightFinanceBudgetID2'];
    if ($pupilsightFinanceExpenseID == '' or $pupilsightFinanceBudgetCycleID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        //Get and check settings
        $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
        $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');
        $expenseRequestTemplate = getSettingByScope($connection2, 'Finance', 'expenseRequestTemplate');
        if ($expenseApprovalType == '' or $budgetLevelExpenseApproval == '') {
            echo "<div class='error'>";
            echo __('An error has occurred with your expense and budget settings.');
            echo '</div>';
        } else {
            //Check if there are approvers
            try {
                $data = array();
                $sql = "SELECT * FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }

            if ($result->rowCount() < 1) {
                echo "<div class='error'>";
                echo __('An error has occurred with your expense and budget settings.');
                echo '</div>';
            } else {
                //Ready to go! Just check record exists and we have access, and load it ready to use...
                try {
                    //Set Up filter wheres
                    $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                    $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, 'Full' AS access
							FROM pupilsightFinanceExpense
							JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
							JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
							WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID AND pupilsightFinanceExpense.status='Approved'";
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

                    if ($status2 != '' or $pupilsightFinanceBudgetID2 != '') {
                        echo "<div class='linkTop'>";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/expenseRequest_manage.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=$status2&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2'>".__('Back to Search Results').'</a>';
                        echo '</div>';
                    }


                    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/expenseRequest_manage_reimburseProcess.php');

                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                    $form->addHiddenValue('status2', $status2);
                    $form->addHiddenValue('pupilsightFinanceBudgetID2', $pupilsightFinanceBudgetID2);
                    $form->addHiddenValue('pupilsightFinanceExpenseID', $pupilsightFinanceExpenseID);
                    $form->addHiddenValue('pupilsightFinanceBudgetCycleID', $pupilsightFinanceBudgetCycleID);

                    $form->addHiddenValue('pupilsightFinanceBudgetCycleID', $pupilsightFinanceBudgetCycleID);

                    $form->addRow()->addHeading(__('Basic Information'));

                    $cycleName = getBudgetCycleName($pupilsightFinanceBudgetCycleID, $connection2);
                    $row = $form->addRow();
                        $row->addLabel('nameBudget', __('Budget Cycle'));
                        $row->addTextField('nameBudget')->setValue($cycleName)->maxLength(20)->required()->readonly();

                    $form->addHiddenValue('pupilsightFinanceBudgetID', $values['pupilsightFinanceBudgetID']);
                    $row = $form->addRow();
                        $row->addLabel('budget', __('Budget'));
                        $row->addTextField('budget')->setValue($values['budget'])->maxLength(20)->required()->readonly();

                    $row = $form->addRow();
                        $row->addLabel('title', __('Title'));
                        $row->addTextField('title')->maxLength(60)->required()->readonly()->setValue($values['title']);

                    $row = $form->addRow();
                        $row->addLabel('status', __('Status'));
                        if ($values['status'] == 'Requested' or $values['status'] == 'Approved' or $values['status'] == 'Ordered') {
                            $statuses = array();
                            if ($values['status'] == 'Approved') {
                                $statuses['Paid'] = __('Paid');
                            }
                            $row->addSelect('status')->fromArray($statuses)->selected('Paid')->required();
                        } else {
                            $row->addTextField('status')->maxLength(60)->required()->readonly()->setValue($values['status']);
                        }

                    $row = $form->addRow();
                        $column = $row->addColumn();
                        $column->addLabel('body', __('Description'));
                        $column->addContent($values['body'])->setClass('fullWidth');

                    $row = $form->addRow();
                        $row->addLabel('cost', __('Total Cost'));
                        $row->addCurrency('cost')->required()->maxLength(15)->readonly()->setValue($values['cost']);

                    $row = $form->addRow();
                        $row->addLabel('countAgainstBudget', __('Count Against Budget'));
                        $row->addTextField('countAgainstBudget')->maxLength(3)->required()->readonly()->setValue(ynExpander($guid, $values['countAgainstBudget']));

                    $row = $form->addRow();
                        $row->addLabel('purchaseBy', __('Purchase By'));
                        $row->addTextField('purchaseBy')->required()->readonly()->setValue($values['purchaseBy']);

                    $row = $form->addRow();
                        $column = $row->addColumn();
                        $column->addLabel('purchaseDetails', __('Purchase Details'));
                        $column->addContent($values['purchaseDetails'])->setClass('fullWidth');

                    $form->addRow()->addHeading(__('Log'));

                    $row = $form->addRow();
                        $column = $row->addColumn();
                        $column->addContent(getExpenseLog($guid, $pupilsightFinanceExpenseID, $connection2));

                    $row = $form->addRow();
                        $column = $row->addColumn();
                        $column->addLabel('comment', __('Comment'));
                        $column->addTextArea('comment')->setRows(8)->setClass('fullWidth');

                    $form->toggleVisibilityByClass('payment')->onSelect('status')->when('Paid');

                    $form->addRow()->addHeading(__('Payment Information'))->addClass('payment');

                    $row = $form->addRow()->addClass('payment');
                        $row->addLabel('paymentDate', __('Date Paid'))->description(__('Date of payment, not entry to system.'));
                        $row->addDate('paymentDate')->required();

                    $row = $form->addRow()->addClass('payment');
                    	$row->addLabel('paymentAmount', __('Amount paid'))->description(__('Final amount paid.'));
            			$row->addCurrency('paymentAmount')->required()->maxLength(15);

                    $form->addHiddenValue('pupilsightPersonIDPayment', $_SESSION[$guid]['pupilsightPersonID']);
                    $row = $form->addRow()->addClass('payment');
                        $row->addLabel('name', __('Payee'))->description(__('Staff who made, or arranged, the payment.'));
                        $row->addTextField('name')->required()->readonly()->setValue(formatName('', ($_SESSION[$guid]['preferredName']), htmlPrep($_SESSION[$guid]['surname']), 'Staff', true, true));

                    $methods = array(
                        'Bank Transfer' => __('Bank Transfer'),
                        'Cash' => __('Cash'),
                        'Cheque' => __('Cheque'),
                        'Credit Card' => __('Credit Card'),
                        'Other' => __('Other')
                    );
                    $row = $form->addRow()->addClass('payment');
                        $row->addLabel('paymentMethod', __('Payment Method'));
                        $row->addSelect('paymentMethod')->fromArray($methods)->placeholder()->required();

                    $row = $form->addRow()->addClass('payment');;
                        $row->addLabel('file', __('Payment Receipt'))->description(__('Digital copy of the receipt for this payment.'));
                        $row->addFileUpload('file')
                            ->accepts('.jpg,.jpeg,.gif,.png,.pdf')
                            ->required();

                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit();

                    echo $form->getOutput();

                }
            }
        }
    }
}
?>
