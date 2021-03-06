<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_transaction_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Item'), 'fee_item_manage.php')
        ->add(__('Add Fee Item'));

    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_manage_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Update Fee Trandsaction status');
    echo '</h2>';

    $transaction = array(
        '' => __('Select Payment Status'),
        'Payment Received' => __('Payment Received')
        
    );
     

    $form = Form::create('updateStatus', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_transaction_updateProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('payment_status', __('Payment Status'));
        $row->addSelect('payment_status')->required()->setId('transStatus')->fromArray($transaction);

    $row = $form->addRow();
        $row->addLabel('payment_status_up_date', __('Date'));
        $row->addDate('payment_status_up_date')->required()->addClass('txtfield');

        
    $row = $form->addRow();
        $row->addFooter();
        $row->addContent('<a id="updateTransStatus" class="btn btn-primary">Submit</a>');

    echo $form->getOutput();

}


?>

<script>
    $(document).on('click', '#updateTransStatus', function () {
        var transStatus = $("#transStatus").val();
        var payment_status_up_date = $("#payment_status_up_date").val();
        var formData = new FormData(document.getElementById("updateStatus"));
        if (transStatus != '' && payment_status_up_date != '') {
            $.ajax({
                url: 'modules/Finance/fee_transaction_updateProcess.php',
                type: 'post',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                async: false,
                success: function (response) {
                    $("#preloader").hide();
                    alert('Transaction Updated Successfully!');
                    //location.reload();
                    $("#updateStatus")[0].reset();
                    $("#closeSM").click();
                    $("#searchTransaction").click();
                }
            });
        } else {
            alert('You Have to Enter Mandatory Fields!');
        }
    });
</script>