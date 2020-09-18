<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\TimetableColumnGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttColumn.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Columns'));
    echo '<p>';
    echo __('In Pupilsight a column is a holder for the structure of a day. A number of columns can be defined, and these can be tied to particular timetable days in the timetable interface.');
    echo '</p>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $ttColumnGateway = $container->get(TimetableColumnGateway::class);
    $columns = $ttColumnGateway->selectTTColumns();

    // DATA TABLE
    $table = DataTable::create('timetableColumns');

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Timetable Admin/ttColumn_add.php')
        ->displayLabel();

    $table->addColumn('name', __('Name'));
    $table->addColumn('nameShort', __('Short Name'));
    $table->addColumn('rowCount', __('Rows'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightTTColumnID')
        ->format(function ($values, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Timetable Admin/ttColumn_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Timetable Admin/ttColumn_delete.php');
        });

    echo $table->render($columns->toDataSet());
}
