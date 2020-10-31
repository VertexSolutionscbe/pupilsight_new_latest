<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
 
$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/edit.php&id='.$id;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM campaign WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            // echo '<pre>';
            // print_r($_POST);
            // echo '</pre>';
            // die();

            $name = $_POST['name'];
            $status = $_POST['status'];
            $seats = $_POST['seats'];
            $limitusers = $_POST['limit_apply_form'];
            $description = $_POST['description'];
            //$academic_year = $_POST['ayear']; 
            $academic_id = $_POST['academic_id'];
            if(!empty($_POST['pupilsightProgramID'])){
                $pupilsightProgramID = implode(',',$_POST['pupilsightProgramID']); 
            } else {
                $pupilsightProgramID = 0; 
            }
        
            if(!empty($_POST['classes'])){
                $cids = array();
                foreach($_POST['classes'] as $v){
                    $arr = explode("-", $v, 2);
                    $first = $arr[0];
                    $cids[] = $first;
                }
                $classes = implode(',',$cids); 
            } else {
                $classes = 0; 
            }
            $start_date = dateConvert($guid, $_POST['start_date']);
            $end_date = dateConvert($guid, $_POST['end_date']);
            $cuid = $_SESSION[$guid]['pupilsightPersonID'];
            $alloseatname = $_POST['seatname'];
            $alloseatno = $_POST['seatallocation'];
            $reg_req =  $_POST['reg_req']; //2-reg-yes(private) //1-reg-No(public)
            $application_series_id = $_POST['application_series_id'];
            $admission_series_id = $_POST['admission_series_id'];
            $fn_fee_structure_id = $_POST['fn_fee_structure_id'];
            $fn_fees_receipt_template_id = $_POST['fn_fees_receipt_template_id'];
            if(!empty($_POST['is_publish_parent'])){
                $is_publish_parent = $_POST['is_publish_parent'];
            } else {
                $is_publish_parent = '0';
            }
            $email_template_id = $_POST['email_template_id'];
            $sms_template_id = $_POST['sms_template_id'];
            $is_fee_generate = $_POST['is_fee_generate'];
            $progseatname = $_POST['progname'];

            if(!empty($_POST['allow_multiple_submission'])){
                $allow_multiple_submission = $_POST['allow_multiple_submission'];
            } else {
                $allow_multiple_submission = '0';
            }

            if ($name == '' or $status == '' or $start_date == '' or $end_date == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'academic_year' => $academic_year, 'id' => $id);
                    $sql = 'SELECT * FROM campaign WHERE (name=:name AND academic_year=:academic_year) AND NOT id=:id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {

                    $sqla = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID = '.$academic_id.' ';
                    $resulta = $connection2->query($sqla);
                    $ayrdata = $resulta->fetch();
                    $academic_year = $ayrdata['name'];
                    

                        //Write to database
                        try {
                            $data = array('name' => $name, 'status' => $status, 'description' => $description, 'start_date' => $start_date, 'end_date' => $end_date, 'academic_id' => $academic_id, 'academic_year' => $academic_year, 'pupilsightProgramID' => $pupilsightProgramID, 'classes' => $classes, 'seats' => $seats,'limit_apply_form' => $limitusers,'cuid' => $cuid,'id' => $id,'page_for'=> $reg_req, 'application_series_id' => $application_series_id, 'admission_series_id' => $admission_series_id, 'fn_fee_structure_id' => $fn_fee_structure_id, 'fn_fees_receipt_template_id' => $fn_fees_receipt_template_id, 'is_publish_parent' => $is_publish_parent, 'email_template_id' => $email_template_id, 'sms_template_id' => $sms_template_id, 'allow_multiple_submission' => $allow_multiple_submission, 'is_fee_generate' => $is_fee_generate);
                            
                            $sql = "UPDATE campaign SET name=:name, description=:description, academic_id=:academic_id, academic_year=:academic_year, pupilsightProgramID=:pupilsightProgramID,classes=:classes, start_date=:start_date, end_date=:end_date, status=:status,seats=:seats, limit_apply_form=:limit_apply_form, cuid=:cuid,page_for=:page_for, application_series_id=:application_series_id, admission_series_id=:admission_series_id, fn_fee_structure_id=:fn_fee_structure_id, fn_fees_receipt_template_id=:fn_fees_receipt_template_id, is_publish_parent=:is_publish_parent,email_template_id=:email_template_id,sms_template_id=:sms_template_id, allow_multiple_submission=:allow_multiple_submission, is_fee_generate=:is_fee_generate WHERE id=:id";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo $e;
                            die();
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        // Update session vars so the user is warned if they're logged into a different year
                        // if ($status == 'Current') {
                        //     $_SESSION[$guid]['idCurrent'] = $id;
                        //     $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'] = $name;
                        //     $_SESSION[$guid]['pupilsightSchoolYearSequenceNumberCurrent'] = $sequenceNumber;
                        // }

                        if(!empty($_POST['classes'])){
                            $campaignid = $id;
                            $data2 = array('campaign_id' => $id);
                            $sql2 = 'DELETE FROM campaign_prog_class WHERE campaign_id=:campaign_id';
                            $result2 = $connection2->prepare($sql2);
                            $result2->execute($data2);
                            foreach($_POST['classes'] as $v){
                                $arr = explode("-", $v, 2);
                                $cls = $arr[0];
                                $prg = $arr[1];
                                $data1 = array('campaign_id' => $campaignid, 'pupilsightProgramID' => $prg, 'pupilsightYearGroupID' => $cls);
                                $sql1 = "INSERT INTO campaign_prog_class SET campaign_id=:campaign_id, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID";
                                $result = $connection2->prepare($sql1);
                                $result->execute($data1);
                            }
                        }

                        if(!empty($progseatname)){
                            $campaignid = $id;
                            
                            foreach($progseatname as $k=> $d){
                                $pname = $d;
                                $sname = $alloseatname[$k];
                                $sno = $alloseatno[$k];
                                if(!empty($pname) && !empty($sname) && !empty($sno)){
                                    $chksql = 'SELECT id FROM seatmatrix WHERE id = '.$k.' AND campaignid = '.$campaignid.' AND pupilsightProgramID = '.$pname.' AND pupilsightYearGroupID = '.$sname.' ';
                                    $resultchk = $connection2->query($chksql);
                                    $rowdatachk = $resultchk->fetch();
                                    if(!empty($rowdatachk)){
                                        $data1 = array('campaignid' => $campaignid, 'pupilsightProgramID' => $pname, 'pupilsightYearGroupID' => $sname, 'seats' => $sno,'cuid' => $cuid, 'id' => $k);
                                        $sql1 = "UPDATE seatmatrix SET campaignid=:campaignid, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, seats=:seats, cuid=:cuid WHERE id=:id ";
                                        $result = $connection2->prepare($sql1);
                                        $result->execute($data1);
                                    } else {
                                        $data1 = array('campaignid' => $campaignid, 'pupilsightProgramID' => $pname, 'pupilsightYearGroupID' => $sname, 'seats' => $sno,'cuid' => $cuid);
                                        $sql1 = "INSERT INTO seatmatrix SET campaignid=:campaignid, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, seats=:seats, cuid=:cuid";
                                        $result = $connection2->prepare($sql1);
                                        $result->execute($data1);
                                    }
                                    
                                }
                            }
                        }    

                        
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                   
                }
            }
        }
    }
}
