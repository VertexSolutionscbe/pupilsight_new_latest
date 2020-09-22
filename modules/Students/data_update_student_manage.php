<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\DataSet;
use Pupilsight\Domain\System\LogGateway;

require __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, "/modules/Students/data_update_student_manage.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Student Data Update From File'));

    echo "<h1>Import Student's Data</h1>";
    $data = '<table class="table">
            <thead>
                <tr>
                    <th>Category</td>
                    <th>Import</th>
                    <th>Export</th>
                </tr>
            </thead>
            <tr>
                <td>Student</td>
                <td><a href="index.php?q=/modules/Students/data_update_student_run.php"><i class="mdi mdi-cloud-upload-outline mdi-24px"></i></a></td>
                <td><a href="index.php?q=/modules/Students/data_update_export_student_run.php"><i class="mdi mdi-file-download mdi-24px"></i></a></td>
            </tr>
            </table>';

    echo $data;        
}
