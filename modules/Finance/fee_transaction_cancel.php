<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$transids = $session->get('transaction_ids');

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_transaction_cancel.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Cancel Transaction'), 'fee_transaction_manage.php')
        ->add(__('Add Fee Cancel Transaction'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Cancel Fee Transaction');
    echo '</h2>';

    echo '<h4>';
    echo __('Are you sure want to Cancel selected Records?');
    echo '</h4>';

     

    $form = Form::create('cancelTransactionForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_transaction_cancelProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('trans_id', $transids);

    $row = $form->addRow();
        $row->addLabel('name', __('Remarks : (Mandatory)'));
        $row->addTextArea('remarks')->setRows(4)->required();

   
        
    $row = $form->addRow();
        $row->addFooter();
        $row->addContent('<a id="cancelTransaction" class="btn btn-primary">Submit</a>');

    echo $form->getOutput();

}
?>

<script>
    $(document).on('click', '#cancelTransaction', function () {
        var remarks = $("#remarks").val();
        var formData = new FormData(document.getElementById("cancelTransactionForm"));
        if (remarks != '') {
            $.ajax({
                url: 'modules/Finance/fee_transaction_cancelProcess.php',
                type: 'post',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                async: false,
                success: function (response) {
                    $("#preloader").hide();
                    alert('Transaction Cancelled Successfully!');
                    //location.reload();
                    $("#cancelTransactionForm")[0].reset();
                    $("#closeSM").click();
                    $("#searchTransaction").click();
                }
            });
        } else {
            alert('You Have to Enter Mandatory Fields!');
        }
    });
</script>