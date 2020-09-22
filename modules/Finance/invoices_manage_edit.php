<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Module\Finance\Forms\FinanceFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightSchoolYearID = isset($_GET['pupilsightSchoolYearID'])? $_GET['pupilsightSchoolYearID'] : '';
    $pupilsightFinanceInvoiceID = isset($_GET['pupilsightFinanceInvoiceID'])? $_GET['pupilsightFinanceInvoiceID'] : '';
    $status = isset($_GET['status'])? $_GET['status'] : '';
    $pupilsightFinanceInvoiceeID = isset($_GET['pupilsightFinanceInvoiceeID'])? $_GET['pupilsightFinanceInvoiceeID'] : '';
    $monthOfIssue = isset($_GET['monthOfIssue'])? $_GET['monthOfIssue'] : '';
    $pupilsightFinanceBillingScheduleID = isset($_GET['pupilsightFinanceBillingScheduleID'])? $_GET['pupilsightFinanceBillingScheduleID'] : '';
    $pupilsightFinanceFeeCategoryID = isset($_GET['pupilsightFinanceFeeCategoryID'])? $_GET['pupilsightFinanceFeeCategoryID'] : '';

    $urlParams = compact('pupilsightSchoolYearID', 'status', 'pupilsightFinanceInvoiceeID', 'monthOfIssue', 'pupilsightFinanceBillingScheduleID', 'pupilsightFinanceFeeCategoryID'); 

    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Invoices'), 'invoices_manage.php', $urlParams)
        ->add(__('Edit Invoice'));    

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('success1' => __('Your request was completed successfully, but one or more requested emails could not be sent.'), 'error3' => __('Some elements of your request failed, but others were successful.')));
    }

    if ($pupilsightFinanceInvoiceID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
            $sql = "SELECT pupilsightFinanceInvoice.*, companyName, companyContact, companyEmail, companyCCFamily, pupilsightSchoolYear.name as schoolYear, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightFinanceBillingSchedule.name as billingScheduleName
                    FROM pupilsightFinanceInvoice 
                    JOIN pupilsightSchoolYear ON (pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightFinanceInvoice.pupilsightSchoolYearID)
                    LEFT JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) 
                    LEFT JOIN pupilsightFinanceBillingSchedule ON (pupilsightFinanceBillingSchedule.pupilsightFinanceBillingScheduleID=pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID)
                    LEFT JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightFinanceInvoicee.pupilsightPersonID)
                    WHERE pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID 
                    AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
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
            $values = $result->fetch();

            if ($status != '' or $pupilsightFinanceInvoiceeID != '' or $monthOfIssue != '' or $pupilsightFinanceBillingScheduleID != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/invoices_manage.php&".http_build_query($urlParams)."'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }
        
            $form = Form::create('invoice', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/invoices_manage_editProcess.php?'.http_build_query($urlParams));
            $form->setFactory(FinanceFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightFinanceInvoiceID', $pupilsightFinanceInvoiceID);

            $form->addRow()->addHeading(__('Basic Information'));

            $row = $form->addRow();
                $row->addLabel('schoolYear', __('School Year'));
                $row->addTextField('schoolYear')->required()->readonly();

            $row = $form->addRow();
                $row->addLabel('personName', __('Invoicee'));
                $row->addTextField('personName')->required()->readonly()->setValue(formatName('', $values['preferredName'], $values['surname'], 'Student', true));

            $row = $form->addRow();
                $row->addLabel('billingScheduleType', __('Scheduling'));
                $row->addTextField('billingScheduleType')->required()->readonly();

            if ($values['billingScheduleType'] == 'Scheduled') {
                $row = $form->addRow();
                    $row->addLabel('billingScheduleName', __('Billing Schedule'));
                    $row->addTextField('billingScheduleName')->required()->readonly();
            } else {
                if ($values['status'] == 'Pending' || $values['status'] == 'Issued') {
                    $row = $form->addRow();
                        $row->addLabel('invoiceDueDate', __('Invoice Due Date'));
                        $row->addDate('invoiceDueDate')->required();
                } else {
                    $row = $form->addRow();
                        $row->addLabel('invoiceDueDate', __('Invoice Due Date'));
                        $row->addDate('invoiceDueDate')->required()->readonly();
                }
            }

            $row = $form->addRow();
                $row->addLabel('status', __('Status'))->description($values['status'] == 'Pending'
                    ? __('This value cannot be changed. Use the Issue function to change the status from "Pending" to "Issued".') 
                    : __('Available options are limited according to current status.'));
                $row->addSelectInvoiceStatus('status', $values['status'])->required();

            // PAYMENT INFO
            if ($values['status'] == 'Issued' or $values['status'] == 'Paid - Partial') {
                $form->toggleVisibilityByClass('paymentInfo')->onSelect('status')->when(array('Paid', 'Paid - Partial', 'Paid - Complete'));
                
                $row = $form->addRow()->addClass('paymentInfo');
                    $row->addLabel('paymentType', __('Payment Type'));
                    $row->addSelectPaymentMethod('paymentType')->required();       

                $row = $form->addRow()->addClass('paymentInfo');
                    $row->addLabel('paymentTransactionID', __('Transaction ID'))->description(__('Transaction ID to identify this payment.'));
                    $row->addTextField('paymentTransactionID')->maxLength(50);

                $row = $form->addRow()->addClass('paymentInfo');
                    $row->addLabel('paidDate', __('Date Paid'))->description(__('Date of payment, not entry to system.'));
                    $row->addDate('paidDate')->required();

                $remainingFee = getInvoiceTotalFee($pdo, $pupilsightFinanceInvoiceID, $values['status']);
                if ($values['status'] == 'Paid - Partial') {
                    $alreadyPaid = getAmountPaid($connection2, $guid, 'pupilsightFinanceInvoice', $pupilsightFinanceInvoiceID);
                    $remainingFee -= $alreadyPaid;
                }

                $row = $form->addRow()->addClass('paymentInfo');
                    $row->addLabel('paidAmount', __('Amount Paid'))->description(__('Amount in current payment.'));
                    $row->addCurrency('paidAmount')->maxLength(14)->required()->setValue(number_format($remainingFee, 2, '.', ''));

                unset($values['paidDate']);
                unset($values['paidAmount']);
            }

            $row = $form->addRow();
                $row->addLabel('notes', __('Notes'))->description(__('Notes will be displayed on the final invoice and receipt.'));
                $row->addTextArea('notes')->setRows(5);

            // FEES
            $form->addRow()->addHeading(__('Fees'));

            // Ad Hoc OR Issued (Fixed Fees)
            $dataFees = array('pupilsightFinanceInvoiceID' => $values['pupilsightFinanceInvoiceID']);
            $sqlFees = "SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceInvoiceFee.name AS name, pupilsightFinanceInvoiceFee.fee, pupilsightFinanceInvoiceFee.description AS description, NULL AS pupilsightFinanceFeeID, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";

            // Union with Standard (Flexible Fees)
            if ($values['status'] == 'Pending') {
                $sqlFees = "(".$sqlFees." AND feeType='Ad Hoc')";
                $sqlFees .= " UNION ";
                $sqlFees .= "(SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceFee.name AS name, pupilsightFinanceFee.fee AS fee, pupilsightFinanceFee.description AS description, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID AS pupilsightFinanceFeeID, pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFee ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID=pupilsightFinanceFee.pupilsightFinanceFeeID) JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND feeType='Standard')";
            }

            $sqlFees .= " ORDER BY sequenceNumber";
            $resultFees = $pdo->executeQuery($dataFees, $sqlFees);

            // CUSTOM BLOCKS
            if ($values['status'] == 'Pending') {
                // Fee selector
                $feeSelector = $form->getFactory()->createSelectFee('addNewFee', $pupilsightSchoolYearID)->addClass('addBlock');

                // Block template
                $blockTemplate = $form->getFactory()->createTable()->setClass('blank');
                $row = $blockTemplate->addRow();
                    $row->addTextField('name')->setClass('standardWidth floatLeft noMargin title')->required()->placeholder(__('Fee Name'))
                        ->append('<input type="hidden" id="pupilsightFinanceFeeID" name="pupilsightFinanceFeeID" value="">')
                        ->append('<input type="hidden" id="feeType" name="feeType" value="">');
                    
                $col = $blockTemplate->addRow()->addColumn()->addClass('inline');
                    $col->addSelectFeeCategory('pupilsightFinanceFeeCategoryID')
                        ->setClass('shortWidth floatLeft noMargin');

                    $col->addCurrency('fee')
                        ->setClass('shortWidth floatLeft')
                        ->required()
                        ->placeholder(__('Value').(!empty($_SESSION[$guid]['currency'])? ' ('.$_SESSION[$guid]['currency'].')' : ''));
                    
                $col = $blockTemplate->addRow()->addClass('showHide fullWidth')->addColumn();
                    $col->addLabel('description', __('Description'));
                    $col->addTextArea('description')->setRows('auto')->setClass('fullWidth floatNone noMargin');

                // Custom Blocks for Fees
                $row = $form->addRow();
                    $customBlocks = $row->addCustomBlocks('feesBlock', $pupilsight->session)
                        ->fromTemplate($blockTemplate)
                        ->settings(array('inputNameStrategy' => 'string', 'addOnEvent' => 'change', 'sortable' => true))
                        ->placeholder(__('Fees will be listed here...'))
                        ->addToolInput($feeSelector)
                        ->addBlockButton('showHide', __('Show/Hide'), 'plus.png');

                // Add existing blocks
                while ($fee = $resultFees->fetch()) {
                    $fee['readonly'] = ($fee['feeType'] == 'Standard')? array('name', 'fee', 'description', 'pupilsightFinanceFeeCategoryID') : array('name', 'fee', 'pupilsightFinanceFeeCategoryID');
                    $fee['pupilsightFinanceInvoiceFeeID'] = str_pad($fee['pupilsightFinanceInvoiceFeeID'], 15, '0', STR_PAD_LEFT);
                    $fee['pupilsightFinanceFeeCategoryID'] = str_pad($fee['pupilsightFinanceFeeCategoryID'], 4, '0', STR_PAD_LEFT);

                    $customBlocks->addBlock($fee['pupilsightFinanceInvoiceFeeID'], $fee);
                }

                // Add predefined block data (for templating new blocks, triggered with the feeSelector)
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                $sql = "SELECT pupilsightFinanceFeeID as groupBy, pupilsightFinanceFeeID, name, description, fee, pupilsightFinanceFeeCategoryID FROM pupilsightFinanceFee ORDER BY name";
                $result = $pdo->executeQuery($data, $sql);
                $feeData = $result->rowCount() > 0? $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

                $customBlocks->addPredefinedBlock('Ad Hoc Fee', array('feeType' => 'Ad Hoc', 'pupilsightFinanceFeeID' => 0));
                foreach ($feeData as $pupilsightFinanceFeeID => $data) {
                    $customBlocks->addPredefinedBlock($pupilsightFinanceFeeID, $data + array('feeType' => 'Standard', 'readonly' => ['name', 'fee', 'description', 'pupilsightFinanceFeeCategoryID']) );
                }
            } else {
                // Display fees already issued (readonly)
                if ($resultFees->rowCount() == 0) {
                    $form->addRow()->addAlert(__('There are no records to display.'), 'error');
                } else {
                    $table = $form->addRow()->addTable()->addClass('colorOddEven');

                    $header = $table->addHeaderRow();
                        $header->addContent(__('Name'));
                        $header->addContent(__('Category'));
                        $header->addContent(__('Description'));
                        $header->addContent(__('Fee'))->append(' <small><i>('.$_SESSION[$guid]['currency'].')</i></small>');

                    $feeTotal = 0;
                    while ($fee = $resultFees->fetch()) {
                        $feeTotal += $fee['fee'];
                        $row = $table->addRow();
                            $row->addContent($fee['name']);
                            $row->addContent($fee['category']);
                            $row->addContent($fee['description']);
                            $row->addContent(number_format($fee['fee'], 2, '.', ','))->prepend(substr($_SESSION[$guid]['currency'], 4).' ');
                    }

                    $row = $table->addRow()->addClass('current');
                        $row->addTableCell(__('Invoice Total:'))->colspan(3)->wrap('<b class="floatRight">', '</b>');
                        $row->addTableCell(number_format($feeTotal, 2, '.', ','))->prepend(substr($_SESSION[$guid]['currency'], 4).' ')->wrap('<b>', '</b>');
                }
            }

            $form->addRow()->addHeading(__('Payment Log'));

            $form->addRow()->addContent(getPaymentLog($connection2, $guid, 'pupilsightFinanceInvoice', $pupilsightFinanceInvoiceID));

            // EMAIL RECEIPTS
            if ($values['status'] == 'Issued' || $values['status'] == 'Paid - Partial') {
                $form->toggleVisibilityByClass('emailReceipts')->onSelect('status')->when(array('Paid', 'Paid - Partial', 'Paid - Complete'));
                $form->addRow()->addHeading(__('Email Receipt'))->addClass('emailReceipts');

                $row = $form->addRow()->addClass('emailReceipts');
                    $row->addYesNoRadio('emailReceipt')->checked('Y');

                $form->toggleVisibilityByClass('emailReceiptsTable')->onRadio('emailReceipt')->when(array('Y'));

                $email = getSettingByScope($connection2, 'Finance', 'email');
                $form->addHiddenValue('email', $email);
                if (empty($email)) {
                    $row = $form->addRow()->addClass('emailReceipts emailReceiptsTable');
                    $row->addAlert(__('An outgoing email address has not been set up under Invoice & Receipt Settings, and so no emails can be sent.'), 'error');
                } else {
                    $row = $form->addRow()->addClass('emailReceipts emailReceiptsTable');
                    $row->addInvoiceEmailCheckboxes('emails[]', 'names[]', $values, $pupilsight->session);
                }
            }

            // EMAIL REMINDERS
            if ($values['status'] == 'Issued' && $values['invoiceDueDate'] < date('Y-m-d')) {

                $form->toggleVisibilityByClass('emailReminders')->onSelect('status')->when(array('Issued'));
                $form->addRow()->addHeading(sprintf(__('Email Reminder %1$s'), ($values['reminderCount'])+1))->addClass('emailReminders');

                $row = $form->addRow()->addClass('emailReminders');
                    $row->addYesNoRadio('emailReminder')->checked('Y');

                $form->toggleVisibilityByClass('emailRemindersTable')->onRadio('emailReminder')->when(array('Y'));

                $email = getSettingByScope($connection2, 'Finance', 'email');
                $form->addHiddenValue('email', $email);
                if (empty($email)) {
                    $row = $form->addRow()->addClass('emailReminders emailRemindersTable');
                    $row->addAlert(__('An outgoing email address has not been set up under Invoice & Receipt Settings, and so no emails can be sent.'), 'error');
                } else {
                    $row = $form->addRow()->addClass('emailReminders emailRemindersTable');
                    $row->addInvoiceEmailCheckboxes('emails[]', 'names[]', $values, $pupilsight->session);
                }
            }

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
