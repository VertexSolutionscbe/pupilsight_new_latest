<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFamilyID = $_GET['pupilsightFamilyID'];
$pupilsightPersonID = $_GET['pupilsightPersonID'];
$search = $_GET['search'];

if ($pupilsightFamilyID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/family_manage_edit_deleteAdult.php&pupilsightFamilyID=$pupilsightFamilyID&pupilsightPersonID=$pupilsightPersonID&search=$search";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/family_manage_edit.php&pupilsightFamilyID=$pupilsightFamilyID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit_deleteAdult.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightPersonID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'SELECT pupilsightPerson.pupilsightPersonID, pupilsightFamilyAdult.contactPriority FROM pupilsightPerson, pupilsightFamily, pupilsightFamilyAdult WHERE pupilsightFamily.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID AND pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightFamily.pupilsightFamilyID=:pupilsightFamilyID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID';
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
                $row = $result->fetch();

                // If we're deleting the first contact priority, move the second one to first
                if ($row['contactPriority'] == 1) {
                    try {
                        $dataCP = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightFamilyID' => $pupilsightFamilyID);
                        $sqlCP = 'UPDATE pupilsightFamilyAdult SET contactPriority=1 WHERE contactPriority=2 AND pupilsightFamilyID=:pupilsightFamilyID AND NOT pupilsightPersonID=:pupilsightPersonID LIMIT 1';
                        $resultCP = $connection2->prepare($sqlCP);
                        $resultCP->execute($dataCP);
                    } catch (PDOException $e) {
                    }
                }

                //Write to database
                try {
                    $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'DELETE FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyID=:pupilsightFamilyID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $URLDelete = $URLDelete.'&return=success0';
                header("Location: {$URLDelete}");
            }
        }
    }
}
