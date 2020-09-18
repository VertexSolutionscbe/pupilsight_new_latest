<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_counter_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Counter'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        //->pageSize(1)
        ->sortBy(['id'])
        ->fromPOST();
       
    $id = $_GET['id'];  

    if($_POST){
        $input = $_POST;
        $from_date =  $_POST['from_date'];
        $to_date = $_POST['to_date'];
    } else {
        $from_date = ''; 
        $to_date = '';
        unset($_SESSION['fee_counter_search']);
    }

    if(!empty($from_date)){
        $_SESSION['fee_counter_search'] = $input;
    }


    $yearGroups = $FeesGateway->getFeesCounterUsedBy($criteria, $id, $input);

    $c_query = $FeesGateway->getFeesCounterUsedTotal($criteria, $id, $input);  
    $sqldr =$c_query;
    $resultdr = $connection2->query($sqldr);
    $master = $resultdr->fetch();
    $Tamount_paying=$master['amount_paying'];
    // foreach($master as $am){
    //     $Tamount_paying+=$am['amount_paying'];
    // }
// print_r($Tamount_paying);

    // DATA TABLE

    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $row = $searchform->addRow();
    
    $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('from_date', __('Start Date'));
        $col->addDate('from_date')->setValue($from_date)->addClass('txtfield');

    $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('to_date', __('End Date'));
        $col->addDate('to_date')->setValue($to_date)->addClass('txtfield');    

    

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('<button class=" btn btn-primary">Search</button>');

    echo $searchform->getOutput();

    $table = DataTable::createPaginated('FeeCounterManage', $criteria);
  
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Finance/fee_counter_manage.php' class=' btn btn-primary'>Back</a>&nbsp;&nbsp<a style='color:#666; cursor:pointer' id='exportCounterDate'><span  style='bottom: 1px;'><i class='fas fa-file-export'></i>Export</span></a></div><div class='float-none'></div></div>";  

    
    
    //$table->addColumn('sequenceNumber', __('sequenceNumber'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('code', __('Code'));
    $table->addColumn('officialName', __('Used By'));
    $table->addColumn('start_time', __('Start Time'));
    $table->addColumn('end_time', __('End Time'));  
    $table->addColumn('active_date', __('Date'))
    ->context('secondary')
    ->width('16%')
    ->translatable()
    ->format(function ($person) {
       $dt = new DateTime($person['active_date']);
       $st_date= $dt->format('d/m/Y');
       return $st_date;        
            });
    $table->addColumn('pname', __('Payment mode')); 
    $table->addColumn('amount_paying', __('Amount collected')); 
  

    echo $table->render($yearGroups);
?><?php
echo"<table id='expore_tbl_2' style='float: right;margin-right: 50px;border: none;'><tr ><td><strong>Total Amount collected</strong></td><td style='border: none;'><strong>".number_format($Tamount_paying,2)."</strong></td></tr>



</table>";

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}

?>
<style>
    .text-xxs {
        display : none;
    }
</style>

<!-- <script>
    $(function () {
        var totval = 0;
        $("#expore_tbl tr:not(:first)").each(function () { 
            var valueOfCell = $(this).find('td:last-child').text();
            if(valueOfCell != ''){
               totval += parseInt(valueOfCell);
            }
        });
        console.log(totval);
    });
</script> -->