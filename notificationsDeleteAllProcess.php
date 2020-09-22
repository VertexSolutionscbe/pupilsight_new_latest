<?php
/*
Pupilsight, Flexible & Open School System
*/

include './pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=notifications.php';

try {
    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
    $sql = 'DELETE FROM pupilsightNotification WHERE pupilsightPersonID=:pupilsightPersonID';
    $result = $connection2->prepare($sql);
    $result->execute($data);
} catch (PDOException $e) {
    $URL = $URL.'&return=error2';
    header("Location: {$URL}");
    exit();
}

$URL = $URL.'&return=success0';
header("Location: {$URL}");
