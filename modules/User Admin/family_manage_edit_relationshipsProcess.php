<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFamilyID = $_GET['pupilsightFamilyID'];
$search = $_GET['search'];
$child_id = $_GET['child_id'];

if ($pupilsightFamilyID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/family_manage_edit.php&pupilsightFamilyID=$pupilsightFamilyID&child_id=$child_id&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Validate Inputs
        $relationships = $_POST['relationships'];
        $pupilsightPersonID1 = $_POST['pupilsightPersonID1'];
        $pupilsightPersonID2 = $_POST['pupilsightPersonID2'];

        $partialFail = false;

        $count = 0;
        foreach ($relationships as $relationshipOuter) {
            foreach ($relationshipOuter as $relationship) {
                //Check for record
                try {
                    $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID1' => $pupilsightPersonID1[$count], 'pupilsightPersonID2' => $pupilsightPersonID2[$count]);
                    $sql = 'SELECT * FROM pupilsightFamilyRelationship WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPersonID1=:pupilsightPersonID1 AND pupilsightPersonID2=:pupilsightPersonID2';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
                if ($result->rowCount() == 0) {
                    try {
                        $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID1' => $pupilsightPersonID1[$count], 'pupilsightPersonID2' => $pupilsightPersonID2[$count], 'relationship' => $relationship);
                        $sql = 'INSERT INTO pupilsightFamilyRelationship SET pupilsightFamilyID=:pupilsightFamilyID, pupilsightPersonID1=:pupilsightPersonID1, pupilsightPersonID2=:pupilsightPersonID2, relationship=:relationship';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                } elseif ($result->rowCount() == 1) {
                    $row = $result->fetch();

                    if ($row['relationship'] != $relationship) {
                        try {
                            $data = array('relationship' => $relationship, 'pupilsightFamilyRelationshipID' => $row['pupilsightFamilyRelationshipID']);
                            $sql = 'UPDATE pupilsightFamilyRelationship SET relationship=:relationship WHERE pupilsightFamilyRelationshipID=:pupilsightFamilyRelationshipID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                } else {
                    $partialFail = true;
                }

                ++$count;
            }
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
