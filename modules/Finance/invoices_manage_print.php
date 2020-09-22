<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage_print.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    $pupilsightFinanceInvoiceID = $_GET['pupilsightFinanceInvoiceID'];
    $status = $_GET['status'];
    $pupilsightFinanceInvoiceeID = $_GET['pupilsightFinanceInvoiceeID'];
    $monthOfIssue = $_GET['monthOfIssue'];
    $pupilsightFinanceBillingScheduleID = $_GET['pupilsightFinanceBillingScheduleID'];
    $pupilsightFinanceFeeCategoryID = $_GET['pupilsightFinanceFeeCategoryID'];

    //Proceed!
    $urlParams = compact('pupilsightSchoolYearID', 'status', 'pupilsightFinanceInvoiceeID', 'monthOfIssue', 'pupilsightFinanceBillingScheduleID', 'pupilsightFinanceFeeCategoryID'); 

    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Invoices'), 'invoices_manage.php', $urlParams)
        ->add(__('Print Invoices, Receipts & Reminders'));    

    if ($pupilsightFinanceInvoiceID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
            $sql = 'SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
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
            $row = $result->fetch();

            if ($status != '' or $pupilsightFinanceInvoiceeID != '' or $monthOfIssue != '' or $pupilsightFinanceBillingScheduleID != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/invoices_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=$status&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            if ($row['status'] == 'Pending') {
                echo "<div class='error'>";
                echo __('There is nothing to print, as the invoice has yet to be issued.');
                echo '</div>';
            } else {
                echo "<table class='table' cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo __('Item');
                echo '</th>';
                echo "<th style='width: 120px'>";
                echo __('Actions');
                echo '</th>';
                echo '</tr>';

                $count = 0;
                $rowNum = 'even';

                ?>
					<tr class='<?php echo $rowNum ?>'>
						<td>
							<b><?php echo __('Invoice') ?></b><br/>
						</td>
						<td class="left">
							<?php
                            echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/invoices_manage_print_print.php&type=invoice&pupilsightFinanceInvoiceID='.$row['pupilsightFinanceInvoiceID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>"; ?>
						</td>
					</tr>
					<?php
                    ++$count;
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ?>
					<?php
                    if ($row['status'] == 'Issued' || $row['status'] == 'Paid - Partial') {
                        if ($row['reminderCount'] >= 0) {
                            ?>
							<tr class='<?php echo $rowNum ?>'>
								<td>
									<b><?php echo __('Reminder 1') ?></b><br/>
								</td>
								<td class="left">
									<?php
                                    echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/invoices_manage_print_print.php&type=reminder1&pupilsightFinanceInvoiceID='.$row['pupilsightFinanceInvoiceID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
                            ?>
								</td>
							</tr>
							<?php

                        }
                        ++$count;
                        if ($count % 2 == 0) {
                            $rowNum = 'even';
                        } else {
                            $rowNum = 'odd';
                        }
                        if ($row['reminderCount'] >= 1) {
                            ?>
							<tr class='<?php echo $rowNum ?>'>
								<td>
									<b><?php echo __('Reminder 2') ?></b><br/>
								</td>
								<td class="left">
									<?php
                                    echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/invoices_manage_print_print.php&type=reminder2&pupilsightFinanceInvoiceID='.$row['pupilsightFinanceInvoiceID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
                            ?>
								</td>
							</tr>
							<?php

                        }
                        ++$count;
                        if ($count % 2 == 0) {
                            $rowNum = 'even';
                        } else {
                            $rowNum = 'odd';
                        }
                        if ($row['reminderCount'] >= 2) {
                            ?>
							<tr class='<?php echo $rowNum ?>'>
								<td>
									<b><?php echo __('Reminder 3') ?></b><br/>
								</td>
								<td class="left">
									<?php
                                    echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/invoices_manage_print_print.php&type=reminder3&pupilsightFinanceInvoiceID='.$row['pupilsightFinanceInvoiceID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
                            ?>
								</td>
							</tr>
							<?php

                        }
                    }
                	if ($row['status'] == 'Paid' OR $row['status'] == 'Paid - Partial' OR $row['status'] == 'Refunded') {
                    //Get individual payments that make up receipt
                        try {
                            $data = array('foreignTable' => 'pupilsightFinanceInvoice', 'foreignTableID' => $pupilsightFinanceInvoiceID);
                            $sql = 'SELECT pupilsightPayment.*, surname, preferredName FROM pupilsightPayment JOIN pupilsightPerson ON (pupilsightPayment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignTable=:foreignTable AND foreignTableID=:foreignTableID ORDER BY timestamp, pupilsightPaymentID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $return .= "<div class='error'>".$e->getMessage().'</div>';
                        }

                    if ($result->rowCount() < 1) {
                        ?>
							<tr class='<?php echo $rowNum ?>'>
								<td>
									<b><?php echo __('Receipt') ?></b><br/>
								</td>
								<td class="left">
									<?php
                                    echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/invoices_manage_print_print.php&type=receipt&pupilsightFinanceInvoiceID='.$row['pupilsightFinanceInvoiceID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
                        ?>
								</td>
							</tr>
							<?php

                    } else {
                        $count2 = 0;
                        while ($row = $result->fetch()) {
                            if ($count % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }
                            ?>
							<tr class='<?php echo $rowNum ?>'>
								<td>
									<b><?php echo sprintf(__('Receipt %1$s'), ($count2 + 1)) ?></b><br/>
								</td>
								<td class="left">
									<?php
                                    echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module']."/invoices_manage_print_print.php&type=receipt&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&pupilsightSchoolYearID=$pupilsightSchoolYearID&receiptNumber=$count2'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
                                    ?>
								</td>
							</tr>
							<?php
                            ++$count;
                            ++$count2;
                        }
                    }
                }
                echo '</table>';
            }
        }
    }
}
?>
