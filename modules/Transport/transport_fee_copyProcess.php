<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transport_fee.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee_copy.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
 
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();

    $id = $_POST['id'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $schedule_name =  $_POST['schedule_name'];
    $route_id = $_POST['route_id'];
    $cdt = date('Y-m-d H:i:s');
    
    if ($schedule_name == '' or $pupilsightSchoolYearID == '' or $route_id == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else{
            //Write to database
            try {
              //print_r($data);die();
              $data = array('schedule_name' => $schedule_name , 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
              $sql = 'SELECT * FROM trans_schedule WHERE schedule_name=:schedule_name AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
              $result = $connection2->prepare($sql);
              $result->execute($data);
                
                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {

                    $datas = array('id' => $id);
                    $sqls = 'SELECT * FROM trans_schedule WHERE id=:id';
                    $results = $connection2->prepare($sqls);
                    $results->execute($datas);
                    $values = $results->fetch();

                    $data1 = array('fee_item_id' => $values['fee_item_id'],'schedule_name' => $schedule_name, 'type'=>$values['type'],'pupilsightSchoolYearID'=> $pupilsightSchoolYearID, 'start_year' => $values['start_year'], 'start_month' => $values['start_month'], 'end_year' => $values['end_year'], 'end_month' => $values['end_month'], 'invoice_series_id' => $values['invoice_series_id'],'receipt_series_id'=>$values['receipt_series_id'],'fee_head_id'=>$values['fee_head_id'],'due_date'=>$values['due_date'],'route_id'=>$route_id,'total_invoice_generate' => $values['total_invoice_generate'],'cdt' => $cdt);

                    $sql1 = 'INSERT INTO trans_schedule SET fee_item_id=:fee_item_id, schedule_name=:schedule_name, type=:type,pupilsightSchoolYearID=:pupilsightSchoolYearID,start_year=:start_year, start_month=:start_month, end_year=:end_year, end_month=:end_month,
                    invoice_series_id=:invoice_series_id, receipt_series_id=:receipt_series_id,fee_head_id=:fee_head_id, due_date=:due_date,route_id=:route_id, total_invoice_generate=:total_invoice_generate, cdt=:cdt';
                    $result1 = $connection2->prepare($sql1);
                    $result1->execute($data1);


                }    
             
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