<?php
/*
Pupilsight, Flexible & Open School System
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'pupilsight.php';

include 'core.php';

//Proceed
$type = '';
if (isset($_POST['type'])) {
    $type = $_POST['type'];
} else {
    $result['status'] = 2;
    $result['msg'] = 'Invalid Message Parameter.';
}

/* open Description Indicator */
if ($type == 'unPublishEvent') {
    try {
        $eventid = getPost("eventid");
        if (empty($eventid)) {
            $result['status'] = 1;
            $result['msg'] = "Invalid Event ID.";
        } else {
            $squ = "update calendar_event set is_publish='1' ";
            $squ .= "and udt='" . $ts . "' ";
            $squ .= "where id='" . $eventid . "' ";

            $connection2->query($squ);
            $result['status'] = 1;
            $result['msg'] = "Event unpublished successfully.";
        }

        //echo $squ;
    } catch (Exception $ex) {
        $result['status'] = 2;
        $result['msg'] = $ex->getMessage();
    }
}
if ($result) {
    echo json_encode($result);
}
