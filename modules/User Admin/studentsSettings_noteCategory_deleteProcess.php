<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStudentNoteCategoryID = $_GET['pupilsightStudentNoteCategoryID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/studentsSettings_noteCategory_delete.php&pupilsightStudentNoteCategoryID='.$pupilsightStudentNoteCategoryID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/studentsSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/studentsSettings_noteCategory_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightStudentNoteCategoryID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightStudentNoteCategoryID' => $pupilsightStudentNoteCategoryID);
            $sql = 'SELECT * FROM pupilsightStudentNoteCategory WHERE pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID';
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
                $data = array('pupilsightStudentNoteCategoryID' => $pupilsightStudentNoteCategoryID);
                $sql = 'DELETE FROM pupilsightStudentNoteCategory WHERE pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID';
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
