<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearIDCopyTo = null ;
if (isset($_POST['pupilsightSchoolYearIDCopyTo']))
    $pupilsightSchoolYearIDCopyTo = $_POST['pupilsightSchoolYearIDCopyTo'];
$action = $_POST['action'];
$search = $_POST['search'];

if (($pupilsightSchoolYearIDCopyTo == '' and $action != 'Delete') or $action == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_manage.php&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $activities = isset($_POST['pupilsightActivityID'])? $_POST['pupilsightActivityID'] : array();

        //Proceed!
        //Check if person specified
        if (count($activities) < 1) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            $partialFail = false;
            if ($action == 'Duplicate' or $action == 'DuplicateParticipants') {
                foreach ($activities AS $pupilsightActivityID) { //For every activity to be copied
                    //Check existence of activity and fetch details
                    try {
                        $data = array('pupilsightActivityID' => $pupilsightActivityID);
                        $sql = 'SELECT * FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }

                    if ($result->rowCount() != 1) {
                        $partialFail = true;
                    } else {
                        $row = $result->fetch();
                        $name = $row['name'];
                        if ($pupilsightSchoolYearIDCopyTo == $_SESSION[$guid]['pupilsightSchoolYearID']) {
                            $name .= ' (Copy)';
                        }

                        //Write the duplicate to the database
                        try {
                            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearIDCopyTo, 'active' => $row['active'], 'registration' => $row['registration'], 'name' => $name, 'provider' => $row['provider'], 'type' => $row['type'], 'pupilsightSchoolYearTermIDList' => $row['pupilsightSchoolYearTermIDList'], 'listingStart' => $row['listingStart'], 'listingEnd' => $row['listingEnd'], 'programStart' => $row['programStart'], 'programEnd' => $row['programEnd'], 'pupilsightYearGroupIDList' => $row['pupilsightYearGroupIDList'], 'maxParticipants' => $row['maxParticipants'], 'description' => $row['description'], 'payment' => $row['payment'], 'paymentType' => $row['paymentType'], 'paymentFirmness' => $row['paymentFirmness']);
                            $sql = 'INSERT INTO pupilsightActivity SET pupilsightSchoolYearID=:pupilsightSchoolYearID, active=:active, registration=:registration, name=:name, provider=:provider, type=:type, pupilsightSchoolYearTermIDList=:pupilsightSchoolYearTermIDList, listingStart=:listingStart, listingEnd=:listingEnd, programStart=:programStart, programEnd=:programEnd, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, maxParticipants=:maxParticipants, description=:description, payment=:payment, paymentType=:paymentType, paymentFirmness=:paymentFirmness';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        //Last insert ID
                        $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

                        //Check and create staff
                        try {
                            $dataParticipants = array('pupilsightActivityID' => $pupilsightActivityID);
                            $sqlParticipants = 'SELECT * FROM pupilsightActivityStaff WHERE pupilsightActivityID=:pupilsightActivityID';
                            $resultParticipants = $connection2->prepare($sqlParticipants);
                            $resultParticipants->execute($dataParticipants);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowParticipants = $resultParticipants->fetch()) {
                            try {
                                $dataParticipants2 = array('pupilsightActivityID' => $AI, 'pupilsightPersonID' => $rowParticipants['pupilsightPersonID'], 'role' => $rowParticipants['role']);
                                $sqlParticipants2 = 'INSERT INTO pupilsightActivityStaff SET pupilsightActivityID=:pupilsightActivityID, pupilsightPersonID=:pupilsightPersonID, role=:role';
                                $resultParticipants2 = $connection2->prepare($sqlParticipants2);
                                $resultParticipants2->execute($dataParticipants2);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }

                        //Check and create slots
                        try {
                            $dataParticipants = array('pupilsightActivityID' => $pupilsightActivityID);
                            $sqlParticipants = 'SELECT * FROM pupilsightActivitySlot WHERE pupilsightActivityID=:pupilsightActivityID';
                            $resultParticipants = $connection2->prepare($sqlParticipants);
                            $resultParticipants->execute($dataParticipants);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowParticipants = $resultParticipants->fetch()) {
                            try {
                                $dataParticipants2 = array('pupilsightActivityID' => $AI, 'pupilsightSpaceID' => $rowParticipants['pupilsightSpaceID'], 'locationExternal' => $rowParticipants['locationExternal'], 'pupilsightDaysOfWeekID' => $rowParticipants['pupilsightDaysOfWeekID'], 'timeStart' => $rowParticipants['timeStart'], 'timeEnd' => $rowParticipants['timeEnd']);
                                $sqlParticipants2 = 'INSERT INTO pupilsightActivitySlot SET pupilsightActivityID=:pupilsightActivityID, pupilsightSpaceID=:pupilsightSpaceID, locationExternal=:locationExternal, pupilsightDaysOfWeekID=:pupilsightDaysOfWeekID, timeStart=:timeStart, timeEnd=:timeEnd';
                                $resultParticipants2 = $connection2->prepare($sqlParticipants2);
                                $resultParticipants2->execute($dataParticipants2);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }

                        //Deal with participants
                        if ($action == 'DuplicateParticipants') {
                            //Check and create staff
                            try {
                                $dataParticipants = array('pupilsightActivityID' => $pupilsightActivityID);
                                $sqlParticipants = 'SELECT * FROM pupilsightActivityStudent WHERE pupilsightActivityID=:pupilsightActivityID';
                                $resultParticipants = $connection2->prepare($sqlParticipants);
                                $resultParticipants->execute($dataParticipants);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            while ($rowParticipants = $resultParticipants->fetch()) {
                                try {
                                    $dataParticipants2 = array('pupilsightActivityID' => $AI, 'pupilsightPersonID' => $rowParticipants['pupilsightPersonID'], 'status' => $rowParticipants['status'], 'timestamp' => $rowParticipants['timestamp']);
                                    $sqlParticipants2 = "INSERT INTO pupilsightActivityStudent SET pupilsightActivityID=:pupilsightActivityID, pupilsightPersonID=:pupilsightPersonID, status=:status, timestamp=:timestamp, pupilsightActivityIDBackup=NULL, invoiceGenerated='N', pupilsightFinanceInvoiceID=NULL";
                                    $resultParticipants2 = $connection2->prepare($sqlParticipants2);
                                    $resultParticipants2->execute($dataParticipants2);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                        }
                    }
                }
            }
            if ($action == 'Delete') {
                foreach ($activities AS $pupilsightActivityID) { //For every activity to be copied
                    //Check existence of activity and fetch details
                    try {
                        $data = array('pupilsightActivityID' => $pupilsightActivityID);
                        $sql = 'SELECT * FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }

                    if ($result->rowCount() != 1) {
                        $partialFail = true;
                    } else {
                        try {
                            $data = array('pupilsightActivityID' => $pupilsightActivityID);
                            $sql = 'DELETE FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }
            }
            else {
                $URL .= '&return=error3';
                header("Location: {$URL}");
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
