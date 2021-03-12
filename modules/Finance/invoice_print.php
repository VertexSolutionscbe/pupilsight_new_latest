<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Finance/view_receipt.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
   
    $id = $_GET['invid'];

    $sql = 'SELECT a.invoice_no, a.pupilsightPersonID, b.* FROM fn_fee_invoice_student_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id WHERE a.id = '.$id.' ';
    $result = $connection2->query($sql);
    $invData = $result->fetch();

    $invoiceId = $invData['id'];
    $invoice_no = $invData['invoice_no'];
    $pupilsightPersonID = $invData['pupilsightPersonID'];

    $sqlstu = "SELECT a.officialName , a.admission_no, b.name as class, c.name as section FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightPersonID = " . $pupilsightPersonID . " ";
    $resultstu = $connection2->query($sqlstu);
    $valuestu = $resultstu->fetch();

    if ($invData['display_fee_item'] == '2') {
        $sqcs = "select SUM(fi.total_amount) AS tamnt from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id =  " . $invoiceId . " ";
        $resultfi = $connection2->query($sqcs);
        $valuefi = $resultfi->fetchAll();
        if (!empty($valuefi)) {
            $cnt = 1;
            foreach ($valuefi as $vfi) {
                $dts_receipt_feeitem[] = array(
                    "serial.all" => $cnt,
                    "particulars.all" => $invData['invoice_title'],
                    "amount.all" => $vfi["tamnt"]
                );
                $cnt++;
            }
        }
    } else {
        $sqcs = "select fi.total_amount, items.name from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id =  " . $invoiceId . " ";
        $resultfi = $connection2->query($sqcs);
        $valuefi = $resultfi->fetchAll();

        if (!empty($valuefi)) {
            $cnt = 1;
            foreach ($valuefi as $vfi) {
                $dts_receipt_feeitem[] = array(
                    "serial.all" => $cnt,
                    "particulars.all" => $vfi["name"],
                    "amount.all" => $vfi["total_amount"]
                );
                $cnt++;
            }
        }
    }



    $class_section = $valuestu["class"] . " " . $valuestu["section"];
    $date = date('d-m-Y');

    $dts_receipt = array(
        "invoice_no" => $invoice_no,
        "date" => $date,
        "student_name" => $valuestu["officialName"],
        "student_id" => $valuestu["admission_no"],
        "class_section" => $class_section,
        "total_amount" => '100'
    );

    if (!empty($dts_receipt) && !empty($dts_receipt_feeitem)) {
        $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/phpword/invoice_print.php';
        $datamerge = array_merge($dts_receipt, $dts_receipt_feeitem);
        $postdata = http_build_query(
            array(
                'dts_receipt' => $dts_receipt,
                'dts_receipt_feeitem' => $dts_receipt_feeitem
            )
        );

        $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postdata
        ));
        $context  = stream_context_create($opts);
        $result = file_get_contents($callback, false, $context);
    }
?>
