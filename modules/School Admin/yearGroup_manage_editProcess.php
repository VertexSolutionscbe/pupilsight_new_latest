<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/yearGroup_manage_edit.php&pupilsightYearGroupID='.$pupilsightYearGroupID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/yearGroup_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightYearGroupID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightYearGroupID' => $pupilsightYearGroupID);
            $sql = 'SELECT * FROM pupilsightYearGroup WHERE pupilsightYearGroupID=:pupilsightYearGroupID';
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
            $sequenceNumber = $_POST['sequenceNumber'];
            $pupilsightPersonIDHOY = $_POST['pupilsightPersonIDHOY'];

            if ($name == '' or $nameShort == '' or $sequenceNumber == '' or is_numeric($sequenceNumber) == false) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
                    $sql = 'SELECT * FROM pupilsightYearGroup WHERE (name=:name OR nameShort=:nameShort OR sequenceNumber=:sequenceNumber) AND NOT pupilsightYearGroupID=:pupilsightYearGroupID';
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
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'sequenceNumber' => $sequenceNumber, 'pupilsightPersonIDHOY' => $pupilsightPersonIDHOY, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
                        $sql = 'UPDATE pupilsightYearGroup SET name=:name, nameShort=:nameShort, sequenceNumber=:sequenceNumber, pupilsightPersonIDHOY=:pupilsightPersonIDHOY WHERE pupilsightYearGroupID=:pupilsightYearGroupID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                    
                    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/yearGroup_manage.php';
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
