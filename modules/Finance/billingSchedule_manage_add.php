<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Finance/billingSchedule_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];

    $urlParams = compact('pupilsightSchoolYearID');
    
    $page->breadcrumbs
        ->add(__('Manage Billing Schedule'), 'billingSchedule_manage.php', $urlParams)
        ->add(__('Add Entry'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/billingSchedule_manage_edit.php&pupilsightFinanceBillingScheduleID='.$_GET['editID'].'&search='.$_GET['search'].'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
    }

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    //Check if school year specified
    $search = $_GET['search'];
    if ($pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        if ($search != '') {
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/billingSchedule_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
            echo '</div>';
        }

        $form = Form::create("scheduleManageAdd", $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/billingSchedule_manage_addProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");

        $form->addHiddenValue("address", $_SESSION[$guid]['address']);

        $row = $form->addRow();
        	$row->addLabel("yearName", __("School Year"))->description(__("This value cannot be changed."));
        	$row->addTextField("yearName")->setValue($_SESSION[$guid]['pupilsightSchoolYearName'])->readonly(true)->required();

        $row = $form->addRow();
        	$row->addLabel("name", __("Name"));
        	$row->addTextField("name")->maxLength(100)->required();

        $row = $form->addRow();
        	$row->addLabel("active", __("Active"));
        	$row->addYesNo("active")->required();

        $row = $form->addRow();
        	$row->addLabel("description", __("Description"));
        	$row->addTextArea("description")->setRows(5);

        $row = $form->addRow();
        	$row->addLabel("invoiceIssueDate", __('Invoice Issue Date'))->description(__('Intended issue date.').'<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
        	$row->addDate('invoiceIssueDate')->required();

        $row = $form->addRow();
			$row->addLabel('invoiceDueDate', __('Invoice Due Date'))->description(__('Final payment date.').'<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
			$row->addDate('invoiceDueDate')->required();

        $row = $form->addRow();
        	$row->addFooter();
        	$row->addSubmit();

        echo $form->getOutput();
    }
}
?>
