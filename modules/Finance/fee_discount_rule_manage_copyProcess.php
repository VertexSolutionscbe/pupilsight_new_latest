<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_discount_rule_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_discount_rule_manage_copy.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $id = $_POST['id'];
    $name = $_POST['name'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    
    $cdt = date('Y-m-d H:i:s');
    
    if ($name == ''  or $pupilsightSchoolYearID == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM fn_fees_discount WHERE name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
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
            //Write to database
            try {
                $datas = array('id' => $id);
                $sqls = 'SELECT * FROM fn_fees_discount WHERE id=:id';
                $results = $connection2->prepare($sqls);
                $results->execute($datas);
                $values = $results->fetch();

                $datac = array('fn_fees_discount_id' => $id);
                $sqlc = 'SELECT * FROM fn_fee_discount_item WHERE fn_fees_discount_id=:fn_fees_discount_id';
                $resultc = $connection2->prepare($sqlc);
                $resultc->execute($datac);
                $childvalues = $resultc->fetchAll();

                $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'description' => $values['description'], 'fees_discount_type' => $values['fees_discount_type'], 'cdt' => $cdt);
                
                $sql = 'INSERT INTO fn_fees_discount SET name=:name, pupilsightSchoolYearID=:pupilsightSchoolYearID, description=:description, fees_discount_type=:fees_discount_type, cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                $disId = $connection2->lastInsertID();

                if(!empty($childvalues)){
                    foreach($childvalues as $cv){
                        $name = $cv['name'];
                        $fees_discount_type = $cv['fees_discount_type'];
                        $feeitemid = $cv['fn_fee_item_id'];
                        $itemtype = $cv['item_type'];
                        $mininv = $cv['min_invoice'];
                        $maxinv = $cv['max_invoice'];
                        $percent = $cv['amount_in_percent'];
                        $amount = $cv['amount_in_number'];

                        $data1 = array('fn_fees_discount_id' => $disId, 'fees_discount_type' => $fees_discount_type, 'fn_fee_item_id' => $feeitemid, 'item_type' => $itemtype, 'name' => $name, 'min_invoice' => $mininv, 'max_invoice' => $maxinv,'amount_in_percent' => $percent, 'amount_in_number' => $amount);
                        $sql1 = "INSERT INTO fn_fee_discount_item SET fn_fees_discount_id=:fn_fees_discount_id, fees_discount_type=:fees_discount_type, fn_fee_item_id=:fn_fee_item_id,  item_type=:item_type, name=:name, min_invoice=:min_invoice, max_invoice=:max_invoice,amount_in_percent=:amount_in_percent, amount_in_number=:amount_in_number";
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);
                    }
                }

                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';
            } catch (PDOException $e) {
                $URL .= '&return=error9';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0";
            header("Location: {$URL}");
        }
    }
}
