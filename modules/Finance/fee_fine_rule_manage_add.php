<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_fine_rule_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Fine Rule'), 'fee_fine_rule_manage.php')
        ->add(__('Add Fee Fine Rule'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_fine_rule_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Fee Fine Rule');
    echo '</h2>';

    $type = array('Fixed'=>'Fixed','Percentage'=>'Percentage');

    

    
    $form = Form::create('fineRuleForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_fine_rule_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('startDate', '');
    $form->addHiddenValue('lastDate', '');
    $form->addHiddenValue('startDay', '');
    $form->addHiddenValue('lastDay', '');

   
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('Name'));
            $col->addTextField('name')->addClass('txtfield')->required();

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('code', __('Code'));
            $col->addTextField('code')->addClass('txtfield')->required();
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');    

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('description', __('Description'));
        $col->addTextField('description')->addClass('txtfield');

    $finetype = array('1'=>'Fixed Fine Method','2'=>'Daily Fine Method','3'=>'Day Slabs Method');    
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('fine_type', __('Fine Method'));
        $col->addRadio('fine_type')->addClass('parentfineType')->fromArray($finetype)->required()->inline()->checked('1');
    
    $amtinper1 = array('1'=>'Amount in %');   
    $amtinper2 = array('2'=>'Fixed Amount');   
    $amtinper3 = array('3'=>'Enable Multiple Fixed Rule'); 
    $amtinper4 = array('4'=>'Apply Fine for Slab');    
    $row = $form->addRow()->addClass('dayslab');;
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('fixed_fine_type', __('Fixed Fine Rule Type'))->setId('labelChng');
            $col->addRadio('fixed_fine_type')->setId('chkd')->addClass('fineType')->fromArray($amtinper1)->inline()->checked('1');
        
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('amount_in_percent')->setId('firstId')->addClass('txtfield hdefield numfield');

        $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField(''); 

    $row = $form->addRow()->addClass('dayslab');
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('fixed_fine_type', __(''));
            $col->addRadio('fixed_fine_type')->addClass('fineType')->fromArray($amtinper2)->inline();
        
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('amount_in_number')->setId('secondId')->readonly()->addClass('txtfield hdefield numfield');

        $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');   
                
    $row = $form->addRow()->addClass('fixedfine');
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('fixed_fine_type', __(''));
            $col->addRadio('fixed_fine_type')->addClass('fineType')->fromArray($amtinper3)->inline();
        
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __(''));
            $col->addButton(__('Add'))->setID('addFixedMultipleFineRule')->addData('cid', '1')->addClass('bttnsubmt bg-dodger-blue fsize hidediv');

        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');  
    
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
            $col->addLabel('fixed_rule_amt_per', __('Amount'))->addClass('dte');
            $col->addTextField('fixed_rule_amt_per[1]')->addClass('chkamnt txtfield kountseat szewdt numfield'); 
            $col->addLabel('', __(''))->addClass('dte');     


    $row = $form->addRow()->addClass('dayslabfine hidediv');
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('fixed_fine_type', __(''));
            $col->addRadio('fixed_fine_type')->addClass('fineType')->fromArray($amtinper4)->inline();
        
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __(''));
            $col->addButton(__('Add'))->setID('addDaySlabFineRule')->addData('cid', '1')->addClass('bttnsubmt bg-dodger-blue fsize hidediv');

        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');  
    
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
            $col->addLabel('day_slab_amt_per', __('Amount'))->addClass('dte');
            $col->addTextField('day_slab_amt_per[1]')->addClass('chkdayamnt txtfield kountseat szewdt numfield'); 
            $col->addLabel('', __(''))->addClass('dte');     
                
    $row = $form->addRow()->setID('lastseatdiv');
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('ignore_holiday', __(''));
        $col->addCheckbox('ignore_holiday')->description(__('Should Not Include Weekends & Holidays'))->setValue('1');    
        
    $row = $form->addRow()->setID('lastseatdiv');
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('is_fine_editable', __(''));
        $col->addCheckbox('is_fine_editable')->description(__('Is this fine editable'))->setValue('1');   
 
                         
        
    $row = $form->addRow();
        $row->addFooter();
        $row->addContent('<a id="submitMasterFine" class=" btn btn-primary" style="position:absolute; right:0; margin-top: -17px;">Submit</a>');

    echo $form->getOutput();

}
?>
<script>
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