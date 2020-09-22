<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$mode = $_GET['mode'];
if ($mode == 'Add') {
    try {
        $data = array('pupilsightRubricID' => $_GET['pupilsightRubricID'], 'pupilsightPersonID' => $_GET['pupilsightPersonID'], 'pupilsightRubricCellID' => $_GET['pupilsightRubricCellID'], 'contextDBTable' => $_GET['contextDBTable'], 'contextDBTableID' => $_GET['contextDBTableID']);
        $sql = 'INSERT INTO pupilsightRubricEntry SET pupilsightRubricID=:pupilsightRubricID, pupilsightPersonID=:pupilsightPersonID, pupilsightRubricCellID=:pupilsightRubricCellID, contextDBTable=:contextDBTable, contextDBTableID=:contextDBTableID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }
}
if ($mode == 'Remove') {
    try {
        $data = array('pupilsightRubricID' => $_GET['pupilsightRubricID'], 'pupilsightPersonID' => $_GET['pupilsightPersonID'], 'pupilsightRubricCellID' => $_GET['pupilsightRubricCellID'], 'contextDBTable' => $_GET['contextDBTable'], 'contextDBTableID' => $_GET['contextDBTableID']);
        $sql = 'DELETE FROM pupilsightRubricEntry WHERE pupilsightRubricID=:pupilsightRubricID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightRubricCellID=:pupilsightRubricCellID AND contextDBTable=:contextDBTable AND contextDBTableID=:contextDBTableID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }
}
