<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_discount_rule_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Discount Rule'), 'fee_discount_rule_manage.php')
        ->add(__('Edit Fee Discount Rule'));

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
            $sql = 'SELECT * FROM fn_fees_discount WHERE id=:id';
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

            $sqlp = 'SELECT id,name FROM fee_category WHERE status="1"';
            $resultp = $connection2->query($sqlp);
            $rowdataprog = $resultp->fetchAll();
            $fee_category=array();  
            $fee_category2=array();  
            $fee_category1=array(''=>'Select fee category');
            foreach ($rowdataprog as $dt) {
            $fee_category2[$dt['id']] = $dt['name'];
            }
            $fee_category= $fee_category1 + $fee_category2;

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
            foreach ($feeItem as $dt) {
                $feeItemData[$dt['id']] = $dt['name'];
            }

            $datac = array('fn_fees_discount_id' => $id);
            $sqlc = 'SELECT * FROM fn_fee_discount_item WHERE fn_fees_discount_id=:fn_fees_discount_id';
            $resultc = $connection2->prepare($sqlc);
            $resultc->execute($datac);
            $childvalues = $resultc->fetchAll();
            if(!empty($childvalues)){
                $last =  end($childvalues);
                $lastId = $last['id'];
                $minInv = $last['min_invoice'];
                $maxInv = $last['max_invoice'];
            } else {
                $lastId = '1';
                $minInv = '';
                $maxInv = '';
            }
            

            echo '<h2>';
            echo __('Edit Fee Discount Rule');
            echo '</h2>';

           
            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_discount_rule_manage_editProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('minInv', $minInv);
            $form->addHiddenValue('maxInv', $maxInv);

            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('name', __('Name'));
                    $col->addTextField('name')->addClass('txtfield')->required()->setValue($values['name']);
        
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
                    $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($values['pupilsightSchoolYearID'])->required();
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');    
        
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('description', __('Description'));
                $col->addTextField('description')->addClass('txtfield')->setValue($values['description']);
        
                $finetype = array('1'=>'Category','2'=>'Invoice Count');    
                $row = $form->addRow();
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('fees_discount_type', __('Discount Type'));
                    $col->addRadio('fees_discount_type')->addClass('discountfineType')->fromArray($finetype)->required()->inline()->checked($values['fees_discount_type']);
                
              
                $type = array('Fixed'=>'Fixed','Percentage'=>'Percentage');

                $row = $form->addRow();
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    if($values['fees_discount_type'] == '1'){
                        $col->addLabel('fixed_fine_type', __('Category'))->setId('labelChng');
                    } else {
                        $col->addLabel('fixed_fine_type', __('Invoice Count'))->setId('labelChng');
                    }    
                        $col->addTextField('')->setClass('hiddencol');
                    
                    $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                        $col->addLabel('', __(''));
                        $col->addTextField('');     

                if($values['fees_discount_type'] == '1' && $values['fees_discount_type'] != '2'){ 
                    $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt');
                         $col->addButton(__('Add'))->setID('addCategoryRule')->addData('cid', $lastId)->addClass('bttnsubmt bg-dodger-blue fsize lftbutt');
                } else {
                    $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt hidediv');
                        $col->addButton(__('Add'))->setID('addCategoryRule')->addData('cid', $lastId)->addClass('bttnsubmt bg-dodger-blue fsize lftbutt');  
                }   
                
                if($values['fees_discount_type'] == '2' && $values['fees_discount_type'] != '1'){ 
                    $col = $row->addColumn()->setClass('newdes nobrdbtm invbutt ');
                         $col->addButton(__('Add'))->setID('addInvoiceCountRule')->addData('cid', $lastId)->addClass('bttnsubmt bg-dodger-blue fsize lftbutt'); 
                } else {
                    $col = $row->addColumn()->setClass('newdes nobrdbtm invbutt hidediv');
                        $col->addButton(__('Add'))->setID('addInvoiceCountRule')->addData('cid', $lastId)->addClass('bttnsubmt bg-dodger-blue fsize lftbutt'); 
                     
                }   
                    
                if($values['fees_discount_type'] == '1' && $values['fees_discount_type'] != '2'){ 
                    
                    $i = 1;
                    foreach($childvalues as $cv){
                    
                    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine deltr'.$cv['id']);
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('cat_name', __('Category Name'))->addClass('dte');
                        }    
                            $col->addSelect('cat_name['.$cv['id'].']')->fromArray($fee_category)->selected($cv['name'])->addClass('txtfield')->required();
                        
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
                        }    
                            $col->addSelect('fn_fee_item_id['.$cv['id'].']')->fromArray($feeItemData)->addClass(' txtfield')->selected($cv['fn_fee_item_id']);
                            
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('item_type', __('Amount Type'))->addClass('dte');
                        }    
                            $col->addSelect('item_type['.$cv['id'].']')->fromArray($type)->addClass('txtfield')->selected($cv['item_type']);    
                
                        $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                        if($i == '1'){
                            $col->addLabel('category_amount', __('Amount / Percent'))->addClass('dte');
                        }    
                        if($cv['item_type'] == 'Fixed'){
                            $amt = $cv['amount_in_number'];
                        } else {
                            $amt = $cv['amount_in_percent'];
                        }
                            $col->addTextField('category_amount['.$cv['id'].']')->addClass('ralignnumfield txtfield kountseat szewdt numfield')->setValue($amt); 
                          //  $col->addContent('<div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="far fa-times-circle delDiscountRuleType " data-id="'.$cv['id'].'" ></i></div>');       
                    }        
                } else {
                     

                    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine hidediv');
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                            $col->addLabel('cat_name', __('Category Name'))->addClass('dte');
                            $col->addSelect('cat_name[1]')->fromArray($fee_category)->addClass('txtfield')->required();

                        
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                            $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
                            $col->addSelect('fn_fee_item_id[1]')->fromArray($feeItemData)->addClass(' txtfield');
                            
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                            $col->addLabel('item_type', __('Amount Type'))->addClass('dte');
                            $col->addSelect('item_type[1]')->fromArray($type)->addClass('txtfield');    
                
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                            $col->addLabel('category_amount', __('Amount / Percent'))->addClass('dte');
                            $col->addTextField('category_amount[1]')->addClass('txtfield kountseat szewdt numfield'); 
                            $col->addLabel('', __(''))->addClass('dte');     
                } 

                if($values['fees_discount_type'] == '2' && $values['fees_discount_type'] != '1'){ 
                
                  
                    $i = 1;
                    foreach($childvalues as $cv){    
                    $row = $form->addRow()->setID('seatdiv2')->addClass('seatdiv2 dayslabfine deltr'.$cv['id']);
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('inv_name', __('Name'))->addClass('dte');
                        }    
                            $col->addTextField('inv_name['.$cv['id'].']')->addClass('txtfield')->setValue($cv['name']);
                
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('min_invoice', __('Minimum Invoice'))->addClass('dte');
                        }    
                            $col->addTextField('min_invoice['.$cv['id'].']')->addClass('min_inv txtfield')->setValue($cv['min_invoice']);
                
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('max_invoice', __('Maximum Invoice'))->addClass('dte');
                        }    
                            $col->addTextField('max_invoice['.$cv['id'].']')->addClass('max_inv txtfield')->setValue($cv['max_invoice']);     

                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('inv_fn_fee_item_id', __('Fee Item'))->addClass('dte');
                        }    
                            $col->addSelect('inv_fn_fee_item_id['.$cv['id'].']')->fromArray($feeItemData)->selected($cv['fn_fee_item_id'])->addClass(' txtfield');    
                            
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('inv_item_type', __('Amount Type'))->addClass('dte');
                        }    
                            $col->addSelect('inv_item_type['.$cv['id'].']')->fromArray($type)->addClass('txtfield')->selected($cv['item_type']);    
                
                        $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                        if($i == '1'){
                            $col->addLabel('inv_amount', __('Amount / Percent'))->addClass('dte');
                        }    
                        if($cv['item_type'] == 'Fixed'){
                            $amt = $cv['amount_in_number'];
                        } else {
                            $amt = $cv['amount_in_percent'];
                        }
                            $col->addTextField('inv_amount['.$cv['id'].']')->addClass('txtfield kountseat szewdt2 numfield')->setValue($amt); 
                            $col->addContent('<div class="dte mb-1"  style="font-size: 20px; padding:  0px 0 0px 4px; width: 20px"><i style="cursor:pointer" class="far fa-times-circle delDiscountRuleType " data-id="'.$cv['id'].'" ></i></div>'); 
                        $i++;       
                    }    
                } else {
                       
                    $row = $form->addRow()->setID('seatdiv2')->addClass('seatdiv2 hidediv dayslabfine');
                        $col = $row->addColumn()->setClass('newdes nobrdbtm2');
                            $col->addLabel('inv_name', __('Name'))->addClass('dte');
                            $col->addTextField('inv_name[1]')->addClass('txtfield');
                
                        $col = $row->addColumn()->setClass('newdes nobrdbtm2');
                            $col->addLabel('min_invoice', __('Minimum Invoice'))->addClass('dte');
                            $col->addTextField('min_invoice[1]')->addClass('min_inv txtfield');
                
                        $col = $row->addColumn()->setClass('newdes nobrdbtm2');
                            $col->addLabel('max_invoice', __('Maximum Invoice'))->addClass('dte');
                            $col->addTextField('max_invoice[1]')->addClass('max_inv txtfield');  
                            
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                            $col->addLabel('inv_fn_fee_item_id', __('Fee Item'))->addClass('dte');
                            $col->addSelect('inv_fn_fee_item_id[1]')->fromArray($feeItemData)->addClass(' txtfield');     
                            
                        $col = $row->addColumn()->setClass('newdes nobrdbtm2');
                            $col->addLabel('inv_item_type', __('Amount Type'))->addClass('dte');
                            $col->addSelect('inv_item_type[1]')->fromArray($type)->addClass('txtfield');    
                
                        $col = $row->addColumn()->setClass('newdes nobrdbtm2');
                            $col->addLabel('inv_amount', __('Amount / Percent'))->addClass('dte');
                            $col->addTextField('inv_amount[1]')->addClass('txtfield kountseat szewdt2 numfield'); 
                            $col->addLabel('', __(''))->addClass('dte');     
                
                } 
            
                   
                $row = $form->addRow()->setID('lastseatdiv');
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
