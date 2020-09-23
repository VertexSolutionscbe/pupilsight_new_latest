<?php
/*
Pupilsight, Flexible & Open School System

*/

use Pupilsight\Domain\System\ModuleGateway;
use Pupilsight\Domain\DataUpdater\DataUpdaterGateway;
use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Domain\User\UserGateway;

/**
 * BOOTSTRAP
 *
 * The bootstrapping process creates the essential variables and services for
 * Pupilsight. These are required for all scripts: page views, CLI and API.
 */
// Pupilsight system-wide include
require_once './pupilsight.php';

// Module include: Messenger has a bug where files have been relying on these
// functions because this file was included via getNotificationTray()
// TODO: Fix that :)
require_once './modules/Messenger/moduleFunctions.php';

?>
<html>
    <body style='margin:0;'>
        <iframe src="http://ivy-school.thimpress.com/demo-3" style="width:100%;height:100vh;border:0;">
    </body>
</html>
