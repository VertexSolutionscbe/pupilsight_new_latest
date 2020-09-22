<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_discount_rule_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Discount Rule'), 'fee_discount_rule_manage.php')
        ->add(__('Add Fee Discount Rule'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_discount_rule_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Fee Discount Rule');
    echo '</h2>';
    
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
    foreach ($feeItem as $dt) {
        $feeItemData[$dt['id']] = $dt['name'];
    }

    
    
    $form = Form::create('feeDiscontForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_discount_rule_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('minInv', '');
    $form->addHiddenValue('maxInv', '');

   
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('Name'));
            $col->addTextField('name')->addClass('txtfield')->required();

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required();
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');    

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('description', __('Description'));
        $col->addTextField('description')->addClass('txtfield');

    $finetype = array('1'=>'Category','2'=>'Invoice Count');    
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('fees_discount_type', __('Discount Type'));
        $col->addRadio('fees_discount_type')->addClass('discountfineType')->fromArray($finetype)->required()->inline()->checked('1');
    
  
    $type = array('Fixed'=>'Fixed','Percentage'=>'Percentage');
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('fixed_fine_type', __('Category'))->setId('labelChng');
            $col->addTextField('')->setClass('hiddencol');
        
        $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');     
        
        $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt');
             $col->addButton(__('Add'))->setID('addCategoryRule')->addData('cid', '1')->addClass('bttnsubmt bg-dodger-blue fsize lftbutt');

        $col = $row->addColumn()->setClass('newdes nobrdbtm invbutt hidediv');
            $col->addButton(__('Add'))->setID('addInvoiceCountRule')->addData('cid', '1')->addClass('bttnsubmt bg-dodger-blue fsize lftbutt');     

        $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine');

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
            $col->addTextField('category_amount[1]')->addClass('ralignnumfield txtfield kountseat szewdt numfield amtPercent')->required(); 
            $col->addLabel('', __(''))->addClass('dte');             
       
    
    $row = $form->addRow()->setID('seatdiv2')->addClass('seatdiv2 hidediv dayslabfine');
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('inv_name', __('Name'))->addClass('dte');
            $col->addTextField('inv_name[1]')->addClass('inv_name txtfield');

        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('min_invoice', __('Minimum Invoice'))->addClass('dte');
            $col->addTextField('min_invoice[1]')->addClass('min_inv txtfield');

        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('max_invoice', __('Maximum Invoice'))->addClass('dte');
            $col->addTextField('max_invoice[1]')->addClass('max_inv txtfield');  
            
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('inv_fn_fee_item_id', __('Fee Item'))->addClass('dte');
            $col->addSelect('inv_fn_fee_item_id[1]')->fromArray($feeItemData)->addClass(' txtfield');    
              
        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('inv_item_type', __('Amount Type'))->addClass('dte');
            $col->addSelect('inv_item_type[1]')->fromArray($type)->addClass('txtfield');    

        $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('inv_amount', __('Amount / Percent'))->addClass('dte');
            $col->addTextField('inv_amount[1]')->addClass('txtfield kountseat szewdt2 numfield inv_amtPercent'); 
            $col->addLabel('', __(''))->addClass('dte');     
                
                      
        
    $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addContent('<a id="submitMasterDiscount" class=" btn btn-primary" style="position:absolute; right:0; margin-top: -20px;">Submit</a>');

        //$row->addSubmit();

    echo $form->getOutput();

}
