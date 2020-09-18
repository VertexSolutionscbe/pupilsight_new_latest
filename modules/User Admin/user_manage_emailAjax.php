<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide include
include '../../pupilsight.php';

if (empty($_SESSION[$guid]['pupilsightPersonID']) || empty($_SESSION[$guid]['pupilsightRoleIDPrimary'])) {
    die(__('Your request failed because you do not have access to this action.'));
} else {
    $pupilsightPersonID = isset($_POST['pupilsightPersonID'])? $_POST['pupilsightPersonID'] : '';
    $email = isset($_POST['email'])? $_POST['email'] : (isset($_POST['value'])? $_POST['value'] : '');

    $data = array('pupilsightPersonID' => $pupilsightPersonID, 'email' => $email);
    $sql = "SELECT COUNT(*) FROM pupilsightPerson WHERE email=:email AND pupilsightPersonID<>:pupilsightPersonID";
    $result = $pdo->executeQuery($data, $sql);

    echo ($result && $result->rowCount() == 1)? $result->fetchColumn(0) : -1;
}
