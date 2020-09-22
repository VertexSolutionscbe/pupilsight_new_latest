<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightMarkbookColumnID = $_GET['pupilsightMarkbookColumnID'];
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$return = $_GET['return'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Markbook/$return";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_view.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    try {
        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID);
        $sql = 'SELECT * FROM pupilsightMarkbookColumn JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE (pupilsightMarkbookColumn.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID)';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit();
    }

    if ($result->rowCount() != 1) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Proceed!
		include './markbook_viewExportContents.php';
    }
}
