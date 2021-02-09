<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_route_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Transport'), 'routes.php')
        ->add(__('Copy Bus Route'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    

    
    $type = array();
    $type =  array(''=>'Select  Type',
        'pickup' =>'Pick Up',
        'drop'=>'Drop',
        'both'=>'Both'
);

    //Check if school year specified
    $id = $_GET['id'];
   
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM trans_routes WHERE id=:id';
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
            // print_r($values);die();
            $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
            $resulta = $connection2->query($sqla);
            $academic = $resulta->fetchAll();
        
            $academicData = array();
            foreach ($academic as $dt) {
                $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        


            $datac = array('route_id' => $id);
             $sqlc = 'SELECT * FROM trans_route_stops WHERE route_id=:route_id';
             $resultc = $connection2->prepare($sqlc);
             $resultc->execute($datac);
             $childvalues = $resultc->fetchAll();
         
             
    $sqlr = 'SELECT id, name FROM trans_bus_details ';
    $resultr = $connection2->query($sqlr);
    $bus_name = $resultr->fetchAll();
    $bus_id = array();
    $bus_name1 = array(''=>'Select bus name');
    $bus_name2 = array();
   
    foreach ($bus_name as $dt) {
        $bus_name2[$dt['id']] = $dt['name'];
    }


    $bus_id = $bus_name1 + $bus_name2;

            echo '<h2>';
            echo __('Copy Bus Route');
            echo '</h2>';

            echo '<h5>(Note: This Route is copied. Here you can edit only Acadamic Year)</h5><br>';

           
            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/transport_route_copiesProcess.php');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('route_name', __('Route Name'));
            $col->addTextField('route_name')->addClass('txtfield')->required()->readonly()->setValue($values['route_name']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($values['pupilsightSchoolYearID']);    

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('bus_id', __('Bus name'));
            $col->addSelect('bus_id')->readonly()->fromArray($bus_id)->selected($values['bus_id']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('type', __('Type'));
            $col->addSelect('type')->readonly()->fromArray($type)->required()->selected($values['type']);     

           
            
            $row = $form->addRow();
                
               

                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('start_point', __('Start Point'));
                    $col->addTextField('start_point')->readonly()->addClass('txtfield')->required()->setValue($values['start_point']);

                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('start_time', __('Start Time'))->addClass('dte');
                    $col->addTime('start_time')->readonly()->addClass('txtfield')->required()->setValue($values['start_time'])->maxLength(8);
        
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('end_point', __('End Point'));
                    $col->addTextField('end_point')->readonly()->addClass('txtfield')->required()->setValue($values['end_point']);
                        
                   
                 
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('end_time', __('End Time'))->addClass('dte');
                    $col->addTime('end_time')->readonly()->addClass('txtfield')->required()->setValue($values['end_time'])->maxLength(8);

                    $row = $form->addRow();
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        $col->addLabel('fixed_fine_type', __('Stops'));
                        $col->addTextField('')->setClass('hiddencol');
                    
                    $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                        $col->addLabel('', __(''));
                        $col->addTextField('');     
                    
                 //   $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt');
                 //        $col->addButton(__('Add'))->setID('addTransportStops')->addData('cid', '1')->addData('disid', 'nodata')->addClass('btn btn-primary');
            
                 if(!empty($childvalues)){
                    $i = 1;
                       foreach($childvalues as $cv){
                        $row = $form->addRow()->setID('seatdiv')->addClass('mt-3 seatdiv fixedfine'.$cv['id']);
            
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                        $col->addLabel('stop_no', __('Stop Number'))->addClass('dte');
                        }
                        $col->addTextField('stop_no['.$cv['id'].']')->readonly()->addClass('txtfield kountseat numfield')->required()->setValue($cv['stop_no']);
            
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                        $col->addLabel('stop_name', __('Stop Name'))->addClass('dte');
                        }
                        $col->addTextField('stop_name['.$cv['id'].']')->readonly()->addClass('txtfield kountseat')->required()->setValue($cv['stop_name']);
                
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                        $col->addLabel('pickup_time', __('Pick Up Time'))->addClass('dte');
                        }
                        $col->addTextField('pickup_time['.$cv['id'].']')->readonly()->addClass('txtfield')->required()->setValue($cv['pickup_time']);   
            
                        $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        if($i == '1'){
                        $col->addLabel('drop_time', __('Drop Time'))->addClass('dte');
                        }
                        $col->addTextField('drop_time['.$cv['id'].']')->readonly()->addClass('txtfield kountseat szewdt')->required()->setValue($cv['drop_time']); 
                        $col->addLabel('', __(''))->addClass('dte');   
                        
                        //$col = $row->addColumn()->setClass('newdes nobrdbtm');
                       
                        // if($i == '1'){
                        // $col->addLabel('oneway_price', __('One way Price'))->addClass('dte');
                        // }
                        // $col->addTextField('oneway_price['.$cv['id'].']')->addClass('txtfield kountseat')->required()->setValue($cv['oneway_price']);
            
                        //     $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        
                        // if($i == '1'){
                        // $col->addLabel('twoway_price', __('Two way Price'))->addClass('dte');
                        // }
                        // $col->addTextField('twoway_price['.$cv['id'].']')->addClass('txtfield')->required()->setValue($cv['twoway_price']);   
            
                        // $col = $row->addColumn()->setClass('newdes nobrdbtm');
                        // if($i == '1'){ $col->addLabel('tax', __('Tax'))->addClass('dte');}
                        // $col->addTextField('tax['.$cv['id'].']')->addClass('txtfield kountseat szewdt_trans')->required()->setValue($cv['tax']);
                        // $col->addLabel('', __(''))->addClass('dte');   

                        $i++;  
                    }             
                } else {  
                    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine');
            
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('stop_no', __('Stop Number'))->addClass('dte');
                    $col->addTextField('stop_no[1]')->readonly()->addClass('txtfield kountseat numfield')->required();
        
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('stop_name', __('Stop Name'))->addClass('dte');
                    $col->addTextField('stop_name[1]')->readonly()->addClass('txtfield kountseat')->required();
            
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('pickup_time', __('Pick Up Time'))->addClass('dte');
                    $col->addTextField('pickup_time[1]')->readonly()->addClass('txtfield')->required();   
        
                    $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    $col->addLabel('drop_time', __('Drop Time'))->addClass('dte');
                    $col->addTextField('drop_time[1]')->readonly()->addClass('txtfield kountseat szewdt')->required();
                    $col->addLabel('', __(''))->addClass('dte');   
                    
                    // $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    // $col->addLabel('oneway_price', __('One way Price'))->addClass('dte');
                    // $col->addTextField('oneway_price[1]')->addClass('txtfield kountseat')->required();
        
                    //     $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    // $col->addLabel('twoway_price', __('Two way Price'))->addClass('dte');
                    // $col->addTextField('twoway_price[1]')->addClass('txtfield')->required();   
        
                    // $col = $row->addColumn()->setClass('newdes nobrdbtm');
                    // $col->addLabel('tax', __('Tax'))->addClass('dte');
                    // $col->addTextField('tax[1]')->addClass('txtfield kountseat szewdt_trans')->required();
                    // $col->addLabel('', __(''))->addClass('dte');  
                   
                }          
                                      
    $row = $form->addRow()->setID('route_stops');




            // $row = $form->addRow();
                
            //     $col = $row->addColumn()->setClass('newdes');
            //         $col->addLabel('fn_fees_fine_rule_id', __('Fine Rule'));
            //         $col->addSelect('fn_fees_fine_rule_id')->fromArray($fineRuleData)->selected($values['fn_fees_fine_rule_id']);    

            //     $col = $row->addColumn()->setClass('newdes');
            //         $col->addLabel('fn_fees_discount_id', __('Discount Rule'));
            //         $col->addSelect('fn_fees_discount_id')->fromArray($feeDiscountData)->selected($values['fn_fees_discount_id']);
                
            //     $col = $row->addColumn()->setClass('hiddencol');
            //         $col->addLabel('', __(''));
            //         $col->addTextField('');           
            

            // $type = array('Y'=>'Yes','N'=>'No');
            // $row = $form->addRow();
            //     $col = $row->addColumn()->setClass('newdes nobrdbtm');
            //         $col->addLabel('fixed_fine_type', __('Fee Structure Item'));
            //         $col->addTextField('')->setClass('hiddencol');
                
            //     $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            //         $col->addLabel('', __(''));
            //         $col->addTextField(''); 
                    
            //     $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt');
            //         $col->addButton(__('Add'))->setID('addFeeStructureItem')->addData('cid', $lastId)->addData('disid', $feeItemIds)->addClass('btn btn-primary');

            
            // if(!empty($childvalues)){
            //     $i = 1;
            //     foreach($childvalues as $cv){
            //         $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine deltr'.$cv['id']);
                
            //         $col = $row->addColumn()->setClass('newdes nobrdbtm');
            //         if($i == '1'){
            //             $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
            //         }    
            //             $col->addSelect('fn_fee_item_id['.$cv['id'].']')->fromArray($feeItemData)->setId('feeStructureItemDisableId')->addClass('txtfield allFeeItemId')->selected($cv['fn_fee_item_id']);
                    
            //         $col = $row->addColumn()->setClass('newdes nobrdbtm');
            //         if($i == '1'){
            //             $col->addLabel('amount', __('Amount'))->addClass('dte');
            //         }     
            //             $col->addTextField('amount['.$cv['id'].']')->addClass('txtfield kountseat numfield')->setValue($cv['amount']);

            //         $col = $row->addColumn()->setClass('newdes nobrdbtm');
            //         if($i == '1'){
            //             $col->addLabel('tax', __('Tax'))->addClass('dte');
            //         }    
            //             $col->addSelect('tax['.$cv['id'].']')->fromArray($type)->addClass('txtfield')->selected($cv['tax']);    

            //         $col = $row->addColumn()->setClass('newdes nobrdbtm remove_icon');
            //         if($i == '1'){
            //             $col->addLabel('tax_percent', __('Tax Percent'))->addClass('dte');
            //         }    
            //             $col->addTextField('tax_percent['.$cv['id'].']')->addClass('txtfield kountseat szewdt numfield')->setValue($cv['tax_percent']); 
            //             $col->addContent('<div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="far fa-times-circle delFeeStructureItem " data-id="'.$cv['id'].'" ></i></div>'); 
            //         $i++;       
            //     }             
            // } else {
            //     $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine');
                
            //         $col = $row->addColumn()->setClass('newdes nobrdbtm');
            //             $col->addLabel('fn_fee_item_id', __('Fee Item'))->addClass('dte');
            //             $col->addSelect('fn_fee_item_id[1]')->fromArray($feeItemData)->addClass(' txtfield');
                    
            //         $col = $row->addColumn()->setClass('newdes nobrdbtm');
            //             $col->addLabel('amount', __('Amount'))->addClass('dte');
            //             $col->addTextField('amount[1]')->addClass('txtfield kountseat numfield');

            //         $col = $row->addColumn()->setClass('newdes nobrdbtm');
            //             $col->addLabel('tax', __('Tax'))->addClass('dte');
            //             $col->addSelect('tax[1]')->fromArray($type)->addClass('txtfield');    

            //         $col = $row->addColumn()->setClass('newdes nobrdbtm');
            //             $col->addLabel('tax_percent', __('Tax Percent'))->addClass('dte');
            //             $col->addTextField('tax_percent[1]')->addClass('txtfield kountseat szewdt numfield'); 
            //             $col->addLabel('', __(''))->addClass('dte');             
            
            // }       
            //     $row = $form->addRow()->setID('lastseatdiv');
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
