<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
// $inv_id = $session->get('inovice_ids');
// $stuID = $session->get('can_stu_id');

$inv_id = $_GET['inv_id'];
$stuID = $_GET['sid'];
if (isActionAccessible($guid, $connection2, '/modules/Campaign/fee_make_payment.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Invoice'), 'invoice_manage.php')
        ->add(__('Edit Invoice'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $inv_id;
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('fn_fee_invoice_id' => $id);
            $sql = 'SELECT b.*, c.pupilsightProgramID, c.pupilsightYearGroupID, c.pupilsightRollGroupID FROM fn_fee_invoice_applicant_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id LEFT JOIN fn_fee_invoice_class_assign AS c ON a.fn_fee_invoice_id = c.fn_fee_invoice_id WHERE a.fn_fee_invoice_id=:fn_fee_invoice_id';
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
        
            $sqlse = 'SELECT id, series_name FROM fn_fee_series WHERE type = "Finance" ';
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


            $datac = array('fn_fee_invoice_id' => $values['id']);
            $sqlc = 'SELECT * FROM fn_fee_invoice_item WHERE fn_fee_invoice_id=:fn_fee_invoice_id';
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
            echo __('Edit Invoice');
            echo '</h2>';

           
            $form = Form::create('edit_invoice_save_form', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/edit_invoice_save_collection.php?id='.$values['id']);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('invid', $id);
            $form->addHiddenValue('fn_fee_structure_id', $values['fn_fee_structure_id']);
            $form->addHiddenValue('transport_schedule_id', $values['transport_schedule_id']);
            $form->addHiddenValue('pupilsightSchoolYearID', $values['pupilsightSchoolYearID']);
            $form->addHiddenValue('pupilsightSchoolFinanceYearID', $values['pupilsightSchoolFinanceYearID']);
            // $form->addHiddenValue('amount_editable', $values['amount_editable']);
            // $form->addHiddenValue('display_fee_item', $values['display_fee_item']);
            $form->addHiddenValue('pupilsightProgramID', $values['pupilsightProgramID']);
            $form->addHiddenValue('pupilsightYearGroupID', $values['pupilsightYearGroupID']);
            $form->addHiddenValue('pupilsightRollGroupID', $values['pupilsightRollGroupID']);
            $form->addHiddenValue('pupilsightPersonID', $stuID);

            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('title', __('Invoice Title'));
                    $col->addTextField('title')->addClass('txtfield')->required()->setValue($values['title']);


                    if(!empty($values['amount_editable'])){
                        $cl="checked";
                     } else {
                         $cl='';
                     }
                     if($values['display_fee_item']=="2"){
                        $cl1="checked";
                     } else {
                         $cl1='';
                     }

                    if($values['is_concat_invoice']=="1"){
                        $cl2="checked";
                    } else {
                        $cl2='';
                    }
                     $col = $row->addColumn()->setClass('newdes');
                     $col->addContent('<br/><label><input type="checkbox" name="amount_editable" '.$cl.' > Transaction editable </label>&nbsp;&nbsp;<label> <input type="checkbox" name="display_fee_item" '.$cl1.' > Do Not display Fee item </label>&nbsp;&nbsp;<label> <input type="checkbox" name="is_concat_invoice" '.$cl2.' > Concat Invoice </label>');

                // $col = $row->addColumn()->setClass('newdes');
                //     $col->addLabel('invoice_title_id', __('Title of Invoice'));
                //     $col->addSelect('invoice_title_id')->fromArray($feeSeriesData)->required()->selected($values['invoice_title_id']);    

                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');   
            
            $row = $form->addRow();
                
            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('inv_fn_fee_series_id', __('Invoice Series'));
                $col->addSelect('inv_fn_fee_series_id')->fromArray($feeSeriesData)->required()->selected($values['inv_fn_fee_series_id']);        

            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('rec_fn_fee_series_id', __('Receipt Series'));
                $col->addSelect('rec_fn_fee_series_id')->fromArray($feeSeriesData)->required()->selected($values['rec_fn_fee_series_id']);    
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');           
            
            $row = $form->addRow();
            
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('fn_fees_head_id', __('Account Head'));
                    $col->addSelect('fn_fees_head_id')->setId('fnFeesHeadId')->fromArray($feeHeadData)->required()->selected($values['fn_fees_head_id']);    

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('due_date', __('Due Date'))->addClass('dte');
                    if($values['due_date'] != '1970-01-01'){
                        $dte = date('d/m/Y', strtotime($values['due_date']));
                    } else {
                        $dte = '';
                    }
                    $col->addDate('due_date')->setValue($dte);
                
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
            

            $type = array('Y'=>'Yes','N'=>'No');
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('fixed_fine_type', __('Invoice Item'));
                    $col->addTextField('')->setClass('hiddencol');
                
                $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                    $col->addLabel('', __(''));
                    $col->addTextField('');     
                
                $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt');
                    //$col->addButton(__('Add'))->setID('addInvoiceItem')->addData('cid', $lastId)->addData('disid', $feeItemIds)->addClass('bttnsubmt bg-dodger-blue fsize lftbutt');
                    $col->addContent('<a style="cursor:pointer;margin-bottom: 15px;" data-cid="'.$lastId.'" data-disid="'.$feeItemIds.'" id="addInvoiceItem" class="btn btn-primary lftbutt">Add</a>');

            
            if(!empty($childvalues)){
                $i = 1;
                foreach($childvalues as $cv){
                    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine deltr'.$cv['id']);
                
                    $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                    if($i == '1'){
                        $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
                    }    
                        $col->addSelect('fn_fee_item_id['.$cv['id'].']')->fromArray($feeItemData)->setId('feeStructureItemDisableId')->addClass('txtfield allFeeItemId')->selected($cv['fn_fee_item_id']);

                    $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                    if($i == '1'){
                        $col->addLabel('description', __('Description'))->addClass('dte');
                    }     
                        $col->addTextField('description['.$cv['id'].']')->addClass('txtfield kountseat')->setValue($cv['description']);    
                    
                    $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                    if($i == '1'){
                        $col->addLabel('amount', __('Amount'))->addClass('dte');
                    }     
                        $col->addTextField('amount['.$cv['id'].']')->addClass('txtfield kountseat numfield')->setValue($cv['amount']);

                    $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                    if($i == '1'){
                        $col->addLabel('tax', __('Tax'))->addClass('dte');
                    }    
                        $col->addTextField('tax['.$cv['id'].']')->addClass('txtfield numfield')->setValue($cv['tax']);  
                        
                    $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                    if($i == '1'){
                        $col->addLabel('discount', __('Discount'))->addClass('dte lastlabel');
                    }     
                        $col->addTextField('discount['.$cv['id'].']')->addClass('txtfield kountseat szewdt2 numfield')->setValue($cv['discount']); 
                        
                    if($i == '1'){
                        $col->addContent('<div class="dte mb-1"  style="font-size: 25px; margin: -35px 50px 0px 0px; float:right; width: 30px"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px delFeeStructureItem " data-id="'.$cv['id'].'" ></i></div>');  
                    } else {
                        $col->addContent('<div class="dte mb-1"  style="font-size: 25px; margin: -35px -47px 0px 0px; float:right; width: 30px"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px delFeeStructureItem " data-id="'.$cv['id'].'" ></i></div>');  
                    }

                    // $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                    // if($i == '1'){
                    //     $col->addLabel('total_amount', __('Total Amount'))->addClass('dte');
                    // }    
                    //     $col->addTextField('total_amount['.$cv['id'].']')->addClass('txtfield kountseat szewdt2 numfield')->setValue($cv['total_amount']); 
                         //$col->addContent('<div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px delFeeStructureItem " data-id="'.$cv['id'].'" ></i></div>'); 
                    $i++;       
                }             
            } else {
                $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine');
                
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
                    $col->addSelect('fn_fee_item_id[1]')->setId('feeStructureItemDisableId')->fromArray($feeItemData)->addClass('txtfield allFeeItemId');
                    
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('description', __('Description'))->addClass('dte');
                    $col->addTextField('description[1]')->addClass('txtfield kountseat ');
        
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('amount', __('Amount'))->addClass('dte');
                    $col->addTextField('amount[1]')->addClass('txtfield kountseat numfield');
        
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('tax', __('Tax'))->addClass('dte');
                    //$col->addSelect('tax[1]')->fromArray($type)->addClass('txtfield'); 
                    $col->addTextField('tax[1]')->addClass('txtfield kountseat numfield');
                    
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('discount', __('Discount'))->addClass('dte');
                    $col->addTextField('discount[1]')->addClass('txtfield kountseat szewdt2 numfield');    
        
                // $col = $row->addColumn()->setClass('newdes nobrdbtm');
                //     $col->addLabel('total_amount', __('Total Amount'))->addClass('dte');
                //     $col->addTextField('total_amount[1]')->addClass('txtfield kountseat szewdt2 numfield'); 
                //     $col->addLabel('', __(''))->addClass('dte');     
            
            }       
                $row = $form->addRow()->setID('lastseatdiv');
                $row->addFooter();
                $row->addContent('<a id="updateAdmissionInvoiceStnButton" class=" btn btn-primary" style="float:right;">Submit</a>');

            echo $form->getOutput();
        }
    }
}

?>

<style>
    .lastlabel {
        width :200px;
    }
</style>