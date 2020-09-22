<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$search = $_GET['search'];

if ($pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/fees_manage_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/fees_manage_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $name = $_POST['name'];
        $nameShort = $_POST['nameShort'];
        $active = $_POST['active'];
        $description = $_POST['description'];
        $pupilsightFinanceFeeCategoryID = $_POST['pupilsightFinanceFeeCategoryID'];
        $fee = $_POST['fee'];

        if ($name == '' or $nameShort == '' or $active == '' or $pupilsightFinanceFeeCategoryID == '' or $fee == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {

            //Write to database
            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'description' => $description, 'pupilsightFinanceFeeCategoryID' => $pupilsightFinanceFeeCategoryID, 'fee' => $fee, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "INSERT INTO pupilsightFinanceFee SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, active=:active, description=:description, pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID, fee=:fee, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestampCreator='".date('Y-m-d H:i:s')."'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 6, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
