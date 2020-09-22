<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_manage_copy.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $id = $_GET['id'];

    $data = array('id' => $id);
    $sql = 'SELECT * FROM fn_fee_structure WHERE id=:id';
    $result = $connection2->prepare($sql);
    $result->execute($data);
    $values = $result->fetch();
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Discount Copy'), 'fee_discount_rule_manage.php')
        ->add(__('Add Fee Discount Copy'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_type_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Copy Fee Structure');
    echo '</h2>';

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }

    $sqlf = 'SELECT pupilsightSchoolFinanceYearID, name FROM pupilsightSchoolFinanceYear ';
    $resultf = $connection2->query($sqlf);
    $financial = $resultf->fetchAll();

    $financialData = array();
    foreach ($financial as $ft) {
        $financialData[$ft['pupilsightSchoolFinanceYearID']] = $ft['name'];
    }

    
    $sqlse = 'SELECT id, series_name FROM fn_fee_series ';
    $resultse = $connection2->query($sqlse);
    $feeSeries = $resultse->fetchAll();

    $feeSeriesData = array();
    $feeSeriesData1 = array(''=>'Select Invoice');
    $feeSeriesData2 = array();
    foreach ($feeSeries as $fs) {
        $feeSeriesData2[$fs['id']] = $fs['series_name'];
    }
    $feeSeriesData = $feeSeriesData1 + $feeSeriesData2;

    $sqlh = 'SELECT id, name FROM fn_fees_head ';
    $resulth = $connection2->query($sqlh);
    $feeHead = $resulth->fetchAll();

    $feeHeadData = array();
    $feeHeadData1 = array(''=>'Select Account Head');
    $feeHeadData2 = array();
    foreach ($feeHead as $fd) {
        $feeHeadData2[$fd['id']] = $fd['name'];
    }
    $feeHeadData = $feeHeadData1 + $feeHeadData2;

    $sqlfr = 'SELECT id, name FROM fn_fees_fine_rule ';
    $resultfr = $connection2->query($sqlfr);
    $fineRule = $resultfr->fetchAll();

    $fineRuleData = array();
    $fineRuleData1 = array(''=>'Select Fine');
    $fineRuleData2 = array();
    foreach ($fineRule as $fr) {
        $fineRuleData2[$fr['id']] = $fr['name'];
    }
    $fineRuleData = $fineRuleData1 + $fineRuleData2;

    $sqldr = 'SELECT id, name FROM fn_fees_discount ';
    $resultdr = $connection2->query($sqldr);
    $feeDiscount = $resultdr->fetchAll();

    $feeDiscountData = array();
    $feeDiscountData1 = array(''=>'Select Discount');
    $feeDiscountData2 = array();
    foreach ($feeDiscount as $dr) {
        $feeDiscountData2[$dr['id']] = $dr['name'];
    }
    $feeDiscountData = $feeDiscountData1 + $feeDiscountData2;


    $datac = array('fn_fee_structure_id' => $id);
    $sqlc = 'SELECT * FROM fn_fee_structure_item WHERE fn_fee_structure_id=:fn_fee_structure_id';
    $resultc = $connection2->prepare($sqlc);
    $resultc->execute($datac);
    $childvalues = $resultc->fetchAll();
    $fItemIds = array();
    foreach($childvalues as $chv){
        $fItemIds[] = $chv['fn_fee_item_id'];
    }
    $feeItemIds = implode(',', $fItemIds);
    if(!empty($childvalues)){
        $last =  end($childvalues);
        $lastId = $last['id'];
    } else {
        $lastId = '1';
    }
    
    $sqli = 'SELECT id, name FROM fn_fee_items ';
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $feeItemData = array();
    $feeItemData1 = array(''=>'Select Fee Item');
    $feeItemData2 = array();
    foreach ($feeItem as $dt) {
        $feeItemData2[$dt['id']] = $dt['name'];
    }
    $feeItemData = $feeItemData1 + $feeItemData2;

    $form = Form::create('copyFeeStructureForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_structure_manage_copyProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addHiddenValue('id', $id);
    $row = $form->addRow();
        $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
        $row->addTextField('name')->required();

    $row = $form->addRow();
        $row->addLabel('name', __('Academic Year'));
        $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required();
       
    $row = $form->addRow();
        $row->addLabel('due_date', __('Due Date'))->addClass('dte');
        $row->addDate('due_date'); 
        
    $row = $form->addRow();
        $row->addLabel('invoice_title', __('Title of Invoice'));
        $row->addTextField('invoice_title')->required()->setValue($values['invoice_title']);  
        
    $row = $form->addRow();
        $row->addLabel('pupilsightSchoolFinanceYearID', __('Financial Year'));
        $row->addSelect('pupilsightSchoolFinanceYearID')->fromArray($financialData)->required()->selected($values['pupilsightSchoolFinanceYearID']);
        
    $row = $form->addRow();
        $row->addLabel('fn_fees_head_id', __('Account Head'));
        $row->addSelect('fn_fees_head_id')->fromArray($feeHeadData)->required()->selected($values['fn_fees_head_id']);    

    $row = $form->addRow();
        $row->addLabel('fn_fees_fine_rule_id', __('Fine Rule'));
        $row->addSelect('fn_fees_fine_rule_id')->fromArray($fineRuleData)->selected($values['fn_fees_fine_rule_id']);    

    $row = $form->addRow();
        $row->addLabel('fn_fees_discount_id', __('Discount Rule'));
        $row->addSelect('fn_fees_discount_id')->fromArray($feeDiscountData)->selected($values['fn_fees_discount_id']);
        

     
    $row = $form->addRow();
        $row->addFooter();
        //$row->addSubmit();
        $row->addContent('<a id="copyFeeStructure"  class=" btn btn-primary" >Submit</a> ');
    echo $form->getOutput();

}
