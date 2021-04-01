<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_collection_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //Check if school year specified
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();

    $stid = trim($_POST['stuid']);
    $discountVal = $_POST['discountVal'];
    $invids = $_POST['invids'];
    $feeItemId = $_POST['feeItemId'];

    $assigned_by_s = $_POST['assgnBy'];
    $date_s = $_POST['assgnDate'];
    $remark_s = $_POST['assnRem'];
    $invno_s = $_POST['invno'];
    

    if (!empty($stid) && !empty($invids)) {
        $count = sizeof($invids);
        for ($i = 0; $i < $count; $i++) {
            $invid = $invids[$i];
            $discountFeeItem = $discountVal[$i];

            $assigned_by = $assigned_by_s[$i];
            $date = $date_s[$i];
            $remark = $remark_s[$i];
            $invno = $invno_s[$i];
            

            $sql = 'SELECT * FROM fn_fee_invoice WHERE id = '.$invid.' ';
            $result = $connection2->query($sql);
            $invData = $result->fetch();

            $sqlc = 'SELECT * FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id = '.$invid.' ';
            $resultc = $connection2->query($sqlc);
            $invClsData = $resultc->fetch();

            $sqlf = 'SELECT * FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = '.$invid.' ';
            $resultf = $connection2->query($sqlf);
            $invFIData = $resultf->fetchAll();

            if(!empty($invData)){
            
                $title = $invData['title'];
                $inv_fn_fee_series_id = $invData['inv_fn_fee_series_id'];
                $rec_fn_fee_series_id = $invData['rec_fn_fee_series_id'];
                $fn_fees_head_id = $invData['fn_fees_head_id'];
                $fn_fees_fine_rule_id = $invData['fn_fees_fine_rule_id'];
                $fn_fees_discount_id = $invData['fn_fees_discount_id'];
                $fd = explode('/', $invData['due_date']);
                $due_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
                
               
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

                $pupilsightProgramID = $invClsData['pupilsightProgramID'];
                $pupilsightYearGroupID = $invClsData['pupilsightYearGroupID'];
                $pupilsightRollGroupID = $invClsData['pupilsightRollGroupID'];

                if ($title == ''  or $inv_fn_fee_series_id == '' or $rec_fn_fee_series_id == ''  or $fn_fees_head_id == '') {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                //Write to database
                    try {
                        $data = array('title' => $title, 'inv_fn_fee_series_id' => $inv_fn_fee_series_id, 'rec_fn_fee_series_id' => $rec_fn_fee_series_id, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_fine_rule_id' => $fn_fees_fine_rule_id, 'fn_fees_discount_id' => $fn_fees_discount_id, 'due_date' => $due_date, 'udt' => $udt, 'fn_fee_structure_id' => $fn_fee_structure_id,'transport_schedule_id' => $transport_schedule_id, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID, 'amount_editable' => $amount_editable, 'display_fee_item' => $display_fee_item);
                        $sql = 'INSERT INTO fn_fee_invoice SET title=:title, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, udt=:udt,fn_fee_structure_id=:fn_fee_structure_id, transport_schedule_id=:transport_schedule_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID, amount_editable=:amount_editable, display_fee_item=:display_fee_item';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        // $datad = array('fn_fee_invoice_id' => $id);
                        // $sqld = 'DELETE FROM fn_fee_invoice_item WHERE fn_fee_invoice_id=:fn_fee_invoice_id';
                        // $resultd = $connection2->prepare($sqld);
                        // $resultd->execute($datad);

                        $invIdNew = $connection2->lastInsertID();

                        
                        // $datau = array('fn_fee_invoice_id' => $invIdNew, 'fn_fee_invoice_id' => $invid, 'pupilsightPersonID' => $stid);
                        // $sqlu = 'UPDATE fn_fee_invoice_student_assign SET fn_fee_invoice_id=:fn_fee_invoice_id WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightPersonID=:pupilsightPersonID';
                        // $resultu = $connection2->prepare($sqlu);
                        // $resultu->execute($datau);

                        $sq = "UPDATE fn_fee_invoice_student_assign SET fn_fee_invoice_id = ".$invIdNew." WHERE pupilsightPersonID = ".$stid." AND fn_fee_invoice_id = ".$invid." ";
                        $connection2->query($sq);

                        $dataca = array('fn_fee_invoice_id' => $invIdNew, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                        $sqlca = "INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID";
                        $resultca = $connection2->prepare($sqlca);
                        $resultca->execute($dataca);

                        if(!empty($invFIData)){
                            foreach($invFIData as $itemData){
                                $feeitem = $itemData['fn_fee_item_id'];
                                $desc = $itemData['description'];
                                $amt = $itemData['amount'];
                                $taxdata = $itemData['tax'];
                                $disc = $itemData['discount'];
                                $total_amount = $itemData['total_amount'];
                                
                                if(!empty($feeitem)){
                                    $data1 = array('fn_fee_invoice_id' => $invIdNew, 'fn_fee_item_id' => $feeitem, 'description' => $desc, 'amount' => $amt, 'tax' => $taxdata, 'discount' => $disc, 'total_amount' => $total_amount);
                                    $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, description=:description, amount=:amount,  tax=:tax, discount=:discount, total_amount=:total_amount";
                                    $result1 = $connection2->prepare($sql1);
                                    $result1->execute($data1);
                                }
                            }
                        }    

                        $total_amountNew = '-'.$discountFeeItem;
                        $data1 = array('fn_fee_invoice_id' => $invIdNew, 'fn_fee_item_id' => $feeItemId, 'discount' => $discountFeeItem, 'total_amount' => $total_amountNew);
                        $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, discount=:discount, total_amount=:total_amount";
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);

                        $fn_fee_invoice_item_id = $connection2->lastInsertID();

                        $sqlc = 'SELECT name FROM fn_fee_items WHERE id = '.$feeItemId.' ';
                        $resultc = $connection2->query($sqlc);
                        $itData = $resultc->fetch();
                        $feeItemName = $itData['name'];

                        if($feeItemName == 'Waive Off'){
                            $done_by = $_SESSION[$guid]['pupilsightPersonID'];
                            if(!empty($date)){
                                $wdate = date('Y-m-d', strtotime($date));
                            } else {
                                $wdate = '';
                            }
                            $data1 = array('pupilsightPersonID' => $stid, 'fn_fee_invoice_id' => $invIdNew, 'fn_fee_invoice_item_id' => $fn_fee_invoice_item_id, 'fn_fee_item_id' => $feeItemId, 'invoice_no' => $invno, 'discount_amount' => $discountFeeItem, 'assigned_by' => $assigned_by,  'done_by' => $done_by, 'waive_off_date' => $wdate, 'remarks' => $remark);
                            //print_r($data1);
                            $sql1 = "INSERT INTO fn_fee_waive_off SET pupilsightPersonID=:pupilsightPersonID, fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, fn_fee_item_id=:fn_fee_item_id,invoice_no=:invoice_no, discount_amount=:discount_amount, assigned_by=:assigned_by, done_by=:done_by, waive_off_date=:waive_off_date, remarks=:remarks";
                            $result1 = $connection2->prepare($sql1);
                            $result1->execute($data1);
                        }

                      //die();

                    } catch (PDOException $e) {
                        print_r($e);
                        echo "error2";
                    }
                }
            }
        }
    }
}
