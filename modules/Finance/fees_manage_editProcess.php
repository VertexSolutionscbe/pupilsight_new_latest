<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightFinanceFeeID = $_POST['pupilsightFinanceFeeID'];
$search = $_GET['search'];

if ($pupilsightFinanceFeeID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/fees_manage_edit.php&pupilsightFinanceFeeID=$pupilsightFinanceFeeID&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/fees_manage_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightFinanceFeeID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceFeeID' => $pupilsightFinanceFeeID);
                $sql = 'SELECT * FROM pupilsightFinanceFee WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceFeeID=:pupilsightFinanceFeeID';
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
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'description' => $description, 'pupilsightFinanceFeeCategoryID' => $pupilsightFinanceFeeCategoryID, 'fee' => $fee, 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightFinanceFeeID' => $pupilsightFinanceFeeID);
                        $sql = "UPDATE pupilsightFinanceFee SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, active=:active, description=:description, pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID, fee=:fee, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, timestampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceFeeID=:pupilsightFinanceFeeID";
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
}
