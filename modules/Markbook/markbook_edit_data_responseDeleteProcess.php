<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightMarkbookColumnID = $_GET['pupilsightMarkbookColumnID'];
$pupilsightPersonID = $_GET['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Markbook/markbook_edit_data.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightMarkbookColumnID=$pupilsightMarkbookColumnID";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_data.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if planner specified
    if ($pupilsightPersonID == '' or $pupilsightCourseClassID == '' or $pupilsightMarkbookColumnID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID);
            $sql = "UPDATE pupilsightMarkbookEntry SET response='' WHERE pupilsightPersonIDStudent=:pupilsightPersonID AND pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $URL .= '&return=success0';
        //Success 0
        header("Location: {$URL}");
    }
}
