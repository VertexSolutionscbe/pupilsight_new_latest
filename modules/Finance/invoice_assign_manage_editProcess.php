<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$fn_fee_invoice_id = $_GET['invoice_id'];
$pupilsightProgramID = $_GET['pupilsightProgramID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_assign_manage.php&id='.$fn_fee_invoice_id;

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_assign_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($fn_fee_invoice_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        
            //Validate Inputs
            $program = $_POST['pupilsightProgramID'];
            $class = $_POST['pupilsightYearGroupID'];
            

            if ($fn_fee_invoice_id == ''  or $program == '' or $class == '' ) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {

                $datad = array('fn_fee_invoice_id' => $fn_fee_invoice_id, 'pupilsightProgramID' => $pupilsightProgramID);
                $sqld = 'DELETE FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID';
                $resultd = $connection2->prepare($sqld);
                $resultd->execute($datad);
                    //Write to database
                    try {
                        foreach($class as $cl){
                            $data = array('fn_fee_invoice_id'=>$fn_fee_invoice_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $cl);
                            $sql = 'SELECT * FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                           if ($result->rowCount() == 0) {
                                $sql1 = 'INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID';
                                $result1 = $connection2->prepare($sql1);
                                $result1->execute($data);
                            }
                        }
                    } catch (PDOException $e) {
                        $URL .= '&return=error6';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
            }
    }
}
