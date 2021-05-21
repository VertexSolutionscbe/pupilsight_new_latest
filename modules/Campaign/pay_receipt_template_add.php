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
        echo __('Upload Pay Receipt');
        echo '</h2>';

        $cid = $_GET['cid'];
        $sid = $_GET['sid'];

        $admissionGateway = $container->get(AdmissionGateway::class);
        $criteria = $admissionGateway->newQueryCriteria()
            ->searchBy($admissionGateway->getSearchableColumns(), $search)
            ->sortBy(['id'])
            ->fromPOST();

        $receipts = $admissionGateway->getAllPayReceipts($criteria, $cid, $sid);

        $sqlchk = "SELECT * FROM campaign_form_status  WHERE campaign_id = ".$cid."  AND submission_id = ".$sid." AND state = 'Student Contract Generated' ";
        $resultchk = $connection2->query($sqlchk);
        $stateData = $resultchk->fetch();

        if(!empty($stateData)){
            $type = array('' => 'Select Type', 'Registration Fee Paid' => 'Registration Fee Paid', 'Term Fee Paid' => 'Term Fee Paid');
        } else {
            $type = array('' => 'Select Type', 'Registration Fee Paid' => 'Registration Fee Paid');
        }

        $form = Form::create('reportTemplate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/pay_receipt_template_addProcess.php?address='.$_SESSION[$guid]['address']);

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('campaign_id', $cid);
        $form->addHiddenValue('submission_id', $sid);

        $row = $form->addRow();
        $row->addLabel('type', __('Payment Type'));
        $row->addSelect('type')->fromArray($type)->required();

        $row = $form->addRow();
        $row->addLabel('pay_amount', __('Amount'));
        $row->addTextField('pay_amount')->required();

        $row = $form->addRow();
        $row->addLabel('pay_date', __('Payment Date'));
        $row->addDate('pay_date')->required();

        $row = $form->addRow();
        $row->addLabel('file', __('Template'));
        $row->addFileUpload('file')->accepts('.docx,.pdf')->setMaxUpload(false)->required();

        $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();
        echo $form->getOutput();


        echo '<br><br><h1>Pay Receipts</h1>';

        $table = DataTable::createPaginated('userManage', $criteria);

        $table->addColumn('serial_number', __('SI No'));
        $table->addColumn('type', __('Receipt Type'));
        $table->addColumn('pay_amount', __('Amount'));
        $table->addColumn('pay_date', __('Date'));
        $table->addColumn('pay_attachment', __('Receipt'))
        ->format(function ($dataSet) {
            if (!empty($dataSet['pay_attachment'])) {
                return '<a href=" '. $dataSet['pay_attachment'] .'"  title="Download Pay Receipt " download><i title="Uploaded Pay receipt" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a>';
            } else {
                return '';
            }
            return $dataSet['pay_attachment'];
        });
        
            
        // ACTIONS
        $table->addActionColumn()
            ->addParam('id')
            ->format(function ($yearGroups, $actions) use ($guid) {
               $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Campaign/pay_receipt_template_delete.php');
            });

        echo $table->render($receipts);
}
?>
<!-- <script type="text/javascript">
    $('#edit_template_form').on('submit',(function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url:"ajaxSwitch.php", 
        type: "POST",             
        data: formData, 
        contentType: false,      
        cache: false,             
        processData:false, 
        async: false,       
        success: function(data)  
        {
           
            alert(data);
            window.location.reload()
        }
    });

    }));
</script> -->