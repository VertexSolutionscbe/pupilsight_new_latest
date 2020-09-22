<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightLibraryItemEventID = $_GET['pupilsightLibraryItemEventID'];
$pupilsightLibraryItemID = $_GET['pupilsightLibraryItemID'];

if ($pupilsightLibraryItemID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/library_lending_item_return.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryItemEventID=$pupilsightLibraryItemEventID&name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'];
    $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/library_lending_item.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryItemEventID=$pupilsightLibraryItemEventID&name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'];

    if (isActionAccessible($guid, $connection2, '/modules/Library/library_lending_item_return.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if event specified
        if ($pupilsightLibraryItemEventID == '' or $pupilsightLibraryItemID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightLibraryItemEventID' => $pupilsightLibraryItemEventID, 'pupilsightLibraryItemID' => $pupilsightLibraryItemID);
                $sql = 'SELECT * FROM pupilsightLibraryItemEvent JOIN pupilsightLibraryItem ON (pupilsightLibraryItemEvent.pupilsightLibraryItemID=pupilsightLibraryItem.pupilsightLibraryItemID) WHERE pupilsightLibraryItemEventID=:pupilsightLibraryItemEventID AND pupilsightLibraryItem.pupilsightLibraryItemID=:pupilsightLibraryItemID';
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
                $returnAction = $_POST['returnAction'];
                $status = '';
                if ($returnAction == 'Reserve') {
                    $status = 'Reserved';
                } elseif ($returnAction == 'Decommission') {
                    $status = 'Decommissioned';
                } elseif ($returnAction == 'Repair') {
                    $status = 'Repair';
                }
                $pupilsightPersonIDReturnAction = null;
                if ($_POST['pupilsightPersonIDReturnAction'] != '') {
                    $pupilsightPersonIDReturnAction = $_POST['pupilsightPersonIDReturnAction'];
                }

                //Write to database
                try {
                    $data = array('timestampReturn' => date('Y-m-d H:i:s', time()), 'pupilsightLibraryItemEventID' => $pupilsightLibraryItemEventID, 'pupilsightPersonIDIn' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "UPDATE pupilsightLibraryItemEvent SET status='Returned', timestampReturn=:timestampReturn, pupilsightPersonIDIn=:pupilsightPersonIDIn WHERE pupilsightLibraryItemEventID=:pupilsightLibraryItemEventID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //No return action, so just mark the item
                if ($returnAction == '') {
                    try {
                        $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID, 'pupilsightPersonIDStatusRecorder' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampStatus' => date('Y-m-d H:i:s', time()));
                        $sql = "UPDATE pupilsightLibraryItem SET status='Available', pupilsightPersonIDStatusResponsible=NULL, pupilsightPersonIDStatusRecorder=:pupilsightPersonIDStatusRecorder, timestampStatus=:timestampStatus, returnExpected=NULL, returnAction='', pupilsightPersonIDReturnAction=NULL WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                }
                //Return action, so mark the item, and create a new event
                else {
                    try {
                        $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID, 'status' => $status, 'pupilsightPersonIDStatusResponsible' => $pupilsightPersonIDReturnAction, 'pupilsightPersonIDOut' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampOut' => date('Y-m-d H:i:s', time()));
                        $sql = "INSERT INTO pupilsightLibraryItemEvent SET pupilsightLibraryItemID=:pupilsightLibraryItemID, status=:status, pupilsightPersonIDStatusResponsible=:pupilsightPersonIDStatusResponsible, pupilsightPersonIDOut=:pupilsightPersonIDOut, timestampOut=:timestampOut, returnExpected=NULL, returnAction='', pupilsightPersonIDReturnAction=NULL";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2'.$e->getMessage();
                        header("Location: {$URL}");
                        exit();
                    }

                    try {
                        $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID, 'status' => $status, 'pupilsightPersonIDStatusResponsible' => $pupilsightPersonIDReturnAction, 'pupilsightPersonIDStatusRecorder' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampStatus' => date('Y-m-d H:i:s', time()));
                        $sql = "UPDATE pupilsightLibraryItem SET status=:status, pupilsightPersonIDStatusResponsible=:pupilsightPersonIDStatusResponsible, pupilsightPersonIDStatusRecorder=:pupilsightPersonIDStatusRecorder, timestampStatus=:timestampStatus, returnExpected=NULL, returnAction='', pupilsightPersonIDReturnAction=NULL WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                }

                $URL = $URLSuccess.'&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
