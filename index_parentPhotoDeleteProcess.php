<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php';

//Proceed!
//Check if planner specified
if ($pupilsightPersonID == '' or $pupilsightPersonID != $_SESSION[$guid]['pupilsightPersonID']) {
    $URL .= '?return=error1';
    header("Location: {$URL}");
} else {
    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL .= '?return=error2';
        header("Location: {$URL}");
        exit();
    }

    if ($result->rowCount() != 1) {
        $URL .= '?return=error2';
        header("Location: {$URL}");
    } else {
        //UPDATE
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = "UPDATE pupilsightPerson SET image_240='' WHERE pupilsightPersonID=:pupilsightPersonID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '?return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Update session variables
        $_SESSION[$guid]['image_240'] = '';

        //Clear cusotm sidebar
        unset($_SESSION[$guid]['index_customSidebar.php']);

        $URL .= '?return=success0';
        //Success 0
        header("Location: {$URL}");
    }
}
