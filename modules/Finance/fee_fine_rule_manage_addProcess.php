<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_fine_rule_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_fine_rule_manage_add.php') == false) {
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
    $code = $_POST['code'];
    $description = $_POST['description'];
    $fine_type = $_POST['fine_type'];
    $fixed_fine_type = $_POST['fixed_fine_type'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $fixed_rule_item_type = $_POST['fixed_rule_item_type'];
    $fixed_rule_amt_per = $_POST['fixed_rule_amt_per'];

    $amount_in_percent = $_POST['amount_in_percent'];
    $amount_in_number = $_POST['amount_in_number'];
    $from_day = $_POST['from_day'];
    $to_day = $_POST['to_day'];
    $day_slab_item_type = $_POST['day_slab_item_type'];
    $day_slab_amt_per = $_POST['day_slab_amt_per'];
    $ignore_holiday = $_POST['ignore_holiday'];
    $fixed_amount_in_number = $_POST['fixed_amount_in_number'];
    $day_amount_in_number = $_POST['day_amount_in_number'];
    $ignore_holiday = $_POST['ignore_holiday'];
    $cdt = date('Y-m-d H:i:s');

    if(!empty($_POST['is_fine_editable'])){
        $is_fine_editable = $_POST['is_fine_editable'];
    } else {
        $is_fine_editable = '0';
    }
    
    if ($name == ''  or $code == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'code' => $code);
            $sql = 'SELECT * FROM fn_fees_fine_rule WHERE name=:name OR code=:code';
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
                $data = array('name' => $name, 'code' => $code, 'description' => $description, 'fine_type' => $fine_type, 'rule_type' => $fixed_fine_type, 'is_fine_editable' => $is_fine_editable, 'cdt' => $cdt);
                $sql = 'INSERT INTO fn_fees_fine_rule SET name=:name, code=:code, description=:description, fine_type=:fine_type,rule_type=:rule_type, is_fine_editable=:is_fine_editable, cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $fineId = $connection2->lastInsertID();

                if($fine_type == '1'){
                    if($fixed_fine_type == '3'){
                        if(!empty($from_date)){
                            foreach($from_date as $k=> $d){
                                $fd = explode('/', $d);
                                $fdate  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
                                $td = explode('/', $to_date[$k]);
                                $tdate  = date('Y-m-d', strtotime(implode('-', array_reverse($td))));
                                $type = $fixed_rule_item_type[$k];
                                if($type == 'Fixed'){
                                    $amt = $fixed_rule_amt_per[$k];
                                    $per = '';
                                } else {
                                    $amt = '';
                                    $per = $fixed_rule_amt_per[$k];
                                }
                                
                                if(!empty($fdate) && !empty($tdate)){
                                    $data1 = array('fn_fees_fine_rule_id' => $fineId, 'fine_type' => $fine_type, 'rule_type' => $fixed_fine_type, 'from_date' => $fdate, 'to_date' => $tdate,'amount_type' => $type,'amount_in_percent' => $per, 'amount_in_number' => $amt, 'ignore_holiday' => $ignore_holiday);
                                    $sql1 = "INSERT INTO fn_fees_rule_type SET fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fine_type=:fine_type, rule_type=:rule_type,  from_date=:from_date, to_date=:to_date, amount_type=:amount_type, amount_in_percent=:amount_in_percent, amount_in_number=:amount_in_number, ignore_holiday=:ignore_holiday";
                                    $result1 = $connection2->prepare($sql1);
                                    $result1->execute($data1);
                                }
                            }
                        }    
                    } else {
                        $data1 = array('fn_fees_fine_rule_id' => $fineId, 'fine_type' => $fine_type, 'rule_type' => $fixed_fine_type, 'amount_in_percent' => $amount_in_percent, 'amount_in_number' => $amount_in_number, 'ignore_holiday' => $ignore_holiday);
                        $sql1 = 'INSERT INTO fn_fees_rule_type SET fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fine_type=:fine_type, rule_type=:rule_type, amount_in_percent=:amount_in_percent, amount_in_number=:amount_in_number, ignore_holiday=:ignore_holiday';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);
                    }

                } else if($fine_type == '2'){
                    $data1 = array('fn_fees_fine_rule_id' => $fineId, 'fine_type' => $fine_type, 'rule_type' => $fixed_fine_type, 'amount_in_percent' => $amount_in_percent, 'amount_in_number' => $amount_in_number, 'ignore_holiday' => $ignore_holiday);
                    $sql1 = 'INSERT INTO fn_fees_rule_type SET fn_fees_fine_rule_id=:fn_fees_fine_rule_id,fine_type=:fine_type, rule_type=:rule_type, amount_in_percent=:amount_in_percent, amount_in_number=:amount_in_number, ignore_holiday=:ignore_holiday';
                    $result1 = $connection2->prepare($sql1);
                    $result1->execute($data1);
                } else {
                    if($fixed_fine_type == '4'){
                       if(!empty($from_day)){
                            foreach($from_day as $k=> $d){
                                $fday  = $d;
                                $tday  = $to_day[$k];
                               
                                $type = $day_slab_item_type[$k];
                                if($type == 'Fixed'){
                                    $amt = $day_slab_amt_per[$k];
                                    $per = '';
                                } else {
                                    $amt = '';
                                    $per = $day_slab_amt_per[$k];
                                }

                                if(!empty($fday) && !empty($tday)){
                                    $data1 = array('fn_fees_fine_rule_id' => $fineId, 'fine_type' => $fine_type, 'rule_type' => $fixed_fine_type, 'from_day' => $fday, 'to_day' => $tday,'amount_type' => $type,'amount_in_percent' => $per,'amount_in_number' => $amt, 'ignore_holiday' => $ignore_holiday);
                                   echo $sql1 = "INSERT INTO fn_fees_rule_type SET fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fine_type=:fine_type, rule_type=:rule_type,  from_day=:from_day, to_day=:to_day, amount_type=:amount_type, amount_in_percent=:amount_in_percent, amount_in_number=:amount_in_number, ignore_holiday=:ignore_holiday";
                                    $result1 = $connection2->prepare($sql1);
                                    $result1->execute($data1);
                                }
                            }
                        } 
                    } else {
                        $data1 = array('fn_fees_fine_rule_id' => $fineId, 'fine_type' => $fine_type, 'rule_type' => $fixed_fine_type, 'amount_in_percent' => $amount_in_percent, 'amount_in_number' => $amount_in_number, 'ignore_holiday' => $ignore_holiday);
                        $sql1 = 'INSERT INTO fn_fees_rule_type SET fn_fees_fine_rule_id=:fn_fees_fine_rule_id,fine_type=:fine_type, rule_type=:rule_type, amount_in_percent=:amount_in_percent, amount_in_number=:amount_in_number, ignore_holiday=:ignore_holiday';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);
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

            //$URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
