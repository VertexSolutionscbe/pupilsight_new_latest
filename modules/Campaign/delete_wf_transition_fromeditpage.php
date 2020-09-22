<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/transitionsList.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/transitionDeleteProcess.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $id = $_POST['id'];
    $wid = $_POST['wid'];
    $cid = $_POST['cid'];

    $data = array('id' => $id);
    $sql = 'DELETE FROM workflow_transition WHERE id=:id';
    $result = $connection2->prepare($sql);
    $result->execute($data);
    
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/edit_wf_transition.php&id='.$cid.'&wid='.$wid.'';
    //header("Location: {$URL}");
    echo $URL;
}
