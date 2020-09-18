<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Data\ImportType;
use Pupilsight\Domain\System\LogGateway;

if (isActionAccessible($guid, $connection2, "/modules/System Admin/import_history_view.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $pupilsightLogID = $_GET['pupilsightLogID'] ?? 0;

    $logGateway = $container->get(LogGateway::class);
    $importLog = $logGateway->getLogByID($pupilsightLogID);

    if (empty($importLog)) {
        $page->addError(__('There are no records to display.'));
        return;
    }

    $importData = isset($importLog['serialisedArray'])? unserialize($importLog['serialisedArray']) : [];
    $importData['log'] = $importLog;
    $importResults = $importData['results'] ?? [];

    if (empty($importData['results']) || !isset($importData['type'])) {
        $page->addError(__('There are no records to display.'));
        return;
    }

    $importType = ImportType::loadImportType($importData['type'], $pdo);
    $importData['name'] = $importType->getDetail('name');

    echo $page->fetchFromTemplate('importer.twig.html', array_merge($importData, $importResults));
}
