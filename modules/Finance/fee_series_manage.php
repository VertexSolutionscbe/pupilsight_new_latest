<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_series_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Fee series'));
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $FeesGateway->getFeesSeries($criteria, $pupilsightSchoolYearID);

    // echo '<pre>';
    // print_r($yearGroups);
    // echo '</pre>';

    // DATA TABLE
    $table = DataTable::createPaginated('FeeItemManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Finance/fee_series_manage_add.php' class=' btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  

    
    
    $table->addColumn('serial_number', __('Sl No'));
    $table->addColumn('series_name', __('Series Name'));
    $table->addColumn('format', __('Format'));
    $table->addColumn('description', __('Description'));
    $table->addColumn('acedemic_year', __('Academic Year'));
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($yearGroups, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Finance/fee_series_manage_edit.php');
            $kount = $yearGroups['invkount'] + $yearGroups['reckount'];
            if(empty($kount)){
                $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Finance/fee_series_manage_delete.php');
            } else {
                $actions->addAction('delete', __('Delete'))
                    ->setClass('delFeeSeriesAlert');
            }        
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}

?>

<script>
    $(document).on('click','.delFeeSeriesAlert', function(){
        alert('This Fee Series is Already in Use');
        return false;
    });
</script>