<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Invoice Assign'), 'invoice_assign_manage.php')
        ->add(__('Add Invoice Assign'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Finance/invoice_assign_manage_edit.php&id=' . $_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Bulk Update Invoice');
    echo '</h2>';
    //if(isset($_REQUEST['sid'])?$id=$_REQUEST['sid']:$id="" );
    $id = $_GET['id'];

    $sqlse = 'SELECT id, series_name FROM fn_fee_series ';
    $resultse = $connection2->query($sqlse);
    $feeSeries = $resultse->fetchAll();

    $feeSeriesData = array();
    $feeSeriesData1 = array(''=>'Select Series');
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

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/update_invoice_bulk_dataProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('invoice_student_id', $id);

   
    $row = $form->addRow();
        $row->addLabel('title', __('Invoice Title'));
        $row->addTextField('title')->addClass('txtfield');

    $row = $form->addRow();
        $row->addLabel('due_date', __('Due Date'))->addClass('dte');
        $row->addDate('due_date')->setId('start_date');

    $row = $form->addRow();
        $row->addLabel('fn_fees_head_id', __('Account Head'));
        $row->addSelect('fn_fees_head_id')->fromArray($feeHeadData);   

    $row = $form->addRow();
        $row->addLabel('fn_fees_fine_rule_id', __('Fine Rule'));
        $row->addSelect('fn_fees_fine_rule_id')->fromArray($fineRuleData);    

    $row = $form->addRow();
        $row->addLabel('inv_fn_fee_series_id', __('Invoice Series'));
        $row->addSelect('inv_fn_fee_series_id')->fromArray($feeSeriesData);        

    $row = $form->addRow();
        $row->addLabel('rec_fn_fee_series_id', __('Receipt Series'));
        $row->addSelect('rec_fn_fee_series_id')->fromArray($feeSeriesData);   

    $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();
    
    echo $form->getOutput();
  
} ?>

<!-- <form method="post">
    <input type="hidden" name="invoice_student_id" value="<?php /* echo $id; */ ?>">
    <input type="text" name="invoice_title">

    <input type="submit" class="btn btn-primary" value="Update">
</form> -->
<script>
    
</script>