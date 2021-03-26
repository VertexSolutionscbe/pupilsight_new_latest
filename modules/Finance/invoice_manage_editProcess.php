<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage_edit.php') == false) {
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
            $sql = 'SELECT * FROM fn_fee_invoice WHERE id=:id';
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

            
            $invid = $_POST['invid'];
            $title = $_POST['title'];

            

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
            //$total_amount = $_POST['total_amount'];
            $udt = date('Y-m-d H:i:s');

            $fn_fee_structure_id = $_POST['fn_fee_structure_id'];
            $transport_schedule_id = $_POST['transport_schedule_id'];
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $pupilsightSchoolFinanceYearID = $_POST['pupilsightSchoolFinanceYearID'];
            if(isset($_POST['amount_editable'])){
                $amount_editable="1";
            } else {
                $amount_editable="0";
            }

            if(isset($_POST['display_fee_item'])){
                $display_fee_item=2;
            } else {
                $display_fee_item=1;
            }

            $pupilsightProgramID = $_POST['pupilsightProgramID'];
            $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];

            
            

            if ($title == ''  or $inv_fn_fee_series_id == '' or $rec_fn_fee_series_id == ''  or $fn_fees_head_id == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                // try {
                //     $data = array('title' => $title, 'id' => $id);
                //     $sql = 'SELECT * FROM fn_fee_invoice WHERE (title=:title) AND NOT id=:id';
                //     $result = $connection2->prepare($sql);
                //     $result->execute($data);
                // } catch (PDOException $e) {
                //     $URL .= '&return=error2';
                //     header("Location: {$URL}");
                //     exit();
                // }

                // if ($result->rowCount() > 0) {
                //     $URL .= '&return=error9';
                //     header("Location: {$URL}");
                // } else {
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

                        
                        // $datau = array('fn_fee_invoice_id' => $invIdNew, 'id' => $invid, 'pupilsightPersonID' => $stid);
                        // $sqlu = 'UPDATE fn_fee_invoice_student_assign SET fn_fee_invoice_id=:fn_fee_invoice_id WHERE id=:id AND pupilsightPersonID=:pupilsightPersonID';
                        // $resultu = $connection2->prepare($sqlu);
                        // $resultu->execute($datau);

                        $sq = "UPDATE fn_fee_invoice_student_assign SET fn_fee_invoice_id = ".$invIdNew." WHERE pupilsightPersonID = ".$stid." AND fn_fee_invoice_id = ".$invid." ";
                        $connection2->query($sq);

                        $dataca = array('fn_fee_invoice_id' => $invIdNew, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                        $sqlca = "INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID";
                        $resultca = $connection2->prepare($sqlca);
                        $resultca->execute($dataca);

                        if(!empty($fn_fee_item_id)){
                            foreach($fn_fee_item_id as $k=> $d){
                                $feeitem = $d;
                                $desc = $description[$k];
                                $amt = $amount[$k];
                                $taxdata = $tax[$k];
                                $disc = $discount[$k];
                                if(!empty($taxdata)){
                                    $total_amount = $amt + (($taxdata / 100) * $amt);
                                } else {
                                    $total_amount = $amt;
                                }
        
                                if(!empty($disc)){
                                    $total_amount = $total_amount - $disc;
                                } else {
                                    $total_amount = $total_amount;
                                }
                                
        
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

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                // }
            }
        }
    }
}
