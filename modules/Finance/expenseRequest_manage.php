<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseRequest_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('My Expense Requests'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<p>';
    echo __('This action allows you to create and manage expense requests, which will be submitted for approval to the relevant individuals. You will be notified when a request has been approved.').'<br/>';
    echo '</p>';

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
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenseRequest_manage.php&pupilsightFinanceBudgetCycleID='.$previousCycle."'>".__('Previous Cycle').'</a> ';
                        } else {
                            echo __('Previous Cycle').' ';
                        }
                        echo ' | ';
                        $nextCycle = getNextBudgetCycleID($pupilsightFinanceBudgetCycleID, $connection2);
                        if ($nextCycle != false) {
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenseRequest_manage.php&pupilsightFinanceBudgetCycleID='.$nextCycle."'>".__('Next Cycle').'</a> ';
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

                    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

                    $form->setClass('noIntBorder fullWidth');

                    $form->addHiddenValue('pupilsightFinanceBudgetCycleID', $pupilsightFinanceBudgetCycleID);
                    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/expenseRequest_manage.php");

                    $statuses = array(
                        '' => __('All'),
                        'Requested' => __('Requested'),
                        'Approved' => __('Approved'),
                        'Rejected' => __('Rejected'),
                        'Cancelled' => __('Cancelled'),
                        'Ordered' => __('Ordered'),
                        'Paid' => __('Paid'),
                    );
                    $row = $form->addRow();
                        $row->addLabel('status2', __('Status'));
                        $row->addSelect('status2')->fromArray($statuses)->selected($status2);

                    $budgetsProcessed = array('' => __('All')) ;
                    foreach ($budgets as $budget) {
                        $budgetsProcessed[$budget[0]] = $budget[1];
                    }
                    $row = $form->addRow();
                        $row->addLabel('pupilsightFinanceBudgetID2', __('Budget'));
                        $row->addSelect('pupilsightFinanceBudgetID2')->fromArray($budgetsProcessed)->selected($pupilsightFinanceBudgetID2);

                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSearchSubmit($pupilsight->session);

                    echo $form->getOutput();

                    try {
                        //Add in filter wheres
                        $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                        $whereBudget = '';
                        if ($pupilsightFinanceBudgetID2 != '') {
                            $data['pupilsightFinanceBudgetID'] = $pupilsightFinanceBudgetID2;
                            $whereBudget .= ' AND pupilsightFinanceBudget.pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                        }
                        $whereStatus = '';
                        if ($status2 != '') {
                            $data['status'] = $status2;
                            $whereStatus .= ' AND status=:status';
                        }
                        //SQL for billing schedule AND pending
                        $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget FROM pupilsightFinanceExpense JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID) WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpense.pupilsightPersonIDCreator=:pupilsightPersonIDCreator $whereBudget $whereStatus";
                        $sql .= " ORDER BY FIND_IN_SET(status, 'Pending,Issued,Paid,Refunded,Cancelled'), timestampCreator DESC";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($result->rowCount() < 1) {
                        echo '<h3>';
                        echo __('View');
                        echo '</h3>';

                        echo "<div class='linkTop' style='text-align: right'>";
                        echo "<a style='margin-right: 3px' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/expenseRequest_manage_add.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=$status2&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a><br/>";
                        echo '</div>';

                        echo "<div class='error'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        echo '<h3>';
                        echo __('View');
                        echo "<span style='font-weight: normal; font-style: italic; font-size: 55%'> ".sprintf(__('%1$s expense requests in current view'), $result->rowCount()).'</span>';
                        echo '</h3>';

                        echo "<div class='linkTop'>";
                        echo "<a style='margin-right: 3px' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/expenseRequest_manage_add.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=$status2&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a><br/>";
                        echo '</div>';

                        echo "<table class='table' cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo "<th style='width: 110px'>";
                        echo __('Title').'<br/>';
                        echo '</th>';
                        echo "<th style='width: 110px'>";
                        echo __('Budget');
                        echo '</th>';
                        echo "<th style='width: 100px'>";
                        echo __('Status')."<br/><span style='font-style: italic; font-size: 75%'>".__('Reimbursement').'</span><br/>';
                        echo '</th>';
                        echo "<th style='width: 90px'>";
                        echo __('Cost')."<br/><span style='font-style: italic; font-size: 75%'>(".$_SESSION[$guid]['currency'].')</span><br/>';
                        echo '</th>';
                        echo "<th style='width: 120px'>";
                        echo __('Date');
                        echo '</th>';
                        echo "<th style='width: 140px'>";
                        echo __('Actions');
                        echo '</th>';
                        echo '</tr>';

                        $count = 0;
                        $rowNum = 'odd';
                        while ($row = $result->fetch()) {
                            if ($count % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }
                            ++$count;

                                //Color row by status
                                if ($row['status'] == 'Approved') {
                                    $rowNum = 'current';
                                }
                            if ($row['status'] == 'Rejected' or $row['status'] == 'Cancelled') {
                                $rowNum = 'error';
                            }

                            echo "<tr class=$rowNum>";
                            echo '<td>';
                            echo '<b>'.$row['title'].'</b><br/>';
                            echo '</td>';
                            echo '<td>';
                            echo $row['budget'];
                            echo '</td>';
                            echo '<td>';
                            echo $row['status'].'<br/>';
                            if ($row['paymentReimbursementStatus'] != '') {
                                echo "<span style='font-style: italic; font-size: 75%'>".$row['paymentReimbursementStatus'].'</span><br/>';
                            }
                            echo '</td>';
                            echo '<td>';
                            echo number_format($row['cost'], 2, '.', ',');
                            echo '</td>';
                            echo '<td>';
                            echo dateConvertBack($guid, substr($row['timestampCreator'], 0, 10));
                            echo '</td>';
                            echo '<td>';
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenseRequest_manage_view.php&pupilsightFinanceExpenseID='.$row['pupilsightFinanceExpenseID']."&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=$status2&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
                            if ($row['status'] == 'Approved' and $row['purchaseBy'] == 'Self') {
                                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenseRequest_manage_reimburse.php&pupilsightFinanceExpenseID='.$row['pupilsightFinanceExpenseID']."&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=$status2&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2'><img title='".__('Request Reimbursement')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/gift.png'/></a> ";
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '<input type="hidden" name="address" value="'.$_SESSION[$guid]['address'].'">';

                        echo '</table>';
                    }
                }
            }
        }
    }
}
?>
