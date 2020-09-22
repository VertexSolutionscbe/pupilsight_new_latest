<?php
/*
Pupilsight, Flexible & Open School System
*/

global $pupilsight, $guid, $connection2;

// Prevent installer redirect
if (!file_exists(__DIR__ . '/../config.php')) {
    $_SERVER['PHP_SELF'] = 'installer/install.php';
}

require_once __DIR__ . '/../pupilsight.php';

if ($pupilsight->isInstalled()) {
    $installType = getSettingByScope($connection2, 'System', 'installType');
    if ($installType == 'Production') {
        die('ERROR: Test suite cannot run on a production system.'."\n");
    }
}