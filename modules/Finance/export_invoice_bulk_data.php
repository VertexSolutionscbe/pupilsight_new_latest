<?php
/*
Pupilsight, Flexible & Open School System

*/

use Pupilsight\Data\ImportType;
use Pupilsight\Domain\System\CustomField;

// Increase max execution time, as this stuff gets big
ini_set('max_execution_time', 7200);
ini_set('memory_limit','1024M');
set_time_limit(1200);

$_POST['address'] = '/modules/Finance/invoice_manage.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_POST['address'];

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage.php') == false) {
    // Access denied
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $customField  = $container->get(CustomField::class);
    $page->breadcrumbs->add(__('Export Invoice Import File'));
    $invNoId = $_GET['tid'];
    // die();
    if(!empty($invNoId)){

        $sql = "SELECT pupilsightPerson.officialName AS Name, pupilsightPerson.pupilsightPersonID AS 'Student Id', fn_fee_invoice_student_assign.invoice_no AS 'Invoice No' , SUM(fn_fee_invoice_item.total_amount) AS 'Invoice Amount' , fn_fee_invoice.title AS 'Invoice Title', pupilsightProgram.name AS Program, pupilsightYearGroup.name AS Class , pupilsightRollGroup.name AS Section 
        FROM fn_fee_invoice_student_assign 
        LEFT JOIN fn_fee_invoice ON fn_fee_invoice_student_assign.fn_fee_invoice_id = fn_fee_invoice.id 
        LEFT JOIN pupilsightPerson ON fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID 
        LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice_student_assign.pupilsightPersonID =pupilsightStudentEnrolment.pupilsightPersonID 
        LEFT JOIN pupilsightProgram ON pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID  
        LEFT JOIN pupilsightYearGroup ON pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID 
        LEFT JOIN pupilsightRollGroup ON pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID 
        LEFT JOIN fn_fee_invoice_item ON fn_fee_invoice_student_assign.fn_fee_invoice_id = fn_fee_invoice_item.fn_fee_invoice_id 
        WHERE fn_fee_invoice_student_assign.id IN (".$invNoId.") GROUP BY fn_fee_invoice_student_assign.id ORDER BY fn_fee_invoice_student_assign.id DESC ";
        $result = $connection2->query($sql);
        $invdata = $result->fetchAll();
    //       echo '<pre>';
    // print_r($data);
    // echo '</pre>';
    // die();

        $header = array();
        if(!empty($invdata)){
            $i = 1;
            foreach($invdata as  $d){
                $header = $d;
                $rowdata[] = $i.','.implode(',', $d);
                $i++;
            }
        }
        $hdr = array_keys($header);
    
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="InvoiceCollection.csv"');
        $columndata = 'Sl No,'.implode(',',$hdr).',Discount,Amount Paid,Payment Mode,Payment Received Date,Instrument No,Instrument Date,Bank Name,Remarks,Manual Receipt No';
        $data = array($columndata);

        $fp = fopen('php://output', 'wb');
        foreach ( $data as $line ) {
            $val = explode(",", $line);
            fputcsv($fp, $val);
        }

        foreach ($rowdata as $linenew ) {
            $valnew = explode(",", $linenew);
            fputcsv($fp, $valnew);
        }
        

        fclose($fp);
        die();
    }

}
    
?>



