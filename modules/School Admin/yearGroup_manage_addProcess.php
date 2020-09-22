<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/yearGroup_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/yearGroup_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];
    $sequenceNumber = $_POST['sequenceNumber'];
    $pupilsightPersonIDHOY = $_POST['pupilsightPersonIDHOY'];

    if ($name == '' or $nameShort == '' or $sequenceNumber == '' or is_numeric($sequenceNumber) == false) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber);
            $sql = 'SELECT * FROM pupilsightYearGroup WHERE name=:name OR nameShort=:nameShort OR sequenceNumber=:sequenceNumber';
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
                $data = array('name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber, 'pupilsightPersonIDHOY' => $pupilsightPersonIDHOY);
                $sql = 'INSERT INTO pupilsightYearGroup SET name=:name, nameShort=:nameShort, sequenceNumber=:sequenceNumber, pupilsightPersonIDHOY=:pupilsightPersonIDHOY';
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
