<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/fee_setting.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/fee_setting.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs

    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    
    $fee_structure_id = $_POST['fee_structure_id'];
    $class = $_POST['class'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $amounts = $_POST['amount'];
    $form_id = $_POST['form_id'];
    $cdt = date('Y-m-d H:i:s');
    
    if ($fee_structure_id == '' or $class == ''  or $pupilsightSchoolYearID == '' or $pupilsightProgramID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        $feeSettingId = array();
        foreach($fee_structure_id as $fid){
            $fn_fee_structure_id = $fid;
            

                $clss = $k;
                $feestructure = $fid;
                $id = $feestructure;
                $datas = array('id' => $id);
                $sqls = 'SELECT a.*, b.format FROM fn_fee_structure AS a LEFT JOIN fn_fee_series AS b ON a.inv_fee_series_id = b.id WHERE a.id=:id';
                $results = $connection2->prepare($sqls);
                $results->execute($datas);
                $values = $results->fetch();
                
           
                $datac = array('fn_fee_structure_id' => $id);
                $sqlc = 'SELECT * FROM fn_fee_structure_item WHERE fn_fee_structure_id=:fn_fee_structure_id';
                $resultc = $connection2->prepare($sqlc);
                $resultc->execute($datac);
                $childvalues = $resultc->fetchAll();

                // $dataa = array('fn_fee_structure_id' => $id, 'pupilsightYearGroupID' => $clss);
                // $sqla = 'SELECT * FROM fn_fees_class_assign WHERE fn_fee_structure_id=:fn_fee_structure_id AND pupilsightYearGroupID=:pupilsightYearGroupID';
                // $resulta = $connection2->prepare($sqla);
                // $resulta->execute($dataa);
                // $assignvalues = $resulta->fetchAll();

                $data = array('title' => $values['invoice_title'], 'fn_fee_structure_id' => $id , 'pupilsightSchoolYearID' => $values['pupilsightSchoolYearID'], 'pupilsightSchoolFinanceYearID' => $values['pupilsightSchoolFinanceYearID'], 'inv_fn_fee_series_id' => $values['inv_fee_series_id'], 'rec_fn_fee_series_id' => $values['recp_fee_series_id'], 'fn_fees_head_id' => $values['fn_fees_head_id'], 'fn_fees_fine_rule_id' => $values['fn_fees_fine_rule_id'], 'fn_fees_discount_id' => $values['fn_fees_discount_id'], 'due_date' => $values['due_date'], 'cdt' => $cdt);
            
                $sql = 'INSERT INTO fn_fee_invoice SET title=:title, fn_fee_structure_id=:fn_fee_structure_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                $invId = $connection2->lastInsertID();

                if(!empty($childvalues)){
                    foreach($childvalues as $cv){
                        $feeitem = $cv['fn_fee_item_id'];
                        $desc = '';
                        $amt = $cv['amount'];
                        $taxdata = $cv['tax_percent'];
                        $disc = '';
                        $tamt = $cv['total_amount'];

                        if(!empty($feeitem) && !empty($amt)){
                            $data1 = array('fn_fee_invoice_id' => $invId, 'fn_fee_item_id' => $feeitem, 'description' => $desc, 'amount' => $amt, 'tax' => $taxdata, 'discount' => $disc, 'total_amount' => $tamt);
                            $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, description=:description, amount=:amount,  tax=:tax, discount=:discount, total_amount=:total_amount";
                            $result1 = $connection2->prepare($sql1);
                            $result1->execute($data1);
                        }
                    }
                }

                foreach($class as $key => $cls){
                    if($key == $fn_fee_structure_id){
                        $classes = implode(',',$cls);
                    }
                }    
                    $clsdata = explode(',', $classes);
                    foreach($clsdata as $clsid){
                        $classId = $clsid;
                        $dataav = array('fn_fee_invoice_id'=>$invId,'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $classId);
                        $sqlav = 'SELECT * FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
                        $resultav = $connection2->prepare($sqlav);
                        $resultav->execute($dataav);
                        if ($resultav->rowCount() == 0) {
                            $sql1av = 'INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID';
                            $result1av = $connection2->prepare($sql1av);
                            $result1av->execute($dataav);
                        }

                        $datast = array('form_id' => $form_id,'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $classId);
                        $sqlst = 'SELECT id FROM wp_fluentform_submissions WHERE 
                        form_id=:form_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
                        $resultst = $connection2->prepare($sqlst);
                        $resultst->execute($datast);
                        $students = $resultst->fetchAll();

                        if(!empty($students)){
                            foreach($students as $stuId){
                                $datastu = array('fn_fee_invoice_id'=>$invId,'submission_id' => $stuId['id']);
                                $sqlstu = 'SELECT * FROM fn_fee_invoice_applicant_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND submission_id=:submission_id';
                                $resultstu = $connection2->prepare($sqlstu);
                                $resultstu->execute($datastu);
                                
                                if ($resultstu->rowCount() == 0) {
                                    $invSeriesId = $values['inv_fee_series_id'];
                                    $invformat = explode('/',$values['format']);
                                    $iformat = '';
                                    $orderwise = 0;
                                    foreach($invformat as $inv){
                                        if($inv == '{AB}'){
                                            $datafort = array('fn_fee_series_id'=>$invSeriesId,'order_wise' => $orderwise, 'type' => 'numberwise');
                                            $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                                            $resultfort = $connection2->prepare($sqlfort);
                                            $resultfort->execute($datafort);
                                            $formatvalues = $resultfort->fetch();
                                            $iformat .= $formatvalues['last_no'].'/';
                                            
                                            $str_length = $formatvalues['no_of_digit'];

                                            $lastnoadd = $formatvalues['last_no'] + 1;

                                            $lastno = substr("0000000{$lastnoadd}", -$str_length); 

                                            $datafort1 = array('fn_fee_series_id'=>$invSeriesId,'order_wise' => $orderwise, 'type' => 'numberwise' , 'last_no' => $lastno);
                                            $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                                            $resultfort1 = $connection2->prepare($sqlfort1);
                                            $resultfort1->execute($datafort1);

                                        } else {
                                            $iformat .= $inv.'/';
                                        }
                                        $orderwise++;
                                    }
                                    
                                    $invoiceno =  rtrim($iformat, "/");
                                    $dataistu = array('fn_fee_invoice_id'=>$invId, 'invoice_no' => $invoiceno,'submission_id' => $stuId['id']);
                                    $sqlstu1 = 'INSERT INTO fn_fee_invoice_applicant_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,invoice_no=:invoice_no, submission_id=:submission_id';
                                    $resultstu1 = $connection2->prepare($sqlstu1);
                                    $resultstu1->execute($dataistu);
                                    
                                    
                                }
                            }
                        }
                    }   
                
                foreach($amounts as $k => $amt){
                    if($k == $fn_fee_structure_id){
                        $amount = $amt;
                    }
                }

               

            
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'fn_fee_structure_id' => $fn_fee_structure_id, 'classes' => $classes, 'amount' => $amount);
            $sql = "INSERT INTO fn_fee_admission_settings SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, fn_fee_structure_id=:fn_fee_structure_id,classes=:classes, amount=:amount";
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $SettingId = $connection2->lastInsertID();
            array_push($feeSettingId, $SettingId);
        }
        echo implode(',',$feeSettingId);
        die();
    }
}



