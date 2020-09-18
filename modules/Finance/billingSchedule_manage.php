<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Finance/billingSchedule_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Billing Schedule'));

    echo '<p>';
    echo __('The billing schedule allows you to layout your overall timing for issueing invoices, making it easier to specify due dates in bulk. Invoices can be issued outside of the billing schedule, should ad hoc invoices be required.');
    echo '</p>';

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    if ($pupilsightSchoolYearID != $_SESSION[$guid]['pupilsightSchoolYearID']) {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        if ($result->rowcount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    if ($pupilsightSchoolYearID != '') {
        echo '<h2>';
        echo $pupilsightSchoolYearName;
        echo '</h2>';

        echo "<div class='linkTop'>";
            //Print year picker
            if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/billingSchedule_manage.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
        echo ' | ';
        if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/billingSchedule_manage.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
        } else {
            echo __('Next Year').' ';
        }
        echo '</div>';

        echo '<h3>';
        echo __('Search');
        echo '</h3>'; 

        $form = Form::create("searchBox", $_SESSION[$guid]['absoluteURL'] . "/index.php", "get", "noIntBorder fullWidth standardForm");

        $form->addHiddenValue("q", "/modules/Finance/billingSchedule_manage.php");

        $row = $form->addRow();
            $row->addLabel("search", __("Search For"))->description(__("Billing schedule name."));
            $row->addTextField("search")->maxLength(20)->setValue(isset($_GET['search']) ? $_GET['search'] : "");

        $row = $form->addRow();
            $row->addSearchSubmit($pupilsight->session, __("Clear Search"));

        echo $form->getOutput();


        echo '<h3>';
        echo __('View');
        echo '</h3>';
        //Set pagination variable
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        if ((!is_numeric($page)) or $page < 1) {
            $page = 1;
        }

        $search = null;
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM pupilsightFinanceBillingSchedule WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY invoiceIssueDate, name';
            if ($search != '') {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'search' => "%$search%");
                $sql = 'SELECT * FROM pupilsightFinanceBillingSchedule WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND name LIKE :search ORDER BY invoiceIssueDate, name';
            }
            $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/billingSchedule_manage_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
        echo '</div>';

        if ($result->rowCount() < 1) {
            echo "<div class='error'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top', "pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");
            }

            echo "<table class='table' cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo __('Name');
            echo '</th>';
            echo '<th>';
            echo __('Invoice Issue Date').'<br/>';
            echo "<span style='font-style: italic; font-size: 85%'>".__('Intended Date').'</span>';
            echo '</th>';
            echo '<th>';
            echo __('Invoice Due Date').'<br/>';
            echo "<span style='font-style: italic; font-size: 85%'>".__('Final Payment Date').'</span>';
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
                ++$count;

				//Color rows based on start and end date
				if ($row['active'] != 'Y') {
					$rowNum = 'error';
				} else {
					if ($row['invoiceIssueDate'] < date('Y-m-d')) {
						$rowNum = 'warning';
					}
					if ($row['invoiceDueDate'] < date('Y-m-d')) {
						$rowNum = 'error';
					}
				}

                echo "<tr class=$rowNum>";
                echo '<td>';
                echo '<b>'.$row['name'].'</b><br/>';
                echo '</td>';
                echo '<td>';
                echo dateConvertBack($guid, $row['invoiceIssueDate']);
                echo '</td>';
                echo '<td>';
                echo dateConvertBack($guid, $row['invoiceDueDate']);
                echo '</td>';
                echo '<td>';
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/billingSchedule_manage_edit.php&pupilsightFinanceBillingScheduleID='.$row['pupilsightFinanceBillingScheduleID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                echo "<script type='text/javascript'>";
                echo '$(document).ready(function(){';
                echo "\$(\".comment-$count-$count\").hide();";
                echo "\$(\".show_hide-$count-$count\").fadeIn(1000);";
                echo "\$(\".show_hide-$count-$count\").click(function(){";
                echo "\$(\".comment-$count-$count\").fadeToggle(1000);";
                echo '});';
                echo '});';
                echo '</script>';
                if ($row['description'] != '') {
                    echo "<a title='".__('View Description')."' class='show_hide-$count-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/page_down.png' alt='".__('Show Comment')."' onclick='return false;' /></a>";
                }
                echo '</td>';
                echo '</tr>';
                if ($row['description'] != '') {
                    echo "<tr class='comment-$count-$count' id='comment-$count-$count'>";
                    echo '<td colspan=6>';
                    echo $row['description'];
                    echo '</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';

            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom', "pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");
            }
        }
    }
}
?>
