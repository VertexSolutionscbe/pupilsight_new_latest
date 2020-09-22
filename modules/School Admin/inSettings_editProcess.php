<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightINDescriptorID = $_GET['pupilsightINDescriptorID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/inSettings_edit.php&pupilsightINDescriptorID=$pupilsightINDescriptorID";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/inSettings_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightINDescriptorID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightINDescriptorID' => $pupilsightINDescriptorID);
            $sql = 'SELECT * FROM pupilsightINDescriptor WHERE pupilsightINDescriptorID=:pupilsightINDescriptorID';
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
            $name = $_POST['name'];
            $nameShort = $_POST['nameShort'];
            $sequenceNumber = $_POST['sequenceNumber'];
            $description = $_POST['description'];

            //Validate Inputs
            if ($name == '' or $nameShort == '' or $sequenceNumber == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber, 'pupilsightINDescriptorID' => $pupilsightINDescriptorID);
                    $sql = 'SELECT * FROM pupilsightINDescriptor WHERE (name=:name OR nameShort=:nameShort OR sequenceNumber=:sequenceNumber) AND NOT pupilsightINDescriptorID=:pupilsightINDescriptorID';
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
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber, 'description' => $description, 'pupilsightINDescriptorID' => $pupilsightINDescriptorID);
                        $sql = 'UPDATE pupilsightINDescriptor SET name=:name, nameShort=:nameShort, sequenceNumber=:sequenceNumber, description=:description WHERE pupilsightINDescriptorID=:pupilsightINDescriptorID';
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
