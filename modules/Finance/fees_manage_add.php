<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fees_manage_add.php') == false) {
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
        ->add(__('Add Fee'));     

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fees_manage_edit.php&pupilsightFinanceFeeID='.$_GET['editID'].'&search='.$_GET['search'].'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $search = $_GET['search'];
    if ($pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        if ($search != '') {
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/fees_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
            echo '</div>';
        }

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/fees_manage_addProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        try {
            $dataYear = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sqlYear = 'SELECT name AS schoolYear FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $resultYear = $connection2->prepare($sqlYear);
            $resultYear->execute($dataYear);
        } catch (PDOException $e) {
            $form->addRow()->addAlert($e->getMessage(), 'error');
        }
        if ($resultYear->rowCount() == 1) {
            $values = $resultYear->fetch();
            $row = $form->addRow();
                $row->addLabel('schoolYear', __('School Year'));
                $row->addTextField('schoolYear')->maxLength(20)->required()->readonly()->setValue($values['schoolYear']);
        }

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

        echo $form->getOutput();
    }
}
?>
