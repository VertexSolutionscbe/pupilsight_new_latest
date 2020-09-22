<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';

try {
    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
    $sql = "UPDATE pupilsightStaff SET smartWorkflowHelp='N' WHERE pupilsightPersonID=:pupilsightPersonID";
    $result = $connection2->prepare($sql);
    $result->execute($data);
} catch (PDOException $e) {
}
