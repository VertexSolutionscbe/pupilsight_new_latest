<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/bus_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Bus Details'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

   
    
    // $searchform = Form::create('searchForm','');
    // $searchform->setFactory(DatabaseFormFactory::create($pdo));
    // $searchform->addHiddenValue('studentId', '0');
    // $row = $searchform->addRow();
    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightProgramID', __('Program'));
    //     $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();

    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    //     $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost)->required();     
   
    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightYearGroupID', __('Class'));
    //     $col->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->required();

    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightRollGroupID', __('Section'));
    //     $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->selected($pupilsightRollGroupID)->required();

    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightPersonID', __('Student Id'));
    //     $col->addTextField('pupilsightPersonID')->setValue($pupilsightPersonID)->addClass('txtfield');
    
    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('student_name', __('Student Name'));
    //     $col->addTextField('student_name')->setValue($student_name)->addClass('txtfield');

    

    // $col = $row->addColumn()->setClass('newdes');   
    // $col->addLabel('', __(''));
    // $col->addContent('<button class=" btn btn-primary">Search</button>');

    // echo $searchform->getOutput();


    $TransportGateway = $container->get(TransportGateway::class);
    $criteria = $TransportGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $TransportGateway->getBusdetails($criteria);
    $table = DataTable::createPaginated('BusManage', $criteria);
    // echo '<pre>';
    // print_r($yearGroups);
    // echo '</pre>';

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    //echo "<div style='height:50px;'><div class='float-right mb-2'>";  
    //echo "&nbsp;&nbsp;<a href='index.php?q=/modules/Transport/bus_manage_add.php' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  

    
    echo "<div style='height:50px;'><div class='float-left mb-2'>";
    echo "<a href='index.php?q=/modules/Transport/bus_manage_add.php' class='btn btn-primary'>Add</a>&nbsp;&nbsp;";  
    echo "<a href='index.php?q=/modules/Transport/bus_bulk_data.php' class='btn btn-primary'>Bulk Data Upload</a>&nbsp;&nbsp;";  
    echo "</div></div>";
    

        //`trans_bus_details`(`id`, `vehicle_number`, `name`, `model`, `vtype`, `capacity`, `register_date`, `insurance_exp`, `fc_expiry`, `driver_name`, `driver_mobile`, `coordinator_name`, `coordinator_mobile`, `photo`, `cdt`, `udt`)

    
    // $table->addColumn('id', __('SI No'))
    //     ->format(function ($yearGroups) {
    //         if ($dataSet['status'] == '1') {
    //             return 'Draft';
    //         } else if ($dataSet['status'] == '2' ) {
    //             return 'Published';
    //         } else {
    //             return 'Stoped';
    //         }
    //         return $dataSet['status'];
    // });  
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('vehicle_number', __('Vehicle Number'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('model', __('Model'));
    $table->addColumn('vtype', __('Type'));
    $table->addColumn('capacity', __('Capacity'));
   /* 
    $table->addColumn('7', __('Req.Date'));
    $table->addColumn('8', __('Insurance Expiry'));
    $table->addColumn('9', __('F.C Expiry Date'));
    $table->addColumn('10', __('Driver Name'));
    $table->addColumn('11', __('Driver Mobile'));
    $table->addColumn('12', __('Transport Coordinator Name'));
    $table->addColumn('13', __('Transport Coordinator Mobile'));

    $table->addColumn('image_240', __('Photo'))
    ->context('secondary')
    ->notSortable()
    ->format(Format::using('userPhoto', ['image_240', 'sm']));*/
  
   
    // $table->addColumn('bank_name', __('Bank Name'));
    // $table->addColumn('ac_no', __('Account No'));
    
        
    // ACTIONS
   $table->addActionColumn()
        ->addParam('id')
        ->format(function ($yearGroups, $actions) use ($guid) {
          

            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Transport/bus_manage_edit.php');
        //if(empty($yearGroups['chkkount'])){
            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Transport/bus_manage_delete.php');
        //}
                  /*  $actions->addAction('view', __('View Details'))
                    ->setURL('/modules/Transport/bus_view_details.php');       
                    */
                    $actions->addAction('view', __('View'))
                    ->setURL('/modules/Transport/bus_view_details.php')
                    ->modalWindow(1100, 550);
           
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);

    // $form = Form::create('importbusdetails', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/bus_manage_addimportProcess.php');
    // $form->setFactory(DatabaseFormFactory::create($pdo));

    // $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    // $row = $form->addRow();
    // $col = $row->addColumn()->setClass('newdes');
    // $col->addLabel('file', __('Select CSV File'));
    // $col->addFileUpload('file')->accepts('.csv')->setMaxUpload(false);

    // $row = $form->addRow();
    // $row->addFooter();
    // $row->addSubmit();
    // echo $form->getOutput();
}
