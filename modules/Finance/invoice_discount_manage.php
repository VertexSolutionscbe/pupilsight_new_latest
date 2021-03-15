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

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_discount_manage.php') == false) {
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
        // $pupilsightSchoolYearID=$_POST['pupilsightSchoolYearID'];
        $pupilsightProgramID=$_POST['pupilsightProgramID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        $fn_fee_invoice_id = $_POST['fn_fee_invoice_id'];
        
        if(!empty($pupilsightProgramID)){
            $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
            if(!empty($pupilsightYearGroupID)){
                $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);
                $invoiceData =  $HelperGateway->getInvoice($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);
            }
        }

        
    } else {
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $invoices = array('' => 'Select Invoice');
        $input = ''; 
        $pupilsightProgramID='';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        //$pupilsightPersonID =  '';
        $fn_fee_invoice_id = '';
        
        unset($_SESSION['invoice_discount_search']);
    }

    if(!empty($input)){
        $_SESSION['invoice_discount_search'] = $input;
    }

    
    ?>
    
    <?php 
    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->pageSize(10000)
        ->sortBy(['id'])
        ->fromPOST();

    if($_POST){
        $invoices = $FeesGateway->getInvoiceforDiscount($criteria, $input, $pupilsightSchoolYearID);
        // $c_query = $FeesGateway->getInvoiceTotal($criteria, $input, $pupilsightSchoolYearID);


        // $t_amount=0;
        // foreach($c_query as $am){
        //     $t_amount+=$am['tot_amount'];
        // }
        // $kountTransaction = count($c_query);
    } else {
        // $t_amount = 0;
        // $kountTransaction = 0;
    }
    // DATA TABLE


    //form 
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/invoice_discount_manage.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

   
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'));
            $col->addSelect('pupilsightProgramID')->setID('getMultiClassByProgStaff')->selected($pupilsightProgramID)->fromArray($program)->required();
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
            $col->addSelect('pupilsightYearGroupID')->setID('showMultiClassByProgStaff')->fromArray($classes)->selected($pupilsightYearGroupID)->required();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('[pupilsightRollGroupID', __('Section'))->addClass('dte');
            $col->addSelect('pupilsightRollGroupID')->setID('showMultiSecByProgClsStaff')->fromArray($sections)->selected($pupilsightRollGroupID)->selectMultiple();

            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('fn_fee_invoice_id', __('Invoice'));
            $col->addSelect('fn_fee_invoice_id')->addClass('txtfield')->fromArray($invoiceData)->selected($fn_fee_invoice_id)->required();
            
      
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel(' ', __(' '));
            $col->addSearchSubmit($pupilsight->session, __('Clear Search'));
            //$col->addContent('<button class=" btn btn-primary">Search</button>');


        // $row = $form->addRow()->addClass('tran_tbl');
        // $col = $row->addColumn()->setClass('newdes');
        // $col->addLabel('', __('No of invoice : '.$kountTransaction));

        // $col = $row->addColumn()->setClass('newdes');
        // $col->addLabel('', __('Total Amount:'.$t_amount));

        
    echo $form->getOutput();

    //ends form
    $table = DataTable::createPaginated('fn_fee_invoice', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    echo "<hr />";
    echo "<div style='height:50px;'><div class='float-right mb-2'><a style='' id='bulkDiscount' class='btn btn-primary' data-hrf='fullscreen.php?q=/modules/Finance/apply_bulk_discount.php&id=".$fn_fee_invoice_id."'>Bulk Discount</a><a href=''   class='thickbox' id='apply_discount_popup' style='display:none'></a></div><div class='float-none'></div></div>";  

    
    
    $table->addCheckboxColumn('std_id', __(''));
    $table->addColumn('serial_number', __('Sl.No:'));
    $table->addColumn('title', __('Invoice Title'));
    $table->addColumn('program', __('Program '));
    $table->addColumn('class', __('Class '));
    $table->addColumn('section', __('Section'));
    $table->addColumn('std_name', __('Name'));
    $table->addColumn('admission_no', __('Admission No'));
    $table->addColumn('invoice_no', __('Invoice Series Number'));
    $table->addColumn('inv_amount', __('Invoice Fee Amount'));
    $table->addColumn('paid', __('Pending'))
    ->format(function ($invoices) {
        if (!empty($invoices['paid'])) {
            $pendingAmt = $invoices['inv_amount'] - $invoices['paid'];
            return $pendingAmt;
        } else {
            $dt = $invoices['inv_amount'];
           return $dt;
        }
        return $invoices['paid'];
    });
    $table->addColumn('tot_discount', __('Discount'));
    $table->addColumn('account_head', __('Ac Head'));
    $table->addColumn('due_date', __('Due Date'))
    ->format(function ($invoices) {
        if ($invoices['due_date'] == '1970-01-01') {
            return '';
        } else {
            $dt = date('d/m/Y', strtotime($invoices['due_date']));
           return $dt;
        }
        return $invoices['due_date'];
    });
    $table->addColumn('invstatus', __('Status'));
  

    if($_POST){
         echo $table->render($invoices);
    }

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
?>

<script>
   
   $(document).ready(function () {
        $('#showMultiSecByProgClsStaff').selectize({
            plugins: ['remove_button'],
        });
    });

    $(document).on('change', '#getMultiClassByProgStaff', function () {
        var id = $(this).val();
        var type = 'getClass';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type },
            async: true,
            success: function (response) {
                $("#showMultiClassByProgStaff").html();
                $("#showMultiClassByProgStaff").html(response);
            }
        });
    });

    $(document).on('change', '#showMultiClassByProgStaff', function () {
        var id = $(this).val();
        var pid = $('#getMultiClassByProgStaff').val();
        var type = 'getSection';
        $('#showMultiSecByProgClsStaff').selectize()[0].selectize.destroy();
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid },
            async: true,
            success: function (response) {
                $("#showMultiSecByProgClsStaff").html();
                $("#showMultiSecByProgClsStaff").html(response);
                $('#showMultiSecByProgClsStaff').selectize({
                    plugins: ['remove_button'],
                });
            }
        });

        var type = 'getInvoice';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid },
            async: true,
            success: function (response) {
                $("#fn_fee_invoice_id").html();
                $("#fn_fee_invoice_id").html(response);
            }
        });
    });

   

    $(document).on('click', '#bulkDiscount', function() {
        var favorite = [];
        $.each($("input[name='std_id[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var invId = favorite.join(",");
        var hrf = $(this).attr('data-hrf');
        if (invId) {
            var newhrf = hrf + '&tid=' + invId + '&width=900';
            $("#apply_discount_popup").attr('href', newhrf);
            $("#apply_discount_popup").click();
        } else {
            alert('You Have to Select Invoice.');
        }
    });


</script>
