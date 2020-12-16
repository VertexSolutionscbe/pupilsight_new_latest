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

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_assign_student_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    //$fn_fee_invoice_id = $_POST['fn_fee_invoice_id'];
    $program = $_POST['pupilsightProgramID'];
    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID1'];
    $pupilsightRollGroupID = 0;
    $student = $_POST['pupilsightPersonID'];
    $cdt = date('Y-m-d H:i:s');
    $structure = $_POST['pupilsightPersonID']['structure'];
//     echo '<pre>';
//     print_r($_POST);
//     echo '</pre>';
//    die();
    if ( $program == '' or $student == '' or $structure == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        if(!empty($student)){
            foreach($student['student'] as $k=> $cls){
                
                if(!empty($student['structure'][$k])){
                    $stu = $k;

                    $feestructure = explode(',',$student['structure'][$k]);

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

                        $dataa = array('fn_fee_structure_id' => $id, 'pupilsightPersonID' => $stu);
                        $sqla = 'SELECT * FROM fn_fees_student_assign WHERE fn_fee_structure_id=:fn_fee_structure_id AND pupilsightPersonID=:pupilsightPersonID';
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

                        $dataca = array('fn_fee_invoice_id' => $invId, 'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                        $sqlca = "INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID";
                        $resultca = $connection2->prepare($sqlca);
                        $resultca->execute($dataca);

                        if(!empty($assignvalues)){
                            foreach($assignvalues as $asv){
                                // $dataav = array('fn_fee_structure_id'=>$id, 'pupilsightPersonID' => $asv['pupilsightPersonID'] , 'invoice_status' => $asv['pupilsightPersonID']);
                                // $sqlav = 'SELECT * FROM fn_fee_invoice_student_assign WHERE fn_fee_structure_id=:fn_fee_structure_id AND pupilsightPersonID=:pupilsightPersonID';
                                // $resultav = $connection2->prepare($sqlav);
                                // $resultav->execute($dataav);

                                $sqlp = 'SELECT COUNT(id) AS kount FROM fn_fee_invoice_student_assign WHERE fn_fee_structure_id =  '.$id.' AND pupilsightPersonID = '.$asv['pupilsightPersonID'].' AND invoice_status != "Canceled"';
                                $resultp = $connection2->query($sqlp);
                                $chkData = $resultp->fetchAll();

                                if ($chkData['kount'] == 0) {
                                    $invSeriesId = $values['inv_fee_series_id'];
                                    //$invformat = explode('/',$values['format']);
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
                                    $dataistu = array('fn_fee_invoice_id'=>$invId,'fn_fee_structure_id' => $id,'pupilsightPersonID' => $asv['pupilsightPersonID'], 'invoice_no' => $invoiceno);
                                    $sql1av = 'INSERT INTO fn_fee_invoice_student_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,fn_fee_structure_id=:fn_fee_structure_id,pupilsightPersonID=:pupilsightPersonID,invoice_no=:invoice_no';
                                    $result1av = $connection2->prepare($sql1av);
                                    $result1av->execute($dataistu);
                                }
                            }
                        }
                    }

                }
                
            }
        }

        //Check unique inputs for uniquness
        

            //Write to database
            // try {
            //     foreach($class as $cl){
            //         $data = array('fn_fee_invoice_id'=>$fn_fee_invoice_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $cl);
            //         $sql = 'SELECT * FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
            //         $result = $connection2->prepare($sql);
            //         $result->execute($data);
            //        if ($result->rowCount() == 0) {
            //             $sql1 = 'INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID';
            //             $result1 = $connection2->prepare($sql1);
            //             $result1->execute($data);
            //         }
            //     }
            // } catch (PDOException $e) {
            //     $URL .= '&return=error2';
            //     header("Location: {$URL}");
            //     exit();
            // }

            //Last insert ID
            //$AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0";
            header("Location: {$URL}");
    
    }
}
