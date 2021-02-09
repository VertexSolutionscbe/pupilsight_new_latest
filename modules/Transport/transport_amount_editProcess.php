<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/transport_amount_edit.php&id='.$id.' ';

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_amount_editProcess.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $oneway_price = $_POST['oneway_price'];
    $twoway_price = $_POST['twoway_price'];

    $tax = $_POST['tax'];

try {


    $data1 = array('oneway_price' => $oneway_price, 'twoway_price' => $twoway_price, 'tax' => $tax, 'id' => $id);
    $sql1 = 'UPDATE trans_route_price SET  oneway_price=:oneway_price, twoway_price=:twoway_price, tax=:tax WHERE id=:id';
    $result1 = $connection2->prepare($sql1);
    $result1->execute($data1);

    $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);
    $URL .= "&return=success0";
    header("Location: {$URL}");
}
catch (PDOException $e){
    $URL .= "&return=error0";
    header("Location: {$URL}");
}

}
