<?php
/**
 * Created by PhpStorm.
 * User: Preetam
 * Date: 25-Feb-21
 * Time: 4:08 PM
 */
use Pupilsight\Domain\Messenger\GroupGateway;

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/messagewall_category_master.php";

if (isActionAccessible($guid, $connection2, '/modules/Messenger/messagewall_category_masterProcess.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    print_r($_POST);
    //Proceed!
    //Validate Inputs
    $createdby = $_POST['createdby'];
    $categoryname = $_POST['categoryname'];
    $categorystatus = $_POST['categorystatus'];
    if($createdby =='' OR $categoryname =='' OR $categorystatus =='' ){
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }else{
        $data = array('categoryname' => $categoryname);
        $sql = "SELECT categoryname FROM messagewall_category_master WHERE categoryname=:categoryname";
        $result = $connection2->prepare($sql);
        $result->execute($data);
        if ($result->rowCount() > 0){
            $URL .= '&return=errorcategoryname';
            header("Location: {$URL}");
            exit;
        }else{
            try {
                $data = array("categoryname" => $categoryname, "status" => $categorystatus, "createdby" => $createdby);
                $sql = "INSERT INTO messagewall_category_master SET categoryname=:categoryname, status=:status, createdby=:createdby";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit;
            }
        }

    }
}
$URL .= '&return=success0';
header("Location: {$URL}");