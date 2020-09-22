<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFinanceBudgetCycleID = $_GET['pupilsightFinanceBudgetCycleID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/budgetCycles_manage_edit.php&pupilsightFinanceBudgetCycleID='.$pupilsightFinanceBudgetCycleID;

if (isActionAccessible($guid, $connection2, '/modules/Finance/budgetCycles_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFinanceBudgetCycleID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
            $sql = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID';
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
            //Validate Inputs
            $name = $_POST['name'];
            $status = $_POST['status'];
            $sequenceNumber = $_POST['sequenceNumber'];
            $dateStart = dateConvert($guid, $_POST['dateStart']);
            $dateEnd = dateConvert($guid, $_POST['dateEnd']);

            if ($name == '' or $status == '' or $sequenceNumber == '' or is_numeric($sequenceNumber) == false or $dateStart == '' or $dateEnd == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'sequenceNumber' => $sequenceNumber, 'pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
                    $sql = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE (name=:name OR sequenceNumber=:sequenceNumber) AND NOT pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID';
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
                    //Write to database
                    try {
                        $data = array('name' => $name, 'status' => $status, 'sequenceNumber' => $sequenceNumber, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
                        $sql = 'UPDATE pupilsightFinanceBudgetCycle SET name=:name, status=:status, sequenceNumber=:sequenceNumber, dateStart=:dateStart, dateEnd=:dateEnd WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //UPDATE CYCLE ALLOCATION VALUES
                    $partialFail = false;
                    if (isset($_POST['values'])) {
                        $values = $_POST['values'];
                        $pupilsightFinanceBudgetIDs = $_POST['pupilsightFinanceBudgetIDs'];
                        $count = 0;
                        foreach ($values as $value) {
                            $failThis = false;

                            try {
                                $dataCheck = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetIDs[$count]);
                                $sqlCheck = 'SELECT * FROM pupilsightFinanceBudgetCycleAllocation WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                                $resultCheck = $connection2->prepare($sqlCheck);
                                $resultCheck->execute($dataCheck);
                            } catch (PDOException $e) {
                                $partialFail = true;
                                $failThis = true;
                            }

                            if ($failThis == false) {
                                try {
                                    $data = array('value' => $value, 'pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetIDs[$count]);
                                    if ($resultCheck->rowCount() == 0) { //INSERT
                                        $sql = 'INSERT INTO pupilsightFinanceBudgetCycleAllocation SET value=:value, pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID, pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                                    } else { //UPDATE
                                        $sql = 'UPDATE pupilsightFinanceBudgetCycleAllocation SET value=:value WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                                    }
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                            ++$count;
                        }
                    }

                    if ($partialFail == true) {
                        $URL .= '&return=warning1';
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
