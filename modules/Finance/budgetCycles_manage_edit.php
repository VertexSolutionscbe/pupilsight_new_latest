<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/budgetCycles_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Budget Cycles'), 'budgetCycles_manage.php')
        ->add(__('Edit Budget Cycle'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return']);
    }

    //Check if school year specified
    $pupilsightFinanceBudgetCycleID = $_GET['pupilsightFinanceBudgetCycleID'];
    if ($pupilsightFinanceBudgetCycleID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
            $sql = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID';
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
            
            $form = Form::create('budgetCycle', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/budgetCycles_manage_editProcess.php?pupilsightFinanceBudgetCycleID='.$pupilsightFinanceBudgetCycleID);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue("address", $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addHeading(__("Basic Information"));

            $row = $form->addRow();
                $row->addLabel("name", __("Name"))->description(__("Must be unique."));
                $row->addTextField("name")->required()->maxLength(7);

            $statusTypes = array(
                'Upcoming' => __("Upcoming"),
                'Current' =>  __("Current"),
                'Past' => __("Past")
            );
            
            $row = $form->addRow();
                $row->addLabel("status", __("Status"));
                $row->addSelect("status")->fromArray($statusTypes);

            $row = $form->addRow();
                $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
                $row->addSequenceNumber('sequenceNumber', 'pupilsightFinanceBudgetCycle', $values['sequenceNumber'])->required()->maxLength(3);

            $row = $form->addRow();
                $row->addLabel("dateStart", __("Start Date"))->description(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
                $row->addDate("dateStart")->required();

            $row = $form->addRow();
                $row->addLabel("dateEnd", __("End Date"))->description(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
                $row->addDate("dateEnd")->required();
            
            $row = $form->addRow();
                $row->addHeading(__("Budget Allocations"));

            try {
                $dataBudget = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
                $sqlBudget = 'SELECT pupilsightFinanceBudget.*, value FROM pupilsightFinanceBudget LEFT JOIN pupilsightFinanceBudgetCycleAllocation ON (pupilsightFinanceBudgetCycleAllocation.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID AND pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID) ORDER BY name';
                $resultBudget = $connection2->prepare($sqlBudget);
                $resultBudget->execute($dataBudget);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($resultBudget->rowCount() < 1) {
                $form->addRow()->addAlert(__('There are no records to display.'), 'error');
            } else {
                while ($rowBudget = $resultBudget->fetch()) {
                    $row = $form->addRow();
                        $row->addLabel($rowBudget['pupilsightFinanceBudgetID'], $rowBudget['name']);
                        $row->addCurrency($rowBudget['pupilsightFinanceBudgetID'])->setName('values[]')->required()->maxLength(15)->setValue((is_null($rowBudget['value'])) ? '0.00' : $rowBudget['value']);
                    $form->addHiddenValue('pupilsightFinanceBudgetIDs[]', $rowBudget['pupilsightFinanceBudgetID']);
                }
            }

            $form->loadAllValuesFrom($values);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            print $form->getOutput();
        }
    }
}
?>
