<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/budgetCycles_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/budgetCycles_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
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
            $data = array('name' => $name, 'sequenceNumber' => $sequenceNumber);
            $sql = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE name=:name OR sequenceNumber=:sequenceNumber';
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
                $data = array('name' => $name, 'status' => $status, 'sequenceNumber' => $sequenceNumber, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "INSERT INTO pupilsightFinanceBudgetCycle SET name=:name, status=:status, sequenceNumber=:sequenceNumber, dateStart=:dateStart, dateEnd=:dateEnd, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestampCreator='".date('Y-m-d H:i:s')."'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo $e->getMessage();
                exit();
                $URL .= '&return=error2';
                header("Location: {$URL}");
            }

            $pupilsightFinanceBudgetCycleID = str_pad($connection2->lastInsertID(), 14, '0', STR_PAD_LEFT);

            //UPDATE CYCLE ALLOCATION VALUES
            $partialFail = false;
            if (isset($_POST['values'])) {
                $values = $_POST['values'];
                $pupilsightFinanceBudgetIDs = $_POST['pupilsightFinanceBudgetIDs'];
                $count = 0;
                foreach ($values as $value) {
                    try {
                        $data = array('value' => $value, 'pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetIDs[$count]);
                        $sql = 'INSERT INTO pupilsightFinanceBudgetCycleAllocation SET value=:value, pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID, pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    ++$count;
                }
            }

            if ($partialFail == true) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                $URL .= "&return=success0&editID=$pupilsightFinanceBudgetCycleID";
                header("Location: {$URL}");
            }
        }
    }
}
