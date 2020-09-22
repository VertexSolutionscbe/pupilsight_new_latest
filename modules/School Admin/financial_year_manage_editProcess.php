<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolFinanceYearID = $_GET['pupilsightSchoolFinanceYearID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/financial_year_manage_edit.php&pupilsightSchoolFinanceYearID='.$pupilsightSchoolFinanceYearID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/financial_year_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightSchoolFinanceYearID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID);
            $sql = 'SELECT * FROM pupilsightSchoolFinanceYear WHERE pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID';
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
            $firstDay = dateConvert($guid, $_POST['firstDay']);
            $lastDay = dateConvert($guid, $_POST['lastDay']);

            if ($name == '' or $status == '' or $sequenceNumber == '' or is_numeric($sequenceNumber) == false or $firstDay == '' or $lastDay == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'sequenceNumber' => $sequenceNumber,'firstDay' => $firstDay, 'lastDay' => $lastDay, 'pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID);
                    $sql = 'SELECT * FROM pupilsightSchoolFinanceYear WHERE (name=:name OR sequenceNumber=:sequenceNumber OR firstDay=:firstDay OR lastDay=:lastDay) AND NOT pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID';
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
                            $data = array('pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID, 'sequenceNumber' => $sequenceNumber);
                            $sql = "UPDATE pupilsightSchoolFinanceYear SET status = (CASE
                                WHEN sequenceNumber < :sequenceNumber THEN 'Past' ELSE 'Upcoming'
                            END) WHERE pupilsightSchoolFinanceYearID <> :pupilsightSchoolFinanceYearID";
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
                            $data = array('name' => $name, 'status' => $status, 'sequenceNumber' => $sequenceNumber, 'firstDay' => $firstDay, 'lastDay' => $lastDay, 'pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID);
                            $sql = "UPDATE pupilsightSchoolFinanceYear SET name=:name, status=:status, sequenceNumber=:sequenceNumber, firstDay=:firstDay, lastDay=:lastDay WHERE pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        // Update session vars so the user is warned if they're logged into a different year
                        if ($status == 'Current') {
                            $_SESSION[$guid]['pupilsightSchoolFinanceYearIDCurrent'] = $pupilsightSchoolFinanceYearID;
                            $_SESSION[$guid]['pupilsightSchoolFinanceYearNameCurrent'] = $name;
                            $_SESSION[$guid]['pupilsightSchoolFinanceYearSequenceNumberCurrent'] = $sequenceNumber;
                        }

                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
