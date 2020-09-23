<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide include
include '../../pupilsight.php';

if (empty($_SESSION[$guid]['pupilsightPersonID']) || empty($_SESSION[$guid]['pupilsightRoleIDPrimary'])) {
    die(__('Your request failed because you do not have access to this action.'));
} elseif (getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2) != 'Staff') {
    die(__('Your request failed because you do not have access to this action.'));
} else {
    $data = array('pupilsightPersonID' => $_POST['pupilsightPersonID'] ?? '');
    $sql = "SELECT image_240 FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID";

    echo $pdo->selectOne($sql, $data);
}
