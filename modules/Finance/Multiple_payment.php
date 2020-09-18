<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$amount = $session->get('amount');

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
    echo __('Multi Select Payment');
    echo '</h2>';
    $sqldr = 'SELECT * FROM fn_masters ';
    $resultdr = $connection2->query($sqldr);
    $master = $resultdr->fetchAll();
    $paymentmode = array();
    $paymentmode1 = array(''=>'Select Payment Mode');
    $paymentmode2 = array();
    $bank = array();
    $bank1 = array(''=>'Select Bank');
    $bank2 = array();
    foreach ($master as $dr) {
        if($dr['type'] == 'payment_mode'){
            if($dr['name'] != 'Multiple'){
            $paymentmode2[$dr['id']] = $dr['name'];
         }
        } else {
            $bank2[$dr['id']] = $dr['name'];
        }
        
    }
    $bank = $bank1 + $bank2;
    $paymentmode = $paymentmode1 + $paymentmode2;
    $credit_card = array(''=>'Select',

'credit_card'=>'Credit Card',
'debit_card'=>'Debit Card'
);
 
   echo '<div style="float:right;color:#666;font-size:15px"><strong>Total Amount:'.$amount.'</strong> <input type="hidden" class="" id="fullamount" value="'.$amount.'"></div>';
    $form = Form::create('multiPaymentForm','');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('type', 'multipaymentformDetails');
    
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('fixed_fine_type', __(''));
            $col->addTextField('')->setClass('hiddencol');
        
        $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');     
        
        $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt');
             $col->addButton(__('Add'))->setID('addMultiPaymentItem')->addData('cid', '1')->addData('disid', 'nodata')->addClass('bttnsubmt bg-dodger-blue fsize lftbutt addbutt');

           

       $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine');       
        $col = $row->addColumn()->setClass('newdes nobrdbtm before_academic');
        $col->addLabel('payment_mode_id', __('Payment Mode'));
        $option='<option value="">Select Payment Mode</option>';
        /*foreach($paymentmode as  $val) {
            $option.="<option value='".$val['id']."'>".$val['name']."</option>"
        }*/
        foreach ($master as $dr) {
        if($dr['type'] == 'payment_mode'){
        if($dr['name'] != 'Multiple'){
            $option.="<option value='".$dr['id']."'>".$dr['name']."</option>";
        //$paymentmode2[$dr['id']] = $dr['name'];
        }
        } else {
            //$option.="<option value='".$dr['id']."'>".$dr['name']."</option>";
        $bank2[$dr['id']] = $dr['name'];
        }

        }
        $col->addContent("
            <select id='py_mode1' class='form-control payment_slt_mode' name='payment_mode_id[]' data-id='1'>
            ".$option."
            </select>
            ");
        //$col->addSelect('payment_mode_id[]')->setId('')->fromArray($paymentmode)->addClass(' txtfield');

        // $col = $row->addColumn()->setClass('newdes nobrdbtm before_academic');
        // $col->addLabel('credit_id', __('Credit Card'));
        // $col->addSelect('credit_id[]')->setId('')->fromArray($credit_card)->addClass('txtfield crdit_1');
        // $col->addContent('<input type="text" readonly placeholder="No option" class="d_crdit_1 form-control" style="display:none">');

    $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('bank_id', __('Bank Name'));
        $col->addSelect('bank_id[]')->fromArray($bank)->addClass('txtfield bank_1'); 
         $col->addContent('<input type="text" readonly placeholder="No option" class="d_bank_1 form-control" style="display:none">'); 
    
    $col = $row->addColumn()->setClass('newdes nobrdbtm');
        $col->addLabel('amount', __('Amount <span id="totalAmount"></span>'))->addClass('dte');
        $col->addTextField('amount[]')->addClass('txtfield kountseat numfield kountAmt amt_1')->required();

        $col = $row->addColumn()->setClass('newdes nobrdbtm');
        $col->addLabel('reference_no', __('Instrument No/Reference No'))->addClass('dte irno');
        // $col->addTextField('reference_no[]')->setId('reference_no')->placeholder('Enter Reference No')->addClass('ref_1');   
        $col->addContent("<input type='text' id='reference_no' name='reference_no[]' class='w-full
        ref_1'>");  

        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('instrument_date', __('Instrument date'))->addClass('dte');
           // $col->addTextField('instrument_date[]')->addClass('txtfield')->required();  
            $col->addContent("<input type='date' id='instrument_date' name='instrument_date[]'  style='background-color: #f0f1f3;height: 35px;
            font-size: 14px; font-size: 14px;color: #111111;border-radius: 4px !important;'class='w-full hasDatepicker LV_valid_field due_1'>");  
               
            $row = $form->addRow()->setID('lastseatdiv');
            $row->addFooter();
        $row = $form->addRow()->setId('Make_Payment')->setClass('buttonAlign');
            $col = $row->addColumn();
            $col->addLabel('', __(''));
            $col->addContent('<a id="MultiPayment" class="multiPayment btn btn-primary">Done</a>');

        echo $form->getOutput();
        
?>
<style>
    .buttonAlign{
        float: right; 
    }
.multiPayment{
    text-align: center;
    float: right;    
    /* padding: 15px;
    border-radius: 50px; */

}

.irno.mb-1{
    margin: -17px 0 0 0px;
}

.addbutt{
    margin-bottom:20px !important;
}
</style>
<?php
}

