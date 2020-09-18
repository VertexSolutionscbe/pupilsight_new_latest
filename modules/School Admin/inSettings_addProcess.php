<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/inSettings_add.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/inSettings_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];
    $sequenceNumber = $_POST['sequenceNumber'];
    $description = $_POST['description'];

    //Validate Inputs
    if ($name == '' or $nameShort == '' or $sequenceNumber == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber);
            $sql = 'SELECT * FROM pupilsightINDescriptor WHERE name=:name OR nameShort=:nameShort OR sequenceNumber=:sequenceNumber';
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
                $data = array('name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber, 'description' => $description);
                $sql = 'INSERT INTO pupilsightINDescriptor SET name=:name, nameShort=:nameShort, sequenceNumber=:sequenceNumber, description=:description';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
