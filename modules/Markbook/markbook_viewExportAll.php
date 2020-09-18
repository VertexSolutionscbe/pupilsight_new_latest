<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$return = $_GET['return'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Markbook/$return";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_view.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    try {
        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
        $sql = 'SELECT * FROM pupilsightMarkbookColumn JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit();
    }

    //Proceed!
	include './markbook_viewExportAllContents.php';
}
