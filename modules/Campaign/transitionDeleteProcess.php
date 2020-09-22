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

    $data = array('id' => $id);
    $sql = 'DELETE FROM campaign_transitions_form_map WHERE id=:id';
    $result = $connection2->prepare($sql);
    $result->execute($data);
    
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/transitionsList.php';
    //header("Location: {$URL}");
    echo $URL;
}
