<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightProgramID = $_GET['pupilsightProgramID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/program_manage_edit.php&pupilsightProgramID='.$pupilsightProgramID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/program_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightProgramID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightProgramID' => $pupilsightProgramID);
            $sql = 'SELECT * FROM pupilsightProgram WHERE pupilsightProgramID=:pupilsightProgramID';
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
            // $nameShort = $_POST['nameShort'];
            // $sequenceNumber = $_POST['sequenceNumber'];
            

            if ($name == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'pupilsightProgramID' => $pupilsightProgramID);
                    $sql = 'SELECT * FROM pupilsightProgram WHERE (name=:name) AND NOT pupilsightProgramID=:pupilsightProgramID';
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
                        $data = array('name' => $name,  'pupilsightProgramID' => $pupilsightProgramID);
                        $sql = 'UPDATE pupilsightProgram SET name=:name WHERE pupilsightProgramID=:pupilsightProgramID';
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
