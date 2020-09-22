<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStaffID = $_GET['pupilsightStaffID'];
$pupilsightPersonID = $_GET['pupilsightPersonID'];
$search = $_GET['search'];

if ($pupilsightStaffID == '' or $pupilsightPersonID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/staff_manage_edit_facility_add.php&pupilsightPersonID=$pupilsightPersonID&pupilsightStaffID=$pupilsightStaffID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_edit_facility_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightStaffID == '' or $pupilsightPersonID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightStaffID' => $pupilsightStaffID, 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'SELECT pupilsightStaff.*, preferredName, surname FROM pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStaffID=:pupilsightStaffID AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
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
                $pupilsightSpaceID = $_POST['pupilsightSpaceID'];
                $usageType = $_POST['usageType'];

                if ($pupilsightSpaceID == '') {
                    $URL .= '&return=error1&step=1';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSpaceID' => $pupilsightSpaceID, 'usageType' => $usageType);
                        $sql = 'INSERT INTO pupilsightSpacePerson SET pupilsightPersonID=:pupilsightPersonID, pupilsightSpaceID=:pupilsightSpaceID, usageType=:usageType';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= "&return=success0";
                    header("Location: {$URL}");
                }
            }
        }
    }
}
