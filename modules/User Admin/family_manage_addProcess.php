<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/family_manage_add.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $name = $_POST['name'];
    $status = $_POST['status'];
    $languageHomePrimary = $_POST['languageHomePrimary'];
    $languageHomeSecondary = $_POST['languageHomeSecondary'];
    $nameAddress = $_POST['nameAddress'];
    $homeAddress = $_POST['homeAddress'];
    $homeAddressDistrict = $_POST['homeAddressDistrict'];
    $homeAddressCountry = $_POST['homeAddressCountry'];

    //Validate Inputs
    if ($name == '' or $nameAddress == '' or $status == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Write to database
        try {
            $data = array('name' => $name, 'status' => $status, 'languageHomePrimary' => $languageHomePrimary, 'languageHomeSecondary' => $languageHomeSecondary, 'nameAddress' => $nameAddress, 'homeAddress' => $homeAddress, 'homeAddressDistrict' => $homeAddressDistrict, 'homeAddressCountry' => $homeAddressCountry);
            $sql = 'INSERT INTO pupilsightFamily SET name=:name, status=:status, languageHomePrimary=:languageHomePrimary, languageHomeSecondary=:languageHomeSecondary, nameAddress=:nameAddress, homeAddress=:homeAddress, homeAddressDistrict=:homeAddressDistrict, homeAddressCountry=:homeAddressCountry';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 6, '0', STR_PAD_LEFT);

        //Success 0
        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}
