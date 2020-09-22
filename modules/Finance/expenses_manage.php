<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\Prefab\BulkActionForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        $page->breadcrumbs->add(__('Manage Expenses'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, array('success0' => __('Your request was completed successfully.'), 'success1' => __('Your request was completed successfully, but notifications could not be sent out.')));
        }

        echo '<p>';
        if ($highestAction == 'Manage Expenses_all') {
            echo __('This action allows you to manage all expenses for all budgets, regardless of your access rights to individual budgets.').'<br/>';
        } else {
            echo __('This action allows you to manage expenses for the budgets in which you have relevant access rights.').'<br/>';
        }
        echo '</p>';

        //Check if have Full, Write or Read access in any budgets
        $budgetsAccess = false;
        $budgetsActionAccess = false;
        $budgets = getBudgetsByPerson($connection2, $_SESSION[$guid]['pupilsightPersonID']);
        $budgetsAll = null;
        if ($highestAction == 'Manage Expenses_all') {
            $budgetsAll = getBudgets($connection2);
            $budgetsAccess = true;
            $budgetsActionAccess = true;
        } else {
            if (is_array($budgets) && count($budgets)>0) {
                foreach ($budgets as $budget) {
                    if ($budget[2] == 'Full' or $budget[2] == 'Write') {
                        $budgetsActionAccess = true;
                        $budgetsAccess = true;
                    }
                    if ($budget[2] == 'Read') {
                        $budgetsAccess = true;
                    }
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
                    //Ready to go!
                    $pupilsightFinanceBudgetCycleID = '';
                    if (isset($_GET['pupilsightFinanceBudgetCycleID'])) {
                        $pupilsightFinanceBudgetCycleID = $_GET['pupilsightFinanceBudgetCycleID'];
                    }
                    if ($pupilsightFinanceBudgetCycleID == '') {
                        try {
                            $data = array();
                            $sql = "SELECT * FROM pupilsightFinanceBudgetCycle WHERE status='Current'";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='error'>".$e->getMessage().'</div>';
                        }
                        if ($result->rowcount() != 1) {
                            echo "<div class='error'>";
                            echo __('The Current budget cycle cannot be determined.');
                            echo '</div>';
                        } else {
                            $row = $result->fetch();
                            $pupilsightFinanceBudgetCycleID = $row['pupilsightFinanceBudgetCycleID'];
                            $pupilsightFinanceBudgetCycleName = $row['name'];
                        }
                    }
                    if ($pupilsightFinanceBudgetCycleID != '') {
                        try {
                            $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
                            $sql = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='error'>".$e->getMessage().'</div>';
                        }
                        if ($result->rowcount() != 1) {
                            echo "<div class='error'>";
                            echo __('The specified budget cycle cannot be determined.');
                            echo '</div>';
                        } else {
                            $row = $result->fetch();
                            $pupilsightFinanceBudgetCycleName = $row['name'];
                        }

                        echo '<h2>';
                        echo $pupilsightFinanceBudgetCycleName;
                        echo '</h2>';

                        echo "<div class='linkTop'>";
                            //Print year picker
                            $previousCycle = getPreviousBudgetCycleID($pupilsightFinanceBudgetCycleID, $connection2);
                        if ($previousCycle != false) {
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenses_manage.php&pupilsightFinanceBudgetCycleID='.$previousCycle."'>".__('Previous Cycle').'</a> ';
                        } else {
                            echo __('Previous Cycle').' ';
                        }
                        echo ' | ';
                        $nextCycle = getNextBudgetCycleID($pupilsightFinanceBudgetCycleID, $connection2);
                        if ($nextCycle != false) {
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenses_manage.php&pupilsightFinanceBudgetCycleID='.$nextCycle."'>".__('Next Cycle').'</a> ';
                        } else {
                            echo __('Next Cycle').' ';
                        }
                        echo '</div>';

                        $status2 = null;
                        if (isset($_GET['status2'])) {
                            $status2 = $_GET['status2'];
                        }
                        $pupilsightFinanceBudgetID2 = null;
                        if (isset($_GET['pupilsightFinanceBudgetID2'])) {
                            $pupilsightFinanceBudgetID2 = $_GET['pupilsightFinanceBudgetID2'];
                        }

                        echo '<h3>';
                        echo __('Filters');
                        echo '</h3>';

                        $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
                        $form->setClass('noIntBorder fullWidth');

                        $form->addHiddenValue('q', '/modules/Finance/expenses_manage.php');
                        $form->addHiddenValue('pupilsightFinanceBudgetCycleID', $pupilsightFinanceBudgetCycleID);

                        $statuses = array(
                            '' => __('All'),
                            'Requested' => __('Requested'),
                            'Requested - Approval Required' => __('Requested - Approval Required'),
                            'Approved' => __('Approved'),
                            'Rejected' => __('Rejected'),
                            'Cancelled' => __('Cancelled'),
                            'Ordered' => __('Ordered'),
                            'Paid' => __('Paid'),
                        );
                        $row = $form->addRow();
                            $row->addLabel('status2', __('Status'));
                            $row->addSelect('status2')
                                ->fromArray($statuses)
                                ->selected($status2);

                        $budgetsList = array_reduce($budgetsAll != null? $budgetsAll : $budgets, function($group, $item) {
                            $group[$item[0]] = $item[1];
                            return $group;
                        }, array());
                        $row = $form->addRow();
                            $row->addLabel('pupilsightFinanceBudgetID2', __('Budget'));
                            $row->addSelect('pupilsightFinanceBudgetID2')
                                ->fromArray(array('' => __('All')))
                                ->fromArray($budgetsList)
                                ->selected($pupilsightFinanceBudgetID2);

                        $row = $form->addRow();
                            $row->addSearchSubmit($pupilsight->session, __('Clear Filters'), array('pupilsightFinanceBudgetCycleID'));

                        echo $form->getOutput();

                        try {
                            //Set Up filter wheres
                            $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
                            $whereBudget = '';
                            if ($pupilsightFinanceBudgetID2 != '') {
                                $data['pupilsightFinanceBudgetID'] = $pupilsightFinanceBudgetID2;
                                $whereBudget .= ' AND pupilsightFinanceBudget.pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                            }
                            $approvalRequiredFilter = false;
                            $whereStatus = '';
                            if ($status2 != '') {
                                if ($status2 == 'Requested - Approval Required') {
                                    $data['status'] = 'Requested';
                                    $approvalRequiredFilter = true;
                                } else {
                                    $data['status'] = $status2;
                                }
                                $whereStatus .= ' AND pupilsightFinanceExpense.status=:status';
                            }
                            //GET THE DATA ACCORDING TO FILTERS
                            if ($highestAction == 'Manage Expenses_all') { //Access to everything
                                $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, 'Full' AS access
                                    FROM pupilsightFinanceExpense
                                    JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
                                    JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
                                    WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID $whereBudget $whereStatus
                                    ORDER BY FIND_IN_SET(pupilsightFinanceExpense.status, 'Pending,Issued,Paid,Refunded,Cancelled'), timestampCreator DESC";
                            } else { //Access only to own budgets
                                $data['pupilsightPersonID'] = $_SESSION[$guid]['pupilsightPersonID'];
                                $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, access
                                    FROM pupilsightFinanceExpense
                                    JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
                                    JOIN pupilsightFinanceBudgetPerson ON (pupilsightFinanceBudgetPerson.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
                                    JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
                                    WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceBudgetPerson.pupilsightPersonID=:pupilsightPersonID $whereBudget $whereStatus
                                    ORDER BY FIND_IN_SET(pupilsightFinanceExpense.status, 'Pending,Issued,Paid,Refunded,Cancelled'), timestampCreator DESC";
                            }
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='error'>".$e->getMessage().'</div>';
                        }

                        echo '<h3>';
                        echo __('View');
                        echo '</h3>';

                        $allowExpenseAdd = getSettingByScope($connection2, 'Finance', 'allowExpenseAdd');
                        if ($highestAction == 'Manage Expenses_all' and $allowExpenseAdd == 'Y') { //Access to everything
                            echo "<div class='linkTop' style='text-align: right'>";
                            echo "<a style='margin-right: 3px' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/expenses_manage_add.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=$status2&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a><br/>";
                            echo '</div>';
                        }
                        
                        $linkParams = array(
                            'status2'                    => $status2,
                            'pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID,
                            'pupilsightFinanceBudgetID2'     => $pupilsightFinanceBudgetID2,
                        );

                        $form = BulkActionForm::create('bulkAction', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/expenses_manage_processBulk.php?'.http_build_query($linkParams));

                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                        if ($budgetsActionAccess) {
                            $bulkActions = array('export' => __('Export'));
                            $row = $form->addBulkActionRow($bulkActions);
                                $row->addSubmit(__('Go'));
                        }

                        $table = $form->addRow()->addTable()->setClass('colorOddEven fullWidth');

                        $header = $table->addHeaderRow();
                            $header->addContent(__('Title'))->append('<br/><small><i>'.__('Budget').'</i></small>');
                            $header->addContent(__('Staff'));
                            $header->addContent(__('Status'))->append('<br/><small><i>'.__('Reimbursement').'</i></small>');
                            $header->addContent(__('Cost'))->append('<br/><small><i>('.$_SESSION[$guid]['currency'].')</i></small>');
                            $header->addContent(__('Date'));

                            if ($budgetsActionAccess) {
                                $header->addContent(__('Actions'));
                                $header->addCheckAll();
                            }

                        if ($result->rowCount() == 0) {
                            $table->addRow()->addTableCell(__('There are no records to display.'))->colSpan(7);
                        }
                        
                        while ($expense = $result->fetch()) {
                            $approvalRequired = approvalRequired($guid, $_SESSION[$guid]['pupilsightPersonID'], $expense['pupilsightFinanceExpenseID'], $pupilsightFinanceBudgetCycleID, $connection2, false);

                            if (!empty($approvalRequiredFilter) && $approvalRequired == false) {
                                continue;
                            }

                            $rowClass = ($expense['status'] == 'Approved')? 'current' : ( ($expense['status'] == 'Rejected' || $expense['status'] == 'Cancelled')? 'error' : '');

                            $row = $table->addRow()->addClass($rowClass);
                                $row->addContent($expense['title'])
                                    ->wrap('<b>', '</b>')
                                    ->append('<br/><span class="small emphasis">'.$expense['budget'].'</span>');
                                $row->addContent(formatName('', $expense['preferredName'], $expense['surname'], 'Staff', false, true));
                                $row->addContent($expense['status'])
                                    ->append('<br/><span class="small emphasis">'.$expense['paymentReimbursementStatus'].'</span>');
                                $row->addContent(number_format($expense['cost'], 2, '.', ','));
                                $row->addContent(dateConvertBack($guid, substr($expense['timestampCreator'], 0, 10)));

                            if ($budgetsActionAccess) {
                                $col = $row->addColumn()->addClass('inline');
                                    $col->addWebLink('<img title="'.__('View').'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/plus.png" />')
                                        ->setURL($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenses_manage_view.php')
                                        ->addParam('pupilsightFinanceExpenseID', $expense['pupilsightFinanceExpenseID'])
                                        ->addParams($linkParams);
                                    $col->addWebLink('<img title="'.__('Print').'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/print.png"  style="margin-left:4px;"/>')
                                        ->setURL($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenses_manage_print.php')
                                        ->addParam('pupilsightFinanceExpenseID', $expense['pupilsightFinanceExpenseID'])
                                        ->addParams($linkParams);

                                if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage_add.php', 'Manage Expenses_all')) {
                                    if ($expense['status'] == 'Requested' or $expense['status'] == 'Approved' or $expense['status'] == 'Ordered' or ($expense['status'] == 'Paid' && $expense['paymentReimbursementStatus'] == 'Requested')) {
                                        $col->addWebLink('<img title="'.__('Edit').'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/config.png"  style="margin-left:4px;"/>')
                                            ->setURL($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenses_manage_edit.php')
                                            ->addParam('pupilsightFinanceExpenseID', $expense['pupilsightFinanceExpenseID'])
                                            ->addParams($linkParams);
                                    }
                                }

                                if ($expense['status'] == 'Requested') {
                                    if ($approvalRequired == true) {
                                        $col->addWebLink('<img title="'.__('Approve/Reject').'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/iconTick.png"  style="margin-left:4px;"/>')
                                            ->setURL($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenses_manage_approve.php')
                                            ->addParam('pupilsightFinanceExpenseID', $expense['pupilsightFinanceExpenseID'])
                                            ->addParams($linkParams);
                                    }
                                }
                            
                                $row->addCheckbox('pupilsightFinanceExpenseIDs[]')->setValue($expense['pupilsightFinanceExpenseID'])->setClass('textCenter');
                            }
                        }

                        echo $form->getOutput();
                    }
                }
            }
        }
    }
}
?>
