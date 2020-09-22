<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$page = $container->get('page');

$id = $_GET['id'];
$mode = null;
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
}
if ($mode == '') {
    $mode = 'masterAdd';
}
$pupilsightUnitBlockID = null;
if (isset($_GET['pupilsightUnitBlockID'])) {
    $pupilsightUnitBlockID = $_GET['pupilsightUnitBlockID'];
}

//IF UNIT DOES NOT CONTAIN HYPHEN, IT IS A Pupilsight UNIT
$pupilsightUnitID = null;
if (isset($_GET['pupilsightUnitID'])) {
    $pupilsightUnitID = $_GET['pupilsightUnitID'];
}

if ($pupilsightUnitBlockID != '') {
    try {
        $data = array('pupilsightUnitBlockID' => $pupilsightUnitBlockID);
        $sql = 'SELECT * FROM pupilsightUnitBlock WHERE pupilsightUnitBlockID=:pupilsightUnitBlockID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }
    if ($result->rowCount() == 1) {
        $row = $result->fetch();
        $title = $row['title'];
        $type = $row['type'];
        $length = $row['length'];
        $contents = $row['contents'];
        $teachersNotes = $row['teachersNotes'];
    }
} else {
    $title = '';
    $type = '';
    $length = '';
    $contents = getSettingByScope($connection2, 'Planner', 'smartBlockTemplate');
    $teachersNotes = '';
}

makeBlock($guid,  $connection2, $id, $mode, $title, $type, $length, $contents, 'N', $pupilsightUnitBlockID, '', $teachersNotes, false);
