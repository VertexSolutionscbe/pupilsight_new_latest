<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Structure'), 'fee_structure_manage.php')
        ->add(__('Edit Fee Structure'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    

    //Check if school year specified
    $id = $_GET['id'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fee_structure WHERE id=:id';
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

            echo '<h2>';
            echo __('Edit Fee Structure');
            echo '</h2>';

           
            $form = Form::create('feeStructureForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_structure_manage_editProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('name', __('Name'));
                    $col->addTextField('name')->addClass('txtfield')->required()->setValue($values['name']);

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('invoice_title', __('Title of Invoice'));
                    $col->addTextField('invoice_title')->required()->setValue($values['invoice_title']);    

                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');   
            
            $row = $form->addRow();
                
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
                    $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($values['pupilsightSchoolYearID']);    

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('pupilsightSchoolFinanceYearID', __('Financial Year'));
                    $col->addSelect('pupilsightSchoolFinanceYearID')->fromArray($financialData)->required()->selected($values['pupilsightSchoolFinanceYearID']);
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');           
            
            $row = $form->addRow();
            
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('fn_fees_head_id', __('Account Head'));
                    $col->addSelect('fn_fees_head_id')->fromArray($feeHeadData)->required()->selected($values['fn_fees_head_id']);    

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('due_date', __('Due Date'))->addClass('dte');
                    if($values['due_date'] != '1970-01-01'){
                        $dte = date('d/m/Y', strtotime($values['due_date']));
                    } else {
                        $dte = '';
                    }
                    $col->addDate('due_date')->setId('dueDate')->setValue($dte);
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');           
            
            $row = $form->addRow();
                
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('fn_fees_fine_rule_id', __('Fine Rule'));
                    $col->addSelect('fn_fees_fine_rule_id')->fromArray($fineRuleData)->selected($values['fn_fees_fine_rule_id']);    

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('fn_fees_discount_id', __('Discount Rule'));
                    $col->addSelect('fn_fees_discount_id')->fromArray($feeDiscountData)->selected($values['fn_fees_discount_id']);
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');  
                  
                $row = $form->addRow();    
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('seq_installment_NO', __('Seq/Installment No'));
                    $col->addTextField('seq_installment_NO')->addClass('txtfield  numfield ')->required()->setValue($values['seq_installment_NO']);  
                    
            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('amount_editable', __(''));
            $col->addCheckbox('amount_editable')->description(__('<b>Transaction Amount editable</b>'))->setValue('1')->checked($values['amount_editable']);   

            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('display_fee_item', __(''));
                $col->addCheckbox('display_fee_item')->description(__('<b>Do Not display Fee item</b>'))->setValue('2')->checked($values['display_fee_item']);      
                
            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('is_concat_invoice', __(''));
                $col->addCheckbox('is_concat_invoice')->description(__('<b>Concat Invoice</b>'))->setValue('1')->checked($values['is_concat_invoice']); ; 
            
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
                    //$col->addButton(__('Add'))->setID('addFeeStructureItem')->addData('cid', $lastId)->addData('disid', $feeItemIds)->addClass('bttnsubmt bg-dodger-blue fsize lftbutt');
                    
                    $col->addContent('<a style="cursor:pointer;" data-cid="'.$lastId.'" data-disid="'.$feeItemIds.'" id="addFeeStructureItem" class="btn btn-primary lftbutt">Add</a>');

            
            if(!empty($childvalues)){
                $i = 1;
                foreach($childvalues as $cv){
                    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine deltr'.$cv['id']);
                
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    if($i == '1'){
                        $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
                    }    
                        $col->addSelect('fn_fee_item_id['.$cv['id'].']')->fromArray($feeItemData)->setId('feeStructureItemDisableId')->addClass('txtfield allFeeItemId')->selected($cv['fn_fee_item_id']);
                    
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    if($i == '1'){
                        $col->addLabel('amount', __('Amount'))->addClass('dte');
                    }     
                        $col->addTextField('amount['.$cv['id'].']')->addClass('txtfield kountseat numfield')->setValue($cv['amount']);

                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    if($i == '1'){
                        $col->addLabel('tax', __('Tax'))->addClass('dte');
                    }    
                        $col->addSelect('tax['.$cv['id'].']')->fromArray($type)->addClass('txtfield taxOptionSelect')->selected($cv['tax'])->addData('id', __(''.$cv['id'].''));   

                    $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                    if($i == '1'){
                        $col->addLabel('tax_percent', __('Tax Percent'))->addClass('dte');
                    }    

                    if($cv['tax'] == 'N'){
                        $col->addTextField('tax_percent['.$cv['id'].']')->addClass('txtfield kountseat szewdt numfield')->setValue($cv['tax_percent'])->setId('taxPercent'.$cv['id'].'')->readonly(); 
                    } else {
                        $col->addTextField('tax_percent['.$cv['id'].']')->addClass('txtfield kountseat szewdt numfield')->setValue($cv['tax_percent'])->setId('taxPercent'.$cv['id'].''); 
                    }
                        
                        $col->addContent('<div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="far fa-times-circle delFeeStructureItem " data-id="'.$cv['id'].'" ></i></div>'); 
                    $i++;       
                }             
            } else {
                $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine');
                
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
                        $col->addSelect('fn_fee_item_id[1]')->fromArray($feeItemData)->addClass(' txtfield');
                    
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        $col->addLabel('amount', __('Amount'))->addClass('dte');
                        $col->addTextField('amount[1]')->addClass('txtfield kountseat numfield');

                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        $col->addLabel('tax', __('Tax'))->addClass('dte');
                        $col->addSelect('tax[1]')->fromArray($type)->addClass('txtfield taxOptionSelect')->addData('id', __('1'));    

                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        $col->addLabel('tax_percent', __('Tax Percent'))->addClass('dte');
                        $col->addTextField('tax_percent[1]')->setId('taxPercent1')->addClass('txtfield kountseat szewdt numfield')->readonly();
                        $col->addLabel('', __(''))->addClass('dte');             
            
            }       
                $row = $form->addRow()->setID('lastseatdiv');
                $row->addFooter();
                $row->addCheckbox('update_invoices')->description(__('<b>Update Invoice</b>'))->setValue('1'); 
                // $row->addSubmit();
                $row->addContent('<a id="saveFeeStructure" class="btn btn-primary" style="float:right;">Submit</a>');

            echo $form->getOutput();
        }
    }
}

?>

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