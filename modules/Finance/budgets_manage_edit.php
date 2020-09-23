<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/budgets_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Budgets'),'budgets_manage.php')
        ->add(__('Edit Budget'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('error4' => __('Your request failed due to an attachment error.')));
    }

    //Check if school year specified
    $pupilsightFinanceBudgetID = $_GET['pupilsightFinanceBudgetID'];
    if ($pupilsightFinanceBudgetID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
            $sql = 'SELECT * FROM pupilsightFinanceBudget WHERE pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/budgets_manage_editProcess.php?pupilsightFinanceBudgetID=$pupilsightFinanceBudgetID");
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('General Settings'));

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
                $row->addTextField('name')->maxLength(100)->required();

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
                $row->addTextField('nameShort')->maxLength(14)->required();

            $row = $form->addRow();
                $row->addLabel('active', __('Active'));
                $row->addYesNo('active')->required();

            $categories = getSettingByScope($connection2, 'Finance', 'budgetCategories');
            if (empty($categories)) {
                $categories = 'Other';
            }
            $row = $form->addRow();
                $row->addLabel('category', __('Category'));
                $row->addSelect('category')->fromString($categories)->placeholder()->required();

            $form->addRow()->addHeading(__('Current Staff'));

            $data = array('pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
            $sql = "SELECT preferredName, surname, pupilsightFinanceBudgetPerson.* FROM pupilsightFinanceBudgetPerson JOIN pupilsightPerson ON (pupilsightFinanceBudgetPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID AND pupilsightPerson.status='Full' ORDER BY FIELD(access,'Full','Write','Read'), surname, preferredName";

            $results = $pdo->executeQuery($data, $sql);

            if ($results->rowCount() == 0) {
                $form->addRow()->addAlert(__('There are no records to display.'), 'error');
            } else {
                $form->addRow()->addContent('<b>'.__('Warning').'</b>: '.__('If you delete a member of staff, any unsaved changes to this record will be lost!'))->wrap('<i>', '</i>');

                $table = $form->addRow()->addTable()->addClass('colorOddEven');

                $header = $table->addHeaderRow();
                $header->addContent(__('Name'));
                $header->addContent(__('Access'));
                $header->addContent(__('Action'));

                while ($staff = $results->fetch()) {
                    $row = $table->addRow();
                    $row->addContent(formatName('', $staff['preferredName'], $staff['surname'], 'Staff', true, true));
                    $row->addContent($staff['access']);
                    $row->addContent("<a onclick='return confirm(\"".__('Are you sure you wish to delete this record?')."\")' href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/budgets_manage_edit_staff_deleteProcess.php?address='.$_GET['q'].'&pupilsightFinanceBudgetPersonID='.$staff['pupilsightFinanceBudgetPersonID']."&pupilsightFinanceBudgetID=$pupilsightFinanceBudgetID'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a>");
                }
            }

            $form->addRow()->addHeading(__('New Staff'));

            $row = $form->addRow();
                $row->addLabel('staff', __('Staff'));
                $row->addSelectStaff('staff')->selectMultiple();

            $access = array(
                "Full" => __("Full"),
                "Write" => __("Write"),
                "Read" => __("Read")
            );
            $row = $form->addRow();
                $row->addLabel('access', 'Access');
                $row->addSelect('access')->fromArray($access);

            $form->loadAllValuesFrom($values);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
?>
