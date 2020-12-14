<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\DataSet;
use Pupilsight\Domain\System\LogGateway;

require __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, "/modules/Finance/fee_master_manage.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
    ->add(__('Manage Bank And Payment Mode'), 'fee_master_manage.php')
    ->add(__('Import Bank And Payment Mode From File'));

    echo "<h1>Import Bank And Payment Mode</h1>";
    $data = '<table class="table">
            <thead>
                <tr>
                    <th>Category</td>
                    <th>Import</th>
                    <th>Export</th>
                </tr>
            </thead>
            <tr>
                <td>Bank And Payment Mode</td>
                <td><a href="index.php?q=/modules/Finance/import_fee_master_run.php"><i class="mdi mdi-cloud-upload-outline mdi-24px"></i></a></td>
                <td><a href="public/BankPaymentMode.csv" download><i class="mdi mdi-file-download mdi-24px"></i></a></td>
            </tr>
            </table>';

    echo $data;        
}
