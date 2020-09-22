<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFamilyUpdateID = $_GET['pupilsightFamilyUpdateID'];
$pupilsightFamilyID = $_POST['pupilsightFamilyID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/data_family_manage_edit.php&pupilsightFamilyUpdateID=$pupilsightFamilyUpdateID";

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_family_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFamilyUpdateID == '' or $pupilsightFamilyID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFamilyUpdateID' => $pupilsightFamilyUpdateID);
            $sql = 'SELECT * FROM pupilsightFamilyUpdate WHERE pupilsightFamilyUpdateID=:pupilsightFamilyUpdateID';
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
            //Set values
            $data = array();
            $set = '';
            if (isset($_POST['newnameAddressOn'])) {
                if ($_POST['newnameAddressOn'] == 'on') {
                    $data['nameAddress'] = $_POST['newnameAddress'];
                    $set .= 'pupilsightFamily.nameAddress=:nameAddress, ';
                }
            }
            if (isset($_POST['newhomeAddressOn'])) {
                if ($_POST['newhomeAddressOn'] == 'on') {
                    $data['homeAddress'] = $_POST['newhomeAddress'];
                    $set .= 'pupilsightFamily.homeAddress=:homeAddress, ';
                }
            }
            if (isset($_POST['newhomeAddressDistrictOn'])) {
                if ($_POST['newhomeAddressDistrictOn'] == 'on') {
                    $data['homeAddressDistrict'] = $_POST['newhomeAddressDistrict'];
                    $set .= 'pupilsightFamily.homeAddressDistrict=:homeAddressDistrict, ';
                }
            }
            if (isset($_POST['newhomeAddressCountryOn'])) {
                if ($_POST['newhomeAddressCountryOn'] == 'on') {
                    $data['homeAddressCountry'] = $_POST['newhomeAddressCountry'];
                    $set .= 'pupilsightFamily.homeAddressCountry=:homeAddressCountry, ';
                }
            }
            if (isset($_POST['newlanguageHomePrimaryOn'])) {
                if ($_POST['newlanguageHomePrimaryOn'] == 'on') {
                    $data['languageHomePrimary'] = $_POST['newlanguageHomePrimary'];
                    $set .= 'pupilsightFamily.languageHomePrimary=:languageHomePrimary, ';
                }
            }
            if (isset($_POST['newlanguageHomeSecondaryOn'])) {
                if ($_POST['newlanguageHomeSecondaryOn'] == 'on') {
                    $data['languageHomeSecondary'] = $_POST['newlanguageHomeSecondary'];
                    $set .= 'pupilsightFamily.languageHomeSecondary=:languageHomeSecondary, ';
                }
            }

            if (strlen($set) > 1) {
                //Write to database
                try {
                    $data['pupilsightFamilyID'] = $pupilsightFamilyID;
                    $sql = 'UPDATE pupilsightFamily SET '.substr($set, 0, (strlen($set) - 2)).' WHERE pupilsightFamilyID=:pupilsightFamilyID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Write to database
                try {
                    $data = array('pupilsightFamilyUpdateID' => $pupilsightFamilyUpdateID);
                    $sql = "UPDATE pupilsightFamilyUpdate SET status='Complete' WHERE pupilsightFamilyUpdateID=:pupilsightFamilyUpdateID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('pupilsightFamilyUpdateID' => $pupilsightFamilyUpdateID);
                    $sql = "UPDATE pupilsightFamilyUpdate SET status='Complete' WHERE pupilsightFamilyUpdateID=:pupilsightFamilyUpdateID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&updateReturn=success1';
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
