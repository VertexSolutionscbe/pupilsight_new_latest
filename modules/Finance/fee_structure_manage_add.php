<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Structure'), 'fee_structure_manage.php')
        ->add(__('Add Fee Structure'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_structure_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Fee Structure');
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

      //get finacial_year 
    if (date('m') <= 6) {
    $financial_year = (date('Y')-1) . '-' . date('Y');
    } else {
    $financial_year = date('Y') . '-' . (date('y') + 1);
    }
    $finacialYearID='';
    
   // ends finacial year 
    $financialData = array();
    foreach ($financial as $ft) {
        $financialData[$ft['pupilsightSchoolFinanceYearID']] = $ft['name'];
        if($ft['name']==$financial_year){
            $finacialYearID=$ft['pupilsightSchoolFinanceYearID'];
        }
    }
    $sqli = 'SELECT id, name FROM fn_fee_items WHERE pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" ';
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

    
    
    $form = Form::create('feeStructureForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_structure_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

   
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('Name'));
            $col->addTextField('name')->addClass('txtfield')->required();

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('invoice_title', __('Title of Invoice'));
            $col->addTextField('invoice_title')->required();    

        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');   
    
    $row = $form->addRow();
        
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelect('pupilsightSchoolYearID')->setID('feeItems')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID);    

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolFinanceYearID', __('Financial Year'));
            $col->addSelect('pupilsightSchoolFinanceYearID')->fromArray($financialData)->selected($finacialYearID)->required();
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');           
    
    $row = $form->addRow();
    
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('fn_fees_head_id', __('Account Head'));
            $col->addSelect('fn_fees_head_id')->fromArray($feeHeadData)->required();    

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('due_date', __('Due Date'))->addClass('dte');
            $col->addDate('due_date')->setId('dueDate');
        
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
            
        $row = $form->addRow();
    
            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('seq_installment_NO', __('Seq/Installment No'));
                $col->addTextField('seq_installment_NO')->addClass('txtfield  numfield ')->required();    

               
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('amount_editable', __(''));
        $col->addCheckbox('amount_editable')->description(__('<b>Transaction Amount editable</b>'))->setValue('1');   

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('display_fee_item', __(''));
        $col->addCheckbox('display_fee_item')->description(__('<b>Do Not display Fee item</b>'))->setValue('2');    
        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('is_concat_invoice', __(''));
        $col->addCheckbox('is_concat_invoice')->description(__('<b>Concat Invoice</b>'))->setValue('1');    

        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');       
      

    $type = array('N'=>'No','Y'=>'Yes');
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('fixed_fine_type', __('Fee Structure Item'));
            $col->addTextField('')->setClass('hiddencol');
        
        $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');     
        
        $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt');
            //  $col->addButton(__('Add'))->setID('addFeeStructureItem')->addData('cid', '1')->addData('disid', 'nodata')->addClass('bttnsubmt bg-dodger-blue fsize lftbutt');
             $col->addContent('<a style="cursor:pointer;" data-cid="1" data-disid="nodata" id="addFeeStructureItem" class="btn btn-primary lftbutt">Add</a>');

       

       $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine');       
        $col = $row->addColumn()->setClass('newdes nobrdbtm before_academic');
        $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
        $col->addSelect('fn_fee_item_id[1]')->setId('feeStructureItemDisableId')->fromArray($feeItemData)->addClass('txtfield allFeeItemId')->required();

        $col = $row->addColumn()->setClass('newdes nobrdbtm after_academic hidediv')->addClass('dte');
        $col->addLabel('fn_fee_item_id', __('Fee Item'));
        $col->addSelect('fn_fee_item_id[1]')->setId('feeitemType')->setId('feeitemType');
        
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('amount', __('Amount <span id="totalAmount"></span>'))->addClass('dte');
            $col->addTextField('amount[1]')->addClass('txtfield kountseat numfield kountAmt')->required();

        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('tax', __('Tax'))->addClass('dte');
            $col->addSelect('tax[1]')->fromArray($type)->addClass('txtfield taxOptionSelect')->addData('id', __('1'))->required();    

        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('tax_percent', __('Tax Percent'))->addClass('dte');
            $col->addTextField('tax_percent[1]')->setId('taxPercent1')->addClass('txtfield kountseat szewdt numfield')->readonly()->required(); 
            $col->addLabel('', __(''))->addClass('dte');             
       
    
                      
        
    $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addContent('<a id="saveFeeStructure" class="btn btn-primary" style="float:right;">Submit</a>');

    echo $form->getOutput();

}

?>

<style>
    #lastseatdiv {
        margin-top:20px;
    }

    /* #seatdiv {
        margin-bottom:20px !important;
    } */
</style>

<script>
    $(document).on('click', '#saveFeeStructure', function(){
        var val = $("#fn_fees_fine_rule_id").val();
        if(val != ''){
            var ddate = $("#dueDate").val();
            if(ddate == ''){
                $("#dueDate").addClass('erroralert');
                alert('You have to Add Due Date');
                return false;
            } else {
                $("#dueDate").removeClass('erroralert');
                $("#feeStructureForm").submit();
            }
        } else {
            $("#feeStructureForm").submit();
        }
    });
</script>
