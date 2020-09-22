<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fileExtensions_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/fileExtensions_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $extension = strtolower($_POST['extension']);
    $name = $_POST['name'];
    $type = $_POST['type'];

    $illegalFileExtensions = Pupilsight\FileUploader::getIllegalFileExtensions();

    if ($extension == '' or $name == '' or $type == '' or in_array($extension, $illegalFileExtensions)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('extension' => $extension);
            $sql = 'SELECT * FROM pupilsightFileExtension WHERE extension=:extension';
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
            //Write to database
            try {
                $data = array('extension' => $extension, 'name' => $name, 'type' => $type);
                $sql = 'INSERT INTO pupilsightFileExtension SET extension=:extension, name=:name, type=:type';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 4, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
