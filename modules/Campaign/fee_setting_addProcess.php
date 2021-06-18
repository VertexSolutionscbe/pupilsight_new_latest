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
    $no_of_invoices = $_POST['no_of_invoices'];
    $state_id = $_POST['state_id'];
    $cdt = date('Y-m-d H:i:s');
    
    if ($fee_structure_id == '' or $class == ''  or $pupilsightSchoolYearID == '' or $pupilsightProgramID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        $feeSettingId = array();
        foreach($fee_structure_id as $fid){
            $fn_fee_structure_id = $fid;
                foreach($class as $key => $cls){
                    if($key == $fn_fee_structure_id){
                         $classes = implode(',',$cls);
                        // $program_class = implode(',',$cls);
                        // $clsAll = explode('-',$program_class);
                        // echo $program = $clsAll[0].'</br>';
                        // echo $classes = $clsAll[1].'</br>';
                    }
                }    
                //die();
                foreach($amounts as $k => $amt){
                    if($k == $fn_fee_structure_id){
                        $amount = $amt;
                    }
                }

            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'fn_fee_structure_id' => $fn_fee_structure_id, 'classes' => $classes,  'amount' => $amount, 'no_of_invoices' => $no_of_invoices, 'state_id' => $state_id);
            $sql = "INSERT INTO fn_fee_admission_settings SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, fn_fee_structure_id=:fn_fee_structure_id,classes=:classes, amount=:amount,no_of_invoices=:no_of_invoices, state_id=:state_id";
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $SettingId = $connection2->lastInsertID();
            array_push($feeSettingId, $SettingId);
        }
        echo implode(',',$feeSettingId);
        die();
    }
}



