<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/leaveHistory.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/leaveApply.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $pupilsightLeaveReasonID = $_POST['pupilsightLeaveReasonID'];
    $remarks = $_POST['remarks'];
    $date = str_replace('/', '-', $_POST['from_date']);
    $from_date = date('Y-m-d', strtotime($date));
    $tdate = str_replace('/', '-', $_POST['to_date']);
    $to_date = date('Y-m-d', strtotime($tdate));
    $pupilsightPersonID = $_POST['pupilsightPersonID'];
    //die();
    
    if ($pupilsightLeaveReasonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightLeaveReasonID' => $pupilsightLeaveReasonID, 'from_date' => $from_date);
            $sql = 'SELECT * FROM pupilsightLeaveApply WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightLeaveReasonID=:pupilsightLeaveReasonID AND from_date=:from_date';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error23';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightLeaveReasonID' => $pupilsightLeaveReasonID, 'from_date' => $from_date, 'to_date' => $to_date, 'remarks' => $remarks);
                $sql = 'INSERT INTO pupilsightLeaveApply SET pupilsightPersonID=:pupilsightPersonID, pupilsightLeaveReasonID=:pupilsightLeaveReasonID, from_date=:from_date, to_date=:to_date, remarks=:remarks';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
