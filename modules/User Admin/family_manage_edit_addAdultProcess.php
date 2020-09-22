<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$search = $_GET['search'];
$pupilsightFamilyID = $_GET['pupilsightFamilyID'];
$pupilsightPersonID = $_POST['pupilsightPersonID2'];
$child_id = $_GET['child_id'];

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
                    $comment = $_POST['comment2'];
                    $childDataAccess = $_POST['childDataAccess'];
                    $contactPriority = $_POST['contactPriority'];
                    if ($contactPriority == 1) {
                        $contactCall = 'Y';
                        $contactSMS = 'Y';
                        $contactEmail = 'Y';
                        $contactMail = 'Y';
                    } else {
                        $contactCall = $_POST['contactCall'];
                        $contactSMS = $_POST['contactSMS'];
                        $contactEmail = $_POST['contactEmail'];
                        $contactMail = $_POST['contactMail'];
                    }

                    //Enforce one and only one contactPriority=1 parent
                    if ($contactPriority == 1) {
                        //Set all other parents in family who are set to 1 to 2, 2 to 3
                        try {
                            $dataCP = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightFamilyID' => $pupilsightFamilyID);
                            $sqlCP = 'UPDATE pupilsightFamilyAdult SET contactPriority=contactPriority+1 WHERE contactPriority < 3 AND pupilsightFamilyID=:pupilsightFamilyID AND NOT pupilsightPersonID=:pupilsightPersonID';
                            $resultCP = $connection2->prepare($sqlCP);
                            $resultCP->execute($dataCP);
                        } catch (PDOException $e) {
                        }
                    } else {
                        //Check to see if there is a parent set to 1 already, and if not, change this one to 1
                        try {
                            $dataCP = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightFamilyID' => $pupilsightFamilyID);
                            $sqlCP = 'SELECT * FROM pupilsightFamilyAdult WHERE contactPriority=1 AND pupilsightFamilyID=:pupilsightFamilyID AND NOT pupilsightPersonID=:pupilsightPersonID';
                            $resultCP = $connection2->prepare($sqlCP);
                            $resultCP->execute($dataCP);
                        } catch (PDOException $e) {
                        }
                        if ($resultCP->rowCount() < 1) {
                            $contactPriority = 1;
                            $contactCall = 'Y';
                            $contactSMS = 'Y';
                            $contactEmail = 'Y';
                            $contactMail = 'Y';
                        }

                        // Set any other contact priority 2 to 3
                        if ($contactPriority == 2) {
                            try {
                            $dataCP = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightFamilyID' => $pupilsightFamilyID);
                                $sqlCP = 'UPDATE pupilsightFamilyAdult SET contactPriority=3 WHERE contactPriority=2 AND pupilsightFamilyID=:pupilsightFamilyID AND NOT pupilsightPersonID=:pupilsightPersonID';
                                $resultCP = $connection2->prepare($sqlCP);
                                $resultCP->execute($dataCP);
                            } catch (PDOException $e) {
                            }
                        }
                    }

                    //Write to database
                    try {
                        $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $pupilsightPersonID, 'comment' => $comment, 'childDataAccess' => $childDataAccess, 'contactPriority' => $contactPriority, 'contactCall' => $contactCall, 'contactSMS' => $contactSMS, 'contactEmail' => $contactEmail, 'contactMail' => $contactMail);
                        $sql = 'INSERT INTO pupilsightFamilyAdult SET pupilsightFamilyID=:pupilsightFamilyID, pupilsightPersonID=:pupilsightPersonID, comment=:comment, childDataAccess=:childDataAccess, contactPriority=:contactPriority, contactCall=:contactCall, contactSMS=:contactSMS, contactEmail=:contactEmail, contactMail=:contactMail';
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
