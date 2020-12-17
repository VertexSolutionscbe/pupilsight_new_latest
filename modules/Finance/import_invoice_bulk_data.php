<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;

$session = $container->get('session');
include $_SERVER["DOCUMENT_ROOT"] . '/pupilsight.php';
include $_SERVER["DOCUMENT_ROOT"] . '/db.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/vendor/phpoffice/phpword/bootstrap.php';



require __DIR__ . '/moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Finance/invoice_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Edited by : Mandeep, Reason : added recomended way for displaying notification
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $page->breadcrumbs
    ->add(__('Manage Invoice'), 'invoice_manage.php')
    ->add(__('Invoice Collection Import'));
    $form = Form::create('importStep1', $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Finance/import_invoice_bulk_data.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    $row = $form->addRow();
    $row->addLabel('file', __('File'))->description(__('See Notes below for specification.'));
    $row->addFileUpload('file')->required()->accepts('.csv');

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();


    if ($_POST) {
        $handle = fopen($_FILES['file']['tmp_name'], "r");
        $headers = fgetcsv($handle, 10000, ",");
        $hders = array();
        // echo '<pre>';
        // print_r($headers);
        // echo '</pre>';
        // die();
        $chkHeaderKey = array();
        foreach ($headers as $key => $hd) {

            if ($hd == 'Invoice No') {
                $headers[$key] = 'invoice_no';
            } else if ($hd == 'Invoice Amount') {
                $headers[$key] = 'transcation_amount';
            } else if ($hd == 'Student Id') {
                $headers[$key] = 'pupilsightPersonID';
            } else if ($hd == 'Amount Paid') {
                $headers[$key] = 'amount_paying';
            } else if ($hd == 'Payment Mode') {
                $headers[$key] = 'payment_mode';
            } else if ($hd == 'Payment Received Date') {
                $headers[$key] = 'payment_date';
            } else if ($hd == 'Instrument No') {
                $headers[$key] = 'instrument_no';
            } else if ($hd == 'Instrument Date') {
                $headers[$key] = 'instrument_date';
            } else if ($hd == 'Bank Name') {
                $headers[$key] = 'bank_name';
            } else if ($hd == 'Remarks') {
                $headers[$key] = 'remarks';
            } else if ($hd == 'Manual Receipt No') {
                $headers[$key] = 'receipt_number';
            }
        }

        $hders = $headers;

        $all_rows = array();
        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            $all_rows[] = array_combine($hders, $data);
        }

        if (!empty($all_rows)) {

           
            // echo '<pre>';
            //     print_r($all_rows);
            //     echo '</pre>';
              // die();
            foreach ($all_rows as $key => $alrow) {
                unset($dts_receipt);
                unset($dts_receipt_feeitem);
                if(!empty($alrow['amount_paying']) && !empty($alrow['invoice_no'])){
                    $pupilsightPersonID = $alrow['pupilsightPersonID'];
                    if($alrow['amount_paying'] < $alrow['transcation_amount']){
                        $invoice_status = 'Partial Paid';
                    } else {
                        $invoice_status = 'Fully Paid';
                    }
                    if(!empty($alrow['payment_mode'])){
                        $sql = 'SELECT id FROM fn_masters WHERE name = "'.$alrow['payment_mode'].'"';
                        $result = $connection2->query($sql);
                        $value = $result->fetch();
                        $payment_mode_id = $value['id'];
                    } else {
                        $payment_mode_id = '';
                    }

                    if(!empty($alrow['bank_name'])){
                        $sql = 'SELECT id FROM fn_masters WHERE name = "'.$alrow['bank_name'].'"';
                        $result = $connection2->query($sql);
                        $value = $result->fetch();
                        $bank_id = $value['id'];
                        $bank_name = $alrow['bank_name'];
                    } else {
                        $bank_id = '';
                        $bank_name = '';
                    }

                    if(!empty($alrow['payment_date'])){
                        $payment_date = date('Y-m-d', strtotime($alrow['payment_date']));
                    } else {
                        $payment_date = date('Y-m-d');
                    }

                    if(!empty($alrow['instrument_date'])){
                        $instrument_date = date('Y-m-d', strtotime($alrow['instrument_date']));
                        $dd_cheque_amount = $alrow['amount_paying'];
                    } else {
                        $instrument_date = date('Y-m-d');
                        $dd_cheque_amount = '';
                    }

                    
                    $sql = 'SELECT b.* FROM fn_fee_invoice_student_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id WHERE a.invoice_no = "'.$alrow['invoice_no'].'"';
                    $result = $connection2->query($sql);
                    $invData = $result->fetch();
                    $invoice_id  = $invData['id'];
                    $fn_fees_receipt_series_id = $invData['rec_fn_fee_series_id'];
                    $pupilsightSchoolYearID = $invData['pupilsightSchoolYearID'];
                    $fn_fees_head_id = $invData['fn_fees_head_id'];
                    $remarks = $alrow['remarks'];
                    $cdt = date('Y-m-d H:i:s');
                    $transcation_amount = $alrow['transcation_amount'];
                    $amount_paying = $alrow['amount_paying'];

                    $sqlrt = 'SELECT b.path FROM fn_fees_head AS a LEFT JOIN fn_fees_receipt_template_master AS b ON a.receipt_template = b.id WHERE a.id = '.$fn_fees_head_id.' ';
                    $resultrt = $connection2->query($sqlrt);
                    $recTempData = $resultrt->fetch();
                    $receiptTemplate = $recTempData['path'];

                    if(!empty($alrow['receipt_number'])){
                        $receipt_number = $alrow['receipt_number'];
                    } else {
                        if(!empty($fn_fees_receipt_series_id)){
                            $sqlrec = 'SELECT id, formatval FROM fn_fee_series WHERE id = "'.$fn_fees_receipt_series_id.'" ';
                            $resultrec = $connection2->query($sqlrec);
                            $recptser = $resultrec->fetch();
                    
                            $invformat = explode('$',$recptser['formatval']);
                            $iformat = '';
                            $orderwise = 0;
                            foreach($invformat as $inv){
                                if($inv == '{AB}'){
                                    $datafort = array('fn_fee_series_id'=>$fn_fees_receipt_series_id,'order_wise' => $orderwise, 'type' => 'numberwise');
                                    $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                                    $resultfort = $connection2->prepare($sqlfort);
                                    $resultfort->execute($datafort);
                                    $formatvalues = $resultfort->fetch();
                                    //$iformat .= $formatvalues['last_no'].'/';
                                    $iformat .= $formatvalues['last_no'];
                                    
                                    $str_length = $formatvalues['no_of_digit'];
                    
                                    $lastnoadd = $formatvalues['last_no'] + 1;
                    
                                    $lastno = substr("0000000{$lastnoadd}", -$str_length); 
                    
                                    $datafort1 = array('fn_fee_series_id'=>$fn_fees_receipt_series_id,'order_wise' => $orderwise, 'type' => 'numberwise' , 'last_no' => $lastno);
                                    $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                                    $resultfort1 = $connection2->prepare($sqlfort1);
                                    $resultfort1->execute($datafort1);
                    
                                } else {
                                    //$iformat .= $inv.'/';
                                    $iformat .= $inv;
                                }
                                $orderwise++;
                            }
                            
                            //    $receipt_number = rtrim($iformat, "/");;
                            $receipt_number = $iformat;
                        } else {
                            $receipt_number = '';
                        }
                    }

                    $rand = mt_rand(10,99);    
                    $t=time();
                    $transactionId = $t.$rand;

                    $data = array('transaction_id' => $transactionId, 'fn_fees_invoice_id' => $invoice_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID,  'receipt_number' => $receipt_number,  'payment_mode_id' => $payment_mode_id, 'bank_id' => $bank_id, 'dd_cheque_no' => $alrow['instrument_no'], 'dd_cheque_date' => $instrument_date, 'dd_cheque_amount' => $dd_cheque_amount, 'payment_status' => $payment_status, 'payment_date' => $payment_date, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_receipt_series_id' => $fn_fees_receipt_series_id, 'transcation_amount' => $transcation_amount, 'total_amount_without_fine_discount' => $transcation_amount, 'amount_paying' => $amount_paying, 'remarks' => $remarks, 'status' => '1', 'cdt' => $cdt,'instrument_no'=>$alrow['instrument_no'],'instrument_date'=>$instrument_date,'invoice_status'=>$invoice_status);
                    
                    $sql = 'INSERT INTO fn_fees_collection SET transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID =:pupilsightSchoolYearID,  receipt_number=:receipt_number,  payment_mode_id=:payment_mode_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, payment_status=:payment_status, payment_date=:payment_date, fn_fees_head_id=:fn_fees_head_id, fn_fees_receipt_series_id=:fn_fees_receipt_series_id, transcation_amount=:transcation_amount, total_amount_without_fine_discount=:total_amount_without_fine_discount, amount_paying=:amount_paying, remarks=:remarks, status=:status,cdt=:cdt,instrument_no=:instrument_no,instrument_date=:instrument_date,invoice_status=:invoice_status';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    
                    $collectionId = $connection2->lastInsertID();

                    $isql = 'SELECT a.id, a.fn_fee_invoice_id, a.total_amount, b.name FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id WHERE a.fn_fee_invoice_id = '.$invoice_id.' ';
                    $resultip = $connection2->query($isql);
                    $valuesip = $resultip->fetchAll();

                    if($amount_paying < $transcation_amount){
                        
                        if(!empty($valuesip)){
                            $chkamount = $amount_paying;
                            //$i = 1;
                            foreach($valuesip as $itmid){
                                $fn_fee_invoice_id = $itmid['fn_fee_invoice_id'];
                                $invoice_no = $alrow['invoice_no'];
                                $itemamount = $itmid['total_amount'];
                                $itid = $itmid['id'];
                                if($itemamount < $chkamount){
                                    $status = '1';
                                    $paidamount = $itemamount;
                                    $chkamount = $chkamount - $itemamount;
                                } else {
                                    $status = '2';
                                    if($chkamount > 0){
                                        $paidamount = $chkamount;
                                    } else {
                                        $paidamount = '';
                                    }
                                    $chkamount = $chkamount - $itemamount;
                                    
                                }
    
                                // $leftAmt = $chkamount - $paidamount; 
                                // $balanceAmt = $leftAmt;
                                    
                                    $datai = array('pupilsightPersonID'=>$pupilsightPersonID,'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'total_amount_collection' => $paidamount, 'status' => $status);
                                    $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, total_amount_collection=:total_amount_collection, status=:status';
                                    $resulti = $connection2->prepare($sqli);
                                    $resulti->execute($datai);
                              
                            }
                        }
                    } else {
                        

                        foreach($valuesip as $itmid){  
                            $itemamount = $itmid['total_amount'];
                            $itid = $itmid['id']; 
                            $fn_fee_invoice_id = $itmid['fn_fee_invoice_id'];
                            $invoice_no = $alrow['invoice_no'];

                            
                            $chkpayitem = 'SELECT a.id FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fees_invoice_id = '.$fn_fee_invoice_id.' AND a.fn_fee_invoice_item_id = '.$itid.' AND a.pupilsightPersonID = '.$pupilsightPersonID.' AND b.transaction_status = 1 ';
                            $resultcp = $connection2->query($chkpayitem);
                            $valuecp = $resultcp->fetch();

                            if(!empty($valuecp)){
                                $datai = array('partial_transaction_id' => $transactionId,'total_amount_collection' => $itemamount, 'status' => '1', 'id' => $valuecp['id']);
                                $sqli = 'UPDATE fn_fees_student_collection SET partial_transaction_id=:partial_transaction_id, total_amount_collection=:total_amount_collection, status=:status WHERE id=:id';
                                $resulti = $connection2->prepare($sqli);
                                $resulti->execute($datai);
                            } else {
                                $datai = array('pupilsightPersonID'=>$pupilsightPersonID,'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'total_amount_collection' => $itemamount, 'status' => '1');
                                $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, total_amount_collection=:total_amount_collection, status=:status';
                                $resulti = $connection2->prepare($sqli);
                                $resulti->execute($datai);
                            }
        
                                
                        }
                    }

                    $sqlstu = "SELECT a.officialName , a.admission_no, b.name as class, c.name as section FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightPersonID = ".$pupilsightPersonID." ";
                    $resultstu = $connection2->query($sqlstu);
                    $valuestu = $resultstu->fetch();
    
                    $sqlpt = "SELECT name FROM fn_masters WHERE id = ".$payment_mode_id." ";
                    $resultpt = $connection2->query($sqlpt);
                    $valuept = $resultpt->fetch();
    
                    $class_section = $valuestu["class"] ." ".$valuestu["section"];
                    $dts_receipt = array(
                        "receipt_no" => $receipt_number,
                        "date" => date("d-M-Y"),
                        "student_name" => $alrow["Name"],
                        "student_id" => $valuestu["admission_no"],
                        "class_section" => $class_section,
                        "instrument_date" => $instrument_date,
                        "instrument_no" => $instrument_no,
                        "transcation_amount" => $amount_paying,
                        "fine_amount" => $fine,
                        "other_amount" => "NA",
                        "pay_mode" => $valuept['name'],
                        "transactionId" => $transactionId,
                        "receiptTemplate" => $receiptTemplate,
                        "bank_name" => $bank_name
                    );
    
                    // echo '<pre>';
                    // print_r($dts_receipt);
                    // echo '</pre>';
                    // die();
                    $stuName = str_replace(' ', '_', $alrow["Name"]);
                    $filename = $stuName.'_'.$transactionId;
                    $session->forget(['doc_receipt_id']);
                    $session->set('doc_receipt_id',$filename);
                   
                        
                            $dataiu = array('invoice_status' => $invoice_status,  'pupilsightPersonID' => $pupilsightPersonID,  'fn_fee_invoice_id' => $invoice_id);
                            $sqliu = 'UPDATE fn_fee_invoice_student_assign SET invoice_status=:invoice_status WHERE pupilsightPersonID=:pupilsightPersonID AND fn_fee_invoice_id=:fn_fee_invoice_id';
                            $resultiu = $connection2->prepare($sqliu);
                            $resultiu->execute($dataiu);
    
                            $chksql = 'SELECT fn_fee_structure_id, display_fee_item, title as invoice_title FROM fn_fee_invoice WHERE id = '.$invoice_id.' ';
                            $resultchk = $connection2->query($chksql);
                            $invData = $resultchk->fetch();
                            if($invData['fn_fee_structure_id'] == ''){
                                $valuech = $invData;
                            } else {
                                $chsql = 'SELECT b.invoice_title, a.display_fee_item FROM fn_fee_invoice AS a LEFT JOIN fn_fee_structure AS b ON a.fn_fee_structure_id = b.id WHERE a.id= '.$invoice_id.' AND a.fn_fee_structure_id IS NOT NULL ';
                                $resultch = $connection2->query($chsql);
                                $valuech = $resultch->fetch();
                            }
    
                            if($valuech['display_fee_item'] == '2'){
                                $sqcs = "select SUM(fi.total_amount) AS tamnt from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id =  ".$invoice_id." ";
                                $resultfi = $connection2->query($sqcs);
                                $valuefi = $resultfi->fetchAll();
                                if (!empty($valuefi)) {
                                    $cnt = 1;
                                    foreach($valuefi as $vfi){
                                        $dts_receipt_feeitem[] = array(
                                            "serial.all"=>$cnt,
                                            "particulars.all"=>$valuech['invoice_title'],
                                            "amount.all"=>$vfi["tamnt"]
                                        );
                                        $cnt ++;
                                    }
                                }
    
                            } else {
                                
                                if (!empty($valuesip)) {
                                    $cnt = 1;
                                    foreach($valuesip as $vfi){
                                        $dts_receipt_feeitem[] = array(
                                            "serial.all"=>$cnt,
                                            "particulars.all"=>$vfi["name"],
                                            "amount.all"=>$vfi["total_amount"]
                                        );
                                        $cnt ++;
                                    }
                                }
                            }
                        
                   


                    if(!empty($dts_receipt) && !empty($dts_receipt_feeitem) && !empty($receiptTemplate)){ 
                        $callback = $_SESSION[$guid]['absoluteURL'].'/thirdparty/phpword/receiptNew.php';
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
                        )
                        );
                        $context  = stream_context_create($opts);
                        $result = file_get_contents($callback, false, $context);
                    } 
                    


                }
               
            }
              
        }


        fclose($handle);

        $URL .= '&return=success1';
        header("Location: {$URL}");
    }
}

