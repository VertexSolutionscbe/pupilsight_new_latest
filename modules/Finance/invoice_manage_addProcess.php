<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $title = $_POST['title'];
    // $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    // $invoice_title_id = $_POST['invoice_title_id'];
    // $pupilsightSchoolFinanceYearID = $_POST['pupilsightSchoolFinanceYearID'];
    $inv_fn_fee_series_id = $_POST['inv_fn_fee_series_id'];
    $rec_fn_fee_series_id = $_POST['rec_fn_fee_series_id'];
    $fn_fees_head_id = $_POST['fn_fees_head_id'];
    $fn_fees_fine_rule_id = $_POST['fn_fees_fine_rule_id'];
    $fn_fees_discount_id = $_POST['fn_fees_discount_id'];
    $fd = explode('/', $_POST['due_date']);
    $due_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    
    $fn_fee_item_id = $_POST['fn_fee_item_id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $tax = $_POST['tax'];
    $discount = $_POST['discount'];
    $total_amount = $_POST['total_amount'];
    $cdt = date('Y-m-d H:i:s');
    
    if ($title == ''  or $inv_fn_fee_series_id == '' or $rec_fn_fee_series_id == ''  or $fn_fees_head_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('title' => $title,);
            $sql = 'SELECT * FROM fn_fee_invoice WHERE title=:title ';
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
                $data = array('title' => $title, 'inv_fn_fee_series_id' => $inv_fn_fee_series_id, 'rec_fn_fee_series_id' => $rec_fn_fee_series_id, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_fine_rule_id' => $fn_fees_fine_rule_id, 'fn_fees_discount_id' => $fn_fees_discount_id, 'due_date' => $due_date, 'cdt' => $cdt);
                $sql = 'INSERT INTO fn_fee_invoice SET title=:title, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                $invId = $connection2->lastInsertID();

                if(!empty($fn_fee_item_id)){
                    foreach($fn_fee_item_id as $k=> $d){
                        $feeitem = $d;
                        $desc = $description[$k];
                        $amt = $amount[$k];
                        $taxdata = $tax[$k];
                        $disc = $discount[$k];
                        $tamt = $total_amount[$k];
                        

                        if(!empty($feeitem) && !empty($amt) && !empty($taxdata)){
                            $data1 = array('fn_fee_invoice_id' => $invId, 'fn_fee_item_id' => $feeitem, 'description' => $desc, 'amount' => $amt, 'tax' => $taxdata, 'discount' => $disc, 'total_amount' => $tamt);
                            $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, description=:description, amount=:amount,  tax=:tax, discount=:discount, total_amount=:total_amount";
                            $result1 = $connection2->prepare($sql1);
                            $result1->execute($data1);
                        }
                    }
                }    
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';
            } catch (PDOException $e) {
                $URL .= '&return=error9';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0";
            header("Location: {$URL}");
        }
    }
}
