<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/in_archive.php';

if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/in_archive.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $deleteCurrentPlans = $_POST['deleteCurrentPlans'];
    $title = $_POST['title'];
    $pupilsightPersonIDs = isset($_POST['pupilsightPersonID'])? $_POST['pupilsightPersonID'] : array();
    if (!is_array($pupilsightPersonIDs)) {
        $pupilsightPersonIDs = array($pupilsightPersonIDs);
    }

    if ($deleteCurrentPlans == '' or $title == '' or count($pupilsightPersonIDs) < 1) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $partialFail = false;

        //SCAN THROUGH EACH USER
        foreach ($pupilsightPersonIDs as $pupilsightPersonID) {
            $userFail = false;
            //Get each user's record
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = "SELECT surname, preferredName, pupilsightIN.* FROM pupilsightPerson JOIN pupilsightIN ON (pupilsightIN.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $userFail = true;
                $partialFail = true;
            }
            if ($result->rowCount() != 1) {
                $userFail = true;
                $partialFail = true;
            }

            if ($userFail == false) {
                $userUpdateFail = false;
                $row = $result->fetch();

                //Check for descriptors, and write to array
                $descriptors = array();
                $descriptorsCount = 0;
                try {
                    $dataDesciptors = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sqlDesciptors = 'SELECT * FROM pupilsightINPersonDescriptor WHERE pupilsightPersonID=:pupilsightPersonID';
                    $resultDesciptors = $connection2->prepare($sqlDesciptors);
                    $resultDesciptors->execute($dataDesciptors);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
                while ($rowDesciptors = $resultDesciptors->fetch()) {
                    $descriptors[$descriptorsCount]['pupilsightINDescriptorID'] = $rowDesciptors['pupilsightINDescriptorID'];
                    $descriptors[$descriptorsCount]['pupilsightAlertLevelID'] = $rowDesciptors['pupilsightAlertLevelID'];
                    ++$descriptorsCount;
                }
                $descriptors = serialize($descriptors);

                //Make archive of record
                try {
                    $dataUpdate = array('strategies' => $row['strategies'], 'targets' => $row['targets'], 'notes' => $row['notes'], 'pupilsightPersonID' => $pupilsightPersonID, 'title' => $title, 'descriptors' => $descriptors);
                    $sqlUpdate = 'INSERT INTO pupilsightINArchive SET pupilsightPersonID=:pupilsightPersonID, strategies=:strategies, targets=:targets, notes=:notes, archiveTitle=:title, descriptors=:descriptors, archiveTimestamp=now()';
                    $resultUpdate = $connection2->prepare($sqlUpdate);
                    $resultUpdate->execute($dataUpdate);
                } catch (PDOException $e) {
                    $userUpdateFail = true;
                    $partialFail = true;
                }

                //If copy was successful and deleteCurrentPlans=Y, update current record to blank IEP fields
                if ($deleteCurrentPlans == 'Y' and $userUpdateFail == false) {
                    try {
                        $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID);
                        $sqlUpdate = "UPDATE pupilsightIN SET strategies='', targets='', notes='' WHERE pupilsightPersonID=:pupilsightPersonID";
                        $resultUpdate = $connection2->prepare($sqlUpdate);
                        $resultUpdate->execute($dataUpdate);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }
            }
        }

        //DEAL WITH OUTCOME
        if ($partialFail) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
