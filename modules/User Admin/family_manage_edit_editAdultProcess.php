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
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/family_manage_edit_editAdult.php&pupilsightFamilyID=$pupilsightFamilyID&pupilsightPersonID=$pupilsightPersonID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit_editAdult.php') == false) {
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
                $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = "SELECT * FROM pupilsightPerson, pupilsightFamily, pupilsightFamilyAdult WHERE pupilsightFamily.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID AND pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightFamily.pupilsightFamilyID=:pupilsightFamilyID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID AND (pupilsightPerson.status='Full' OR pupilsightPerson.status='Expected')";
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
                $comment = $_POST['comment'];
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
                    //Set all other parents in family who are set to 1, to 2
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
                    $data = array('comment' => $comment, 'childDataAccess' => $childDataAccess, 'contactPriority' => $contactPriority, 'contactCall' => $contactCall, 'contactSMS' => $contactSMS, 'contactEmail' => $contactEmail, 'contactMail' => $contactMail, 'pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'UPDATE pupilsightFamilyAdult SET comment=:comment, childDataAccess=:childDataAccess, contactPriority=:contactPriority, contactCall=:contactCall, contactSMS=:contactSMS, contactEmail=:contactEmail, contactMail=:contactMail WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPersonID=:pupilsightPersonID';
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
