<?php
/*
Pupilsight, Flexible & Open School System
*/

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseApprovers_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Expense Approvers'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
    }

    //Set pagination variable
    $page = 1;
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    if ((!is_numeric($page)) or $page < 1) {
        $page = 1;
    }

    $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
    $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');
    try {
        $data = array();
        if ($expenseApprovalType == 'Chain Of All') {
            $sql = "SELECT pupilsightFinanceExpenseApprover.*, surname, preferredName FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' ORDER BY sequenceNumber, surname, preferredName";
        } else {
            $sql = "SELECT pupilsightFinanceExpenseApprover.*, surname, preferredName FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' ORDER BY surname, preferredName";
        }
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='error'>".$e->getMessage().'</div>';
    }

    echo '<p>';
    if ($expenseApprovalType == 'One Of') {
        if ($budgetLevelExpenseApproval == 'Y') {
            echo __("Expense approval has been set as 'One Of', which means that only one of the people listed below (as well as someone with Full budget access) needs to approve an expense before it can go ahead.");
        } else {
            echo __("Expense approval has been set as 'One Of', which means that only one of the people listed below needs to approve an expense before it can go ahead.");
        }
    } elseif ($expenseApprovalType == 'Two Of') {
        if ($budgetLevelExpenseApproval == 'Y') {
            echo __("Expense approval has been set as 'Two Of', which means that only two of the people listed below (as well as someone with Full budget access) need to approve an expense before it can go ahead.");
        } else {
            echo __("Expense approval has been set as 'Two Of', which means that only two of the people listed below need to approve an expense before it can go ahead.");
        }
    } elseif ($expenseApprovalType == 'Chain Of All') {
        if ($budgetLevelExpenseApproval == 'Y') {
            echo __("Expense approval has been set as 'Chain Of All', which means that all of the people listed below (as well as someone with Full budget access) need to approve an expense, in order from lowest to highest, before it can go ahead.");
        } else {
            echo __("Expense approval has been set as 'Chain Of All', which means that all of the people listed below need to approve an expense, in order from lowest to highest, before it can go ahead.");
        }
    } else {
        echo __('Expense Approval policies have not been set up: this should be done under Admin > School Admin > Manage Finance Settings.');
    }
    echo '</p>';

    echo "<div class='linkTop'>";
    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/expenseApprovers_manage_add.php'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
    echo '</div>';

    if ($result->rowCount() < 1) {
        echo "<div class='error'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        echo "<table class='table' cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Name');
        echo '</th>';
        if ($expenseApprovalType == 'Chain Of All') {
            echo '<th>';
            echo __('Sequence Number');
            echo '</th>';
        }
        echo '<th>';
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

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo formatName('', $row['preferredName'], $row['surname'], 'Staff', true, true);
            echo '</td>';
            if ($expenseApprovalType == 'Chain Of All') {
                echo '<td>';
                if ($row['sequenceNumber'] != '') {
                    echo __($row['sequenceNumber']);
                }
                echo '</td>';
            }
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenseApprovers_manage_edit.php&pupilsightFinanceExpenseApproverID='.$row['pupilsightFinanceExpenseApproverID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/expenseApprovers_manage_delete.php&pupilsightFinanceExpenseApproverID='.$row['pupilsightFinanceExpenseApproverID']."&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
            echo '</td>';
            echo '</tr>';

            ++$count;
        }
        echo '</table>';
    }
}
