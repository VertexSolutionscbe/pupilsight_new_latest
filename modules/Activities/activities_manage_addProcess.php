<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/activities_manage_add.php&search='.$_GET['search'].'&pupilsightSchoolYearTermID='.$_GET['pupilsightSchoolYearTermID'];

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $name = $_POST['name'];
    $provider = $_POST['provider'];
    $active = $_POST['active'];
    $registration = $_POST['registration'];
    $dateType = $_POST['dateType'];
    if ($dateType == 'Term') {
        $pupilsightSchoolYearTermIDList = isset($_POST['pupilsightSchoolYearTermIDList'])? $_POST['pupilsightSchoolYearTermIDList'] : array();
        $pupilsightSchoolYearTermIDList = implode(',', $pupilsightSchoolYearTermIDList);
    } elseif ($dateType == 'Date') {
        $listingStart = dateConvert($guid, $_POST['listingStart']);
        $listingEnd = dateConvert($guid, $_POST['listingEnd']);
        $programStart = dateConvert($guid, $_POST['programStart']);
        $programEnd = dateConvert($guid, $_POST['programEnd']);
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
        //Write to database
        $type = '';
        if (isset($_POST['type'])) {
            $type = $_POST['type'];
        }

        try {
            if ($dateType == 'Date') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'name' => $name, 'provider' => $provider, 'type' => $type, 'active' => $active, 'registration' => $registration, 'listingStart' => $listingStart, 'listingEnd' => $listingEnd, 'programStart' => $programStart, 'programEnd' => $programEnd, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'maxParticipants' => $maxParticipants, 'payment' => $payment, 'paymentType' => $paymentType, 'paymentFirmness' => $paymentFirmness, 'description' => $description);
                $sql = "INSERT INTO pupilsightActivity SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, provider=:provider, type=:type, active=:active, registration=:registration, pupilsightSchoolYearTermIDList='', listingStart=:listingStart, listingEnd=:listingEnd, programStart=:programStart, programEnd=:programEnd, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, maxParticipants=:maxParticipants, payment=:payment, paymentType=:paymentType, paymentFirmness=:paymentFirmness, description=:description";
            } else {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'name' => $name, 'provider' => $provider, 'type' => $type, 'active' => $active, 'registration' => $registration, 'pupilsightSchoolYearTermIDList' => $pupilsightSchoolYearTermIDList, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'maxParticipants' => $maxParticipants, 'payment' => $payment, 'paymentType' => $paymentType, 'paymentFirmness' => $paymentFirmness, 'description' => $description);
                $sql = 'INSERT INTO pupilsightActivity SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, provider=:provider, type=:type, active=:active, registration=:registration, pupilsightSchoolYearTermIDList=:pupilsightSchoolYearTermIDList, listingStart=NULL, listingEnd=NULL, programStart=NULL, programEnd=NULL, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, maxParticipants=:maxParticipants, payment=:payment, paymentType=:paymentType, paymentFirmness=:paymentFirmness, description=:description';
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 14, '0', STR_PAD_LEFT);

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
                    $data = array('AI' => $AI, 'pupilsightDaysOfWeekID' => $pupilsightDaysOfWeekID, 'timeStart' => $timeStart, 'timeEnd' => $timeEnd, 'pupilsightSpaceID' => $pupilsightSpaceID, 'locationExternal' => $locationExternal);
                    $sql = 'INSERT INTO pupilsightActivitySlot SET pupilsightActivityID=:AI, pupilsightDaysOfWeekID=:pupilsightDaysOfWeekID, timeStart=:timeStart, timeEnd=:timeEnd, pupilsightSpaceID=:pupilsightSpaceID, locationExternal=:locationExternal';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
            }
        }

        //Scan through staff
        $staff = isset($_POST['staff'])? $_POST['staff'] : null;
        $role = isset($_POST['role']) ? $_POST['role'] : 'Other';

        if (count($staff) > 0) {
            foreach ($staff as $t) {
                //Check to see if person is already registered in this activity
                try {
                    $dataGuest = array('pupilsightPersonID' => $t, 'pupilsightActivityID' => $AI);
                    $sqlGuest = 'SELECT * FROM pupilsightActivityStaff WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightActivityID=:pupilsightActivityID';
                    $resultGuest = $connection2->prepare($sqlGuest);
                    $resultGuest->execute($dataGuest);
                } catch (PDOException $e) {
                    $partialFail = true;
                }

                if ($resultGuest->rowCount() == 0) {
                    try {
                        $data = array('pupilsightPersonID' => $t, 'pupilsightActivityID' => $AI, 'role' => $role);
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

        if ($partialFail == true) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
