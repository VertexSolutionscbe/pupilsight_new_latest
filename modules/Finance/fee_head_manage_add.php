<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_head_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Head'), 'fee_head_manage.php')
        ->add(__('Add Fee Head'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_head_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Fee Head');
    echo '</h2>';

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }
    
    $sqla = 'SELECT id, series_name FROM fn_fee_series WHERE type = "Finance" ';
    $resulta = $connection2->query($sqla);
    $series = $resulta->fetchAll();

    $seriesData = array();
    $seriesData2 = array();
    $seriesData1 = array(''=>'Select Series');
    foreach ($series as $dt) {
        $seriesData2[$dt['id']] = $dt['series_name'];
    }
    $seriesData = $seriesData1 + $seriesData2;

    $enablepymtgateway = array('N'=>'No','Y'=>'Yes');

    $sqlg = 'SELECT id, gateway_name FROM fn_fee_payment_gateway ';
    $resultg = $connection2->query($sqlg);
    $gateway = $resultg->fetchAll();

    $gatewayData = array();
    $gatewayData2 = array();
    $gatewayData1 = array(''=>'Select Payment Gateway');
    foreach ($gateway as $dt) {
        $gatewayData2[$dt['id']] = $dt['gateway_name'];
    }
    $gatewayData = $gatewayData1 + $gatewayData2;

    $sqlrt = 'SELECT id, name FROM fn_fees_receipt_template_master WHERE type != "Invoice Template" ';
    $resultrt = $connection2->query($sqlrt);
    $templateData = $resultrt->fetchAll();

    $receiptTemplate = array();
    $receiptTemplate2 = array();
    $receiptTemplate1 = array(''=>'Select Receipt Template');
    foreach ($templateData as $td) {
        $receiptTemplate2[$td['id']] = $td['name'];
    }
    $receiptTemplate = $receiptTemplate1 + $receiptTemplate2;


    $sqlit = 'SELECT id, name FROM fn_fees_receipt_template_master WHERE type = "Invoice Template" ';
    $resultit = $connection2->query($sqlit);
    $invtemplateData = $resultit->fetchAll();

    $invTemplate = array();
    $invTemplate2 = array();
    $invTemplate1 = array(''=>'Select Invoice Template');
    foreach ($invtemplateData as $td) {
        $invTemplate2[$td['id']] = $td['name'];
    }
    $invTemplate = $invTemplate1 + $invTemplate2;

    
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_head_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

   
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('Name'));
            $col->addTextField('name')->addClass('txtfield')->required();

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('account_code', __('Account Code'));
            $col->addTextField('account_code')->addClass('txtfield')->required();
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');    

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
        $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required();

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('description', __('Description'));
        $col->addTextField('description')->addClass('txtfield');

        $col = $row->addColumn()->setClass('hiddencol');
        $col->addLabel('', __(''));
        $col->addTextField('');   
    
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('bank_name', __('Bank Name'));
            $col->addTextField('bank_name')->addClass('txtfield');

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('ac_no', __('Account Number'));
            $col->addTextField('ac_no')->addClass('txtfield');
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');    

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('inv_fee_series_id', __('Invoice Series'));
            $col->addSelect('inv_fee_series_id')->fromArray($seriesData)->required();

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('recp_fee_series_id', __('Receipt Series'));
            $col->addSelect('recp_fee_series_id')->fromArray($seriesData)->required();
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');  
            
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('recp_fee_series_online_pay', __('Receipt Series for Online Payment'));
            $col->addSelect('recp_fee_series_online_pay')->fromArray($seriesData);

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('refund_fee_series_online_pay', __('Refund Series Number'));
            $col->addSelect('refund_fee_series_online_pay')->fromArray($seriesData);
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');  
            
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('payment_gateway', __('Enable Online Payment Gateway'));
            $col->addSelect('payment_gateway')->setId('enableGateway')->fromArray($enablepymtgateway);

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('payment_gateway_id', __('Payment Gateway'))->addClass('dte');
            $col->addSelect('payment_gateway_id')->setId('enableSelectGateway')->fromArray($gatewayData)->disabled()->required();
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');    
                
    $row = $form->addRow(); 
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('invoice_template', __('Invoice Template'));
            $col->addSelect('invoice_template')->addClass('txtfield')->fromArray($invTemplate);

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('receipt_template', __('Receipt Template'));
            $col->addSelect('receipt_template')->addClass('txtfield')->fromArray($receiptTemplate);
        
        // $col = $row->addColumn()->setClass('newdes');
        //     $col->addLabel('', __(''))->setClass('hiddencol');
        //     $col->addTextField('')->setClass('hiddencol');
        
        $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');    
        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
