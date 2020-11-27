<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/assign_staff_toClassSection.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_staff_toClassSection.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {

    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //Proceed!
    //Validate Inputs
    //$fn_fee_structure_id = $_POST['fn_fee_structure_id'];
    $pupilsightMappingID  = explode(',', $_POST['stu_id']);
    $pupilsightPersonID  =  $_POST['staff'];
    $cnt= count($pupilsightPersonID);
    
    if ($pupilsightMappingID == ''  or $cnt == 0) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
         foreach($pupilsightMappingID as $pmID){
            foreach($pupilsightPersonID as $pid){
                $sqlchk = 'SELECT id FROM assignstaff_toclasssection WHERE pupilsightPersonID= '.$pid.' AND pupilsightMappingID = '.$pmID.' ';
                $resultchk = $connection2->query($sqlchk);
                $chkData = $resultchk->fetch();

                if(empty($chkData['id'])){
                    $data = array('pupilsightMappingID' => $pmID, 'pupilsightPersonID' => $pid);
                    $sql = 'INSERT INTO assignstaff_toclasssection SET pupilsightMappingID=:pupilsightMappingID, pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);               
                    $result->execute($data);
                }
            }
        }
        $URL .= "&return=success0";
        header("Location: {$URL}");
            
    }
}
