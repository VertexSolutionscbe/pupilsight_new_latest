<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_view_print.php') == false) {
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
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
        $pupilsightFinanceInvoiceID = $_GET['pupilsightFinanceInvoiceID'];
        $type = $_GET['type'];
        $pupilsightPersonID = null;
        if (isset($_GET['pupilsightPersonID'])) {
            $pupilsightPersonID = $_GET['pupilsightPersonID'];
        }

        if ($pupilsightFinanceInvoiceID == '' or $pupilsightSchoolYearID == '' or $type == '' or $pupilsightPersonID == '') {
            echo "<div class='error'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
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

                try {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "SELECT surname, preferredName, pupilsightFinanceInvoice.* FROM pupilsightFinanceInvoice JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND pupilsightFinanceInvoicee.pupilsightPersonID=:pupilsightPersonID AND (pupilsightFinanceInvoice.status='Issued' OR pupilsightFinanceInvoice.status='Paid' OR pupilsightFinanceInvoice.status='Paid - Partial')";
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

                    $statusExtra = '';
                    if ($row['status'] == 'Issued' and $row['invoiceDueDate'] < date('Y-m-d')) {
                        $statusExtra = 'Overdue';
                    }
                    if ($row['status'] == 'Paid' and $row['invoiceDueDate'] < $row['paidDate']) {
                        $statusExtra = 'Late';
                    }

                    if ($type == 'invoice') {
                        echo '<h2>';
                        echo 'Invoice';
                        echo '</h2>';
                        $invoiceContents = invoiceContents($guid, $connection2, $pupilsightFinanceInvoiceID, $pupilsightSchoolYearID, $_SESSION[$guid]['currency'], false, true);
                        if ($invoiceContents == false) {
                            echo "<div class='error'>";
                            echo __('An error occurred.');
                            echo '</div>';
                        } else {
                            echo $invoiceContents;
                        }
                    } elseif ($type = 'receipt') {
                        echo '<h2>';
                        echo __('Receipt');
                        echo '</h2>';
                        //Get receipt number
                        $receiptNumber = null;
                        try {
                            $dataReceiptNumber = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                            $sqlReceiptNumber = "SELECT *
                                FROM pupilsightPayment
                                JOIN pupilsightFinanceInvoice ON (pupilsightPayment.foreignTableID=pupilsightFinanceInvoice.pupilsightFinanceInvoiceID AND pupilsightPayment.foreignTable='pupilsightFinanceInvoice')
                                WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID
                                ORDER BY timestamp DESC, pupilsightPayment.pupilsightPaymentID DESC
                            ";
                            $resultReceiptNumber = $connection2->prepare($sqlReceiptNumber);
                            $resultReceiptNumber->execute($dataReceiptNumber);
                        } catch (PDOException $e) { }
                        $receiptNumber = ($resultReceiptNumber->rowCount()-1) ;
                        $receiptContents = receiptContents($guid, $connection2, $pupilsightFinanceInvoiceID, $pupilsightSchoolYearID, $_SESSION[$guid]['currency'], false, $receiptNumber);
                        if ($receiptContents == false) {
                            echo "<div class='error'>";
                            echo __('An error occurred.');
                            echo '</div>';
                        } else {
                            echo $receiptContents;
                        }
                    }
                }
            }
        }
    }
}
