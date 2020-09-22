<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\System\I18nGateway;

include '../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$pupilsighti18nID = $_POST['pupilsighti18nID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/System Admin/i18n_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/i18n_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    if (empty($pupilsighti18nID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        $i18nGateway = $container->get(I18nGateway::class);
        $i18n = $i18nGateway->getI18nByID($pupilsighti18nID);

        if (empty($i18n)) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        }

        // Download & install the required language files
        $installed = i18nFileInstall($_SESSION[$guid]['absolutePath'], $i18n['code']);

        // Tag this i18n with the current version it was installed at
        $updated = $i18nGateway->updateI18nVersion($pupilsighti18nID, 'Y', $version);

        if (!$installed) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
            exit;
        } else if (!$updated) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
            exit;
        } else {
            $URL .= '&return=success0';
            header("Location: {$URL}");
            exit;
        }
    }
}
