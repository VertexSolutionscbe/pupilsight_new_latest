<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Invoice'), 'invoice_manage.php')
        ->add(__('Add Invoice'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/invoice_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Invoice');
    echo '</h2>';

    
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
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

    $sqlse = 'SELECT id, series_name FROM fn_fee_series ';
    $resultse = $connection2->query($sqlse);
    $feeSeries = $resultse->fetchAll();

    $feeSeriesData = array();
    $feeSeriesData1 = array(''=>'Select Invoice Series');
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

    
    
    $form = Form::create('add_invoice_collection_process_form', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/add_invoice_collection_process.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

   
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('title', __('Invoice Title'));
            $col->addTextField('title')->addClass('txtfield')->required();

        $col = $row->addColumn()->setClass('newdes');
            /*$col->addLabel('transaction_editable', __('Transaction editable'));
            $col->addCheckbox('transaction_editable'); */   
            $col->addContent('<br/><label><input type="checkbox" name="amount_editable"> Transaction editable </label> <label>&nbsp;&nbsp;<input type="checkbox" name="display_fee_item"> Do Not display Fee item </label>');
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');   

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('fn_fees_head_id', __('Account Head'));
            $col->addSelect('fn_fees_head_id')->fromArray($feeHeadData)->required(); 
            
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('due_date', __('Due Date'))->addClass('dte');
            $col->addDate('due_date');

        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');     


    $row = $form->addRow();
        
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('inv_fn_fee_series_id', __('Invoice Series'));
            $col->addSelect('inv_fn_fee_series_id')->fromArray($feeSeriesData)->required();        

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('rec_fn_fee_series_id', __('Receipt Series'));
            $col->addSelect('rec_fn_fee_series_id')->fromArray($feeSeriesData)->required();    
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');           
    
    //$row = $form->addRow();
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');           
      
    $row = $form->addRow();
        
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('fn_fees_fine_rule_id', __('Fine Rule'));
            $col->addSelect('fn_fees_fine_rule_id')->fromArray($fineRuleData);    

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('fn_fees_discount_id', __('Discount Rule'));
            $col->addSelect('fn_fees_discount_id')->fromArray($feeDiscountData);
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');           
      

    $type = array('Y'=>'Yes','N'=>'No');
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('fixed_fine_type', __('Invoice Item'));
            $col->addTextField('')->setClass('hiddencol');
            $col->addContent('<br/><br/>');
        
        $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');     
        
        $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt');
             $col->addButton(__('Add'))->setID('addInvoiceItem')->addData('cid', '1')->addData('disid', 'nodata')->addClass('bttnsubmt bg-dodger-blue fsize lftbutt');

       

    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine');
       
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
            $col->addSelect('fn_fee_item_id[1]')->setId('feeStructureItemDisableId')->fromArray($feeItemData)->addClass('txtfield allFeeItemId');
            
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('description', __('Description'))->addClass('dte');
            $col->addTextField('description[1]')->addClass('txtfield kountseat ');

        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('amount', __('Amount'))->addClass('dte');
            $col->addTextField('amount[1]')->setId('invamt')->addClass('txtfield kountseat numfield');

        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('tax', __('Tax'))->addClass('dte');
            //$col->addSelect('tax[1]')->fromArray($type)->addClass('txtfield'); 
            $col->addTextField('tax[1]')->addClass('txtfield kountseat numfield');
            
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('discount', __('Discount'))->addClass('dte');
            $col->addTextField('discount[1]')->addClass('txtfield kountseat szewdt2 numfield ');    

        // $col = $row->addColumn()->setClass('newdes nobrdbtm ');
        //     $col->addLabel('total_amount', __('Total Amount'))->addClass('dte');
        //     $col->addTextField('total_amount[1]')->addClass('txtfield kountseat szewdt2 numfield'); 
        //     $col->addLabel('', __(''))->addClass('dte');             
       
    
                      
        
    $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addContent('<a id="addInvoiceStnButton" class=" btn btn-primary">Submit</a>'); 

    echo $form->getOutput();

}

?>

<style>
    #lastseatdiv {
        margin-top:25px;
    }

    #seatdiv {
        margin-bottom:20px;
    }
</style>