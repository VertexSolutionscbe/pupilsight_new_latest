<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$session = $container->get('session');
$transids = $session->get('doc_receipt_id');

if (isActionAccessible($guid, $connection2, '/modules/Campaign/view_receipt.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $transid = $_GET['tid'];
    //$transid = '160249470972';
?>

<?php
    $filePath = urlencode($_SESSION[$guid]['absoluteURL'] . "/public/receipts/" . $transid . ".pdf");
    $pdfView = $_SESSION[$guid]['absoluteURL'] . "/thirdparty/pdfjs/web/viewer.php?src=" . $filePath;
} ?>

<iframe style="width:100%; height:500px;border=0;" border='0' id="printPage" src="<?php echo $pdfView; ?>"></iframe>