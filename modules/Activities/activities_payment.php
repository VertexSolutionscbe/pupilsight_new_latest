<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_payment.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Generate Invoices'));
    
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h2>';
    echo __('Invoices Not Yet Generated');
    echo '</h2>';
    echo '<p>';
    echo sprintf(__('The list below shows students who have been accepted for an activity in the current year, who have yet to have invoices generated for them. You can generate invoices to a given %1$sBilling Schedule%2$s, or you can simulate generation (e.g. mark them as generated, but not actually produce an invoice).'), "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/billingSchedule_manage.php'>", '</a>');
    echo '</p>';

    try {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'today' => date('Y-m-d'));
        $sql = "SELECT pupilsightActivityStudentID, pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightRollGroup.nameShort AS rollGroup, pupilsightActivityStudent.status, payment, paymentType, pupilsightActivity.name, programStart, programEnd FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today) AND pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightActivityStudent.status='Accepted' AND payment>0 AND invoiceGenerated='N' ORDER BY surname, preferredName, name";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        $lastPerson = '';

        $form = Form::create('generateInvoices', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/activities_paymentProcessBulk.php');
        $form->addConfirmation(__('Are you sure you wish to process this action? It cannot be undone.'));
        $form->setClass('w-full blank');
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sql = "SELECT pupilsightFinanceBillingScheduleID as value, name FROM pupilsightFinanceBillingSchedule WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
        $resultSchedule = $pdo->executeQuery($data, $sql);

        $billingSchedules = ($resultSchedule->rowCount() > 0)? $resultSchedule->fetchAll(\PDO::FETCH_KEY_PAIR) : array();
        $billingSchedules = array_map(function($item) {
            return sprintf(__('Generate Invoices To %1$s'), $item);
        }, $billingSchedules);
        $defaultActions = array('Generate Invoice - Simulate' => __('Generate Invoice - Simulate'));

        $row = $form->addRow();
            $bulkAction = $row->addColumn()->addClass('flex justify-end items-center');
            $bulkAction->addSelect('action')
                ->fromArray($billingSchedules)
                ->fromArray($defaultActions)
                ->required()
                ->setClass('mediumWidth floatNone')
                ->placeholder(__('Select action'));
            $bulkAction->addSubmit(__('Go'));
        
        $table = $form->addRow()->addTable()->addClass('colorOddEven');

        $header = $table->addHeaderRow();
        $header->addContent(__('Roll Group'));
        $header->addContent(__('Student'));
        $header->addContent(__('Activity'));
        $header->addContent(__('Cost'))->append('<br/><span class="small emphasis">'.$_SESSION[$guid]['currency'].'</span>');
        $header->addCheckbox('checkall')->setClass('floatNone textCenter checkall');

        while ($student = $result->fetch()) {
            $pupilsightActivityStudentID = $student['pupilsightActivityStudentID'];

            $row = $table->addRow();
            $row->addContent($student['rollGroup']);
            $row->addContent(formatName('', $student['preferredName'], $student['surname'], 'Student', true));
            $row->addContent($student['name']);
            $row->addCurrency("payment[$pupilsightActivityStudentID]")->required()->setValue($student['payment']);
            $row->addCheckbox("pupilsightActivityStudentID[$pupilsightActivityStudentID]")->setValue($student['pupilsightActivityStudentID'])->setClass('');
        }
        
        echo $form->getOutput();
    }

    echo '<h2>';
    echo __('Invoices Generated');
    echo '</h2>';

    try {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'today' => date('Y-m-d'));
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, studentID, surname, preferredName, pupilsightRollGroup.nameShort AS rollGroup, pupilsightActivityStudent.status, payment, pupilsightActivity.name, programStart, programEnd, pupilsightFinanceInvoiceID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today) AND pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightActivityStudent.status='Accepted' AND payment>0 AND invoiceGenerated='Y' ORDER BY surname, preferredName, name";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        $lastPerson = '';

        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Roll Group');
        echo '</th>';
        echo '<th>';
        echo __('Student');
        echo '</th>';
        echo '<th>';
        echo __('Activity');
        echo '</th>';
        echo '<th>';
        echo __('Invoice Number').'<br/>';
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

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo $row['rollGroup'];
            echo '</td>';
            echo '<td>';
            echo formatName('', $row['preferredName'], $row['surname'], 'Student', true);
            echo '</td>';
            echo '<td>';
            echo $row['name'];
            echo '</td>';
            echo '<td>';
            $invoiceNumber = getSettingByScope($connection2, 'Finance', 'invoiceNumber');
            if ($invoiceNumber == 'Person ID + Invoice ID') {
                echo ltrim($row['pupilsightPersonID'], '0').'-'.ltrim($row['pupilsightFinanceInvoiceID'], '0');
            } elseif ($invoiceNumber == 'Student ID + Invoice ID') {
                echo ltrim($row['studentID'], '0').'-'.ltrim($row['pupilsightFinanceInvoiceID'], '0');
            } else {
                echo ltrim($row['pupilsightFinanceInvoiceID'], '0');
            }
            echo '</td>';
            echo '</tr>';

            $lastPerson = $row['pupilsightPersonID'];
        }
        if ($count == 0) {
            echo "<tr class=$rowNum>";
            echo '<td colspan=4>';
            echo __('There are no records to display.');
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
?>
