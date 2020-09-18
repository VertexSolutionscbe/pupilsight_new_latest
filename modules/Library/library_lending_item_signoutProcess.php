<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$statusCurrent = $_POST['statusCurrent'];
$status = $_POST['status'];
$type = 'Other';
if ($status == 'Decommissioned') {
    $type = 'Decommission';
} elseif ($status == 'Lost') {
    $type = 'Loss';
} elseif ($status == 'On Loan') {
    $type = 'Loan';
} elseif ($status == 'Repair') {
    $type = 'Repair';
} elseif ($status == 'Reserved') {
    $type = 'Reserve';
}
$pupilsightPersonIDStatusResponsible = $_POST['pupilsightPersonIDStatusResponsible'];
if ($_POST['returnExpected'] != '') {
    $returnExpected = dateConvert($guid, $_POST['returnExpected']);
}
$returnAction = $_POST['returnAction'];
$pupilsightPersonIDReturnAction = null;
if ($_POST['pupilsightPersonIDReturnAction'] != '') {
    $pupilsightPersonIDReturnAction = $_POST['pupilsightPersonIDReturnAction'];
}

$pupilsightLibraryItemID = $_POST['pupilsightLibraryItemID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/library_lending_item_signOut.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'];
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/library_lending_item.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'];

if (isActionAccessible($guid, $connection2, '/modules/Library/library_lending_item_signOut.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightLibraryItemID == '' or $status == '' or $pupilsightPersonIDStatusResponsible == '' or $statusCurrent != 'Available') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID);
            $sql = 'SELECT * FROM pupilsightLibraryItem WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID, 'type' => $type, 'status' => $status, 'pupilsightPersonIDStatusResponsible' => $pupilsightPersonIDStatusResponsible, 'pupilsightPersonIDOut' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampOut' => date('Y-m-d H:i:s', time()), 'returnExpected' => $returnExpected, 'returnAction' => $returnAction, 'pupilsightPersonIDReturnAction' => $pupilsightPersonIDReturnAction);
                $sql = 'INSERT INTO pupilsightLibraryItemEvent SET pupilsightLibraryItemID=:pupilsightLibraryItemID, type=:type, status=:status, pupilsightPersonIDStatusResponsible=:pupilsightPersonIDStatusResponsible, pupilsightPersonIDOut=:pupilsightPersonIDOut, timestampOut=:timestampOut, returnExpected=:returnExpected, returnAction=:returnAction, pupilsightPersonIDReturnAction=:pupilsightPersonIDReturnAction';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2'.$e->getMessage();
                header("Location: {$URL}");
                exit();
            }

            try {
                $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID, 'status' => $status, 'pupilsightPersonIDStatusResponsible' => $pupilsightPersonIDStatusResponsible, 'pupilsightPersonIDStatusRecorder' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampStatus' => date('Y-m-d H:i:s', time()), 'returnExpected' => $returnExpected, 'returnAction' => $returnAction, 'pupilsightPersonIDReturnAction' => $pupilsightPersonIDReturnAction);
                $sql = 'UPDATE pupilsightLibraryItem SET status=:status, pupilsightPersonIDStatusResponsible=:pupilsightPersonIDStatusResponsible, pupilsightPersonIDStatusRecorder=:pupilsightPersonIDStatusRecorder, timestampStatus=:timestampStatus, returnExpected=:returnExpected, returnAction=:returnAction, pupilsightPersonIDReturnAction=:pupilsightPersonIDReturnAction WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URL = $URLSuccess.'&return=success0';
            header("Location: {$URL}");
        }
    }
}
