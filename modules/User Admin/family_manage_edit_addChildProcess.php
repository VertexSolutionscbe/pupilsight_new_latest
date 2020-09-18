<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFamilyID = $_GET['pupilsightFamilyID'];
$pupilsightPersonID = $_POST['pupilsightPersonID'];
$child_id = $_GET['child_id'];

$search = $_GET['search'];

if ($pupilsightFamilyID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/family_manage_edit.php&pupilsightFamilyID=$pupilsightFamilyID&child_id=$child_id&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightPersonID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
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
                //Check for an existing child or parent record in this family
                try {
                    $dataCheck = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightFamilyID1' => $pupilsightFamilyID, 'pupilsightPersonID2' => $pupilsightPersonID, 'pupilsightFamilyID2' => $pupilsightFamilyID);
                    $sqlCheck = 'SELECT pupilsightPersonID FROM pupilsightFamilyChild WHERE pupilsightPersonID=:pupilsightPersonID1 AND pupilsightFamilyID=:pupilsightFamilyID2 UNION SELECT pupilsightPersonID FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID2 AND pupilsightFamilyID=:pupilsightFamilyID2';
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($resultCheck->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Validate Inputs
                    $comment = $_POST['comment'];

                    //Write to database
                    try {
                        $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $pupilsightPersonID, 'comment' => $comment);
                        $sql = 'INSERT INTO pupilsightFamilyChild SET pupilsightFamilyID=:pupilsightFamilyID, pupilsightPersonID=:pupilsightPersonID, comment=:comment';
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
}
