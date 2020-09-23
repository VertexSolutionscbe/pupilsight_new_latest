<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_fine_rule_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Fine Rule'), 'fee_fine_rule_manage.php')
        ->add(__('Edit Fee Fine Rule'));

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
            $sql = 'SELECT * FROM fn_fees_fine_rule WHERE id=:id';
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

            $type = array('Fixed'=>'Fixed','Percentage'=>'Percentage');
            //Let's go!
            $values = $result->fetch();

            $datac = array('fn_fees_fine_rule_id' => $id);
            $sqlc = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id=:fn_fees_fine_rule_id';
            $resultc = $connection2->prepare($sqlc);
            $resultc->execute($datac);
            $childvalues = $resultc->fetchAll();
            // echo '<pre>';
            // print_r($childvalues);
            // echo '</pre>';
           
            if(!empty($childvalues)){
                $last =  end($childvalues);
                $lastId = $last['id'];
                $startdate = date('d/m/Y', strtotime($last['from_date']. ' +1 day'));
                $lastdate = date('d/m/Y', strtotime($last['to_date']. ' +1 day'));
                $fromday = $last['from_day'];
                $today = $last['to_day'];
            } else {
                $lastId = '1';
                $startdate = '';
                $lastdate = '';
                $fromday = '';
                $today = '';
            }
            
            echo '<h2>';
            echo __('Edit Fee Fine Rule');
            echo '</h2>';


            $form = Form::create('fineRuleForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_fine_rule_manage_editProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('startDate', $startdate);
            $form->addHiddenValue('lastDate', $lastdate);
            $form->addHiddenValue('startDay', $fromday);
            $form->addHiddenValue('lastDay', $today);

            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('name', __('Name'));
                    $col->addTextField('name')->addClass('txtfield')->required()->setValue($values['name']);
        
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('code', __('Code'));
                    $col->addTextField('code')->addClass('txtfield')->required()->setValue($values['code']);
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');    
        
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('description', __('Description'));
                $col->addTextField('description')->addClass('txtfield')->setValue($values['description']);
        
            $finetype = array('1'=>'Fixed Fine Method','2'=>'Daily Fine Method','3'=>'Day Slabs Method');    
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('fine_type', __('Fine Method'));
                $col->addRadio('fine_type')->addClass('parentfineType')->fromArray($finetype)->required()->inline()->checked($values['fine_type']);
            
            $amtinper1 = array('1'=>'Amount in %');   
            $amtinper2 = array('2'=>'Fixed Amount');   
            $amtinper3 = array('3'=>'Enable Multiple Fixed Rule'); 
            $amtinper4 = array('4'=>'Apply Fine for Slab');    
            $row = $form->addRow()->addClass('dayslab');
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                if($values['rule_type'] == '1'){
                    $col->addLabel('fixed_fine_type', __('Fixed Fine Rule Type'))->setId('labelChng');
                } else if($values['rule_type'] == '2'){
                    $col->addLabel('fixed_fine_type', __('Daily Fine Rule Type'))->setId('labelChng');;
                } else {
                    $col->addLabel('fixed_fine_type', __('Day Fine Rule Type'))->setId('labelChng');;
                }
                    
                    $col->addRadio('fixed_fine_type')->setId('chkd')->addClass('fineType')->fromArray($amtinper1)->inline()->checked($values['rule_type']);
                
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('', __(''));
                    if($values['rule_type'] == '1' && !empty($childvalues[0]['amount_in_percent'])){
                        $col->addTextField('amount_in_percent')->setId('firstId')->addClass('txtfield hdefield numfield')->setValue($childvalues[0]['amount_in_percent']);
                    } else {
                        $col->addTextField('amount_in_percent')->setId('firstId')->addClass('txtfield hdefield numfield');
                    }
                    
        
                $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                    $col->addLabel('', __(''));
                    $col->addTextField(''); 
        
            $row = $form->addRow()->addClass('dayslab');
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('fixed_fine_type', __(''));
                    $col->addRadio('fixed_fine_type')->addClass('fineType')->fromArray($amtinper2)->inline()->checked($values['rule_type']);
                
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('', __(''));
                    if($values['rule_type'] == '2' && !empty($childvalues[0]['amount_in_percent'])){
                        $col->addTextField('amount_in_number')->setId('secondId')->addClass('txtfield hdefield numfield')->setValue($childvalues[0]['amount_in_number']);
                    } else {
                        $col->addTextField('amount_in_number')->setId('secondId')->readonly()->addClass('txtfield hdefield numfield');
                    }
                    
        
                $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                    $col->addLabel('', __(''));
                    $col->addTextField('');   
                    
            if($values['fine_type'] == '1'){       
                $row = $form->addRow()->addClass('fixedfine');
                    $col = $row->addColumn()->setClass('newdes');
                        $col->addLabel('fixed_fine_type', __(''));
                        $col->addRadio('fixed_fine_type')->addClass('fineType')->fromArray($amtinper3)->inline()->checked($values['rule_type']);
            } else {
                $row = $form->addRow()->addClass('fixedfine hidediv');
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('fixed_fine_type', __(''));
                    $col->addRadio('fixed_fine_type')->addClass('fineType')->fromArray($amtinper3)->inline()->checked($values['rule_type']);
            }    
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('', __(''));
                    if($values['rule_type'] == '3' && !empty($childvalues)){
                        //$col->addButton(__('Add'))->setID('addFixedMultipleFineRule')->addData('cid', $lastId)->addClass('bttnsubmt bg-dodger-blue fsize');
                        $col->addContent('<a class="btn btn-primary fsize" id="addFixedMultipleFineRule" data-cid='.$lastId.'>Add</a>');
                    } else {
                        //$col->addButton(__('Add'))->setID('addFixedMultipleFineRule')->addData('cid', $lastId)->addClass('bttnsubmt bg-dodger-blue fsize hidediv');
                        $col->addContent('<a class="btn btn-primary fsize hidediv" id="addFixedMultipleFineRule" data-cid='.$lastId.'>Add</a>');
                    }
        
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');  
            if($values['rule_type'] == '3' && !empty($childvalues)){
                $i = 1;
                foreach($childvalues as $cv){
                    if($cv['amount_type'] == 'Fixed'){
                        $amtper = $cv['amount_in_number'];
                    } else {
                        $amtper = $cv['amount_in_percent'];
                    }
                    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine deltr'.$cv['id']);
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $fdate = date('d/m/Y', strtotime($cv['from_date']));
                    $edate = date('d/m/Y', strtotime($cv['to_date']));
                    if($i == '1'){
                        $col->addLabel('from_date', __('From Date'))->addClass('dte');
                    }    
                        $col->addDate('from_date['.$cv['id'].']')->setId('from_date'.$cv['id'])->addClass('chkfrmdate fdatenew txtfield')->setValue($fdate);
                    
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    if($i == '1'){
                        $col->addLabel('to_date', __('To Date'))->addClass('dte');
                    }    
                        $col->addDate('to_date['.$cv['id'].']')->setId('t0_date'.$cv['id'])->addClass('chktodate tdatenew txtfield')->setValue($edate); 
                        
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    if($i == '1'){
                        $col->addLabel('fixed_rule_item_type', __('Amount Type'))->addClass('dte');
                    }    
                        $col->addSelect('fixed_rule_item_type['.$cv['id'].']')->fromArray($type)->addClass('txtfield')->selected($cv['amount_type']);    

                    
                    $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                    if($i == '1'){
                        $col->addLabel('fixed_rule_amt_per', __('Amount / Percent'))->addClass('dte fee_width');
                    }    
                        $col->addTextField('fixed_rule_amt_per['.$cv['id'].']')->addClass('chkamnt txtfield kountseat szewdt numfield')->setValue($amtper); 
                        $col->addContent('<div class="dte mb-1"  style="float: right; margin: -32px -28px 0px 0px;"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px  delFineRuleType " data-id="'.$cv['id'].'" ></i></div>'); 
                    $i++;    
                }       
            } else {
            $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv hidediv fixedfine');
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('from_date', __('From Date'))->addClass('dte');
                    $col->addDate('from_date[1]')->setId('fdate')->addClass('chkfrmdate txtfield');
                
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('to_date', __('To Date'))->addClass('dte');
                    $col->addDate('to_date[1]')->setId('tdate')->addClass('chktodate txtfield');    
        
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('fixed_rule_item_type', __('Amount Type'))->addClass('dte');
                    $col->addSelect('fixed_rule_item_type[1]')->fromArray($type)->addClass('txtfield');     
        
                $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('fixed_rule_amt_per', __('Amount / Percent'))->addClass('dte');
                    $col->addTextField('fixed_rule_amt_per[1]')->addClass('chkamnt txtfield kountseat szewdt numfield'); 
                    $col->addLabel('', __(''))->addClass('dte');          
            }    

            if($values['fine_type'] == '3'){ 
        
                $row = $form->addRow()->addClass('dayslabfine');
                    $col = $row->addColumn()->setClass('newdes');
                        $col->addLabel('fixed_fine_type', __(''));
                        $col->addRadio('fixed_fine_type')->addClass('fineType')->fromArray($amtinper4)->inline()->checked($values['rule_type']);
            } else {
                $row = $form->addRow()->addClass('dayslabfine hidediv');
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('fixed_fine_type', __(''));
                    $col->addRadio('fixed_fine_type')->addClass('fineType')->fromArray($amtinper4)->inline()->checked($values['rule_type']);
            }    
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('', __(''));
                    if($values['rule_type'] == '4' && !empty($childvalues)){
                        //$col->addButton(__('Add'))->setID('addDaySlabFineRule')->addData('cid', $lastId)->addClass('bttnsubmt bg-dodger-blue fsize');
                        $col->addContent('<a class="btn btn-primary fsize" id="addDaySlabFineRule" data-cid='.$lastId.'>Add</a>');
                    } else {
                        //$col->addButton(__('Add'))->setID('addDaySlabFineRule')->addData('cid', $lastId)->addClass('bttnsubmt bg-dodger-blue fsize hidediv');
                        $col->addContent('<a class="btn btn-primary fsize hidediv" id="addDaySlabFineRule" data-cid='.$lastId.'>Add</a>');
                    }
                    
        
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');  

            if($values['rule_type'] == '4' && !empty($childvalues)){
                $i = 1;
                foreach($childvalues as $cv){ 
                    if($cv['amount_type'] == 'Fixed'){
                        $damtper = $cv['amount_in_number'];
                    } else {
                        $damtper = $cv['amount_in_percent'];
                    }
                    $row = $form->addRow()->setID('seatdiv2')->addClass('seatdiv2  deltr'.$cv['id']);
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('from_day', __('From Day'))->addClass('dte');
                        }    
                            $col->addTextField('from_day['.$cv['id'].']')->setId('fdate')->addClass('chkfrmday txtfield numfield')->setValue($cv['from_day']);
                        
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('to_day', __('To Day'))->addClass('dte');
                        }    
                            $col->addTextField('to_day['.$cv['id'].']')->setId('tdate')->addClass('chktoday txtfield numfield')->setValue($cv['to_day']);    


                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                            $col->addLabel('day_slab_item_type', __('Amount Type'))->addClass('dte');
                        }
                            $col->addSelect('day_slab_item_type['.$cv['id'].']')->fromArray($type)->addClass('txtfield')->selected($cv['amount_type']);     
                        
                        $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
                        if($i == '1'){
                            $col->addLabel('day_slab_amt_per', __('Amount / Percent'))->addClass('dte fee_width');
                        }    
                            $col->addTextField('day_slab_amt_per['.$cv['id'].']')->addClass('chkdayamnt txtfield kountseat szewdt numfield')->setValue($damtper); 
                            $col->addContent('<div class="dte mb-1"  style="float: right; margin: -32px -28px 0px 0px;"><i style="cursor:pointer" class="mdi mdi-close-circle mdi-24px  delFineRuleType " data-id="'.$cv['id'].'" ></i></div>');   
                    $i++;           
                }    
            } else {
                $row = $form->addRow()->setID('seatdiv2')->addClass('seatdiv2 hidediv ');
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        $col->addLabel('from_day', __('From Day'))->addClass('dte');
                        $col->addTextField('from_day[1]')->addClass('chkfrmday txtfield numfield');
                    
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        $col->addLabel('to_day', __('To Day'))->addClass('dte');
                        $col->addTextField('to_day[1]')->addClass('chktoday txtfield numfield');   
                        
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        $col->addLabel('day_slab_item_type', __('Amount Type'))->addClass('dte');
                        $col->addSelect('day_slab_item_type[1]')->fromArray($type)->addClass('txtfield');     
                    
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        $col->addLabel('day_slab_amt_per', __('Amount / Percent'))->addClass('dte');
                        $col->addTextField('day_slab_amt_per[1]')->addClass('chkdayamnt txtfield kountseat szewdt numfield'); 
                        $col->addLabel('', __(''))->addClass('dte');         
              
            }            
            $row = $form->addRow()->setID('lastseatdiv');
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('ignore_holiday', __(''));
                $col->addCheckbox('ignore_holiday')->description(__('Should Not Include Weekends & Holidays'))->setValue('1')->checked($childvalues[0]['ignore_holiday']);  
                
            $row = $form->addRow()->setID('lastseatdiv');
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('is_fine_editable', __(''));
                $col->addCheckbox('is_fine_editable')->description(__('Is this fine editable'))->setValue('1')->checked($values['is_fine_editable']);   
             

            $row = $form->addRow();
                $row->addFooter();
                $row->addContent('<a id="submitMasterFine" class=" btn btn-primary" style="position:absolute; right:0; margin-top: -17px;">Submit</a>');

            echo $form->getOutput();
        }
    }
}
?>
<script>
    <?php if($values['fine_type']==3) { ?>
    $(".fixedfine").hide();
    $(".dayslabfine").removeClass('hidediv');
    $(".dayslab").hide();
    $(".dayslabfine").removeAttr("style");
    $(".seatdiv").addClass('hidediv');
<?php } ?>
    $(document).on('change','.fineType', function(){
        var val= $(this).val();
        if(val!=""){
          if(val=='1'){
            $("#secondId").val('');
          } else if(val=="3"){
            $("#secondId").val('');
            $("#firstId").val('');
          } else {
            $("#firstId").val('');
          } 
        }
    });
    $(document).on('change','.parentfineType',function(){
        var val = $(this).val();
        if(val!=""){
           if(val=="1" || val=="2"){
              $("#firstId").val('');
              $("#secondId").val('');
           } else  {
                $("#firstId").val('');
                $("#secondId").val('');
           }
        }
    });
</script>