<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_structure_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $name = $_POST['name'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $invoice_title = $_POST['invoice_title'];
    $pupilsightSchoolFinanceYearID = $_POST['pupilsightSchoolFinanceYearID'];
    $fn_fees_head_id = $_POST['fn_fees_head_id'];
    $fn_fees_fine_rule_id = $_POST['fn_fees_fine_rule_id'];
    $fn_fees_discount_id = $_POST['fn_fees_discount_id'];
    $fd = explode('/', $_POST['due_date']);
    $seq_installment_NO = $_POST['seq_installment_NO'];
    $due_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    if(!empty($_POST['amount_editable'])){
        $amount_editable = $_POST['amount_editable'];
    } else {
        $amount_editable = '0';
    }

    if(!empty($_POST['display_fee_item'])){
        $display_fee_item = $_POST['display_fee_item'];
    } else {
        $display_fee_item = '1';
    }
    
    $fn_fee_item_id = $_POST['fn_fee_item_id'];
    $amount = $_POST['amount'];
    $tax = $_POST['tax'];
    $tax_percent = $_POST['tax_percent'];
    $cdt = date('Y-m-d H:i:s');
    
    if ($name == ''  or $pupilsightSchoolYearID == '' or $invoice_title == ''  or $pupilsightSchoolFinanceYearID == '' or $fn_fees_head_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM fn_fee_structure WHERE name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
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
                if(!empty($fn_fees_head_id)){
                    $datah = array('id' => $fn_fees_head_id);
                    $sqlh = 'SELECT inv_fee_series_id, recp_fee_series_id FROM fn_fees_head WHERE id=:id';
                    $resulth = $connection2->prepare($sqlh);
                    $resulth->execute($datah);
                    $valueh = $resulth->fetch();
                    $inv_fee_series_id = $valueh['inv_fee_series_id'];
                    $recp_fee_series_id = $valueh['recp_fee_series_id'];
                } else {
                    $inv_fee_series_id = '';
                    $recp_fee_series_id = '';
                }
                


                $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'invoice_title' => $invoice_title, 'pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID, 'fn_fees_head_id' => $fn_fees_head_id, 'inv_fee_series_id' => $inv_fee_series_id, 'recp_fee_series_id' => $recp_fee_series_id, 'fn_fees_fine_rule_id' => $fn_fees_fine_rule_id, 'fn_fees_discount_id' => $fn_fees_discount_id, 'seq_installment_NO'=>$seq_installment_NO,'due_date' => $due_date,'amount_editable' => $amount_editable, 'display_fee_item' => $display_fee_item, 'cdt' => $cdt);
                
                $sql = 'INSERT INTO fn_fee_structure SET name=:name, pupilsightSchoolYearID=:pupilsightSchoolYearID, invoice_title=:invoice_title, pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID, fn_fees_head_id=:fn_fees_head_id, inv_fee_series_id=:inv_fee_series_id, recp_fee_series_id=:recp_fee_series_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id,seq_installment_NO=:seq_installment_NO, due_date=:due_date, amount_editable=:amount_editable, display_fee_item=:display_fee_item, cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                $strId = $connection2->lastInsertID();

                if(!empty($fn_fee_item_id)){
                    foreach($fn_fee_item_id as $k=> $d){
                        $feeitem = $d;
                        $amt = $amount[$k];
                        $taxdata = $tax[$k];
                        $taxpr = $tax_percent[$k];
                        if($taxdata == 'Y' && $taxpr != ''){
                            $total_amount = $amt + (($taxpr / 100) * $amt);
                        } else {
                            $total_amount = $amt;
                        }
                        

                        if(!empty($feeitem) && !empty($amt) && !empty($taxdata)){
                            $data1 = array('fn_fee_structure_id' => $strId, 'fn_fee_item_id' => $feeitem, 'amount' => $amt, 'tax' => $taxdata, 'tax_percent' => $taxpr,'total_amount' => $total_amount);
                            $sql1 = "INSERT INTO fn_fee_structure_item SET fn_fee_structure_id=:fn_fee_structure_id, fn_fee_item_id=:fn_fee_item_id, amount=:amount,  tax=:tax, tax_percent=:tax_percent, total_amount=:total_amount";
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
