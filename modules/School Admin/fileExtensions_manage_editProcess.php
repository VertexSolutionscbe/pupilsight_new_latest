<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFileExtensionID = $_GET['pupilsightFileExtensionID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fileExtensions_manage_edit.php&pupilsightFileExtensionID='.$pupilsightFileExtensionID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/fileExtensions_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFileExtensionID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFileExtensionID' => $pupilsightFileExtensionID);
            $sql = 'SELECT * FROM pupilsightFileExtension WHERE pupilsightFileExtensionID=:pupilsightFileExtensionID';
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
            $extension = strtolower($_POST['extension']);
            $name = $_POST['name'];
            $type = $_POST['type'];

            $illegalFileExtensions = Pupilsight\FileUploader::getIllegalFileExtensions();

            if ($extension == '' or $name == '' or $type == '' or in_array($extension, $illegalFileExtensions)) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'pupilsightFileExtensionID' => $pupilsightFileExtensionID);
                    $sql = 'SELECT * FROM pupilsightFileExtension WHERE (name=:name) AND NOT pupilsightFileExtensionID=:pupilsightFileExtensionID';
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
                        $data = array('extension' => $extension, 'name' => $name, 'type' => $type, 'pupilsightFileExtensionID' => $pupilsightFileExtensionID);
                        $sql = 'UPDATE pupilsightFileExtension SET extension=:extension, name=:name, type=:type WHERE pupilsightFileExtensionID=:pupilsightFileExtensionID';
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
