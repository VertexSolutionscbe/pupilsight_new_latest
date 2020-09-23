<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fees_manage_edit.php') == false) {
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
        ->add(__('Manage Fees'),'fees_manage.php', $urlParams)
        ->add(__('Edit Fee'));         

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightFinanceFeeID = $_GET['pupilsightFinanceFeeID'];
    $search = $_GET['search'];
    if ($pupilsightFinanceFeeID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceFeeID' => $pupilsightFinanceFeeID);
            $sql = 'SELECT pupilsightFinanceFee.*, pupilsightSchoolYear.name AS schoolYear
                FROM pupilsightFinanceFee
                    JOIN pupilsightSchoolYear ON (pupilsightFinanceFee.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                WHERE pupilsightFinanceFee.pupilsightSchoolYearID=:pupilsightSchoolYearID
                    AND pupilsightFinanceFeeID=:pupilsightFinanceFeeID';
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

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/fees_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/fees_manage_editProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightFinanceFeeID', $pupilsightFinanceFeeID);

            $row = $form->addRow();
                $row->addLabel('schoolYear', __('School Year'));
                $row->addTextField('schoolYear')->maxLength(20)->required()->readonly()->setValue($values['schoolYear']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->maxLength(100)->required();

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'));
                $row->addTextField('nameShort')->maxLength(6)->required();

            $row = $form->addRow();
                $row->addLabel('active', __('Active'));
                $row->addYesNo('active')->required();

            $row = $form->addRow();
                $row->addLabel('description', __('Description'));
                $row->addTextArea('description');

            $data = array();
            $sql = "SELECT pupilsightFinanceFeeCategoryID AS value, name FROM pupilsightFinanceFeeCategory WHERE active='Y' AND NOT pupilsightFinanceFeeCategoryID=1 ORDER BY name";
            $row = $form->addRow();
                $row->addLabel('pupilsightFinanceFeeCategoryID', __('Category'));
                $row->addSelect('pupilsightFinanceFeeCategoryID')->fromQuery($pdo, $sql, $data)->fromArray(array('1' => __('Other')))->required()->placeholder();

            $row = $form->addRow();
                $row->addLabel('fee', __('Fee'))
                    ->description(__('Numeric value of the fee.'));
                $row->addCurrency('fee')->required();

            $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
?>
