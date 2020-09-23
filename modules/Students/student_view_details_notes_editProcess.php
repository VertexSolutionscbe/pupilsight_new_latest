<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$subpage = $_GET['subpage'];
$pupilsightStudentNoteID = $_GET['pupilsightStudentNoteID'];
$allStudents = '';
if (isset($_GET['allStudents'])) {
    $allStudents = $_GET['allStudents'];
}
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/student_view_details_notes_edit.php&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search']."&subpage=Notes&pupilsightStudentNoteID=$pupilsightStudentNoteID&category=".$_GET['category']."&allStudents=$allStudents";

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details_notes_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $enableStudentNotes = getSettingByScope($connection2, 'Students', 'enableStudentNotes');
    if ($enableStudentNotes != 'Y') {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if note specified
        if ($pupilsightStudentNoteID == '' or $pupilsightPersonID == '' or $subpage == '') {
            echo 'Fatal error loading this page!';
        } else {
            try {
                $data = array('pupilsightStudentNoteID' => $pupilsightStudentNoteID, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = 'SELECT * FROM pupilsightStudentNote WHERE pupilsightStudentNoteID=:pupilsightStudentNoteID AND pupilsightPersonIDCreator=:pupilsightPersonIDCreator';
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
                $row = $result->fetch();
                //Validate Inputs
                $title = $_POST['title'];
                $pupilsightStudentNoteCategoryID = $_POST['pupilsightStudentNoteCategoryID'];
                if ($pupilsightStudentNoteCategoryID == '') {
                    $pupilsightStudentNoteCategoryID = null;
                }
                $note = $_POST['note'];

                if ($note == '') {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightStudentNoteCategoryID' => $pupilsightStudentNoteCategoryID, 'title' => $title, 'note' => $note, 'pupilsightStudentNoteID' => $pupilsightStudentNoteID);
                        $sql = 'UPDATE pupilsightStudentNote SET pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID, title=:title, note=:note WHERE pupilsightStudentNoteID=:pupilsightStudentNoteID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Attempt to write logo
                    setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], getModuleIDFromName($connection2, 'Students'), $_SESSION[$guid]['pupilsightPersonID'], 'Student Profile - Note Edit', array('pupilsightStudentNoteID' => $pupilsightStudentNoteID, 'noteOriginal' => $row['note'], 'noteNew' => $note), $_SERVER['REMOTE_ADDR']);

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
