<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Module\Finance\Forms\FinanceFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage_issue.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //Check if school year specified
    $pupilsightSchoolYearID = isset($_GET['pupilsightSchoolYearID'])? $_GET['pupilsightSchoolYearID'] : '';
    $pupilsightFinanceInvoiceID = isset($_GET['pupilsightFinanceInvoiceID'])? $_GET['pupilsightFinanceInvoiceID'] : '';
    $status = isset($_GET['status'])? $_GET['status'] : '';
    $pupilsightFinanceInvoiceeID = isset($_GET['pupilsightFinanceInvoiceeID'])? $_GET['pupilsightFinanceInvoiceeID'] : '';
    $monthOfIssue = isset($_GET['monthOfIssue'])? $_GET['monthOfIssue'] : '';
    $pupilsightFinanceBillingScheduleID = isset($_GET['pupilsightFinanceBillingScheduleID'])? $_GET['pupilsightFinanceBillingScheduleID'] : '';
    $pupilsightFinanceFeeCategoryID = isset($_GET['pupilsightFinanceFeeCategoryID'])? $_GET['pupilsightFinanceFeeCategoryID'] : '';

    $urlParams = compact('pupilsightSchoolYearID', 'status', 'pupilsightFinanceInvoiceeID', 'monthOfIssue', 'pupilsightFinanceBillingScheduleID', 'pupilsightFinanceFeeCategoryID'); 

    $page->breadcrumbs
        ->add(__('Manage Invoices'), 'invoices_manage.php', $urlParams)
        ->add(__('Issue Invoice'));       

    echo '<p>';
    echo __('Issuing an invoice confirms it in the system, meaning the financial details within the invoice can no longer be edited. On issue, you also have the choice to email the invoice to the appropriate family and company recipients.');
    echo '</p>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('error4' => 'Some aspects of your request failed, but others were successful. Because of the errors, the system did not attempt to send any requested emails.'));
    }

    if ($pupilsightFinanceInvoiceID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
            $sql = "SELECT pupilsightFinanceInvoice.*, companyName, companyContact, companyEmail, companyCCFamily, pupilsightSchoolYear.name as schoolYear, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightFinanceBillingSchedule.name as billingScheduleName, pupilsightFinanceBillingSchedule.invoiceDueDate as billingScheduleInvoiceDueDate
					FROM pupilsightFinanceInvoice 
					JOIN pupilsightSchoolYear ON (pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightFinanceInvoice.pupilsightSchoolYearID)
					LEFT JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) 
					LEFT JOIN pupilsightFinanceBillingSchedule ON (pupilsightFinanceBillingSchedule.pupilsightFinanceBillingScheduleID=pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID)
					LEFT JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightFinanceInvoicee.pupilsightPersonID)
					WHERE pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID 
					AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID
					AND pupilsightFinanceInvoice.status='Pending'";
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
			
			$form = Form::create('invoice', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/invoices_manage_issueProcess.php?'.http_build_query($urlParams));
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
					$form->addHiddenValue('invoiceDueDate', dateConvertBack($guid, $values['billingScheduleInvoiceDueDate']));
			} else {
				$row = $form->addRow();
					$row->addLabel('invoiceDueDate', __('Invoice Due Date'));
					$row->addDate('invoiceDueDate')->required()->readonly();
			}

			$row = $form->addRow();
				$row->addLabel('status', __('Status'));
				$row->addTextField('status')->required()->readonly();

			$row = $form->addRow();
                $row->addLabel('notes', __('Notes'))->description(__('Notes will be displayed on the final invoice and receipt.'));
				$row->addTextArea('notes')->setRows(5);
				
			$form->addRow()->addHeading(__('Fees'));

			$totalFee = getInvoiceTotalFee($pdo, $pupilsightFinanceInvoiceID, $values['status']);
			$row = $form->addRow();
				$row->addLabel('totalFee', __('Total'))->description('<small><i>('.$_SESSION[$guid]['currency'].')</i></small>');
				$row->addTextField('totalFee')->required()->readonly()->setValue(number_format($totalFee, 2));

			$row = $form->addRow();
				$row->addLabel('invoiceTo', __('Invoice To'));
				$row->addTextField('invoiceTo')->required()->readonly();

			$form->addRow()->addHeading(__('Email Invoice'));

			$email = getSettingByScope($connection2, 'Finance', 'email');
			$form->addHiddenValue('email', $email);
			if (empty($email)) {
				$form->addRow()->addAlert(__('An outgoing email address has not been set up under Invoice & Receipt Settings, and so no emails can be sent.'), 'error');
			} else {
				$form->addRow()->addInvoiceEmailCheckboxes('emails[]', 'names[]', $values, $pupilsight->session);
			}

			$row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
