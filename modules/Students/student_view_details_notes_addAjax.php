<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$pupilsightStudentNoteCategoryID = $_GET['pupilsightStudentNoteCategoryID'];

if ($pupilsightStudentNoteCategoryID != '') {
    try {
        $data = array('pupilsightStudentNoteCategoryID' => $pupilsightStudentNoteCategoryID);
        $sql = 'SELECT * FROM pupilsightStudentNoteCategory WHERE pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }
    if ($result->rowCount() == 1) {
        $row = $result->fetch();
        echo $row['template'];
    }
}
