<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Get variables
$pupilsightFinanceInvoiceID = '';
if (isset($_GET['pupilsightFinanceInvoiceID'])) {
    $pupilsightFinanceInvoiceID = $_GET['pupilsightFinanceInvoiceID'];
}
$key = '';
if (isset($_GET['key'])) {
    $key = $_GET['key'];
}

if (isset($_GET['return'])) {
    returnProcess($guid, $_GET['return'], null, array('error3' => __("Your payment could not be made as the payment gateway does not support the system's currency."), 'success1' => __('Your payment has been successfully made to your credit card. A receipt has been emailed to you.'), 'success2' => __('Your payment could not be made to your credit card. Please try an alternative payment method.'), 'success3' => sprintf(__('Your payment has been successfully made to your credit card, but there has been an error recording your payment in %1$s. Please print this screen and contact the school ASAP, quoting code %2$s.'), $_SESSION[$guid]['systemName'], $pupilsightFinanceInvoiceID)));
}

if (!isset($_GET['return'])) { //No return message, so must just be landing to make payment
    //Check variables
    if ($pupilsightFinanceInvoiceID == '' or $key == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        //Check for record
        $keyReadFail = false;
        try {
            $dataKeyRead = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'key' => $key);
            $sqlKeyRead = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND `key`=:key AND status='Issued'";
            $resultKeyRead = $connection2->prepare($sqlKeyRead);
            $resultKeyRead->execute($dataKeyRead);
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo __('Your request failed due to a database error.');
            echo '</div>';
        }

        if ($resultKeyRead->rowCount() != 1) { //If not exists, report error
            echo "<div class='error'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {    //If exists check confirmed
            $rowKeyRead = $resultKeyRead->fetch();

            //Get value of the invoice.
            $feeOK = true;
            try {
                $dataFees['pupilsightFinanceInvoiceID'] = $pupilsightFinanceInvoiceID;
                $sqlFees = 'SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceInvoiceFee.name AS name, pupilsightFinanceInvoiceFee.fee, pupilsightFinanceInvoiceFee.description AS description, NULL AS pupilsightFinanceFeeID, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID ORDER BY sequenceNumber';
                $resultFees = $connection2->prepare($sqlFees);
                $resultFees->execute($dataFees);
            } catch (PDOException $e) {
                echo "<div class='error'>";
                echo __('Your request failed due to a database error.');
                echo '</div>';
                $feeOK = false;
            }

            if ($feeOK == true) {
                $feeTotal = 0;
                while ($rowFees = $resultFees->fetch()) {
                    $feeTotal += $rowFees['fee'];
                }

                $currency = getSettingByScope($connection2, 'System', 'currency');
                $enablePayments = getSettingByScope($connection2, 'System', 'enablePayments');
                $paypalAPIUsername = getSettingByScope($connection2, 'System', 'paypalAPIUsername');
                $paypalAPIPassword = getSettingByScope($connection2, 'System', 'paypalAPIPassword');
                $paypalAPISignature = getSettingByScope($connection2, 'System', 'paypalAPISignature');

                if ($enablePayments == 'Y' and $paypalAPIUsername != '' and $paypalAPIPassword != '' and $paypalAPISignature != '' and $feeTotal > 0) {
                    $financeOnlinePaymentEnabled = getSettingByScope($connection2, 'Finance', 'financeOnlinePaymentEnabled');
                    $financeOnlinePaymentThreshold = getSettingByScope($connection2, 'Finance', 'financeOnlinePaymentThreshold');
                    if ($financeOnlinePaymentEnabled == 'Y') {
                        echo "<h3 style='margin-top: 40px'>";
                        echo __('Online Payment');
                        echo '</h3>';
                        echo '<p>';
                        if ($financeOnlinePaymentThreshold == '' or $financeOnlinePaymentThreshold >= $feeTotal) {
                            echo sprintf(__('Payment can be made by credit card, using our secure PayPal payment gateway. When you press Pay Online Now, you will be directed to PayPal in order to make payment. During this process we do not see or store your credit card details. Once the transaction is complete you will be returned to %1$s.'), $_SESSION[$guid]['systemName']).' ';

                            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/invoices_payOnlineProcess.php');
                
                            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                            $form->addHiddenValue('pupilsightFinanceInvoiceID', $pupilsightFinanceInvoiceID);
                            $form->addHiddenValue('key', $key);

                            $row = $form->addRow();
                                $row->addContent($currency.$feeTotal);
                                $row->addSubmit(__('Pay Online Now'));

                            echo $form->getOutput();
                        } else {
                            echo "<div class='error'>".__('Payment is not permitted for this invoice, as the total amount is greater than the permitted online payment threshold.').'</div>';
                        }
                        echo '</p>';
                    } else {
                        echo "<div class='error'>";
                        echo __('Your request failed due to a database error.');
                        echo '</div>';
                    }
                } else {
                    echo "<div class='error'>";
                    echo __('Your request failed due to a database error.');
                    echo '</div>';
                }
            }
        }
    }
}
