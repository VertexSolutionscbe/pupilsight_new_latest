<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightHouseID = $_GET['pupilsightHouseID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/house_manage_edit.php&pupilsightHouseID='.$pupilsightHouseID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/house_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightHouseID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightHouseID' => $pupilsightHouseID);
            $sql = 'SELECT * FROM pupilsightHouse WHERE pupilsightHouseID=:pupilsightHouseID';
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
            $name = $_POST['name'];
            $nameShort = $_POST['nameShort'];

            if ($name == '' or $nameShort == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $dataCheck = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightHouseID' => $pupilsightHouseID);
                    $sqlCheck = 'SELECT * FROM pupilsightHouse WHERE (name=:name OR nameShort=:nameShort) AND NOT pupilsightHouseID=:pupilsightHouseID';
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
                    $row = $result->fetch();

                    //Sort out logo
                    $imageFail = false;
                    $logo = $_POST['logo'];
                    if (!empty($_FILES['file1']['tmp_name'])) {
                        $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
                        
                        $file = (isset($_FILES['file1']))? $_FILES['file1'] : null;

                        // Upload the file, return the /uploads relative path
                        $logo = $fileUploader->uploadFromPost($file, $name);

                        if (empty($logo)) {
                            $imageFail = true;
                        }
                    }

                    //Write to database
                    try {
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'logo' => $logo, 'pupilsightHouseID' => $pupilsightHouseID);
                        $sql = 'UPDATE pupilsightHouse SET name=:name, nameShort=:nameShort, logo=:logo WHERE pupilsightHouseID=:pupilsightHouseID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($imageFail) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                    } else {
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
