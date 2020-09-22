<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// die();

//$fn_fee_invoice_id = $_POST['fn_fee_invoice_id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_assign_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    //$fn_fee_invoice_id = $_POST['fn_fee_invoice_id'];
    $program = $_POST['pupilsightProgramID'];
    $class = $_POST['pupilsightYearGroupID'];
    $cdt = date('Y-m-d H:i:s');
    $structure = $_POST['pupilsightYearGroupID']['structure'];
//     echo '<pre>';
//     print_r($_POST);
//     echo '</pre>';
//    die();
    if ( $program == '' or $class == '' or $structure == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        if(!empty($class)){
            foreach($class['class'] as $k=> $cls){
                
                if(!empty($class['structure'][$k])){
                    $clss = $k;
                    $feestructure = explode(',',$class['structure'][$k]);
                    foreach($feestructure as $fs){

                        $id = $fs;
                        $datas = array('id' => $id);
                        $sqls = 'SELECT a.*, b.formatval FROM fn_fee_structure AS a LEFT JOIN fn_fee_series AS b ON a.inv_fee_series_id = b.id WHERE a.id=:id';
                        $results = $connection2->prepare($sqls);
                        $results->execute($datas);
                        $values = $results->fetch();
                        
                
                        $datac = array('fn_fee_structure_id' => $id);
                        $sqlc = 'SELECT * FROM fn_fee_structure_item WHERE fn_fee_structure_id=:fn_fee_structure_id';
                        $resultc = $connection2->prepare($sqlc);
                        $resultc->execute($datac);
                        $childvalues = $resultc->fetchAll();

                        $dataa = array('fn_fee_structure_id' => $id, 'pupilsightYearGroupID' => $clss);
                        $sqla = 'SELECT * FROM fn_fees_class_assign WHERE fn_fee_structure_id=:fn_fee_structure_id AND pupilsightYearGroupID=:pupilsightYearGroupID';
                        $resulta = $connection2->prepare($sqla);
                        $resulta->execute($dataa);
                        $assignvalues = $resulta->fetchAll();

                        $data = array('title' => $values['invoice_title'], 'fn_fee_structure_id' => $id , 'pupilsightSchoolYearID' => $values['pupilsightSchoolYearID'], 'pupilsightSchoolFinanceYearID' => $values['pupilsightSchoolFinanceYearID'], 'inv_fn_fee_series_id' => $values['inv_fee_series_id'], 'rec_fn_fee_series_id' => $values['recp_fee_series_id'], 'fn_fees_head_id' => $values['fn_fees_head_id'], 'fn_fees_fine_rule_id' => $values['fn_fees_fine_rule_id'], 'fn_fees_discount_id' => $values['fn_fees_discount_id'], 'due_date' => $values['due_date'],'amount_editable' => $values['amount_editable'],'display_fee_item' => $values['display_fee_item'], 'cdt' => $cdt);
                    
                        $sql = 'INSERT INTO fn_fee_invoice SET title=:title, fn_fee_structure_id=:fn_fee_structure_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, amount_editable=:amount_editable, display_fee_item=:display_fee_item, cdt=:cdt';
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

                        if(!empty($assignvalues)){
                            foreach($assignvalues as $asv){
                                $classId = $asv['pupilsightYearGroupID'];
                                $dataav = array('fn_fee_invoice_id'=>$invId,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $asv['pupilsightYearGroupID']);
                                $sqlav = 'SELECT * FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
                                $resultav = $connection2->prepare($sqlav);
                                $resultav->execute($dataav);
                                if ($resultav->rowCount() == 0) {
                                    $sql1av = 'INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID';
                                    $result1av = $connection2->prepare($sql1av);
                                    $result1av->execute($dataav);
                                }
                                
                                $datast = array('pupilsightSchoolYearID' => $values['pupilsightSchoolYearID'], 'pupilsightYearGroupID' => $classId, 'pupilsightProgramID' => $program);
                                $sqlst = 'SELECT pupilsightPersonID FROM pupilsightStudentEnrolment WHERE 
                                pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightProgramID=:pupilsightProgramID';
                                $resultst = $connection2->prepare($sqlst);
                                $resultst->execute($datast);
                                $students = $resultst->fetchAll();

                                if(!empty($students)){
                                    foreach($students as $stuId){
                                        $datastu = array('fn_fee_structure_id'=>$id,'pupilsightPersonID' => $stuId['pupilsightPersonID']);
                                        $sqlstu = 'SELECT * FROM fn_fee_invoice_student_assign WHERE fn_fee_structure_id=:fn_fee_structure_id AND pupilsightPersonID=:pupilsightPersonID';
                                        $resultstu = $connection2->prepare($sqlstu);
                                        $resultstu->execute($datastu);
                                        
                                        if ($resultstu->rowCount() == 0) {
                                            $invSeriesId = $values['inv_fee_series_id'];
                                            //$invformat = explode('/',$values['formatval']);
                                            $invformat = explode('$',$values['formatval']);
                                            $iformat = '';
                                            $orderwise = 0;
                                            foreach($invformat as $inv){
                                                if($inv == '{AB}'){
                                                    $datafort = array('fn_fee_series_id'=>$invSeriesId,'order_wise' => $orderwise, 'type' => 'numberwise');
                                                    $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                                                    $resultfort = $connection2->prepare($sqlfort);
                                                    $resultfort->execute($datafort);
                                                    $formatvalues = $resultfort->fetch();
                                                    //$iformat .= $formatvalues['last_no'].'/';
                                                    $iformat .= $formatvalues['last_no'];
                                                    
                                                    $str_length = $formatvalues['no_of_digit'];

                                                    $lastnoadd = $formatvalues['last_no'] + 1;

                                                    $lastno = substr("0000000{$lastnoadd}", -$str_length); 

                                                    $datafort1 = array('fn_fee_series_id'=>$invSeriesId,'order_wise' => $orderwise, 'type' => 'numberwise' , 'last_no' => $lastno);
                                                    $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                                                    $resultfort1 = $connection2->prepare($sqlfort1);
                                                    $resultfort1->execute($datafort1);

                                                } else {
                                                    //$iformat .= $inv.'/';
                                                    $iformat .= $inv;
                                                }
                                                $orderwise++;
                                            }
                                            
                                            //$invoiceno =  rtrim($iformat, "/");
                                            $invoiceno =  $iformat;

                                            $datastuchk = array('invoice_no'=>$invoiceno,'pupilsightPersonID' => $stuId['pupilsightPersonID']);
                                            $sqlstuchk = 'SELECT * FROM fn_fee_invoice_student_assign WHERE invoice_no=:invoice_no AND pupilsightPersonID=:pupilsightPersonID';
                                            $resultstuchk = $connection2->prepare($sqlstuchk);
                                            $resultstuchk->execute($datastuchk);
                                            
                                            if ($resultstuchk->rowCount() == 0) {

                                                $dataistu = array('fn_fee_invoice_id'=>$invId,'fn_fee_structure_id' => $id, 'pupilsightPersonID' => $stuId['pupilsightPersonID'], 'invoice_no' => $invoiceno);
                                                $sqlstu1 = 'INSERT INTO fn_fee_invoice_student_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,fn_fee_structure_id=:fn_fee_structure_id,pupilsightPersonID=:pupilsightPersonID, invoice_no=:invoice_no';
                                                $resultstu1 = $connection2->prepare($sqlstu1);
                                                $resultstu1->execute($dataistu);
                                            }
                                            
                                        }
                                    }
                                }

                            }
                        }

                    }
                
                }
            }
        }
            $URL .= "&return=success0";
            header("Location: {$URL}");
    
    }
}
