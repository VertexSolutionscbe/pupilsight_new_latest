<?php
include 'pupilsight.php';
set_time_limit(0);

$seriesData = getSeriesData($connection2);
$invoiceData = getInvoiceData($connection2);
// echo '<pre>';
//echo $last_no = $seriesData['2'];
// print_r($invoiceData);
// die();
$sql = 'SELECT id, invoice_no, fn_fee_invoice_id FROM fn_fee_invoice_student_assign GROUP BY invoice_no';
$result = $connection2->query($sql);
$invData = $result->fetchAll();
$arr = array();
foreach ($invData as $inv) {
    $somestring = $inv['invoice_no'];
    $arr[]  = substr($somestring, strrpos($somestring, '/') + 1);
}
// echo '<pre>';
// print_r($arr);
$maxNo = max($arr);
// die();
if (!empty($invData)) {
    $last_no = $maxNo;
    $newinvoice_no = '';
    //$i = 0;
    $squ = "";
    foreach ($invData as $inv) {
        $invoice_no = $inv['invoice_no'];
        $id = $inv['id'];

        $sql1 = 'SELECT id, invoice_no FROM fn_fee_invoice_student_assign WHERE invoice_no = "' . $invoice_no . '" AND invoice_status = "Not Paid" AND id != ' . $id . ' ';
        $result1 = $connection2->query($sql1);
        $dupData = $result1->fetch();
        if (!empty($dupData['id'])) {
            //echo 'working';
            if ($invoice_no == $dupData['invoice_no']) {
                $fn_fee_invoice_id = $inv['fn_fee_invoice_id'];
                //$fn_fee_series_id = $inv['inv_fn_fee_series_id'];

                // $fn_fee_series_id = $invoiceData[$fn_fee_invoice_id];
                // if (!empty($fn_fee_series_id)) {
                $last_no++;
                $dupInvNo = $dupData['invoice_no'];
                $get = substr($dupInvNo, 0, strrpos($dupInvNo, '/'));
                $newinvoice_no = $get . '/' . $last_no;
                $squ .= "update fn_fee_invoice_student_assign SET invoice_no ='" . $newinvoice_no . "' where id = " . $dupData['id'] . "; ";

                // }
                // if ($i > 4) {
                //     break;
                // }
                // $i++;
            }
        }
    }
    if ($squ) {

        $fn_fee_series_id = 2;
        $squ .= "update fn_fee_series_number_format SET last_no = " . $last_no . " where fn_fee_series_id = " . $fn_fee_series_id . " AND type = 'numberwise'; ";
        echo $squ . '<br>';
        $connection2->query($squ);
    }
}

function getSeriesData($connection2)
{
    $sql = 'SELECT * FROM fn_fee_series_number_format WHERE type = "numberwise" ';
    $result = $connection2->query($sql);
    $serData = $result->fetchAll();
    $series = array();
    foreach ($serData as $sdata) {
        $series[$sdata['fn_fee_series_id']] = $sdata['last_no'];
    }
    return $series;
}

function getInvoiceData($connection2)
{
    $sql = 'SELECT id, inv_fn_fee_series_id  FROM fn_fee_invoice ';
    $result = $connection2->query($sql);
    $invData = $result->fetchAll();
    $invoices = array();
    foreach ($invData as $sdata) {
        $invoices[$sdata['id']] = $sdata['inv_fn_fee_series_id'];
    }
    return $invoices;
}
