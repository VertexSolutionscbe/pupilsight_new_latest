<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFamilyID = $_GET['pupilsightFamilyID'];
$search = $_GET['search'];
$child_id = $_GET['child_id'];
  //print_r($child_id);die();

if ($pupilsightFamilyID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/family_manage_edit.php&pupilsightFamilyID=$pupilsightFamilyID&child_id=$child_id&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Validate Inputs
        $name = $_POST['name'];
        $status = $_POST['status'];
        $languageHomePrimary = $_POST['languageHomePrimary'];
        $languageHomeSecondary = $_POST['languageHomeSecondary'];
        $nameAddress = $_POST['nameAddress'];
        $homeAddress = $_POST['homeAddress'];
        $homeAddressDistrict = $_POST['homeAddressDistrict'];
        $homeAddressCountry = $_POST['homeAddressCountry'];

        //Write to database
        try {
            $data = array('name' => $name, 'status' => $status, 'languageHomePrimary' => $languageHomePrimary, 'languageHomeSecondary' => $languageHomeSecondary, 'nameAddress' => $nameAddress, 'homeAddress' => $homeAddress, 'homeAddressDistrict' => $homeAddressDistrict, 'homeAddressCountry' => $homeAddressCountry, 'pupilsightFamilyID' => $pupilsightFamilyID);
            $sql = 'UPDATE pupilsightFamily SET name=:name, status=:status, languageHomePrimary=:languageHomePrimary, languageHomeSecondary=:languageHomeSecondary, nameAddress=:nameAddress, homeAddress=:homeAddress, homeAddressDistrict=:homeAddressDistrict, homeAddressCountry=:homeAddressCountry WHERE pupilsightFamilyID=:pupilsightFamilyID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Success 0
        $URL .= '&return=success0';
        header("Location: {$URL}");
        exit();
    }
}
