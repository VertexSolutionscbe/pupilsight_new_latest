<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_discount_rule_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_discount_rule_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fees_discount WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
               // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
            $name = $_POST['name'];
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $description = $_POST['description'];
            $fees_discount_type = $_POST['fees_discount_type'];
            $cat_name = $_POST['cat_name'];
            $fn_fee_item_id = $_POST['fn_fee_item_id'];
            $item_type = $_POST['item_type'];
            $category_amount = $_POST['category_amount'];
            
            $inv_name = $_POST['inv_name'];
            $min_invoice = $_POST['min_invoice'];
            $max_invoice = $_POST['max_invoice'];
            $inv_fn_fee_item_id = $_POST['inv_fn_fee_item_id'];
            $inv_item_type = $_POST['inv_item_type'];
            $inv_amount = $_POST['inv_amount'];
            $udt = date('Y-m-d H:i:s');
            

            if ($name == ''  or $pupilsightSchoolYearID == '' ) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'id' => $id);
                    $sql = 'SELECT * FROM fn_fees_discount WHERE (name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID) AND NOT id=:id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'description' => $description, 'fees_discount_type' => $fees_discount_type, 'udt' => $udt, 'id' => $id);
                        $sql = 'UPDATE fn_fees_discount SET name=:name, pupilsightSchoolYearID=:pupilsightSchoolYearID, description=:description, fees_discount_type=:fees_discount_type, udt=:udt WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $datad = array('fn_fees_discount_id' => $id);
                        $sqld = 'DELETE FROM fn_fee_discount_item WHERE fn_fees_discount_id=:fn_fees_discount_id';
                        $resultd = $connection2->prepare($sqld);
                        $resultd->execute($datad);

                        $disId = $id;

                        if($fees_discount_type == '1'){
                            if(!empty($cat_name)){
                                foreach($cat_name as $k=> $d){
                                    $name = $d;
                                    $feeitemid = $fn_fee_item_id[$k];
                                    $itemtype = $item_type[$k];
                                    if($itemtype == 'Fixed'){
                                        $amount = $category_amount[$k];
                                        $percent = '0';
                                    } else {
                                        $percent = $category_amount[$k];
                                        $amount = '0';
                                    }
        
                                    if(!empty($name) && !empty($feeitemid) && !empty($itemtype)){
                                        $data1 = array('fn_fees_discount_id' => $disId, 'fees_discount_type' => $fees_discount_type, 'fn_fee_item_id' => $feeitemid, 'item_type' => $itemtype, 'name' => $name, 'amount_in_percent' => $percent, 'amount_in_number' => $amount);
                                        $sql1 = "INSERT INTO fn_fee_discount_item SET fn_fees_discount_id=:fn_fees_discount_id, fees_discount_type=:fees_discount_type, fn_fee_item_id=:fn_fee_item_id,  item_type=:item_type, name=:name, amount_in_percent=:amount_in_percent, amount_in_number=:amount_in_number";
                                        $result1 = $connection2->prepare($sql1);
                                        $result1->execute($data1);
                                    }
                                }
                            }    
                        } else {
                            if(!empty($inv_name)){
                                foreach($inv_name as $k=> $d){
                                    $name = $d;
                                    $feeitemid = $inv_fn_fee_item_id[$k];
                                    $mininv = $min_invoice[$k];
                                    $maxinv = $max_invoice[$k];
                                    $itemtype = $inv_item_type[$k];
                                    if($itemtype == 'Fixed'){
                                        $amount = $inv_amount[$k];
                                        $percent = '0';
                                    } else {
                                        $percent = $inv_amount[$k];
                                        $amount = '0';
                                    }
        
                                    if(!empty($name) && !empty($feeitemid) && !empty($mininv) && !empty($maxinv) && !empty($itemtype)){
                                        $data1 = array('fn_fees_discount_id' => $disId, 'fees_discount_type' => $fees_discount_type, 'fn_fee_item_id' => $feeitemid, 'min_invoice' => $mininv, 'max_invoice' => $maxinv, 'item_type' => $itemtype, 'name' => $name, 'amount_in_percent' => $percent, 'amount_in_number' => $amount);
                                        $sql1 = "INSERT INTO fn_fee_discount_item SET fn_fees_discount_id=:fn_fees_discount_id, fees_discount_type=:fees_discount_type, fn_fee_item_id=:fn_fee_item_id, min_invoice=:min_invoice, max_invoice=:max_invoice,  item_type=:item_type, name=:name, amount_in_percent=:amount_in_percent, amount_in_number=:amount_in_number";
                                        $result1 = $connection2->prepare($sql1);
                                        $result1->execute($data1);
                                    }
                                }
                            }  
                        }

                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
