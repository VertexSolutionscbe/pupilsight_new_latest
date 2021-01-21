<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transport_fee.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
 
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();


    $fee_item_id = $_POST['fee_item_id'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $schedule_name =  $_POST['schedule_name'];
    $route_id = $_POST['route_id'];
    $type = $_POST['type'];
    $start_year = $_POST['start_year'];
    $start_month = $_POST['start_month'];
    $end_year= $_POST['end_year'];
    $end_month =$_POST['end_month'] ;
    
    $fee_head_id = $_POST['fee_head_id'];
    if(!empty($start_month) && !empty($start_year)){
        $month = $_POST['start_month'];
        $m = date('m',strtotime($month));
        //$due_date = $start_year.'-'.$m.'-05';
        $due_date = $_POST['due_date'];
    } else {
        $due_date = '';
    }

    $total_invoice_generate = $_POST['total_invoice_generate'];
    $cdt = date('Y-m-d H:i:s');
    $id = $_POST['id'];
    
    if ($fee_item_id == ''  or $schedule_name == '' or $pupilsightSchoolYearID == '' or $type == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else{
            //Write to database
            try {
              //print_r($data);die();
                $data = array('fee_item_id' => $fee_item_id , 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'id' => $id);
                $sql = 'SELECT * FROM trans_schedule WHERE (fee_item_id=:fee_item_id AND pupilsightSchoolYearID=:pupilsightSchoolYearID) AND NOT id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {

                    if(!empty($fee_head_id)){
                        $datah = array('id' => $fee_head_id);
                        $sqlh = 'SELECT inv_fee_series_id, recp_fee_series_id FROM fn_fees_head WHERE id=:id';
                        $resulth = $connection2->prepare($sqlh);
                        $resulth->execute($datah);
                        $valueh = $resulth->fetch();
                        $invoice_series_id = $valueh['inv_fee_series_id'];
                        $receipt_series_id = $valueh['recp_fee_series_id'];
                    } else {
                        $invoice_series_id = '';
                        $receipt_series_id = '';
                    }

                    $data1 = array('fee_item_id' => $fee_item_id,'schedule_name' => $schedule_name, 'type'=>$type,'pupilsightSchoolYearID'=> $pupilsightSchoolYearID, 'start_year' => $start_year, 'start_month' => $start_month, 'end_year' => $end_year, 'end_month' => $end_month, 'invoice_series_id' => $invoice_series_id,'receipt_series_id'=>$receipt_series_id,'fee_head_id'=>$fee_head_id,'due_date'=>$due_date,'route_id'=>$route_id,'total_invoice_generate' => $total_invoice_generate,'cdt' => $cdt,'id' => $id);

                    $sql1 = 'UPDATE trans_schedule SET fee_item_id=:fee_item_id, schedule_name=:schedule_name, type=:type,pupilsightSchoolYearID=:pupilsightSchoolYearID,start_year=:start_year, start_month=:start_month, end_year=:end_year, end_month=:end_month, invoice_series_id=:invoice_series_id,receipt_series_id=:receipt_series_id,fee_head_id=:fee_head_id, due_date=:due_date,route_id=:route_id, total_invoice_generate=:total_invoice_generate, cdt=:cdt  WHERE id=:id';
                    $result1 = $connection2->prepare($sql1);
                    $result1->execute($data1);

                    $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

                    $URL .= "&return=success0";
                    header("Location: {$URL}");
                }    
               
            } catch (PDOException $e) {
                $URL .= '&return=error9';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            
        
    }
}