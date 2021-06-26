<?php
/*
Pupilsight, Flexible & Open School System
 */

use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Domain\Archive\ArchiveGateway;

include $_SERVER["DOCUMENT_ROOT"] . '/pupilsight/db.php';

function getDomain()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function expandDirectories($base_dir) {
    $directories = array();
    foreach(scandir($base_dir) as $file) {
        if($file == '.' || $file == '..') continue;
        $dir = $base_dir.DIRECTORY_SEPARATOR.$file;
        if(is_dir($dir)) {
            $directories = array_merge($directories, expandDirectories($dir));
        }else{
            if(strstr($dir,".pdf")){
                $directories []= $dir;
            }
        }
    }
    return $directories;
}

$baseurl = getDomain();

$accessFlag = true;
if ($accessFlag == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $roleid = $_SESSION[$guid]["pupilsightRoleIDPrimary"];
    $page->breadcrumbs->add(__('Import Archives'));

    if($_POST){
        $type = $_POST['type'];
        if($type == 1){
            importFeeStructure($connection2, $conn);
        } else if($type == 2){
            importFeeTransaction($connection2, $conn);
        }
    }
?>
        
    <!----Report Details---->
    <div class="my-2" id='reportList'>
        <?php
            try{
            
            $helperGateway = $container->get(HelperGateway::class);
            $res = $helperGateway->getArchiveReport($connection2);

            $archiveGateway = $container->get(ArchiveGateway::class);

            // $term = $archiveGateway->listFeeInvoiceTerm($connection2);
            // $academicYear = $archiveGateway->listFeeInvoiceAcademicYear($connection2);
            // $stream = $archiveGateway->listFeeInvoiceStream($connection2);

            // $termTrans = $archiveGateway->listFeeTransTerm($connection2);
            // $academicYearTrans = $archiveGateway->listFeeTransAcademicYear($connection2);
            // $streamTrans = $archiveGateway->listFeeTransStream($connection2);
            
            }catch(Exception $ex){
                echo $ex->getMessage();
            }
        ?>
        
        <form method="post">
        <button type="submit" href="#feeTransactions" class="btn btn-primary" name="type" value="1">Fee Invoice</button>
        <button type="submit" href="#feeTransactions" class="btn btn-primary" name="type" value="2">Fee Transaction</button>
        </form>
        <!-- <ul class="nav nav-tabs">
            <li class="nav-item">
                <a href="#archiveList" class="nav-link active">Archive List</a>
            </li>
            <li class="nav-item">
                <a href="#feeTransactions" class="nav-link">Fee Transactions</a>
            </li>
            <li class="nav-item">
                <a href="#feeInvoice" class="nav-link">Fee Invoice</a>
            </li>
            <li class="nav-item">
                <a href="#feeRecipt" class="nav-link">Fee Recipt</a>
            </li>
            <li class="nav-item">
                <a href="#reportCard" class="nav-link">Report Card</a>
            </li>
        </ul> -->

        
    </div>

    <!-- <button type="button" id='btnReportParam' data-toggle="modal" data-target="#reportParamDialog"></button> -->

<?php
}

function importFeeStructure($connection2, $conn){
    $type = '1';
    $archiveData = getArchiveData($connection2, $type);
    // echo '<pre>';
    // print_r($archiveData);
    // echo '</pre>';
    // die();
    if(!empty($archiveData)){
        $academicYearData = getAllAcademicYearID($connection2);
        $programData = getAllProgramID($connection2);
        $studentAllData = getAllStudentData($connection2);
        $classData = getAllClassID($connection2);
        $allFeeHeadData = getAllFeeHead($connection2);
        $allFineData = getAllFine($connection2);
        $allFeeItemData = getAllFeeItem($connection2);
        // echo '<pre>';
        // print_r($archiveData);
        // echo '</pre>';
        // die();
        $feeItem = array();
        $fn_fees_fine_rule_id = 0;
        foreach($archiveData as $arData){
            $invoice_status = trim($arData['invoice_status']);
            if($invoice_status != 'CANCELED'){
                $pupilsightPersonID = array_search($arData['StudentID'], $studentAllData);
                if(!empty($pupilsightPersonID)){
                    $pupilsightSchoolYearID = array_search($arData['AcademicYear'], $academicYearData);
                    $pupilsightProgramID = array_search($arData['Program'], $programData);
                    $pupilsightYearGroupID = getClassID($classData, $pupilsightSchoolYearID, $arData['Term']);
                    $feeHeadData = getFeeHead($allFeeHeadData, $pupilsightSchoolYearID, $arData['Account_Head']);
                    if(!empty($arData['fine_name'])){
                        $fn_fees_fine_rule_id = getFineData($allFineData, $arData['fine_name']);
                    }   
                    
                    $invoice_no = $arData['invoice_no'];
                    if(!empty($invoice_no) && !empty($pupilsightSchoolYearID) && !empty($pupilsightProgramID) && !empty($pupilsightYearGroupID) && !empty($feeHeadData)){
                        $invoice_title = $arData['invoice_title'];
                        $final_amount = $arData['final_amount'];
                        $amount = $arData['amount'];
                        $tax = $arData['tax'];
                        $invoice_generated_date = $arData['invoice_generated_date'];
                        $amount_paid = $arData['amount_paid'];
                        $amount_pending = $arData['amount_pending'];
                        $due_date = date('Y-m-d', strtotime($arData['due_date']));
                        $fine_name = $arData['fine_name'];
                        $fn_fees_head_id = $feeHeadData['id'];
                        $inv_fee_series_id = $feeHeadData['inv_fee_series_id'];
                        $recp_fee_series_id = $feeHeadData['recp_fee_series_id'];
                        $fn_fees_discount_id = 0;
                        $cdt = date('Y-m-d h:i:s', strtotime($arData['invoice_generated_date']));
                        if($invoice_status == 'NOT_PAID'){
                            $invoiceStatus = 'Not Paid';
                        } else if($invoice_status == 'PARTIALY_PAID'){
                            $invoiceStatus = 'Partial Paid';
                        }
                        
                        $feeItem[0]['invoice_no']    = $arData['invoice_no'];
                        $feeItem[0]['fee_item_name'] = $arData['fee_item_name'];
                        $feeItem[0]['fee_item_amount'] = $arData['fee_item_amount'];
                        $feeItem[0]['fee_item_discount'] = $arData['fee_item_discount'];
                        $feeItem[0]['FeeItemAmountPaid'] = $arData['FeeItemAmountPaid'];
                        $feeItem[0]['item_amount_discounted'] = $arData['item_amount_discounted'];
                        $feeItem[0]['FeeItemAmountPending'] = $arData['FeeItemAmountPending'];
                        $feeItem[0]['invoice_item_status'] = $arData['invoice_item_status'];
                        $feeItem[0]['fee_item_tax'] = $arData['fee_item_tax'];
                        $feeItem[0]['FeeItemFinalAmount'] = $arData['FeeItemFinalAmount'];
                        $feeItem[0]['FeeItemOrder'] = $arData['FeeItemOrder'];

                        if(!empty($arData['feeItem'])){
                            $feeitems = array_merge($feeItem,$arData['feeItem']);
                        } else {
                            $feeitems = $feeItem;
                        }
                       
                        //Fee Structure Insert Start Here
                        try {
                            $sql1 = "INSERT INTO fn_fee_structure (name, pupilsightSchoolYearID, invoice_title,  fn_fees_head_id, inv_fee_series_id, recp_fee_series_id, fn_fees_fine_rule_id, fn_fees_discount_id, due_date, cdt) 
                            VALUES 
                            ('" . $invoice_title . "','" . $pupilsightSchoolYearID . "','" . $invoice_title . "','" . $fn_fees_head_id . "','" . $inv_fee_series_id . "','" . $recp_fee_series_id . "','" . $fn_fees_fine_rule_id . "','" . $fn_fees_discount_id . "','" . $due_date . "','" . $cdt . "')";
                            //echo $sql1.'</br>';
                            //$conn->autocommit(FALSE);
                            $conn->query($sql1);
                            $fn_fee_structure_id = $conn->insert_id;
                            
                            if(!empty($feeitems) && !empty($fn_fee_structure_id)){
                                foreach($feeitems as $feeitem){
                                    $fn_fee_item_id = getFeeItemData($allFeeItemData, $pupilsightSchoolYearID, $feeitem['fee_item_name']);
                                    $amt = $feeitem['fee_item_amount'];
                                    $taxdata = 'N';
                                    $taxpr = $feeitem['fee_item_tax'];
                                    $total_amount = $feeitem['FeeItemFinalAmount'];

                                    $sql2 = "INSERT INTO fn_fee_structure_item (fn_fee_structure_id, fn_fee_item_id, amount,  tax, tax_percent, total_amount) 
                                    VALUES 
                                    ('" . $fn_fee_structure_id . "','" . $fn_fee_item_id . "','" . $amt . "','" . $taxdata . "','" . $taxpr . "','" . $total_amount . "')";
                                    //echo $sql2.'</br>';
                                    $conn->query($sql2);
                                    
                                }

                                $sql3 = "INSERT INTO fn_fees_student_assign (fn_fee_structure_id, pupilsightPersonID) 
                                VALUES 
                                (" . $fn_fee_structure_id . "," . $pupilsightPersonID . ")";
                                //echo $sql3.'</br>';
                                $conn->query($sql3);

                            } 
                            //$conn->commit();
                        }catch(Exception $ex){
                            //$conn->rollback();
                            echo $ex->getMessage();
                        }
                        //Fee Structure Insert End Here

                        //Invoice Insert Start Here
                        try{

                            $sql4 = "INSERT INTO fn_fee_invoice (title, fn_fee_structure_id, pupilsightSchoolYearID, inv_fn_fee_series_id, rec_fn_fee_series_id, fn_fees_head_id, fn_fees_fine_rule_id, fn_fees_discount_id, due_date, cdt) 
                            VALUES 
                            ('" . $invoice_title . "','" . $fn_fee_structure_id . "','" . $pupilsightSchoolYearID . "','" . $inv_fn_fee_series_id . "','" . $recp_fee_series_id . "','" . $fn_fees_head_id . "','" . $fn_fees_fine_rule_id . "','" . $fn_fees_discount_id . "','" . $due_date . "','" . $cdt . "')";
                            //echo $sql4.'</br>';
                            $conn->query($sql4);
                            $invId = $conn->insert_id;

                            if(!empty($feeitems) && !empty($invId)){
                                foreach($feeitems as $feeitem){
                                    $fn_fee_item_id = getFeeItemData($allFeeItemData, $pupilsightSchoolYearID, $feeitem['fee_item_name']);
                                    $amt = $feeitem['fee_item_amount'];
                                    $taxdata = 'N';
                                    $taxpr = $feeitem['fee_item_tax'];
                                    $total_amount = $feeitem['FeeItemFinalAmount'];
                                    $item_amount_discounted = $feeitem['item_amount_discounted'];

                                    $sql5 = "INSERT INTO fn_fee_invoice_item (fn_fee_invoice_id, fn_fee_item_id, amount,  tax, discount, total_amount) 
                                    VALUES 
                                    (" . $invId . "," . $fn_fee_item_id . "," . $amt . "," . $taxpr . "," . $item_amount_discounted . "," . $total_amount . ")";
                                    //echo $sql5.'</br>';
                                    $conn->query($sql5);
                                }

                                
                                $sql6 = "INSERT INTO fn_fee_invoice_class_assign (fn_fee_invoice_id, pupilsightProgramID, pupilsightYearGroupID) 
                                VALUES 
                                (" . $invId . "," . $pupilsightProgramID . "," . $pupilsightYearGroupID . ")";
                                //echo $sql6.'</br>';
                                $conn->query($sql6);

                                $sql7 = "INSERT INTO fn_fee_invoice_student_assign (fn_fee_invoice_id, fn_fee_structure_id, pupilsightPersonID, invoice_no, invoice_status) 
                                VALUES 
                                ('" . $invId . "','" . $fn_fee_structure_id . "','" . $pupilsightPersonID . "','" . $invoice_no . "','" . $invoiceStatus . "')";
                                //echo $sql7.'</br>';
                                $conn->query($sql7);

                                
                            }
                        }catch(Exception $ex){
                            echo $ex->getMessage();
                        }
                        //Invoice Insert End Here

                        //Update Archive Invoice Start Here
                        try{
                            
                            $sql8 = "UPDATE archive_feeInvoices SET structure_status= '1', invoice_stauts= '1' WHERE invoice_no= '".$invoice_no."' ";
                            //echo $sql8.'</br>';
                            $conn->query($sql8);

                        }catch(Exception $ex){
                            echo $ex->getMessage();
                        }
                        //Update Archive Invoice End Here
                        //die();
                        
                        // echo '<pre>';
                        // print_r($feeitems);
                        // echo '</pre>';
                    }
                }
            }
        }
    }
}

function importFeeInvoice($connection2){
    $type = '2';
    $archiveData = getArchiveData($connection2, $type);
}

function importFeeTransaction($connection2){
    $type = '3';
    $archiveData = getArchiveData($connection2, $type);
}

function getArchiveData($connection2, $type){
    $sql = 'SELECT * FROM archive_feeInvoices WHERE invoice_status != "FULLY_PAID" ';
    if($type == 1){
        $sql .= ' AND structure_status = "0" ';
    } else if($type == 2){
        $sql .= ' AND invoice_stauts = "0" ';
    } else if($type == 3){
        $sql .= ' AND transaction_status = "0" ';
    }
    //echo $sql;
    $result = $connection2->query($sql);
    $archiveData = $result->fetchAll();
    if(!empty($archiveData)){
        $i = 1;
        $invoiceData = array();
        $j = 0;
        $key = '';
        foreach($archiveData as $k => $ad){
            $FeeItemOrder = $ad['FeeItemOrder'];
            $feeOrder = $FeeItemOrder;
            if($feeOrder == 1){
                $invoice_no = $ad['invoice_no'];
                $key = $k;
            } 
            
            if($invoice_no == $ad['invoice_no'] && $feeOrder != '1'){
                $invoiceData[$key]['feeItem'][$j] = $ad;
                $j++;
            } else {
                $invoiceData[$key] = $ad;
                $j = 0;
            }
            $i++;
            
        }
    }

    return array_values($invoiceData);
}

function getAllAcademicYearID($connection2){
    $sql = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear  ';
    $result = $connection2->query($sql);
    $academicData = $result->fetchAll();
    if(!empty($academicData)){
        $academic = array();
        foreach($academicData as $ad){
            $academic[$ad['pupilsightSchoolYearID']] = $ad['name'];
        }
    }
    return $academic;
}

function getAllFeeHead($connection2){
    $sql = 'SELECT * FROM fn_fees_head';
    $result = $connection2->query($sql);
    $feeHeadData = $result->fetchAll();
    return $feeHeadData;
}

function getFeeHead($allFeeHeadData, $pupilsightSchoolYearID, $feeHead){
    $feeHeadData = array();
    if(!empty($allFeeHeadData)){
        foreach($allFeeHeadData as $cd){
            if($cd['pupilsightSchoolYearID'] == $pupilsightSchoolYearID && $cd['name'] == trim($feeHead)){
                $feeHeadData = $cd;
            }
        }
    }
    return $feeHeadData;
}

function getAllProgramID($connection2){
    $sql = 'SELECT pupilsightProgramID, name FROM pupilsightProgram  ';
    $result = $connection2->query($sql);
    $programData = $result->fetchAll();
    if(!empty($programData)){
        $program = array();
        foreach($programData as $ad){
            $program[$ad['pupilsightProgramID']] = $ad['name'];
        }
    }
    return $program;
}

function getAllClassID($connection2){
    $sql = 'SELECT pupilsightSchoolYearID, pupilsightYearGroupID, name FROM pupilsightYearGroup  ';
    $result = $connection2->query($sql);
    $classData = $result->fetchAll();
    return $classData;
}

function getClassID($classData, $pupilsightSchoolYearID, $className){
    $pupilsightYearGroupID = '';
    if(!empty($classData)){
        foreach($classData as $cd){
            if($cd['pupilsightSchoolYearID'] == $pupilsightSchoolYearID && $cd['name'] == trim($className)){
                $pupilsightYearGroupID = $cd['pupilsightYearGroupID'];
            }
        }
    }
    return $pupilsightYearGroupID;
}

function getAllStudentData($connection2){
    $sql = 'SELECT pupilsightPersonID, old_pupilpod_id  FROM pupilsightPerson WHERE pupilsightRoleIDPrimary = "003" AND old_pupilpod_id != "" ';
    $result = $connection2->query($sql);
    $studentData = $result->fetchAll();
    if(!empty($studentData)){
        $students = array();
        foreach($studentData as $ad){
            $students[$ad['pupilsightPersonID']] = $ad['old_pupilpod_id'];
        }
    }
    return $students;
}

function getAllFine($connection2){
    $sql = 'SELECT * FROM fn_fees_fine_rule';
    $result = $connection2->query($sql);
    $fineData = $result->fetchAll();
    return $fineData;
}

function getFineData($allFineData, $fine_name){
    $fineData = array();
    if(!empty($allFineData)){
        foreach($allFineData as $cd){
            if($cd['name'] == trim($fine_name)){
                $fineId = $cd['id'];
            }
        }
    }
    return $fineId;
}

function getAllFeeItem($connection2){
    $sql = 'SELECT * FROM fn_fee_items';
    $result = $connection2->query($sql);
    $fineData = $result->fetchAll();
    return $fineData;
}

function getFeeItemData($allFeeItemData, $pupilsightSchoolYearID, $fee_item_name){
    $fineData = array();
    if(!empty($allFeeItemData)){
        foreach($allFeeItemData as $cd){
            // if($cd['pupilsightSchoolYearID'] == $pupilsightSchoolYearID && $cd['name'] == trim($fee_item_name)){
            if($cd['name'] == trim($fee_item_name)){
                $fee_item_id = $cd['id'];
            }
        }
    }
    return $fee_item_id;
}