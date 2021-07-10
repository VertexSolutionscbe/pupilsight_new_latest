<?php
/*
Pupilsight, Flexible & Open School System
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'pupilsight.php';

//Proceed
$type = '';
if (isset($_POST['type'])) {
    $type = $_POST['type'];
}else{
    $result['status'] = 2;
    $result['msg'] = 'Invalid Message Parameter.';
}

/* open Description Indicator */
if ($type == 'feeInvoice') {
    try {
        //print_r($_POST);
        
        $result = [];
        $sq ='select * from archive_feeInvoices ';
        $sq .="where Stakeholder <> '' ";
        
        if(!empty($_POST["AcademicYear"])){
            $sq .="and AcademicYear='".$_POST["AcademicYear"]."' ";
        }
        if(!empty($_POST["Term"])){
            $sq .="and Term='".$_POST["Term"]."' ";
        }
        if(!empty($_POST["Stream"])){
            $sq .="and Stream='".$_POST["Stream"]."' ";
        }
        if(!empty($_POST["StudentID"])){
            $sq .="and StudentID='".$_POST["StudentID"]."' ";
        }
        if(!empty($_POST["Name"])){
            $sq .="and Name like '%".$_POST["Name"]."%' ";
        }
        $sq .="order by Name asc limit 0, 5000 ";
        //echo $sq;

        $query = $connection2->query($sq);
        $result["data"] = $query->fetchAll();
        $result['status'] = 1;
        //echo $squ;
    } catch (Exception $ex) {
        $result['status'] = 2;
        $result['msg'] = $ex->getMessage();
    }
}else if ($type == 'feeTransactions') {
    try {
        //print_r($_POST);
        
        $result = [];
        $sq ='select * from archive_fee_transactions_backup ';
        $sq .="where Stakeholder <> '' ";
        
        if(!empty($_POST["AcademicYear"])){
            $sq .="and AcademicYear='".$_POST["AcademicYear"]."' ";
        }
        if(!empty($_POST["Term"])){
            $sq .="and Term='".$_POST["Term"]."' ";
        }
        if(!empty($_POST["Stream"])){
            $sq .="and Stream='".$_POST["Stream"]."' ";
        }
        if(!empty($_POST["StudentID"])){
            $sq .="and StudentID='".$_POST["StudentID"]."' ";
        }
        if(!empty($_POST["FullName"])){
            $sq .="and FullName like '%".$_POST["FullName"]."%' ";
        }
        $sq .="order by FullName asc limit 0, 5000 ";
        //echo $sq;

        $query = $connection2->query($sq);
        $result["data"] = $query->fetchAll();
        $result['status'] = 1;
        //echo $squ;
    } catch (Exception $ex) {
        $result['status'] = 2;
        $result['msg'] = $ex->getMessage();
    }
}
if ($result) {
    echo json_encode($result);
}