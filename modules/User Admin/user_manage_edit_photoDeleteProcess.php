<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$size = $_GET['size'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/User Admin/user_manage_edit.php&pupilsightPersonID=$pupilsightPersonID&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if planner specified
    if ($pupilsightPersonID == '' or $size == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
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
            //UPDATE
            try {
                $sizeField = null;
                if ($size == '240') {
                    $sizeField = 'image_240';
                } elseif ($size == 'birthCertificate') {
                    $sizeField = 'birthCertificateScan';
                } elseif ($size == 'passport') {
                    $sizeField = 'citizenship1PassportScan';
                } elseif ($size == 'id') {
                    $sizeField = 'nationalIDCardScan';
                }
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = "UPDATE pupilsightPerson SET $sizeField='' WHERE pupilsightPersonID=:pupilsightPersonID";
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
