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

?>

<?php
 echo "<a  href='".$_SESSION[$guid]['absoluteURL']."/public/receipts/".$transid.".docx' download class='btn btn-primary' type='' >Download receipt</a></br>";
  
} ?> 

<iframe style="width:100%; height:500px;" src="https://docs.google.com/gview?url=<?php echo $_SESSION[$guid]['absoluteURL'];?>/public/receipts/<?php echo $transid;?>.docx&embedded=true"></iframe>
      

