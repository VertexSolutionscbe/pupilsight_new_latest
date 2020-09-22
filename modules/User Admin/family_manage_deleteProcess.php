<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFamilyID = $_GET['pupilsightFamilyID'];
$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/family_manage_delete.php&pupilsightFamilyID=$pupilsightFamilyID&search=$search";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/family_manage.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if family specified
    if ($pupilsightFamilyID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFamilyID' => $pupilsightFamilyID);
            $sql = 'SELECT * FROM pupilsightFamily WHERE pupilsightFamilyID=:pupilsightFamilyID';
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
            //Delete children
            try {
                $dataDelete = array('pupilsightFamilyID' => $pupilsightFamilyID);
                $sqlDelete = 'DELETE FROM pupilsightFamilyChild WHERE pupilsightFamilyID=:pupilsightFamilyID';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
            } catch (PDOException $e) {
            }

            //Delete adults
            try {
                $dataDelete = array('pupilsightFamilyID' => $pupilsightFamilyID);
                $sqlDelete = 'DELETE FROM pupilsightFamilyAdult WHERE pupilsightFamilyID=:pupilsightFamilyID';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
            } catch (PDOException $e) {
            }

            //Delete Family
            try {
                $dataDelete = array('pupilsightFamilyID' => $pupilsightFamilyID);
                $sqlDelete = 'DELETE FROM pupilsightFamily WHERE pupilsightFamilyID=:pupilsightFamilyID';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
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
