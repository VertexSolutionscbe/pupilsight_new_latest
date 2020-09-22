<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/house_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/house_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];

    if ($name == '' or $nameShort == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort);
            $sql = 'SELECT * FROM pupilsightHouse WHERE name=:name OR nameShort=:nameShort';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Deal with file upload
            $logo = '';
            $imageFail = false;
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
                $data = array('name' => $name, 'nameShort' => $nameShort, 'logo' => $logo);
                $sql = 'INSERT INTO pupilsightHouse SET name=:name, nameShort=:nameShort, logo=:logo';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            if ($imageFail) {
                $URL .= "&return=warning1&editID=$AI";
                header("Location: {$URL}");
            } else {
                $URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
            }
        }
    }
}
