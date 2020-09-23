<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseRequest_manage_view.php') == false) {
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
        ->add(__('View Expense Request'));       

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null);
    }

    //Check if params are specified
    $pupilsightFinanceExpenseID = isset($_GET['pupilsightFinanceExpenseID'])? $_GET['pupilsightFinanceExpenseID'] : '';
    $status = '';
    $status2 = isset($_GET['status2'])? $_GET['status2'] : '';
    $pupilsightFinanceBudgetID2 = isset($_GET['pupilsightFinanceBudgetID2'])? $_GET['pupilsightFinanceBudgetID2'] : '';
    if ($pupilsightFinanceExpenseID == '' or $pupilsightFinanceBudgetCycleID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        //Check if have Full or Write in any budgets
        $budgets = getBudgetsByPerson($connection2, $_SESSION[$guid]['pupilsightPersonID']);
        $budgetsAccess = false;
        if (is_array($budgets) && count($budgets)>0) {
            foreach ($budgets as $budget) {
                if ($budget[2] == 'Full' or $budget[2] == 'Write') {
                    $budgetsAccess = true;
                }
            }
        }
        if ($budgetsAccess == false) {
            echo "<div class='error'>";
            echo __('You do not have Full or Write access to any budgets.');
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
                        $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget FROM pupilsightFinanceExpense JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID) WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID AND pupilsightFinanceExpense.pupilsightPersonIDCreator=:pupilsightPersonIDCreator';
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

                        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/expenseRequest_manage_viewProcess.php');

                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                        $form->addHiddenValue('status2', $status2);
                        $form->addHiddenValue('pupilsightFinanceBudgetID2', $pupilsightFinanceBudgetID2);
                        $form->addHiddenValue('pupilsightFinanceExpenseID', $pupilsightFinanceExpenseID);
                        $form->addHiddenValue('pupilsightFinanceBudgetCycleID', $pupilsightFinanceBudgetCycleID);
                        $form->addHiddenValue('status', $status);

                        $form->addHiddenValue('pupilsightFinanceBudgetCycleID', $pupilsightFinanceBudgetCycleID);

                        $form->addRow()->addHeading(__('Basic Information'));

                        $cycleName = getBudgetCycleName($pupilsightFinanceBudgetCycleID, $connection2);
                        $row = $form->addRow();
                            $row->addLabel('name', __('Budget Cycle'));
                            $row->addTextField('name')->setValue($cycleName)->maxLength(20)->required()->readonly();

                        $form->addHiddenValue('pupilsightFinanceBudgetID', $values['pupilsightFinanceBudgetID']);
                        $row = $form->addRow();
                            $row->addLabel('budget', __('Budget'));
                            $row->addTextField('budget')->setValue($cycleName)->maxLength(20)->required()->readonly()->setValue($values['budget']);

                        $row = $form->addRow();
                            $row->addLabel('title', __('Title'));
                            $row->addTextField('title')->maxLength(60)->required()->readonly()->setValue($values['title']);

                        $row = $form->addRow();
                            $row->addLabel('status', __('Status'));
                            $row->addTextField('status')->maxLength(60)->required()->readonly()->setValue($values['status']);

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

                        $row = $form->addRow();
                            $row->addFooter();
                            $row->addSubmit();

                        echo $form->getOutput();
                    }
                }
            }
        }
    }
}
?>
