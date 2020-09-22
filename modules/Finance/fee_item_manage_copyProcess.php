<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/fee_item_manage.php";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/fee_item_manage_copyProcess.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $feeid = $_POST['tid'];
            // //Proceed!
            // echo '<pre>';
            // print_r($_POST);
            // echo '</pre>';
            // die();
          
            if ($feeid == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                   
                     $copytestid = explode(',',$_POST['tid']);
                    // echo '<pre>';
                    // print_r($copytestid);
                    // echo '</pre>';
                    // die();
                    $new_date=$_POST['pupilsightSchoolYearID'];
                    foreach($copytestid as $ctestId){
                        $data = array('id' => $ctestId);
                        $sql = 'SELECT * FROM fn_fee_items WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    
                        $values = $result->fetch();
                        $name = $values['name'];
                        $code = $values['code'];
                        $pupilsightSchoolYearID =$new_date;
                        $fn_fee_item_type_id = $values['fn_fee_item_type_id'];
                       
                        $dataInsert = array('name' => $name,'code' => $code,'pupilsightSchoolYearID' => $pupilsightSchoolYearID,'fn_fee_item_type_id' => $fn_fee_item_type_id);
                        if($values['pupilsightSchoolYearID']!=$new_date){
                            $sqlInsert = 'INSERT INTO fn_fee_items SET  name=:name, code=:code, pupilsightSchoolYearID=:pupilsightSchoolYearID,fn_fee_item_type_id=:fn_fee_item_type_id';
                        $resultInsert = $connection2->prepare($sqlInsert);
                        $resultInsert->execute($dataInsert);
                        } 
                        
                    }
                    
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                   
                }
}
