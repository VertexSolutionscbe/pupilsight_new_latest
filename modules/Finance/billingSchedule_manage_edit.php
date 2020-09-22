<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Finance/billingSchedule_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //Check if school year specified
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    
    $urlParams = compact('pupilsightSchoolYearID');
    
    $page->breadcrumbs
        ->add(__('Manage Billing Schedule'), 'billingSchedule_manage.php', $urlParams)
        ->add(__('Edit Entry'));    

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightFinanceBillingScheduleID = $_GET['pupilsightFinanceBillingScheduleID'];
    $search = $_GET['search'];
    if ($pupilsightFinanceBillingScheduleID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceBillingScheduleID' => $pupilsightFinanceBillingScheduleID);
            $sql = 'SELECT * FROM pupilsightFinanceBillingSchedule WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID';
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
            $resultRow = $result->fetch();

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/billingSchedule_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $yearName = '';
            try {
                $dataYear = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                $sqlYear = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $resultYear = $connection2->prepare($sqlYear);
                $resultYear->execute($dataYear);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($resultYear->rowCount() == 1) {
                $rowYear = $resultYear->fetch();
                $yearName = $rowYear['name'];
            }

            $form = Form::create("edit", $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/billingSchedule_manage_editProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");

            $form->addHiddenValue("pupilsightFinanceBillingScheduleID", $pupilsightFinanceBillingScheduleID);
            $form->addHiddenValue("address", $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel("yearName", __("School Year"))->description(__("This value cannot be changed."));
                $row->addTextField("yearName")->setValue($yearName)->readonly(true)->required();

            $row = $form->addRow();
                $row->addLabel("name", __("Name"));
                $row->addTextField("name")->setValue(htmlprep($resultRow['name']))->maxLength(100)->required();

            $row = $form->addRow();
                $row->addLabel("active", __("Active"));
                $row->addYesNo("active")->selected($resultRow['active'])->required();

            $row = $form->addRow();
                $row->addLabel("description", __("Description"));
                $row->addTextArea("description")->setValue(htmlPrep($resultRow['description']))->setRows(5);

            $row = $form->addRow();
                $row->addLabel("invoiceIssueDate", __('Invoice Issue Date'))->description(__('Intended issue date.').'<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
                $row->addDate('invoiceIssueDate')->setValue(dateConvertBack($guid, $resultRow['invoiceIssueDate']))->required();

            $row = $form->addRow();
                $row->addLabel('invoiceDueDate', __('Invoice Due Date'))->description(__('Final payment date.').'<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
                $row->addDate('invoiceDueDate')->setValue(dateConvertBack($guid, $resultRow['invoiceDueDate']))->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            print $form->getOutput();
        }
    }
}
