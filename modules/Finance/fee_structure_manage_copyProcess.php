<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_structure_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_manage_copy.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $id = $_POST['id'];
    $name = $_POST['name'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $fd = explode('/', $_POST['due_date']);
    $due_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));

    $invoice_title = $_POST['invoice_title'];
    $pupilsightSchoolFinanceYearID = $_POST['pupilsightSchoolFinanceYearID'];
    $fn_fees_head_id = $_POST['fn_fees_head_id'];
    $fn_fees_fine_rule_id = $_POST['fn_fees_fine_rule_id'];
    $fn_fees_discount_id = $_POST['fn_fees_discount_id'];
   
    $cdt = date('Y-m-d H:i:s');
    
    if ($name == ''  or $pupilsightSchoolYearID == '' ) {
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


                $datas = array('id' => $id);
                $sqls = 'SELECT * FROM fn_fee_structure WHERE id=:id';
                $results = $connection2->prepare($sqls);
                $results->execute($datas);
                $values = $results->fetch();

                $datac = array('fn_fee_structure_id' => $id);
                $sqlc = 'SELECT * FROM fn_fee_structure_item WHERE fn_fee_structure_id=:fn_fee_structure_id';
                $resultc = $connection2->prepare($sqlc);
                $resultc->execute($datac);
                $childvalues = $resultc->fetchAll();

                $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'invoice_title' => $invoice_title, 'pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID, 'fn_fees_head_id' => $fn_fees_head_id, 'inv_fee_series_id' => $inv_fee_series_id, 'recp_fee_series_id' => $recp_fee_series_id, 'fn_fees_fine_rule_id' => $fn_fees_fine_rule_id, 'fn_fees_discount_id' => $fn_fees_discount_id, 'due_date' => $due_date, 'cdt' => $cdt);

                
                
               $sql = 'INSERT INTO fn_fee_structure SET name=:name, pupilsightSchoolYearID=:pupilsightSchoolYearID, invoice_title=:invoice_title, pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID, fn_fees_head_id=:fn_fees_head_id, inv_fee_series_id=:inv_fee_series_id, recp_fee_series_id=:recp_fee_series_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);
               
                $strId = $connection2->lastInsertID();
               
                if(!empty($childvalues)){
                    foreach($childvalues as $cv){
                        $feeitem = $cv['fn_fee_item_id'];
                        $amt = $cv['amount'];
                        $taxdata = $cv['tax'];
                        $taxpr = $cv['tax_percent'];
                        $total_amount = $cv['total_amount'];

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
