<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/bus_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Bus'), 'bus_manage.php')
        ->add(__('Edit Bus'));

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
            // //`trans_bus_details`(`id`, `vehicle_number`, `name`, `model`, `vtype`, `capacity`, `register_date`, `insurance_exp`, `fc_expiry`, `driver_name`, `driver_mobile`, `coordinator_name`, `coordinator_mobile`, `photo`, `cdt`, `udt`)  
            $data = array('id' => $id);
            $sql = 'SELECT * FROM trans_bus_details WHERE id=:id';
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
            echo __('Edit Bus Details');
            echo '</h2>';


 
            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/bus_manage_editProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));
        
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('oldimage', $values['photo']);
                //`trans_bus_details`(`id`, `vehicle_number`, `name`, `model`, `vtype`, `capacity`, `register_date`, `insurance_exp`, `fc_expiry`, `driver_name`, `driver_mobile`, `coordinator_name`, `coordinator_mobile`, `photo`, `cdt`, `udt`)
        
           
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('vehicle_number', __('Vehicle Number'));
                    $col->addTextField('vehicle_number')->addClass('txtfield')->required()->setValue($values['vehicle_number']);
        
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('name', __('Name'));
                    $col->addTextField('name')->required()->setValue($values['name']);   
        
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('model', __('Model'));
                    $col->addTextField('model')->required()->setValue($values['model']); 
        
               $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('vtype', __('Type'));
                    $col->addTextField('vtype')->required()->setValue($values['vtype']);        
        
                  //  $date = isset($_GET['date']) ? Format::dateConvert($_GET['date']) : date('Y-m-d');
        
                    $row = $form->addRow();
                    $col = $row->addColumn()->setClass('newdes');
                        $col->addLabel('capacity', __('Capacity'));
                        $col->addTextField('capacity')->addClass('txtfield numfield')->required()->setValue($values['capacity']);
            
                    $col = $row->addColumn()->setClass('newdes');
                       
                    $col->addLabel('register_date', __('Reg. Date'))->addClass('dte');

                    $register_date = date('d/m/Y', strtotime($values['register_date']));
                    $col->addDate('register_date')->required()->setValue($register_date);
                   
            
                    $col = $row->addColumn()->setClass('newdes');
                        $col->addLabel('insurance_exp', __('Insurance Expiry'))->addClass('dte');
                        $insurance_exp = date('d/m/Y', strtotime($values['insurance_exp']));             
                        $col->addDate('insurance_exp')->required()->setValue($insurance_exp);
            
                   $col = $row->addColumn()->setClass('newdes');
                   $col->addLabel('fc_expiry', __('FC Expiry Date'))->addClass('dte');
                   $fc_expiry = date('d/m/Y', strtotime($values['fc_expiry']));          
                   $col->addDate('fc_expiry')->required()->setValue($fc_expiry);
                       
                        
                   $imgpath = 'modules/Transport/'.$values['photo'];  
        
                        $row = $form->addRow();
                        $col = $row->addColumn()->setClass('newdes');
                            $col->addLabel('driver_name', __('Driver Name'));
                            $col->addTextField('driver_name')->addClass('txtfield')->required()->setValue($values['driver_name']);
                
                        $col = $row->addColumn()->setClass('newdes');
                            $col->addLabel('driver_mobile', __('Driver Mobile'));
                            $col->addTextField('driver_mobile')->addClass('txtfield   numfield')->required()->setValue($values['driver_mobile']);  
                
                        $col = $row->addColumn()->setClass('newdes');
                            $col->addLabel('coordinator_name', __('Transport Coordinator Name'));
                            $col->addTextField('coordinator_name')->required()->setValue($values['coordinator_name']);    
                
                       $col = $row->addColumn()->setClass('newdes');
                            $col->addLabel('coordinator_mobile', __('Transport Coordinator Mobile'));
                            $col->addTextField('coordinator_mobile')->addClass('txtfield   numfield')->required()->setValue($values['coordinator_mobile']);        
                    
                         
                          
                                $row = $form->addRow();
                                $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('file', __('Edit Image File'));
                                    $col->addFileUpload('file')->addClass(' szewdt_file')->setValue($values['photo'])
                                    ->accepts('.jpg,.jpeg,.gif,.png')
                                    ->setMaxUpload(false);   
                                
                                $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('', __(''));
                                    if(!empty($values['photo'])){
                                        $col->addContent('<img src="'.$imgpath.'" height="100px" width="100px" />');  
                                    }
                                         

                
            $row = $form->addRow()->setID('lastseatdiv');
                $row->addFooter();
                $row->addSubmit();
        
            echo $form->getOutput();


           
        }
    }
}
