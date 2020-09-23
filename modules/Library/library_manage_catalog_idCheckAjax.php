<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide include
include '../../pupilsight.php';

if (isActionAccessible($guid, $connection2, '/modules/Library/library_manage_catalog.php') == false) {
    die(__('Your request failed because you do not have access to this action.'));
} else {
    $pupilsightLibraryItemID = isset($_POST['pupilsightLibraryItemID'])? $_POST['pupilsightLibraryItemID'] : '';
    $id = isset($_POST['id'])? $_POST['id'] : (isset($_POST['idCheck'])? $_POST['idCheck'] : '');

    $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID, 'id' => $id);
    $sql = "SELECT COUNT(*) FROM pupilsightLibraryItem WHERE pupilsightLibraryItemID<>:pupilsightLibraryItemID AND id=:id";
    $result = $pdo->executeQuery($data, $sql);

    echo ($result && $result->rowCount() == 1)? $result->fetchColumn(0) : -1;
}
