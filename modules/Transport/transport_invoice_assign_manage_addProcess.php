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
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transport_fee.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_invoice_assign_manage_add.php') == false) {
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
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
   // die();
    if ( $program == '' or $class == '' or $structure == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        if(!empty($class)){
            foreach($class['class'] as $k=> $cls){
                
                if(!empty($class['structure'][$k])){
                    $clss = $k;
                    $feestructure = $class['structure'][$k];
                    $id = $feestructure;
                    $datas = array('id' => $id);
                    $sqls = 'SELECT a.*, b.format FROM trans_schedule AS a LEFT JOIN fn_fee_series AS b ON a.invoice_series_id = b.id WHERE a.id=:id';
                    $results = $connection2->prepare($sqls);
                    $results->execute($datas);
                    $values = $results->fetch();
                    
               
                    $datac = array('route_id' => $values['route_id']);
                    $sqlc = 'SELECT * FROM trans_route_stops WHERE route_id=:route_id';
                    $resultc = $connection2->prepare($sqlc);
                    $resultc->execute($datac);
                    $childvalues = $resultc->fetchAll();

                    $dataa = array('schedule_id' => $id, 'pupilsightYearGroupID' => $clss);
                    $sqla = 'SELECT * FROM trans_schedule_assign_class WHERE schedule_id=:schedule_id AND pupilsightYearGroupID=:pupilsightYearGroupID';
                    $resulta = $connection2->prepare($sqla);
                    $resulta->execute($dataa);
                    $assignvalues = $resulta->fetchAll();

                    $data = array('title' => $values['schedule_name'], 'transport_schedule_id' => $id , 'pupilsightSchoolYearID' => $values['pupilsightSchoolYearID'], 'pupilsightSchoolFinanceYearID' => $values['pupilsightSchoolFinanceYearID'], 'inv_fn_fee_series_id' => $values['invoice_series_id'], 'rec_fn_fee_series_id' => $values['receipt_series_id'], 'fn_fees_head_id' => $values['fee_head_id'], 'due_date' => $values['due_date'], 'cdt' => $cdt);
                
                    $sql = 'INSERT INTO fn_fee_invoice SET title=:title, transport_schedule_id=:transport_schedule_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, due_date=:due_date, cdt=:cdt';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    
                    $invId = $connection2->lastInsertID();

                    if(!empty($childvalues)){
                        foreach($childvalues as $cv){
                            // $feeitem = $cv['fn_fee_item_id'];
                            // $desc = '';
                            // $amt = $cv['amount'];
                            // $taxdata = $cv['tax_percent'];
                            // $disc = '';
                            // $tamt = $cv['total_amount'];

                             //if(!empty($feeitem) && !empty($amt)){
                                $data1 = array('fn_fee_invoice_id' => $invId, 'fn_fee_item_id' => $values['fee_item_id'], 'transport_route_id' => $cv['route_id'], 'transport_stop_id' => $cv['id']);
                                $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, transport_route_id=:transport_route_id, transport_stop_id=:transport_stop_id";
                                $result1 = $connection2->prepare($sql1);
                                $result1->execute($data1);
                             //}
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
                                    $datastu = array('fn_fee_invoice_id'=>$invId,'pupilsightPersonID' => $stuId['pupilsightPersonID']);
                                    $sqlstu = 'SELECT * FROM fn_fee_invoice_student_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightPersonID=:pupilsightPersonID';
                                    $resultstu = $connection2->prepare($sqlstu);
                                    $resultstu->execute($datastu);
                                    
                                    if ($resultstu->rowCount() == 0) {
                                        $invSeriesId = $values['invoice_series_id'];
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
                                        $dataistu = array('fn_fee_invoice_id'=>$invId,'pupilsightPersonID' => $stuId['pupilsightPersonID'], 'invoice_no' => $invoiceno);
                                        $sqlstu1 = 'INSERT INTO fn_fee_invoice_student_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,pupilsightPersonID=:pupilsightPersonID, invoice_no=:invoice_no';
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
