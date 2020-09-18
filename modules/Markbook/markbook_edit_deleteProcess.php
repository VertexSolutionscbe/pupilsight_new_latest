<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$pupilsightMarkbookColumnID = $_GET['pupilsightMarkbookColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/markbook_edit_delete.php&pupilsightMarkbookColumnID=$pupilsightMarkbookColumnID&pupilsightCourseClassID=$pupilsightCourseClassID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/markbook_view.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightMarkbookColumnID == '' or $pupilsightCourseClassID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID AND pupilsightCourseClassID=:pupilsightCourseClassID';
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
            //Write to database
            try {
                $data = array('pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID);
                $sql = 'DELETE FROM pupilsightMarkbookColumn WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
