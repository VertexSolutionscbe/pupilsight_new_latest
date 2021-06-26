<?php
/*
Pupilsight, Flexible & Open School System
 */

use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Domain\Archive\ArchiveGateway;

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
            importFeeStructure($connection2);
        } else if($type == 2){
            importFeeTransaction($connection2);
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

function importFeeStructure($connection2){
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
        $fn_fees_fine_rule_id = '';
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
                        $fn_fees_discount_id = '';
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
                            $data = array('name' => $invoice_title, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'invoice_title' => $invoice_title, 'fn_fees_head_id' => $fn_fees_head_id, 'inv_fee_series_id' => $inv_fee_series_id, 'recp_fee_series_id' => $recp_fee_series_id, 'fn_fees_fine_rule_id' => $fn_fees_fine_rule_id, 'fn_fees_discount_id' => $fn_fees_discount_id,'due_date' => $due_date, 'cdt' => $cdt);
                    
                            $sql = 'INSERT INTO fn_fee_structure SET name=:name, pupilsightSchoolYearID=:pupilsightSchoolYearID, invoice_title=:invoice_title,  fn_fees_head_id=:fn_fees_head_id, inv_fee_series_id=:inv_fee_series_id, recp_fee_series_id=:recp_fee_series_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, cdt=:cdt';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                            
                            $fn_fee_structure_id = $connection2->lastInsertID();

                            if(!empty($feeitems) && !empty($fn_fee_structure_id)){
                                foreach($feeitems as $feeitem){
                                    $fn_fee_item_id = getFeeItemData($allFeeItemData, $pupilsightSchoolYearID, $feeitem['fee_item_name']);
                                    $amt = $feeitem['fee_item_amount'];
                                    $taxdata = 'N';
                                    $taxpr = $feeitem['fee_item_tax'];
                                    $total_amount = $feeitem['FeeItemFinalAmount'];
                                    
                                    $data1 = array('fn_fee_structure_id' => $fn_fee_structure_id, 'fn_fee_item_id' => $fn_fee_item_id, 'amount' => $amt, 'tax' => $taxdata, 'tax_percent' => $taxpr,'total_amount' => $total_amount);
                                    $sql1 = "INSERT INTO fn_fee_structure_item SET fn_fee_structure_id=:fn_fee_structure_id, fn_fee_item_id=:fn_fee_item_id, amount=:amount,  tax=:tax, tax_percent=:tax_percent, total_amount=:total_amount";
                                    $result1 = $connection2->prepare($sql1);
                                    $result1->execute($data1);
                                }

                                $data2 = array('fn_fee_structure_id' => $fn_fee_structure_id, 'pupilsightPersonID' => $pupilsightPersonID);
                                $sql2 = "INSERT INTO fn_fees_student_assign SET fn_fee_structure_id=:fn_fee_structure_id, pupilsightPersonID=:pupilsightPersonID";
                                $result2 = $connection2->prepare($sql2);
                                $result2->execute($data2);
                            } 
                        }catch(Exception $ex){
                            echo $ex->getMessage();
                        }
                        //Fee Structure Insert End Here

                        //Invoice Insert Start Here
                        try{
                            $data = array('title' => $invoice_title, 'fn_fee_structure_id' => $fn_fee_structure_id , 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'inv_fn_fee_series_id' => $inv_fee_series_id, 'rec_fn_fee_series_id' => $recp_fee_series_id, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_fine_rule_id' => $fn_fees_fine_rule_id, 'fn_fees_discount_id' => $fn_fees_discount_id, 'due_date' => $due_date, 'cdt' => $cdt);
                        
                            $sql = 'INSERT INTO fn_fee_invoice SET title=:title, fn_fee_structure_id=:fn_fee_structure_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, cdt=:cdt';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                            
                            $invId = $connection2->lastInsertID();

                            if(!empty($feeitems) && !empty($invId)){
                                foreach($feeitems as $feeitem){
                                    $fn_fee_item_id = getFeeItemData($allFeeItemData, $pupilsightSchoolYearID, $feeitem['fee_item_name']);
                                    $amt = $feeitem['fee_item_amount'];
                                    $taxdata = 'N';
                                    $taxpr = $feeitem['fee_item_tax'];
                                    $total_amount = $feeitem['FeeItemFinalAmount'];
                                    $item_amount_discounted = $feeitem['item_amount_discounted'];
                                    
                                    $data1 = array('fn_fee_invoice_id' => $invId, 'fn_fee_item_id' => $fn_fee_item_id, 'amount' => $amt, 'tax' => $taxpr, 'discount' => $item_amount_discounted, 'total_amount' => $total_amount);
                                    $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, amount=:amount,  tax=:tax, discount=:discount, total_amount=:total_amount";
                                    $result1 = $connection2->prepare($sql1);
                                    $result1->execute($data1);
                                }

                                
                                // $dataca = array('fn_fee_invoice_id' => $invId, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                                // $sqlca = "INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID";
                                // $resultca = $connection2->prepare($sqlca);
                                // $resultca->execute($dataca);

                                $dataca = array('fn_fee_invoice_id' => $invId, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
                                $sqlca = "INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID";
                                $resultca = $connection2->prepare($sqlca);
                                $resultca->execute($dataca);

                                $dataistu = array('fn_fee_invoice_id'=>$invId,'fn_fee_structure_id' => $fn_fee_structure_id,'pupilsightPersonID' => $pupilsightPersonID, 'invoice_no' => $invoice_no, 'invoice_status' => $invoiceStatus);
                                $sql1av = 'INSERT INTO fn_fee_invoice_student_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,fn_fee_structure_id=:fn_fee_structure_id,pupilsightPersonID=:pupilsightPersonID,invoice_no=:invoice_no, invoice_status=:invoice_status';
                                $result1av = $connection2->prepare($sql1av);
                                $result1av->execute($dataistu);
                            }
                        }catch(Exception $ex){
                            echo $ex->getMessage();
                        }
                        //Invoice Insert End Here

                        //Update Archive Invoice Start Here
                        try{
                            $datafort1 = array('structure_status'=>1,'invoice_stauts' => 1, 'invoice_no' => $invoice_no);
                            $sqlfort1 = 'UPDATE archive_feeInvoices SET structure_status=:structure_status, invoice_stauts=:invoice_stauts WHERE invoice_no=:invoice_no';
                            $resultfort1 = $connection2->prepare($sqlfort1);
                            $resultfort1->execute($datafort1);
                        }catch(Exception $ex){
                            echo $ex->getMessage();
                        }
                        //Update Archive Invoice End Here

                        
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
            if($cd['pupilsightSchoolYearID'] == $pupilsightSchoolYearID && $cd['name'] == trim($fee_item_name)){
                $fee_item_id = $cd['id'];
            }
        }
    }
    return $fee_item_id;
}