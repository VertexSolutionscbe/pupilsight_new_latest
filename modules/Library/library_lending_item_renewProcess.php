<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightLibraryItemEventID = $_GET['pupilsightLibraryItemEventID'];
$pupilsightLibraryItemID = $_GET['pupilsightLibraryItemID'];

if ($pupilsightLibraryItemID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/library_lending_item_renew.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryItemEventID=$pupilsightLibraryItemEventID&name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'];

    if (isActionAccessible($guid, $connection2, '/modules/Library/library_lending_item_renew.php') == false) {
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
                $returnExpected = null;
                if ($_POST['returnExpected'] != '') {
                    $returnExpected = dateConvert($guid, $_POST['returnExpected']);
                }

                //Write to database
                try {
                    $data = array('pupilsightLibraryItemEventID' => $pupilsightLibraryItemEventID, 'returnExpected' => $returnExpected);
                    $sql = 'UPDATE pupilsightLibraryItemEvent SET returnExpected=:returnExpected WHERE pupilsightLibraryItemEventID=:pupilsightLibraryItemEventID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2'.$e->getMessage();
                    header("Location: {$URL}");
                    exit();
                }

                try {
                    $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID, 'returnExpected' => $returnExpected);
                    $sql = 'UPDATE pupilsightLibraryItem SET returnExpected=:returnExpected WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
