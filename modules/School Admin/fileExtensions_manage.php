<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\FileExtensionGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/fileExtensions_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage File Extensions'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $fileExtensionGateway = $container->get(FileExtensionGateway::class);

    // QUERY
    $criteria = $fileExtensionGateway->newQueryCriteria()
        ->sortBy('extension')
        ->fromPOST();

    $fileExtensions = $fileExtensionGateway->queryFileExtensions($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('fileExtensionManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/School Admin/fileExtensions_manage_add.php')
        ->displayLabel();

    $table->addColumn('extension', __('Extension'));
    $table->addColumn('name', __('Name'))->translatable();
    $table->addColumn('type', __('Type'))->translatable();
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightFileExtensionID')
        ->format(function ($fileExtension, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/fileExtensions_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/fileExtensions_manage_delete.php');
        });

    echo $table->render($fileExtensions);
}
