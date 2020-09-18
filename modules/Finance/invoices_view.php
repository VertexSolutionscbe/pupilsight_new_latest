<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_view.php') == false) {
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
        $entryCount = 0;

        $page->breadcrumbs->add(__('View Invoices'));

        if ($highestAction=="View Invoices_myChildren") {
            //Test data access field for permission
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() < 1) {
                echo "<div class='error'>";
                echo __('Access denied.');
                echo '</div>';
            } else {
                //Get child list
                $count = 0;
                $options = array();
                while ($row = $result->fetch()) {
                    try {
                        $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }
                    while ($rowChild = $resultChild->fetch()) {
                        $options[$rowChild['pupilsightPersonID']]=formatName('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
                    }
                }

                if (count($options) == 0) {
                    echo "<div class='error'>";
                    echo __('Access denied.');
                    echo '</div>';
                } elseif (count($options) == 1) {
                    $_GET['search'] = key($options);
                } else {
                    echo '<h2>';
                    echo 'Choose Student';
                    echo '</h2>';

                    $pupilsightPersonID = (isset($_GET['search']))? $_GET['search'] : null;

                    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
                    $form->setClass('noIntBorder fullWidth standardForm');

                    $form->addHiddenValue('q', '/modules/Finance/invoices_view.php');
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    $row = $form->addRow();
                        $row->addLabel('search', __('Student'));
                        $row->addSelect('search')->fromArray($options)->selected($pupilsightPersonID)->placeholder();

                    $row = $form->addRow();
                        $row->addSearchSubmit($pupilsight->session);

                    echo $form->getOutput();
                }

                $pupilsightPersonID = null;
                if (isset($_GET['search'])) {
                    $pupilsightPersonID = $_GET['search'];
                }
            }
        } else if ($highestAction=="View Invoices_mine") {
            $count = 1;
            $pupilsightPersonID = $_SESSION[$guid]["pupilsightPersonID"];
        }

        if (!empty($pupilsightPersonID) and count($options) > 0) {
            //Confirm access to this student
            try {
                if ($highestAction=="View Invoices_myChildren") {
                    $dataChild = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlChild = "SELECT pupilsightPerson.pupilsightPersonID FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                } else if ($highestAction=="View Invoices_mine") {
                    $dataChild = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sqlChild = "SELECT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightPersonID=:pupilsightPersonID" ;
                }
                $resultChild = $connection2->prepare($sqlChild);
                $resultChild->execute($dataChild);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($resultChild->rowCount() < 1) {
                echo "<div class='error'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $rowChild = $resultChild->fetch();

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
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/invoices_view.php&search=$pupilsightPersonID&pupilsightSchoolYearID=".getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
                        } else {
                            echo __('Previous Year').' ';
                        }
                    echo ' | ';
                    if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/invoices_view.php&search=$pupilsightPersonID&pupilsightSchoolYearID=".getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
                    } else {
                        echo __('Next Year').' ';
                    }
                    echo '</div>';

                    try {
                        //Add in filter wheres
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightSchoolYearID2' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $pupilsightPersonID);
                        //SQL for NOT Pending
                        $sql = "SELECT pupilsightFinanceInvoice.pupilsightFinanceInvoiceID, surname, preferredName, pupilsightFinanceInvoice.invoiceTo, pupilsightFinanceInvoice.status, pupilsightFinanceInvoice.invoiceIssueDate, pupilsightFinanceInvoice.invoiceDueDate, paidDate, paidAmount, billingScheduleType AS billingSchedule, pupilsightFinanceBillingSchedule.name AS billingScheduleExtra, notes, pupilsightRollGroup.name AS rollGroup FROM pupilsightFinanceInvoice LEFT JOIN pupilsightFinanceBillingSchedule ON (pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID=pupilsightFinanceBillingSchedule.pupilsightFinanceBillingScheduleID) JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT pupilsightFinanceInvoice.status='Pending' AND pupilsightFinanceInvoicee.pupilsightPersonID=:pupilsightPersonID ORDER BY invoiceIssueDate, surname, preferredName";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($result->rowCount() < 1) {
                        echo '<h3>';
                        echo __('View');
                        echo '</h3>';

                        echo "<div class='error'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        echo '<h3>';
                        echo __('View');
                        echo "<span style='font-weight: normal; font-style: italic; font-size: 55%'> ".sprintf(__('%1$s invoice(s) in current view'), $result->rowCount()).'</span>';
                        echo '</h3>';

                        echo "<table class='table' cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo "<th style='width: 110px'>";
                        echo __('Student').'<br/>';
                        echo "<span style='font-style: italic; font-size: 85%'>".__('Invoice To').'</span>';
                        echo '</th>';
                        echo "<th style='width: 110px'>";
                        echo __('Roll Group');
                        echo '</th>';
                        echo "<th style='width: 100px'>";
                        echo __('Status');
                        echo '</th>';
                        echo "<th style='width: 90px'>";
                        echo __('Schedule');
                        echo '</th>';
                        echo "<th style='width: 120px'>";
                        echo __('Total')." <span style='font-style: italic; font-size: 75%'>(".$_SESSION[$guid]['currency'].')</span><br/>';
                        echo "<span style='font-style: italic; font-size: 75%'>".__('Paid').' ('.$_SESSION[$guid]['currency'].')</span>';
                        echo '</th>';
                        echo "<th style='width: 80px'>";
                        echo __('Issue Date').'<br/>';
                        echo "<span style='font-style: italic; font-size: 75%'>".__('Due Date').'</span>';
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

                            //Work out extra status information
                            $statusExtra = '';
                            if ($row['status'] == 'Issued' and $row['invoiceDueDate'] < date('Y-m-d')) {
                                $statusExtra = 'Overdue';
                            }
                            if ($row['status'] == 'Paid' and $row['invoiceDueDate'] < $row['paidDate']) {
                                $statusExtra = 'Late';
                            }

							//Color row by status
							if ($row['status'] == 'Paid') {
								$rowNum = 'current';
							}
                            if ($row['status'] == 'Issued' and $statusExtra == 'Overdue') {
                                $rowNum = 'error';
                            }

                            echo "<tr class=$rowNum>";
                            echo '<td>';
                            echo '<b>'.formatName('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', true).'</b><br/>';
                            echo "<span style='font-style: italic; font-size: 85%'>".$row['invoiceTo'].'</span>';
                            echo '</td>';
                            echo '<td>';
                            echo $row['rollGroup'];
                            echo '</td>';
                            echo '<td>';
                            echo $row['status'];
                            if ($statusExtra != '') {
                                echo " - $statusExtra";
                            }
                            echo '</td>';
                            echo '<td>';
                            if ($row['billingScheduleExtra'] != '') {
                                echo $row['billingScheduleExtra'];
                            } else {
                                echo $row['billingSchedule'];
                            }
                            echo '</td>';
                            echo '<td>';
							//Calculate total value
							$totalFee = 0;
                            $feeError = false;
                            try {
                                $dataTotal = array('pupilsightFinanceInvoiceID' => $row['pupilsightFinanceInvoiceID']);
                                if ($row['status'] == 'Pending') {
                                    $sqlTotal = 'SELECT pupilsightFinanceInvoiceFee.fee AS fee, pupilsightFinanceFee.fee AS fee2 FROM pupilsightFinanceInvoiceFee LEFT JOIN pupilsightFinanceFee ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID=pupilsightFinanceFee.pupilsightFinanceFeeID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                                } else {
                                    $sqlTotal = 'SELECT pupilsightFinanceInvoiceFee.fee AS fee, NULL AS fee2 FROM pupilsightFinanceInvoiceFee WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                                }
                                $resultTotal = $connection2->prepare($sqlTotal);
                                $resultTotal->execute($dataTotal);
                            } catch (PDOException $e) {
                                echo $e->getMessage();
                                echo '<i>Error calculating total</i>';
                                $feeError = true;
                            }
                            while ($rowTotal = $resultTotal->fetch()) {
                                if (is_numeric($rowTotal['fee2'])) {
                                    $totalFee += $rowTotal['fee2'];
                                } else {
                                    $totalFee += $rowTotal['fee'];
                                }
                            }
                            if ($feeError == false) {
                                if (substr($_SESSION[$guid]['currency'], 4) != '') {
                                    echo substr($_SESSION[$guid]['currency'], 4).' ';
                                }
                                echo number_format($totalFee, 2, '.', ',').'<br/>';
                                if ($row['paidAmount'] != '') {
                                    $styleExtra = '';
                                    if ($row['paidAmount'] != $totalFee) {
                                        $styleExtra = 'color: #c00;';
                                    }
                                    echo "<span style='$styleExtra font-style: italic; font-size: 85%'>";
                                    if (substr($_SESSION[$guid]['currency'], 4) != '') {
                                        echo substr($_SESSION[$guid]['currency'], 4).' ';
                                    }
                                    echo number_format($row['paidAmount'], 2, '.', ',').'</span>';
                                }
                            }
                            echo '</td>';
                            echo '<td>';
                            if (is_null($row['invoiceIssueDate'])) {
                                echo 'NA<br/>';
                            } else {
                                echo dateConvertBack($guid, $row['invoiceIssueDate']).'<br/>';
                            }
                            echo "<span style='font-style: italic; font-size: 75%'>".dateConvertBack($guid, $row['invoiceDueDate']).'</span>';
                            echo '</td>';
                            echo '<td>';
                            if ($row['status'] == 'Issued') {
                                echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/invoices_view_print.php&type=invoice&pupilsightFinanceInvoiceID='.$row['pupilsightFinanceInvoiceID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightPersonID=$pupilsightPersonID'><img title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
                            } elseif ($row['status'] == 'Paid' or $row['status'] == 'Paid - Partial') {
                                echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/invoices_view_print.php&type=receipt&pupilsightFinanceInvoiceID='.$row['pupilsightFinanceInvoiceID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightPersonID=$pupilsightPersonID'><img title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
                            }
                            echo "<script type='text/javascript'>";
                            echo '$(document).ready(function(){';
                            echo "\$(\".comment-$count\").hide();";
                            echo "\$(\".show_hide-$count\").fadeIn(1000);";
                            echo "\$(\".show_hide-$count\").click(function(){";
                            echo "\$(\".comment-$count\").fadeToggle(1000);";
                            echo '});';
                            echo '});';
                            echo '</script>';
                            if ($row['notes'] != '') {
                                echo "<a title='View Notes' class='show_hide-$count' onclick='false' href='#'><img style='margin-left: 5px' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/page_down.png' alt='".__('Show Comment')."' onclick='return false;' /></a>";
                            }
                            echo '</td>';
                            echo '</tr>';
                            if ($row['notes'] != '') {
                                echo "<tr class='comment-$count' id='comment-$count'>";
                                echo '<td colspan=7>';
                                echo $row['notes'];
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        echo '</table>';
                    }
                }
            }
        }
    }
}
?>
