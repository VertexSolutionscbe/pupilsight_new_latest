<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/add.php';
$session = $container->get('session');
$session->forget(['campaignid']);

if (isActionAccessible($guid, $connection2, '/modules/Campaign/add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
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
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    if(!empty($_POST['classes'])){
        $classes = implode(',',$_POST['classes']); 
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

    if ($name == '' or $status == ''  or $start_date == '' or $end_date == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'academic_id' => $academic_id);
            $sql = 'SELECT * FROM campaign WHERE name=:name AND academic_id=:academic_id';
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
            //Check for other currents
            
                //Write to databas
                $sqla = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID = '.$academic_id.' ';
                $resulta = $connection2->query($sqla);
                $ayrdata = $resulta->fetch();
                $academic_year = $ayrdata['name'];

                try {
                    $data = array('name' => $name, 'status' => $status, 'description' => $description, 'start_date' => $start_date, 'end_date' => $end_date, 'academic_id' => $academic_id, 'academic_year' => $academic_year, 'pupilsightProgramID' => $pupilsightProgramID, 'classes' => $classes, 'seats' => $seats,'limit_apply_form' => $limitusers,'cuid' => $cuid,'page_for'=> $reg_req, 'application_series_id' => $application_series_id, 'admission_series_id' => $admission_series_id, 'fn_fee_structure_id' => $fn_fee_structure_id, 'fn_fees_receipt_template_id' => $fn_fees_receipt_template_id, 'is_publish_parent' => $is_publish_parent, 'email_template_id' => $email_template_id, 'sms_template_id' => $sms_template_id);
                    $sql = "INSERT INTO campaign SET name=:name, description=:description, academic_id=:academic_id,academic_year=:academic_year, pupilsightProgramID=:pupilsightProgramID,classes=:classes, start_date=:start_date, end_date=:end_date, status=:status,seats=:seats, limit_apply_form=:limit_apply_form, cuid=:cuid,page_for=:page_for, application_series_id=:application_series_id, admission_series_id=:admission_series_id, fn_fee_structure_id=:fn_fee_structure_id, fn_fees_receipt_template_id=:fn_fees_receipt_template_id, is_publish_parent=:is_publish_parent,email_template_id=:email_template_id,sms_template_id=:sms_template_id";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);
                $lid = $connection2->lastInsertID();

                // Update session vars so the user is warned if they're logged into a different year
                // if ($status == 'Current') {
                //     $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'] = $AI;
                //     $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'] = $name;
                //     $_SESSION[$guid]['pupilsightSchoolYearSequenceNumberCurrent'] = $sequenceNumber;
                // }
                if(!empty($alloseatname)){
                    foreach($alloseatname as $k=> $d){
                        $sname = $d;
                        $sno = $alloseatno[$k];
                        if(!empty($sname) && !empty($sno)){
                            $data1 = array('campaignid' => $AI, 'name' => $sname, 'seats' => $sno,'cuid' => $cuid);
                            $sql1 = "INSERT INTO seatmatrix SET campaignid=:campaignid, name=:name, seats=:seats, cuid=:cuid";
                            $result = $connection2->prepare($sql1);
                            $result->execute($data1);
                        }
                    }
                }    
                // $URL .= "&return=success0&editID=$AI";
                $session->set('campaignid', $lid);
                $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/wplogin.php';
                header("Location: {$URL}");
           
        }
    }
}



