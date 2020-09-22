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
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    $pupilsightFinanceInvoiceID = $_GET['pupilsightFinanceInvoiceID'];
    $type = $_GET['type'];
    $preview = false;
    if (isset($_GET['preview']) && $_GET['preview'] == 'true') {
        $preview = $_GET['preview'];
    }
    $receiptNumber = null;
    if (isset($_GET['receiptNumber'])) {
        $receiptNumber = $_GET['receiptNumber'];
    }

    if ($pupilsightFinanceInvoiceID == '' or $pupilsightSchoolYearID == '' or $type == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
            $sql = 'SELECT surname, preferredName, pupilsightFinanceInvoice.* FROM pupilsightFinanceInvoice JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
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
                if ($preview) {
                    echo "<p style='font-weight: bold; color: #c00; font-size: 100%; letter-spacing: -0.5px'>";
                    echo __('THIS INVOICE IS A PREVIEW: IT HAS NOT YET BEEN ISSUED AND IS FOR TESTING PURPOSES ONLY!');
                    echo '</p>';
                }

                $invoiceContents = invoiceContents($guid, $connection2, $pupilsightFinanceInvoiceID, $pupilsightSchoolYearID, $_SESSION[$guid]['currency'], false, $preview);
                if ($invoiceContents == false) {
                    echo "<div class='error'>";
                    echo __('An error occurred.');
                    echo '</div>';
                } else {
                    echo $invoiceContents;
                }
            } elseif ($type == 'reminder1' or $type == 'reminder2' or $type == 'reminder3') {
                //Update reminder count
                if ($row['reminderCount'] < 3) {
                    try {
                        $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                        $sql = 'UPDATE pupilsightFinanceInvoice SET reminderCount='.($row['reminderCount'] + 1).' WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }
                }

                //Reminder Text
                if ($type == 'reminder1') {
                    echo '<h2>';
                    echo 'Reminder 1';
                    echo '</h2>';
                    $reminderText = getSettingByScope($connection2, 'Finance', 'reminder1Text');
                } elseif ($type == 'reminder2') {
                    echo '<h2>';
                    echo 'Reminder 2';
                    echo '</h2>';
                    $reminderText = getSettingByScope($connection2, 'Finance', 'reminder2Text');
                } elseif ($type == 'reminder3') {
                    echo '<h2>';
                    echo 'Reminder 3';
                    echo '</h2>';
                    $reminderText = getSettingByScope($connection2, 'Finance', 'reminder3Text');
                }
                if ($reminderText != '') {
                    echo '<p>';
                    echo $reminderText;
                    echo '</p>';
                }

                echo '<h2>';
                echo __('Invoice');
                echo '</h2>';
                $invoiceContents = invoiceContents($guid, $connection2, $pupilsightFinanceInvoiceID, $pupilsightSchoolYearID, $_SESSION[$guid]['currency']);
                if ($invoiceContents == false) {
                    echo "<div class='error'>";
                    echo __('An error occurred.');
                    echo '</div>';
                } else {
                    echo $invoiceContents;
                }
            } elseif ($type = 'Receipt') {
                echo '<h2>';
                echo __('Receipt');
                echo '</h2>';
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
