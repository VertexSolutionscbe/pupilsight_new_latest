<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Module\Finance\Forms\FinanceFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightSchoolYearID = isset($_GET['pupilsightSchoolYearID'])? $_GET['pupilsightSchoolYearID'] : '';
    $status = isset($_GET['status'])? $_GET['status'] : '';
    $pupilsightFinanceInvoiceeID = isset($_GET['pupilsightFinanceInvoiceeID'])? $_GET['pupilsightFinanceInvoiceeID'] : '';
    $monthOfIssue = isset($_GET['monthOfIssue'])? $_GET['monthOfIssue'] : '';
    $pupilsightFinanceBillingScheduleID = isset($_GET['pupilsightFinanceBillingScheduleID'])? $_GET['pupilsightFinanceBillingScheduleID'] : '';
    $pupilsightFinanceFeeCategoryID = isset($_GET['pupilsightFinanceFeeCategoryID'])? $_GET['pupilsightFinanceFeeCategoryID'] : '';

    $urlParams = compact('pupilsightSchoolYearID', 'status', 'pupilsightFinanceInvoiceeID', 'monthOfIssue', 'pupilsightFinanceBillingScheduleID', 'pupilsightFinanceFeeCategoryID'); 

    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Invoices'), 'invoices_manage.php', $urlParams)
        ->add(__('Add Fees & Invoices'));

    $error3 = __('Some aspects of your update failed, effecting the following areas:').'<ul>';
    if (!empty($_GET['studentFailCount'])) {
        $error3 .= '<li>'.$_GET['studentFailCount'].' '.__('students encountered problems.').'</li>';
    }
    if (!empty($_GET['invoiceFailCount'])) {
        $error3 .= '<li>'.$_GET['invoiceFailCount'].' '.__('invoices encountered problems.').'</li>';
    }
    if (!empty($_GET['invoiceFeeFailCount'])) {
        $error3 .= '<li>'.$_GET['invoiceFeeFailCount'].' '.__('fee entries encountered problems.').'</li>';
    }
    $error3 .= '</ul>'.__('It is recommended that you remove all pending invoices and try to recreate them.');

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('error3' => $error3));
    }

    echo '<p>';
    echo __('Here you can add fees to one or more students. These fees will be added to an existing invoice or used to form a new invoice, depending on the specified billing schedule and other details.');
    echo '</p>';

    if ($pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $data= array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT name AS schoolYear FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID";
        $result = $pdo->executeQuery($data, $sql);
        $schoolYearName = $result->rowCount() > 0? $result->fetchColumn(0) : '';

        if ($status != '' or $pupilsightFinanceInvoiceeID != '' or $monthOfIssue != '' or $pupilsightFinanceBillingScheduleID != '') {
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/invoices_manage.php&".http_build_query($urlParams)."'>".__('Back to Search Results').'</a>';
            echo '</div>';
        }
        
        $form = Form::create('invoice', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/invoices_manage_addProcess.php?'.http_build_query($urlParams));
        $form->setFactory(FinanceFormFactory::create($pdo));

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $form->addRow()->addHeading(__('Basic Information'));

        $row = $form->addRow();
            $row->addLabel('schoolYear', __('School Year'));
            $row->addTextField('schoolYear')->required()->readonly()->setValue($schoolYearName);

        $row = $form->addRow();
            $row->addLabel('pupilsightFinanceInvoiceeIDs', __('Invoicees'))->append(sprintf(__('Visit %1$sManage Invoicees%2$s to automatically generate missing students.'), "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/invoicees_manage.php'>", '</a>'));
            $row->addSelectInvoicee('pupilsightFinanceInvoiceeIDs', $pupilsightSchoolYearID)->required()->selectMultiple();

        $scheduling = array('Scheduled' => __('Scheduled'), 'Ad Hoc' => __('Ad Hoc'));
        $row = $form->addRow();
            $row->addLabel('scheduling', __('Scheduling'))->description(__('When using scheduled, invoice due date is linked to and determined by the schedule.'));
            $row->addRadio('scheduling')->fromArray($scheduling)->required()->inline()->checked('Scheduled');

        $form->toggleVisibilityByClass('schedulingScheduled')->onRadio('scheduling')->when('Scheduled');
        $form->toggleVisibilityByClass('schedulingAdHoc')->onRadio('scheduling')->when('Ad Hoc');

        $row = $form->addRow()->addClass('schedulingScheduled');
            $row->addLabel('pupilsightFinanceBillingScheduleID', __('Billing Schedule'));
            $row->addSelectBillingSchedule('pupilsightFinanceBillingScheduleID', $pupilsightSchoolYearID)->required()->selected($pupilsightFinanceBillingScheduleID);

        $row = $form->addRow()->addClass('schedulingAdHoc');
            $row->addLabel('invoiceDueDate', __('Invoice Due Date'))->description(__('For fees added to existing invoice, specified date will override existing due date.'));
            $row->addDate('invoiceDueDate')->required();

        $row = $form->addRow();
            $row->addLabel('notes', __('Notes'))->description(__('Notes will be displayed on the final invoice and receipt.'));
            $row->addTextArea('notes')->setRows(5);

        $form->addRow()->addHeading(__('Fees'));

        // CUSTOM BLOCKS
        
        // Fee selector
        $feeSelector = $form->getFactory()->createSelectFee('addNewFee', $pupilsightSchoolYearID)->addClass('addBlock');

        // Block template
        $blockTemplate = $form->getFactory()->createTable()->setClass('blank');
            $row = $blockTemplate->addRow();
                $row->addTextField('name')->setClass('w-full pr-10 title')->required()->placeholder(__('Fee Name'))
                    ->append('<input type="hidden" id="pupilsightFinanceFeeID" name="pupilsightFinanceFeeID" value="">')
                    ->append('<input type="hidden" id="feeType" name="feeType" value="">');
                
            $col = $blockTemplate->addRow()->addColumn()->addClass('flex mt-1');
                $col->addSelectFeeCategory('pupilsightFinanceFeeCategoryID')
                    ->setClass('w-48 m-0');

                $col->addCurrency('fee')
                    ->setClass('w-48 ml-1')
                    ->required()
                    ->placeholder(__('Value').(!empty($_SESSION[$guid]['currency'])? ' ('.$_SESSION[$guid]['currency'].')' : ''));
                
            $col = $blockTemplate->addRow()->addClass('showHide w-full')->addColumn();
                $col->addLabel('description', __('Description'));
                $col->addTextArea('description')->setRows('auto')->setClass('w-full float-none m-0');

        // Custom Blocks for Fees
        $row = $form->addRow();
            $customBlocks = $row->addCustomBlocks('feesBlock', $pupilsight->session)
                ->fromTemplate($blockTemplate)
                ->settings(array('inputNameStrategy' => 'string', 'addOnEvent' => 'change', 'sortable' => true))
                ->placeholder(__('Fees will be listed here...'))
                ->addToolInput($feeSelector)
                ->addBlockButton('showHide', __('Show/Hide'), 'plus.png');

        // Add predefined block data (for templating new blocks, triggered with the feeSelector)
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightFinanceFeeID as groupBy, pupilsightFinanceFeeID, name, description, fee, pupilsightFinanceFeeCategoryID FROM pupilsightFinanceFee ORDER BY name";
        $result = $pdo->executeQuery($data, $sql);
        $feeData = $result->rowCount() > 0? $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

        $customBlocks->addPredefinedBlock('Ad Hoc Fee', array('feeType' => 'Ad Hoc', 'pupilsightFinanceFeeID' => 0));
        foreach ($feeData as $pupilsightFinanceFeeID => $data) {
            $customBlocks->addPredefinedBlock($pupilsightFinanceFeeID, $data + array('feeType' => 'Standard', 'readonly' => ['name', 'fee', 'description', 'pupilsightFinanceFeeCategoryID']) );
        }

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();
    }
}

