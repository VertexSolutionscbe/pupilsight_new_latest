<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Data\ImportType;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\DataSet;
use Pupilsight\Domain\System\LogGateway;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, "/modules/System Admin/import_history.php")==false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
        ->add(__('Import From File'), 'import_manage.php')
        ->add(__('Import History'));

    // Get a list of available import options
    $importTypeList = ImportType::loadImportTypeList($pdo, false);

    $logGateway = $container->get(LogGateway::class);
    $logsByType = $logGateway->selectLogsByModuleAndTitle('System Admin', 'Import - %')->fetchAll();

    $logsByType = array_map(function ($log) use (&$importTypeList) {
        $log['data'] = isset($log['serialisedArray'])? unserialize($log['serialisedArray']) : [];
        $log['importType'] = @$importTypeList[$log['data']['type']];
        return $log['importType'] ? $log : null;
    }, $logsByType);
    $logsByType = array_filter($logsByType);

    $table = DataTable::create('importHistory');
    $table->setTitle(__('Import History'));

    $table->addColumn('timestamp', __('Date'))
        ->format(Format::using('dateTime', 'timestamp'));

    $table->addColumn('user', __('User'))
        ->format(Format::using('name', ['', 'preferredName', 'surname', 'Staff', false, true]));

    $table->addColumn('category', __('Category'))
        ->format(function ($log) {
            return $log['importType']->getDetail('category');
        });

    $table->addColumn('name', __('Name'))
        ->format(function ($log) {
            return $log['importType']->getDetail('name');
        });
        
    $table->addColumn('details', __('Details'))
        ->format(function ($log) {
            return !empty($log['data']['success']) ? __('Success') : __('Failed');
        });

    $table->addActionColumn()
        ->addParam('pupilsightLogID')
        ->format(function ($importType, $actions) {
            $actions->addAction('view', __('View'))
                ->modalWindow('600', '550')
                ->setURL('/modules/System Admin/import_history_view.php');
        });

    echo $table->render(new DataSet($logsByType));
}
