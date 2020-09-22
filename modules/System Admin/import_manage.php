<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Data\ImportType;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\DataSet;
use Pupilsight\Domain\System\LogGateway;

require __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, "/modules/System Admin/import_manage.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Import From File'));

    $logGateway = $container->get(LogGateway::class);
    $logsByType = $logGateway->selectLogsByModuleAndTitle('System Admin', 'Import - %')->fetchGrouped();

    // Get a list of available import options
    $importTypeList = ImportType::loadImportTypeList($pdo, false);
    //print_r($importTypeList);
    
    // Build an array of combined import type info and log data
    $importTypeGroups = array_reduce($importTypeList, function ($group, $importType) use ($guid, $connection2, $logsByType) {
        if ($importType->isValid()) {
            $type = $importType->getDetail('type');
            $log = $logsByType['Import - '.$type] ?? [];

            $group[$importType->getDetail('grouping', 'System')][] = [
                'type'         => $type,
                'log'          => current($log),
                'category'     => $importType->getDetail('category'),
                'name'         => $importType->getDetail('name'),
                'isAccessible' => $importType->isImportAccessible($guid, $connection2),
            ];
        }
        return $group;
    }, []);

    foreach ($importTypeGroups as $importGroupName => $importTypes) {
        $table = DataTable::create('imports');
        $table->setTitle(__($importGroupName));

        $table->addColumn('category', __('Category'))
            ->width('20%')
            ->format(function ($importType) {
                return __($importType['category']);
            });
        $table->addColumn('name', __('Name'))
            ->format(function ($importType) {
                $nameParts = array_map('trim', explode('-', $importType['name']));
                return implode(' - ', array_map('__', $nameParts));
            });
        $table->addColumn('timestamp', __('Last Import'))
            ->width('25%')
            ->format(function ($importType) use ($guid) {
                if ($log = $importType['log']) {
                    $text = Format::dateReadable($log['timestamp']);
                    $url = $_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/System Admin/import_history_view.php&pupilsightLogID='.$log['pupilsightLogID'].'&width=600&height=550';
                    $title = Format::dateTime($log['timestamp']).' - '.Format::nameList([$log]);
                    return Format::link($url, $text, ['title' => $title, 'class' => 'thickbox']);
                }
                return '';
            });

        $table->addActionColumn()
            ->addParam('type')
            ->format(function ($importType, $actions) {
                if ($importType['isAccessible']) {
                    $actions->addAction('import', __('Import'))
                        ->setIcon('upload')
                        ->setURL('/modules/System Admin/import_run.php');

                    $actions->addAction('export', __('Export Columns'))
                        ->directLink()
                        ->addParam('q', $_GET['q'])
                        ->addParam('data', 0)
                        ->setIcon('download')
                        ->setURL('/modules/System Admin/export_run.php');
                }
            });

        echo $table->render(new DataSet($importTypes));
    }
}
