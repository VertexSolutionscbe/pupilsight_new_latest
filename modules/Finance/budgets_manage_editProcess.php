<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$pupilsightFinanceBudgetID = $_GET['pupilsightFinanceBudgetID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/budgets_manage_edit.php&pupilsightFinanceBudgetID=$pupilsightFinanceBudgetID";

if (isActionAccessible($guid, $connection2, '/modules/Finance/budgets_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFinanceBudgetID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
            $sql = 'SELECT * FROM pupilsightFinanceBudget WHERE pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
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
            $category = $_POST['category'];

            if ($name == '' or $nameShort == '' or $active == '' or $category == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
                    $sql = 'SELECT * FROM pupilsightFinanceBudget WHERE (name=:name OR nameShort=:nameShort) AND NOT pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error7';
                    header("Location: {$URL}");
                } else {
                    //Scan through staff
                    $partialFail = false;
                    $staff = array();
                    if (isset($_POST['staff'])) {
                        $staff = $_POST['staff'];
                    }
                    $access = $_POST['access'];
                    if ($access != 'Full' and $access != 'Write' and $access != 'Read') {
                        $role = 'Read';
                    }
                    if (count($staff) > 0) {
                        foreach ($staff as $t) {
                            //Check to see if person is already registered in this budget
                            try {
                                $dataGuest = array('pupilsightPersonID' => $t, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
                                $sqlGuest = 'SELECT * FROM pupilsightFinanceBudgetPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                                $resultGuest = $connection2->prepare($sqlGuest);
                                $resultGuest->execute($dataGuest);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            if ($resultGuest->rowCount() == 0) {
                                try {
                                    $data = array('pupilsightPersonID' => $t, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID, 'access' => $access);
                                    $sql = 'INSERT INTO pupilsightFinanceBudgetPerson SET pupilsightPersonID=:pupilsightPersonID, pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID, access=:access';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                        }
                    }

                    //Write to database
                    try {
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'category' => $category, 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
                        $sql = "UPDATE pupilsightFinanceBudget SET name=:name, nameShort=:nameShort, active=:active, category=:category, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, timestampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($partialFail == true) {
                        $URL .= '&return=error4';
                        header("Location: {$URL}");
                    } else {
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
