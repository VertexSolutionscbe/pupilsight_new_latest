<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_POST['invoice_id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage_edit.php') == false) {
    //$URL .= '&return=error0';
    //echo "error0";
    echo "error";
    //header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
       // $URL .= '&return=error1';
        //header("Location: {$URL}");
        echo "error1";
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fee_invoice WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
           /* $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();*/
            echo "error2";
        }

        if ($result->rowCount() != 1) {
            //$URL .= '&return=error2';
            echo "error2";
            //header("Location: {$URL}");
        } else {
            //Validate Inputs
               // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
            $title = $_POST['title'];
            $old_title = $_POST['old_title'];
            $pupilsightPersonID=$_POST['pstid'];
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

            $pupilsightSchoolYearID = $_POST['yid'];
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
            $udt = date('Y-m-d H:i:s');
            

            if ($title == ''  or $inv_fn_fee_series_id == '' or $rec_fn_fee_series_id == ''  or $fn_fees_head_id == '') {
                //$URL .= '&return=error3';
                //header("Location: {$URL}");
                echo "error3";
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('title' => $title, 'id' => $id);
                    $sql = 'SELECT * FROM fn_fee_invoice WHERE (title=:title) AND NOT id=:id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    //$URL .= '&return=error2';
                    //header("Location: {$URL}");
                   // exit();
                    echo "error2";
                }

                if ($result->rowCount() > 0) {
                    //$URL .= '&return=error3';
                    //header("Location: {$URL}");
                    echo "Invoice title is duplicate";
                } else {
                    //Write to database
                    try {
                    $sqlpt = "SELECT * FROM fn_fee_series WHERE id = ".$inv_fn_fee_series_id." ";
                    $resultpt = $connection2->query($sqlpt);
                    $values = $resultpt->fetch();
                    //get invoice_id
                    $invSeriesId = $values['id'];
                    //$invformat = explode('/',$values['format']);
                    $invformat = explode('$',$values['formatval']);
                    $iformat = '';
                    $orderwise = 0;
                    foreach($invformat as $inv){
                    if($inv == '{AB}'){
                    $datafort = array('fn_fee_series_id'=>$invSeriesId,'order_wise' => $orderwise, 'type' => 'numberwise');
                    $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                    $resultfort = $connection2->prepare($sqlfort);
                    $resultfort->execute($datafort);
                    $formatvalues = $resultfort->fetch();
                    //$iformat .= $formatvalues['last_no'].'/';
                    $iformat .= $formatvalues['last_no'];

                    $str_length = $formatvalues['no_of_digit'];

                    $lastnoadd = $formatvalues['last_no'] + 1;

                    $lastno = substr("0000000{$lastnoadd}", -$str_length); 

                    $datafort1 = array('fn_fee_series_id'=>$invSeriesId,'order_wise' => $orderwise, 'type' => 'numberwise' , 'last_no' => $lastno);
                    $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                    $resultfort1 = $connection2->prepare($sqlfort1);
                    $resultfort1->execute($datafort1);

                    } else {
                    //$iformat .= $inv.'/';
                    $iformat .= $inv;
                    }
                    $orderwise++;
                    }
                    //$invoiceno =  rtrim($iformat, "/");
                    $invoiceno =  $iformat;
                    
                        $data = array('title' => $title, 'inv_fn_fee_series_id' => $inv_fn_fee_series_id, 'rec_fn_fee_series_id' => $rec_fn_fee_series_id, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_fine_rule_id' => $fn_fees_fine_rule_id, 'fn_fees_discount_id' => $fn_fees_discount_id, 'due_date' => $due_date, 'udt' => $udt, 'amount_editable'=>$amount_editable,'display_fee_item'=>$display_fee_item,'id' => $id);
                        $sql = 'UPDATE fn_fee_invoice SET title=:title, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, udt=:udt,amount_editable=:amount_editable ,display_fee_item=:display_fee_item WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $datad = array('fn_fee_invoice_id' => $id);
                        $sqld = 'DELETE FROM fn_fee_invoice_item WHERE fn_fee_invoice_id=:fn_fee_invoice_id';
                        $resultd = $connection2->prepare($sqld);
                        $resultd->execute($datad);

                        $invId = $id;

                        if(!empty($fn_fee_item_id)){
                            foreach($fn_fee_item_id as $k=> $d){
                                $feeitem = $d;
                                $desc = $description[$k];
                                $amt = $amount[$k];
                                $taxdata = $tax[$k];
                                $disc = $discount[$k];
                                $tamt = $total_amount[$k];
                                
        
                                if(!empty($feeitem) && !empty($amt)){
                                    $data1 = array('fn_fee_invoice_id' => $invId, 'fn_fee_item_id' => $feeitem, 'description' => $desc, 'amount' => $amt, 'tax' => $taxdata, 'discount' => $disc, 'total_amount' => $tamt);
                                    $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, description=:description, amount=:amount,  tax=:tax, discount=:discount, total_amount=:total_amount";
                                    $result1 = $connection2->prepare($sql1);
                                    $result1->execute($data1);
                                }
                                if(!empty($invId) && !empty($invoiceno)){
                                $data1 = array('pupilsightPersonID'=>$pupilsightPersonID,'fn_fee_invoice_id' => $id , 'invoice_no' => $invoiceno);
                                $sql1 = 'UPDATE fn_fee_invoice_student_assign SET invoice_no=:invoice_no WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightPersonID=:pupilsightPersonID ';
                                $result1 = $connection2->prepare($sql1);
                                $result1->execute($data1);
                                }
                            }
                        }    
                    } catch (PDOException $e) {
                        /*$URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();*/
                        echo "error2";
                    }
                   echo "success";
                    //$URL .= '&return=success0';
                   // header("Location: {$URL}");
                }
            }
        }
    }
}
