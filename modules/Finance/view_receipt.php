<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$transids = $session->get('doc_receipt_id');

if (isActionAccessible($guid, $connection2, '/modules/Finance/view_receipt.php') != false) {
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
?>

<?php
//  echo "<a  href='".$_SESSION[$guid]['absoluteURL']."/public/receipts/".$transids.".docx' download class='btn btn-primary' type='' >Download receipt</a></br>";
// echo "<a  href='".$_SESSION[$guid]['absoluteURL']."/cms/convertPdf.php?id=".$transids."' class='btn btn-primary' type='' >Download receipt</a></br>";
  
// } ?> 

<?php /* ?>
<iframe style="width:100%; height:500px;" src="https://docs.google.com/gview?url=<?php echo $_SESSION[$guid]['absoluteURL'];?>/public/receipts/<?php echo $transids;?>.docx&embedded=true"></iframe>

<?php */ ?>
<?php
    $filePath = urlencode($_SESSION[$guid]['absoluteURL'] . "/public/receipts/" . $transids . ".pdf");
    $pdfView = $_SESSION[$guid]['absoluteURL'] . "/thirdparty/pdfjs/web/viewer.php?src=" . $filePath;
} ?>

<iframe style="width:100%; height:500px;border=0;" border='0' id="printPage" src="<?php echo $pdfView; ?>"></iframe>
      

