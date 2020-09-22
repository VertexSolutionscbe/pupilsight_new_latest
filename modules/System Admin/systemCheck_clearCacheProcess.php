<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include '../../config.php';

//Module includes
include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/System Admin/systemCheck.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/systemCheck.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Clear the templates cache folder
    removeDirectoryContents($_SESSION[$guid]['absolutePath'].'/uploads/cache');

    $URL .= '&return=success0';
    header("Location: {$URL}");
    exit;
}
