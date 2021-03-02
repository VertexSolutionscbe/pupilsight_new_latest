<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    $id = $_POST['invoice_student_id'];
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        
        $title = $_POST['title'];
        $inv_fn_fee_series_id = $_POST['inv_fn_fee_series_id'];
        $rec_fn_fee_series_id = $_POST['rec_fn_fee_series_id'];
        $fn_fees_head_id = $_POST['fn_fees_head_id'];
        $fn_fees_fine_rule_id = $_POST['fn_fees_fine_rule_id'];
        if(!empty($_POST['due_date'])){
            $fd = explode('/', $_POST['due_date']);
            $due_date = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
        } else {
            $due_date = '';
        }
        
       
        $invAssignIds = explode(',', $id);

        foreach($invAssignIds as $invid){
            $sql = 'SELECT b.* FROM fn_fee_invoice_student_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id WHERE a.id = '.$invid.' ';
            $result = $connection2->query($sql);
            $invData = $result->fetch();

            $invoiceId = $invData['id'];

            $sqlc = 'SELECT * FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id = '.$invoiceId.' ';
            $resultc = $connection2->query($sqlc);
            $invClsAssnData = $resultc->fetch();

            $sqli = 'SELECT * FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = '.$invoiceId.' ';
            $resulti = $connection2->query($sqli);
            $invItemData = $resulti->fetchAll();

            if(!empty($title)){
                $title = $title;
            } else {
                $title = $invData['title'];
            }

            if(!empty($inv_fn_fee_series_id)){
                $inv_fn_fee_series_id = $inv_fn_fee_series_id;
            } else {
                $inv_fn_fee_series_id = $invData['inv_fn_fee_series_id'];
            }

            if(!empty($rec_fn_fee_series_id)){
                $rec_fn_fee_series_id = $rec_fn_fee_series_id;
            } else {
                $rec_fn_fee_series_id = $invData['rec_fn_fee_series_id'];
            }

            if(!empty($fn_fees_head_id)){
                $fn_fees_head_id = $fn_fees_head_id;
            } else {
                $fn_fees_head_id = $invData['fn_fees_head_id'];
            }

            if(!empty($fn_fees_fine_rule_id)){
                $fn_fees_fine_rule_id = $fn_fees_fine_rule_id;
            } else {
                $fn_fees_fine_rule_id = $invData['fn_fees_fine_rule_id'];
            }

            if(!empty($due_date)){
                $due_date = $due_date;
            } else {
                $due_date = $invData['due_date'];
            }

            $fn_fees_discount_id = $invData['fn_fees_discount_id'];
            
            $udt = date('Y-m-d H:i:s');

            $fn_fee_structure_id = $invData['fn_fee_structure_id'];
            $transport_schedule_id = $invData['transport_schedule_id'];
            $pupilsightSchoolYearID = $invData['pupilsightSchoolYearID'];
            $pupilsightSchoolFinanceYearID = $invData['pupilsightSchoolFinanceYearID'];
            if(isset($invData['amount_editable'])){
                $amount_editable="1";
            } else {
                $amount_editable="0";
            }

            if(isset($invData['display_fee_item'])){
                $display_fee_item=2;
            } else {
                $display_fee_item=1;
            }

            $pupilsightProgramID = $invClsAssnData['pupilsightProgramID'];
            $pupilsightYearGroupID = $invClsAssnData['pupilsightYearGroupID'];
            $pupilsightRollGroupID = $invClsAssnData['pupilsightRollGroupID'];

            try {
                $data = array('title' => $title, 'inv_fn_fee_series_id' => $inv_fn_fee_series_id, 'rec_fn_fee_series_id' => $rec_fn_fee_series_id, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_fine_rule_id' => $fn_fees_fine_rule_id, 'fn_fees_discount_id' => $fn_fees_discount_id, 'due_date' => $due_date, 'udt' => $udt, 'fn_fee_structure_id' => $fn_fee_structure_id,'transport_schedule_id' => $transport_schedule_id, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID, 'amount_editable' => $amount_editable, 'display_fee_item' => $display_fee_item);
                $sql = 'INSERT INTO fn_fee_invoice SET title=:title, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, udt=:udt,fn_fee_structure_id=:fn_fee_structure_id, transport_schedule_id=:transport_schedule_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID, amount_editable=:amount_editable, display_fee_item=:display_fee_item';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $invIdNew = $connection2->lastInsertID();

                $datau = array('fn_fee_invoice_id' => $invIdNew, 'id' => $invid);
                $sqlu = 'UPDATE fn_fee_invoice_student_assign SET fn_fee_invoice_id=:fn_fee_invoice_id WHERE id=:id';
                $resultu = $connection2->prepare($sqlu);
                $resultu->execute($datau);

                $dataca = array('fn_fee_invoice_id' => $invIdNew, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                $sqlca = "INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID";
                $resultca = $connection2->prepare($sqlca);
                $resultca->execute($dataca);

                if(!empty($invItemData)){
                    foreach($invItemData as $data){
                        $feeitem = $data['fn_fee_item_id'];
                        $desc = $data['description'];
                        $amt = $data['amount'];
                        $taxdata = $data['tax'];
                        $disc = $data['discount'];
                        $total_amount = $data['total_amount'];
                        

                        if(!empty($feeitem) && !empty($amt) && !empty($taxdata)){
                            $data1 = array('fn_fee_invoice_id' => $invIdNew, 'fn_fee_item_id' => $feeitem, 'description' => $desc, 'amount' => $amt, 'tax' => $taxdata, 'discount' => $disc, 'total_amount' => $total_amount);
                            $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, description=:description, amount=:amount,  tax=:tax, discount=:discount, total_amount=:total_amount";
                            $result1 = $connection2->prepare($sql1);
                            $result1->execute($data1);
                        }
                    }
                }    
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

        }
        $URL .= '&return=success0';
        header("Location: {$URL}");

    }
}
