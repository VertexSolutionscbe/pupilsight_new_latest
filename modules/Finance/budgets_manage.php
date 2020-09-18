<?php
/*
Pupilsight, Flexible & Open School System
*/

if (isActionAccessible($guid, $connection2, '/modules/Finance/budgets_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Budgets'));

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

    try {
        $data = array();
        $sql = 'SELECT * FROM pupilsightFinanceBudget ORDER BY name';
        $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='error'>".$e->getMessage().'</div>';
    }

    echo '<p>';
    echo __('Budgets are used within purchase requisitions and expense records in order to segregate records into different areas.');
    echo '</p>';

    echo "<div class='linkTop'>";
    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/budgets_manage_add.php'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
    echo '</div>';

    if ($result->rowCount() < 1) {
        echo "<div class='error'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top');
        }

        echo "<table class='table' cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Name');
        echo '</th>';
        echo '<th>';
        echo __('Short Name');
        echo '</th>';
        echo '<th>';
        echo __('Category');
        echo '</th>';
        echo '<th>';
        echo __('Active');
        echo '</th>';
        echo '<th>';
        echo __('Actions');
        echo '</th>';
        echo '</tr>';

        $count = 0;
        $rowNum = 'odd';
        try {
            $resultPage = $connection2->prepare($sqlPage);
            $resultPage->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        while ($row = $resultPage->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }

            if ($row['active'] != 'Y') {
                $rowNum = 'error';
            }

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo '<b>'.$row['name'].'</b><br/>';
            echo '</td>';
            echo '<td>';
            echo $row['nameShort'];
            echo '</td>';
            echo '<td>';
            echo $row['category'];
            echo '</td>';
            echo '<td>';
            echo ynExpander($guid, $row['active']);
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/budgets_manage_edit.php&pupilsightFinanceBudgetID='.$row['pupilsightFinanceBudgetID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/budgets_manage_delete.php&pupilsightFinanceBudgetID='.$row['pupilsightFinanceBudgetID']."&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
            echo '</td>';
            echo '</tr>';

            ++$count;
        }
        echo '</table>';

        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom');
        }
    }
}
