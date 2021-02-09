<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Transport/bus_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Bus Details'), 'bus_manage.php')
        ->add(__('Add Bus Details'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/bus_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Bus Details');
    echo '</h2>';
    //echo "<div style='height:50px;'><div class='float-right mb-2'>";
    //echo "&nbsp;<a href='index.php?q=/modules/Transport/bus_manage_add_upload.php' class='btn btn-primary'><i class='mdi mdi-cloud-upload-outline mdi-24px mdi-24px'> Import </i></a></div><div class='float-none'></div></div>";
    
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

  

    
    
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/bus_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        //`trans_bus_details`(`id`, `vehicle_number`, `name`, `model`, `vtype`, `capacity`, `register_date`, `insurance_exp`, `fc_expiry`, `driver_name`, `driver_mobile`, `coordinator_name`, `coordinator_mobile`, `photo`, `cdt`, `udt`)

   
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('vehicle_number', __('Vehicle Number'));
            $col->addTextField('vehicle_number')->addClass('txtfield')->required();

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('Name'));
            $col->addTextField('name')->required();   

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('model', __('Model'));
            $col->addTextField('model')->required();    

       $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('vtype', __('Type'));
            $col->addTextField('vtype')->required();         

          //  $date = isset($_GET['date']) ? Format::dateConvert($_GET['date']) : date('Y-m-d');

            $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('capacity', __('Capacity'));
                $col->addTextField('capacity')->addClass('numfield')->required();
    
            $col = $row->addColumn()->setClass('newdes');
               
            $col->addLabel('register_date', __('Reg. Date'))->addClass('dte');
            $col->addDate('register_date');
    
            $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('insurance_exp', __('Insurance Expiry'))->addClass('dte');
                $col->addDate('insurance_exp');
    
           $col = $row->addColumn()->setClass('newdes');
           $col->addLabel('fc_expiry', __('FC Expiry Date'))->addClass('dte');
           $col->addDate('fc_expiry');
               
                
                

                $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('driver_name', __('Driver Name'));
                    $col->addTextField('driver_name')->addClass('txtfield')->required();
        
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('driver_mobile', __('Driver Mobile'));
                    $col->addTextField('driver_mobile')->addClass('numfield')->maxLength(12)->required();
        
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('coordinator_name', __('Transport Coordinator Name'));
                    $col->addTextField('coordinator_name')->required();    
        
               $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('coordinator_mobile', __('Transport Coordinator Mobile'));
                    $col->addTextField('coordinator_mobile')->addClass('numfield')->maxLength(12)->required();
            
                 
                  
                        $row = $form->addRow();
                        $col = $row->addColumn()->setClass('newdes');
                            $col->addLabel('file', __('Image File'));
                            $col->addFileUpload('file')->addClass(' szewdt_file')
                            ->accepts('.jpg,.jpeg,.gif,.png')
                            ->setMaxUpload(false);              
                   

      /*  $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');   */
            
    
           
       
    
                      
        
    $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

    $form = Form::create('importbusdetails', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/bus_manage_addimportProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    //`trans_bus_details`(`id`, `vehicle_number`, `name`, `model`, `vtype`, `capacity`, `register_date`, `insurance_exp`, `fc_expiry`, `driver_name`, `driver_mobile`, `coordinator_name`, `coordinator_mobile`, `photo`, `cdt`, `udt`)


    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('file', __('Select CSV File'));
    $col->addFileUpload('file')->accepts('.csv')->setMaxUpload(false);

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();
    echo $form->getOutput();
}
