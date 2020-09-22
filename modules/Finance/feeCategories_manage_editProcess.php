<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$pupilsightFinanceFeeCategoryID = $_GET['pupilsightFinanceFeeCategoryID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/feeCategories_manage_edit.php&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID";

if (isActionAccessible($guid, $connection2, '/modules/Finance/feeCategories_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFinanceFeeCategoryID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFinanceFeeCategoryID' => $pupilsightFinanceFeeCategoryID);
            $sql = 'SELECT * FROM pupilsightFinanceFeeCategory WHERE pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID';
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
            //Proceed!
            $name = $_POST['name'];
            $nameShort = $_POST['nameShort'];
            $active = $_POST['active'];
            $description = $_POST['description'];
            if ($name == '' or $nameShort == '' or $active == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'description' => $description, 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightFinanceFeeCategoryID' => $pupilsightFinanceFeeCategoryID);
                    $sql = "UPDATE pupilsightFinanceFeeCategory SET name=:name, nameShort=:nameShort, active=:active, description=:description, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, timestampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
