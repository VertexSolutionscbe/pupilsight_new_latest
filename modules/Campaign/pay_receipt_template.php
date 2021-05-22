<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Admission\AdmissionGateway;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/check_status.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Receipts Template'), 'fee_receipts_manage.php')
        ->add(__('Add Fee Receipts Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    

        echo '<h2>';
        echo __('Pay Receipts');
        echo '</h2>';

        $cid = $_GET['cid'];
        $sid = $_GET['sid'];

        $admissionGateway = $container->get(AdmissionGateway::class);
        $criteria = $admissionGateway->newQueryCriteria()
            ->sortBy(['id'])
            ->fromPOST();

        $receipts = $admissionGateway->getAllPayReceipts($criteria, $cid, $sid);

        
        $table = DataTable::createPaginated('userManage', $criteria);

        $table->addColumn('serial_number', __('SI No'));
        $table->addColumn('type', __('Document Type'));
        $table->addColumn('pay_amount', __('Amount'));
        $table->addColumn('pay_date', __('Date'));
        $table->addColumn('pay_attachment', __('Document'))
        ->format(function ($dataSet) {
            if (!empty($dataSet['pay_attachment'])) {
                return '<a href=" '. $dataSet['pay_attachment'] .'"  title="Download Pay Receipt " download><i title="Uploaded Pay receipt" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a>';
            } else {
                return '';
            }
            return $dataSet['pay_attachment'];
        });
        
            
        // ACTIONS
        // $table->addActionColumn()
        //     ->addParam('id')
        //     ->format(function ($yearGroups, $actions) use ($guid) {
        //        $actions->addAction('delete', __('Delete'))
        //                 ->setURL('/modules/Campaign/pay_receipt_template_delete.php');
        //     });

        echo $table->render($receipts);
}
?>
<script type="text/javascript">
    $.fn.dataTableExt.sErrMode = 'throw';
    // window.setTimeout(function () {
    //     var table = $('#expore_tbl').DataTable();
    //     table.destroy();
    // }, 500);
    
</script>