<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightActivityID = $_GET['pupilsightActivityID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_manage_edit.php&pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search'].'&pupilsightSchoolYearTermID='.$_GET['pupilsightSchoolYearTermID'];

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightActivityID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT * FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
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
            $provider = $_POST['provider'];
            $active = $_POST['active'];
            $registration = $_POST['registration'];
            $dateType = $_POST['dateType'];
            if ($dateType == 'Term') {
                $pupilsightSchoolYearTermIDList = isset($_POST['pupilsightSchoolYearTermIDList'])? $_POST['pupilsightSchoolYearTermIDList'] : array();
                $pupilsightSchoolYearTermIDList = implode(',', $pupilsightSchoolYearTermIDList);
            } elseif ($dateType == 'Date') {
                $listingStart = isset($_POST['listingStart'])? dateConvert($guid, $_POST['listingStart']) : '';
                $listingEnd = isset($_POST['listingEnd'])? dateConvert($guid, $_POST['listingEnd']) : '';
                $programStart = isset($_POST['programStart'])? dateConvert($guid, $_POST['programStart']) : '';
                $programEnd = isset($_POST['programEnd'])? dateConvert($guid, $_POST['programEnd']) : '';
            }
            $pupilsightYearGroupIDList = isset($_POST['pupilsightYearGroupIDList'])? $_POST['pupilsightYearGroupIDList'] : array();
            $pupilsightYearGroupIDList = implode(',', $pupilsightYearGroupIDList);

            $maxParticipants = $_POST['maxParticipants'];
            if (getSettingByScope($connection2, 'Activities', 'payment') == 'None' or getSettingByScope($connection2, 'Activities', 'payment') == 'Single') {
                $paymentOn = false;
                $payment = null;
                $paymentType = null;
                $paymentFirmness = null;
            } else {
                $paymentOn = true;
                $payment = $_POST['payment'];
                $paymentType = $_POST['paymentType'];
                $paymentFirmness = $_POST['paymentFirmness'];
            }
            $description = $_POST['description'];

            if ($dateType == '' or $name == '' or $provider == '' or $active == '' or $registration == '' or $maxParticipants == '' or ($paymentOn and ($payment == '' or $paymentType == '' or $paymentFirmness == '')) or ($dateType == 'Date' and ($listingStart == '' or $listingEnd == '' or $programStart == '' or $programEnd == ''))) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Scan through slots
                $partialFail = false;
                for ($i = 1; $i < 3; ++$i) {
                    $pupilsightDaysOfWeekID = $_POST["pupilsightDaysOfWeekID$i"];
                    $timeStart = $_POST["timeStart$i"];
                    $timeEnd = $_POST["timeEnd$i"];
                    $type = 'Internal';
                    if (isset($_POST['slot'.$i.'Location'])) {
                        $type = $_POST['slot'.$i.'Location'];
                    }
                    $pupilsightSpaceID = null;
                    if ($type == 'Internal') {
                        $pupilsightSpaceID = isset($_POST["pupilsightSpaceID$i"])? $_POST["pupilsightSpaceID$i"] : null;
                        $locationExternal = '';
                    } else {
                        $locationExternal = $_POST['location'.$i.'External'];
                    }

                    if ($pupilsightDaysOfWeekID != '' and $timeStart != '' and $timeEnd != '') {
                        try {
                            $data = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightDaysOfWeekID' => $pupilsightDaysOfWeekID, 'timeStart' => $timeStart, 'timeEnd' => $timeEnd, 'pupilsightSpaceID' => $pupilsightSpaceID, 'locationExternal' => $locationExternal);
                            $sql = 'INSERT INTO pupilsightActivitySlot SET pupilsightActivityID=:pupilsightActivityID, pupilsightDaysOfWeekID=:pupilsightDaysOfWeekID, timeStart=:timeStart, timeEnd=:timeEnd, pupilsightSpaceID=:pupilsightSpaceID, locationExternal=:locationExternal';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }

                //Scan through staff
                $staff = null;
                if (isset($_POST['staff'])) {
                    $staff = $_POST['staff'];
                }
                $role = $_POST['role'];
                if ($role == '') {
                    $role = 'Other';
                }
                if (count($staff) > 0) {
                    foreach ($staff as $t) {
                        //Check to see if person is already registered in this activity
                        try {
                            $dataGuest = array('pupilsightPersonID' => $t, 'pupilsightActivityID' => $pupilsightActivityID);
                            $sqlGuest = 'SELECT * FROM pupilsightActivityStaff WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightActivityID=:pupilsightActivityID';
                            $resultGuest = $connection2->prepare($sqlGuest);
                            $resultGuest->execute($dataGuest);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        if ($resultGuest->rowCount() == 0) {
                            try {
                                $data = array('pupilsightPersonID' => $t, 'pupilsightActivityID' => $pupilsightActivityID, 'role' => $role);
                                $sql = 'INSERT INTO pupilsightActivityStaff SET pupilsightPersonID=:pupilsightPersonID, pupilsightActivityID=:pupilsightActivityID, role=:role';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                echo "here<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                $partialFail = true;
                            }
                        }
                    }
                }

                //Write to database
                $type = isset($_POST['type'])? $_POST['type'] : '';

                try {
                    if ($dateType == 'Date') {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID, 'name' => $name, 'provider' => $provider, 'type' => $type, 'active' => $active, 'registration' => $registration, 'listingStart' => $listingStart, 'listingEnd' => $listingEnd, 'programStart' => $programStart, 'programEnd' => $programEnd, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'maxParticipants' => $maxParticipants, 'payment' => $payment, 'paymentType' => $paymentType, 'paymentFirmness' => $paymentFirmness, 'description' => $description);
                        $sql = "UPDATE pupilsightActivity SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, provider=:provider, type=:type, active=:active, registration=:registration, pupilsightSchoolYearTermIDList='', listingStart=:listingStart, listingEnd=:listingEnd, programStart=:programStart, programEnd=:programEnd, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, maxParticipants=:maxParticipants, payment=:payment, paymentType=:paymentType, paymentFirmness=:paymentFirmness, description=:description WHERE pupilsightActivityID=:pupilsightActivityID";
                    } else {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID, 'name' => $name, 'provider' => $provider, 'type' => $type, 'active' => $active, 'registration' => $registration, 'pupilsightSchoolYearTermIDList' => $pupilsightSchoolYearTermIDList, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'maxParticipants' => $maxParticipants, 'payment' => $payment, 'paymentType' => $paymentType, 'paymentFirmness' => $paymentFirmness, 'description' => $description);
                        $sql = 'UPDATE pupilsightActivity SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, provider=:provider, type=:type, active=:active, registration=:registration, pupilsightSchoolYearTermIDList=:pupilsightSchoolYearTermIDList, listingStart=NULL, listingEnd=NULL, programStart=NULL, programEnd=NULL, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, maxParticipants=:maxParticipants, payment=:payment, paymentType=:paymentType, paymentFirmness=:paymentFirmness, description=:description WHERE pupilsightActivityID=:pupilsightActivityID';
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($partialFail == true) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
