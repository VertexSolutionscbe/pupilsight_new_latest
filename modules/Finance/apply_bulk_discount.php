<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
    $session = $container->get('session');
    $a_stuid = $session->get(['a_stuid']);
    $a_yid = $session->get(['a_yid']);
    $a_invoices_ids = $session->get(['a_invoices_ids']);
if (isActionAccessible($guid, $connection2, '/modules/Finance/apply_discount.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Apply Discount'), 'fee_transaction_manage.php')
        ->add(__('Apply Discount'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Apply Discount');
    echo '</h2>';

    $fn_fee_invoice_id = $_GET['id'];
    $std_id = $_GET['tid'];

    if(!empty($fn_fee_invoice_id) && !empty($std_id)){

        $sql = 'SELECT a.*, b.name FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id WHERE fn_fee_invoice_id = '.$fn_fee_invoice_id.' ';
        $result = $connection2->query($sql);
        $itemData = $result->fetchAll();

    // $discount_array=array(" "=>'Choose discount', "2"=>'AT FEE ITEM LEVEL');
        $discount_array=array("2"=>'AT FEE ITEM LEVEL');

    
        // $form = Form::create('apply_discount_form','');
        // $form->setFactory(DatabaseFormFactory::create($pdo));

        // $form->addHiddenValue('a_stuid',$a_stuid);
        // $form->addHiddenValue('a_yid',$a_yid);
        // $form->addHiddenValue('a_invoices_ids',$a_invoices_ids);
        // $form->addHiddenValue('type','apply_discount_request');
        // $row = $form->addRow();
        // $row->addLabel('discount_type_change', __('Apply Discount : (Mandatory)'));
        // $row->addSelect('discount_type_change')->fromArray($discount_array);
        // $row = $form->addRow();
        // $row->addContent('<div class="discount_type_change_results" style="width: 124%;">');
?>
<form method="post">
    <a id="applyDiscount" class="btn btn-primary" style="float:right;margin-bottom:10px;">Apply</a>
    <input type="hidden" id="std_id" value="<?php echo $std_id?>">
    <input type="hidden" id="fn_fee_invoice_id" value="<?php echo $fn_fee_invoice_id?>">
    <table class="table" cellspacing="0" style="width: 100%;">
        <thead>
            <tr class="head">
                <th>Sl.No</th>
                <th>Fee Item</th>
                <th>Amount</th>
                <th>Discount</th>
                <th>Select</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($itemData)){ 
                $i = 1;
                foreach($itemData as $fI) {    
        ?>
            <tr class="odd invrow" role="row">
                <td width="5%">
                    <?php echo $i++; ?>
                </td>
                
                <td width="10%">
                <?php echo $fI['name']; ?>   
                </td> 
                <td width="5%">
                <?php echo $fI['total_amount'] ; ?> 
                </td>                          
                <td width="10%">
                    <input type="number" class="form-control itid_<?php echo $fI['id']; ?>" value="" readonly>
                </td>
                <td width="10%"><label class="leading-normal" for="feeItemid"></label> <input type="checkbox" class="a_selFeeItem" id="feeItemid" data-id="<?php echo $fI['id']; ?>" value="<?php echo  $fI['id'] ; ?>" ></td>
            </tr>
        <?php } } ?>
        </tbody>
    </table>
</form>
<?php 
}

}
?>

<script>
    $(document).on('click','#applyDiscount',function(){
        var items = [];
        var dicout_val=[];
        var stdId = $("#std_id").val();
        var invId = $("#fn_fee_invoice_id").val();
        var dicout_val=[];
        var type = 'bulkItemDiscount';
        $.each($(".a_selFeeItem:checked"), function() {
            var id=$(this).val();
            var val =$('.itid_'+id).val();
            items.push(id);
            dicout_val.push(val);
        });
        if(items.length!=0){
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: invId,type:type,discountVal:dicout_val,items:items,stdId:stdId},
                async: true,
                success: function(response) {
                    alert("Discount is Applied");
                    $("#TB_closeWindowButton").click();
                    location.reload();
                }
            });
        } else {
            alert('You Have to Select Item');
        }

    });
</script>