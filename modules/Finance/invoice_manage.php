<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    //Proceed!
    $page->breadcrumbs->add(__('Manage Invoice'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $sections = array(
        'Sectionsa' => __('Sections A'),
        'Sectionsb' => __('Sections B'),
        'Sectionsc' => __('Sections C'),
         'Sectionsd' => __('Sections D'),
    );

    $pStatus = array(
        "" => __('Select Status'),
        "Fully Paid" => __('Fully Paid'),
        "Partial Paid" => __('Partial Paid'),
        "Not Paid" => __('Not Paid'),
        "Canceled" => __('Canceled'));

    $school_list = array(
        '' => __('select School Name'),
        'School name' => __('School name A'),
        'Sectionsb' => __('School name B'),
        'Sectionsc' => __('School name C'),
         'Sectionsd' => __('School name D'),
    );
    $transaction = array(
        '' => __('Select'),
        
    );
    
    
    $tran_status =  array(
        ''=>__('Select Transaction Status')
    );
  
   // print_r($pupilsightSchoolYearID);die();
    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program=array();  
    $program2=array();  
    $program1=array(''=>'Select Program');
    foreach ($rowdataprog as $key => $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program= $program1 + $program2; 
    
    
    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
         $rowdata = $resultval->fetchAll();
         $academic=array();
         $ayear = '';
        if(!empty($rowdata)){
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }
        $pupilsightSchoolYearID = '';
        if (isset($_GET['pupilsightSchoolYearID'])) {
            $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
        }
        if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
            $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
        }
        
        $FeesGateway = $container->get(FeesGateway::class);
    
        // QUERY
        $criteria = $FeesGateway->newQueryCriteria()
            ->pageSize(5000)
            ->sortBy(['id'])
            ->fromPOST();
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
    //    die();
    if($_POST){
        $input = $_POST; 
        $invoice_title=$_POST['invoice_title'];
        $invoice_no=$_POST['invoice_no'];
       // $pupilsightSchoolYearID=$_POST['pupilsightSchoolYearID'];
        $pupilsightProgramID=$_POST['pupilsightProgramID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        //$pupilsightPersonID =  $_POST['pupilsightPersonID'];
        $admission_no = $_POST['admission_no'];
        $student_name = $_POST['student_name'];
        $due_date = $_POST['due_date'];
       // $invoice_date =  $_POST['invoice_date'];
        $invoice_status=$_POST['invoice_status'];

        

        $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
        $sections =  $HelperGateway->getMultipleSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);

        
    } else {
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $input = ''; 
        $invoice_no='';
      //  $pupilsightSchoolYearID='';
        $invoice_title='';
        $pupilsightProgramID='';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        //$pupilsightPersonID =  '';
        $admission_no = '';
        $student_name = '';
        $due_date = '';
        // $invoice_date =  '';
        $invoice_status='';
        unset($_SESSION['invoice_search']);
    }

    if(!empty($input)){
        $_SESSION['invoice_search'] = $input;
    }

    
    ?>
    <input type="hidden" class="cl_sltid" value="<?php echo $pupilsightYearGroupID;?>">
    <input type="hidden" class="sl_sltid" value="<?php echo $pupilsightRollGroupID;?>">
    <?php 
    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->pageSize(5000)
        ->sortBy(['id'])
        ->fromPOST();

    $invoices = $FeesGateway->getInvoice($criteria, $input, $pupilsightSchoolYearID);
    $c_query = $FeesGateway->getInvoiceTotal($criteria, $input, $pupilsightSchoolYearID);

    // echo '<pre>';
    // print_r($invoices);
    // echo '</pre>';
    // die();
   
    //$total = $FeesGateway->getFeesCancelTransactionTotalCount($criteria, $input, $pupilsightSchoolYearID);
    // $sqldr =$c_query;

    // $resultdr = $connection2->query($sqldr);
    // $master = $resultdr->fetchAll();    
    // echo '<pre>';
    // print_r($master);
    // echo '</pre>';

    $t_amount=0;
    foreach($c_query as $am){
        $t_amount+=$am['tot_amount'];
    }
    $kountTransaction = count($c_query);
    // DATA TABLE


    //form 
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/invoice_manage.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

   
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'));
            $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProg')->selected($pupilsightProgramID)->fromArray($program);
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
            $col->addSelect('pupilsightYearGroupID')->setId('showMultiClassByProg')->fromArray($classes)->selected($pupilsightYearGroupID)->selectMultiple();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('[pupilsightRollGroupID', __('Section'))->addClass('dte');
            $col->addSelect('pupilsightRollGroupID')->setId('showMultiSecByProgCls')->fromArray($sections)->selected($pupilsightRollGroupID)->selectMultiple();

            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('student_name', __('Student Name'));
            $col->addTextField('student_name')->setValue($student_name)->addClass('txtfield');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('admission_no', __('Admission No'));
            $col->addTextField('admission_no')->setValue($admission_no)->addClass('txtfield');
             
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelect('pupilsightSchoolYearID')->addClass('txtfield')->fromArray($academic)->selected($pupilsightSchoolYearID)->required();
            
      

            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('invoice_title', __('Invoice Title'));
            $col->addTextField('invoice_title')->setValue($invoice_title)->addClass('txtfield');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('invoice_no', __('Invoice Number'));
            $col->addTextField('invoice_no')->setValue($invoice_no)->addClass('txtfield');
            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('schoolnlist', __('Schools List'));
            // $col->addSelect('schoolnlist')->fromArray($school_list);

            
           
            
            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('select transaction status', __('Transaction'));
            // $col->addSelect('tran_status')->fromArray($tran_status);
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('due_date', __('Due Date'))->addClass('dte');
            $col->addDate('due_date')->setValue($due_date)->addClass('txtfield'); 

            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('invoice_date', __('Invoice Date'))->addClass('dte');
            // $col->addDate('invoice_date')->setValue($enddate)->addClass('txtfield');
             
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('invoice_status', __('Invoice Status'));
            $col->addSelect('invoice_status')->selected($invoice_status)->fromArray($pStatus);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel(' ', __(' '));
            $col->addContent('<button class=" btn btn-primary">Search</button>');
        $row = $form->addRow()->addClass('tran_tbl');
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __('No of invoice : '.$kountTransaction));

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __('Total Amount:'.$t_amount));

        // $col = $row->addColumn()->setClass('newdes');
        // $col->addLabel('', __('Paid:'));
              
        // $col = $row->addColumn()->setClass('newdes');
        // $col->addLabel('', __('Pending:'));

        // $col = $row->addColumn()->setClass('newdes');
        // $col->addContent('<button style="color:#666"  id="export_transaction"><span  style="position: absolute;  left: 220px;
        // bottom: 1px;"><i class="fas fa-file-export"></i>Export</span></button>');

        // $col = $row->addColumn()->setClass('newdes');
        // $col->addLabel('', __('Export:'))
    echo $form->getOutput();

    //ends form
    $table = DataTable::createPaginated('fn_fee_invoice', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    echo "<hr />";
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Finance/invoice_assign_manage_add.php' class='thickbox btn btn-primary'>Generate Invoice By Class</a>";  
    echo "&nbsp;&nbsp;<a href='fullscreen.php?q=/modules/Finance/invoice_assign_student_manage_add.php' class='thickbox btn btn-primary'>Generate Invoice By Student</a>&nbsp;&nbsp;<a style='color:#666;cursor:pointer;font-size: 15px;' id='export_invoice'><i title='Export Excel' class='mdi mdi-file-excel mdi-24px download_icon'></i></a></div><div class='float-none'></div></div>";  

    
    
    //$table->addCheckboxColumn('insid', __(''));
    $table->addColumn('serial_number', __('Sl.No:'));
    $table->addColumn('title', __('Invoice Title'));
    $table->addColumn('program', __('Program '));
    $table->addColumn('class', __('Class '));
    $table->addColumn('section', __('Section'));
    $table->addColumn('std_name', __('Name'));
    $table->addColumn('admission_no', __('Admission No'));
    $table->addColumn('invoice_no', __('Invoice Series Number'));
    $table->addColumn('account_head', __('Ac Head'));
    $table->addColumn('due_date', __('Due Date'))
    ->format(function ($dataSet) {
        if ($dataSet['due_date'] == '1970-01-01') {
            return '';
        } else {
            $dt = date('d/m/Y', strtotime($dataSet['due_date']));
           return $dt;
        }
        return $dataSet['due_date'];
    });
    $table->addColumn('invstatus', __('Status'));
    // ->format(function ($dataSet) {
    //     if ($dataSet['status'] == '1') {
    //         return 'Active';
    //     } else {
    //        return 'Cancelled';
    //     }
    //     return $dataSet['type'];
    // }); 
    // $table->addColumn('bank_name', __('Bank Name'));
    // $table->addColumn('ac_no', __('Account No'));
    // ACTIONS
    $table->addActionColumn()
        ->addParam('invid')
        ->format(function ($dataSet, $actions) use ($guid) {
            if ($dataSet['chkstatus'] == '1') {
                if($dataSet['chkinvstatus'] == 'paid'){
                    $actions->addAction('editAlert', __('EditAlert'))
                    ->setId('alertInvoiceEditData');
                   
                    $actions->addAction('deleteAlert', __('DeleteAlert'))
                    ->setId('alertInvoiceDeleteData');
                } else {
                    $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Finance/invoice_manage_edit.php');

                    $actions->addAction('delete', __('Cancel'))
                    ->setURL('/modules/Finance/invoice_manage_delete.php');
                }
               
            
        
            } else {
                $actions->addAction('reason', __('Reason'))
                    ->setURL('/modules/Finance/invoice_manage_reason_delete.php');
            }

           
        });

    echo $table->render($invoices);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
?>

<script>
   
    $(document).ready(function () {
      	$('#showMultiClassByProg').selectize({
      		plugins: ['remove_button'],
      	});
    });
    
    $(document).ready(function () {
      	$('#showMultiSecByProgCls').selectize({
      		plugins: ['remove_button'],
      	});
    });

</script>
