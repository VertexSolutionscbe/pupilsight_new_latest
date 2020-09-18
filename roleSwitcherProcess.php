<?php
/*
Pupilsight, Flexible & Open School System
*/

// Pupilsight system-wide include
require_once './pupilsight.php';

$URL = './index.php';
$role = (isset($_GET['pupilsightRoleID']))? $_GET['pupilsightRoleID'] : '';
$role = str_pad(intval($role), 3, '0', STR_PAD_LEFT);

$_SESSION[$guid]['pageLoads'] = null;

//Check for parameter
if (empty(intval($role))) {
    $URL .= '?return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Check for access to role
    try {
        $data = array('username' => $_SESSION[$guid]['username'], 'pupilsightRoleID' => $role);
        $sql = 'SELECT pupilsightPerson.pupilsightPersonID
                FROM pupilsightPerson JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll))
                WHERE (pupilsightPerson.username=:username) AND pupilsightRole.pupilsightRoleID=:pupilsightRoleID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL .= '?return=error2';
        header("Location: {$URL}");
        exit;
    }

    if ($result->rowCount() != 1) {
        $URL .= '?return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        //Make the switch
        $pupilsight->session->set('pupilsightRoleIDCurrent', $role);

        // Reload cached FF actions
        $pupilsight->session->cacheFastFinderActions($role);

        // Clear the main menu from session cache
        $pupilsight->session->forget('menuMainItems');

        $URL .= '?return=success0';
        header("Location: {$URL}");
        exit;
    }
}
