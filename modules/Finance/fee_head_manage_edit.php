<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_head_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Head'), 'fee_head_manage.php')
        ->add(__('Edit Fee Head'));

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
            $sql = 'SELECT * FROM fn_fees_head WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);


            // $chksql = 'SELECT COUNT(id) AS kount FROM fn_fee_structure WHERE fn_fees_head_id = '.$id.' ';
            // $resultchk = $connection2->query($chksql);
            // $chkStr = $resultchk->fetch();

            // if($chkStr['kount'] >= 1){
            //     $readonly = 'true';
            // } else {
            //     $readonly = '';
            // }

            $readonly = '';
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

            echo '<h2>';
            echo __('Edit Fee Head');
            echo '</h2>';

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
        
            $enablepymtgateway = array('Y'=>'Yes','N'=>'No');
        
            $sqlg = 'SELECT id, name FROM fn_fee_payment_gateway ';
            $resultg = $connection2->query($sqlg);
            $gateway = $resultg->fetchAll();
        
            $gatewayData = array();
            $gatewayData2 = array();
            $gatewayData1 = array(''=>'Select Payment Gateway');
            foreach ($gateway as $dt) {
                $gatewayData2[$dt['id']] = $dt['name'];
            }
            $gatewayData = $gatewayData1 + $gatewayData2;

            $sqlrt = 'SELECT id, name FROM fn_fees_receipt_template_master ';
            $resultrt = $connection2->query($sqlrt);
            $templateData = $resultrt->fetchAll();

            $receiptTemplate = array();
            $receiptTemplate2 = array();
            $receiptTemplate1 = array(''=>'Select Receipt Template');
            foreach ($templateData as $td) {
                $receiptTemplate2[$td['id']] = $td['name'];
            }
            $receiptTemplate = $receiptTemplate1 + $receiptTemplate2;

            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_head_manage_editProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('name', __('Name'));
                    $col->addTextField('name')->addClass('txtfield')->setValue($values['name'])->required()->readonly($readonly);

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('account_code', __('Account Code'));
                    $col->addTextField('account_code')->addClass('txtfield')->setValue($values['account_code'])->required()->readonly($readonly);
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');    

            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
                $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($values['pupilsightSchoolYearID'])->required()->readonly($readonly);

                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('description', __('Description'));
                $col->addTextField('description')->addClass('txtfield')->setValue($values['description'])->readonly($readonly);

                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');    
            
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('bank_name', __('Bank Name'));
                    $col->addTextField('bank_name')->addClass('txtfield')->setValue($values['bank_name'])->readonly($readonly);

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('ac_no', __('Account Number'));
                    $col->addTextField('ac_no')->addClass('txtfield')->setValue($values['ac_no'])->readonly($readonly);
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');    

            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('inv_fee_series_id', __('Invoice Series'));
                    $col->addSelect('inv_fee_series_id')->fromArray($seriesData)->selected($values['inv_fee_series_id'])->required()->readonly($readonly);

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('recp_fee_series_id', __('Receipt Series'));
                    $col->addSelect('recp_fee_series_id')->fromArray($seriesData)->selected($values['recp_fee_series_id'])->required()->readonly($readonly);
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');  
                    
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('recp_fee_series_online_pay', __('Receipt Series for Online Payment'));
                    $col->addSelect('recp_fee_series_online_pay')->fromArray($seriesData)->selected($values['recp_fee_series_online_pay'])->readonly($readonly);

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('refund_fee_series_online_pay', __('Refund Series Number'));
                    $col->addSelect('refund_fee_series_online_pay')->fromArray($seriesData)->selected($values['refund_fee_series_online_pay'])->readonly($readonly);
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');  
                    
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('payment_gateway', __('Enable Online Payment Gateway'));
                    $col->addSelect('payment_gateway')->setId('enableGateway')->fromArray($enablepymtgateway)->selected($values['payment_gateway']);

                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('payment_gateway_id', __('Payment Gateway'));
                if(!empty($values['payment_gateway_id'])){
                    $col->addSelect('payment_gateway_id')->setId('enableSelectGateway')->fromArray($gatewayData)->selected($values['payment_gateway_id']);
                } else {
                    $col->addSelect('payment_gateway_id')->setId('enableSelectGateway')->fromArray($gatewayData)->selected($values['payment_gateway_id'])->disabled();
                }
                    
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');    
                        
            $row = $form->addRow(); 
                // $col = $row->addColumn()->setClass('newdes');
                //     $col->addLabel('invoice_template', __('Invoice Template'));
                //     $col->addSelect('invoice_template')->addClass('txtfield')->fromArray($seriesData);
        
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('receipt_template', __('Receipt Template'));
                    $col->addSelect('receipt_template')->addClass('txtfield')->fromArray($receiptTemplate)->selected($values['receipt_template']);
                
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('', __(''))->setClass('hiddencol');
                    $col->addTextField('')->setClass('hiddencol');
                
                $col = $row->addColumn()->setClass('hiddencol');
                    $col->addLabel('', __(''));
                    $col->addTextField('');  
           
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
?>
<style>
    .text-xxs{
        display:none;
    }
</style>