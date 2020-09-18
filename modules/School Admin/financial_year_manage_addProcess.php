<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/financial_year_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/financial_year_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['name'];
    $status = $_POST['status'];
    $sequenceNumber = $_POST['sequenceNumber'];
    $firstDay = dateConvert($guid, $_POST['firstDay']);
    $lastDay = dateConvert($guid, $_POST['lastDay']);

    if ($name == '' or $status == '' or $sequenceNumber == '' or is_numeric($sequenceNumber) == false or $firstDay == '' or $lastDay == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'sequenceNumber' => $sequenceNumber,'firstDay' => $firstDay, 'lastDay' => $lastDay);
            $sql = 'SELECT * FROM pupilsightSchoolFinanceYear WHERE name=:name OR sequenceNumber=:sequenceNumber OR firstDay=:firstDay OR lastDay=:lastDay';
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
            //Check for other currents
            $currentFail = false;
            if ($status == 'Current') {
                // Enforces a single current school year by updating the status of other years
                try {
                    $data = array('sequenceNumber' => $sequenceNumber);
                    $sql = "UPDATE pupilsightSchoolFinanceYear SET status = (CASE
                        WHEN sequenceNumber < :sequenceNumber THEN 'Past' ELSE 'Upcoming'
                    END)";
                    $resultUpdate = $connection2->prepare($sql);
                    $resultUpdate->execute($data);
                } catch (PDOException $e) {
                    $currentFail = true;
                }
            }

            if ($currentFail) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            } else {
                //Write to database
                try {
                    $data = array('name' => $name, 'status' => $status, 'sequenceNumber' => $sequenceNumber, 'firstDay' => $firstDay, 'lastDay' => $lastDay);
                    $sql = "INSERT INTO pupilsightSchoolFinanceYear SET name=:name, status=:status, sequenceNumber=:sequenceNumber, firstDay=:firstDay, lastDay=:lastDay";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

                // Update session vars so the user is warned if they're logged into a different year
                if ($status == 'Current') {
                    $_SESSION[$guid]['pupilsightSchoolFinanceYearIDCurrent'] = $AI;
                    $_SESSION[$guid]['pupilsightSchoolFinanceYearNameCurrent'] = $name;
                    $_SESSION[$guid]['pupilsightSchoolFinanceYearSequenceNumberCurrent'] = $sequenceNumber;
                }

                $URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
            }
        }
    }
}
