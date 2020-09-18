<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';
$_SESSION[$guid]['sidebarExtra'] = '';

//Check to see if system settings are set from databases
if (empty($_SESSION[$guid]['systemSettingsSet'])) {
    getSystemSettings($guid, $connection2);
}

if (empty($_SESSION[$guid]['systemSettingsSet']) || empty($_SESSION[$guid]['pupilsightPersonID'])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$_SESSION[$guid]['address'] = isset($_GET['q'])? $_GET['q'] : '';
$_SESSION[$guid]['module'] = getModuleName($_SESSION[$guid]['address']);
$_SESSION[$guid]['action'] = getActionName($_SESSION[$guid]['address']);

if (empty($_SESSION[$guid]['address']) || strstr($_SESSION[$guid]['address'], '..') != false) {
    header("HTTP/1.1 403 Forbidden");
    exit;
} else {
    if (is_file('./'.$_SESSION[$guid]['address'])) {
        include './'.$_SESSION[$guid]['address'];
    } else {
        header("HTTP/1.1 404 Not Found");
        exit;
    }
}
