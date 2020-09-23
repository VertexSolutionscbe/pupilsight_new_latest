<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\TimetableColumnGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttColumn_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Columns'), 'ttColumn.php')
        ->add(__('Edit Column'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $ttColumnGateway = $container->get(TimetableColumnGateway::class);

    //Check if school year specified
    $pupilsightTTColumnID = $_GET['pupilsightTTColumnID'];
    if ($pupilsightTTColumnID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {

        $values = $ttColumnGateway->getTTColumnByID($pupilsightTTColumnID);

        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/ttColumn_editProcess.php');

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightTTColumnID', $values['pupilsightTTColumnID']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique for this school year.'));
                $row->addTextField('name')->maxLength(30)->required();

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'));
                $row->addTextField('nameShort')->maxLength(12)->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();

            echo '<h2>';
            echo __('Edit Column Rows');
            echo '</h2>';

            $rows = $ttColumnGateway->selectTTColumnRowsByID($pupilsightTTColumnID);

            // DATA TABLE
            $table = DataTable::create('timetableColumnRows');

            $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/Timetable Admin/ttColumn_edit_row_add.php')
                ->addParam('pupilsightTTColumnID', $pupilsightTTColumnID)
                ->displayLabel();

            $table->addColumn('name', __('Name'));
            $table->addColumn('nameShort', __('Short Name'));
            $table->addColumn('time', __('Time'))->format(Format::using('timeRange', ['timeStart', 'timeEnd']));
            $table->addColumn('type', __('Type'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightTTColumnID', $pupilsightTTColumnID)
                ->addParam('pupilsightTTColumnRowID')
                ->format(function ($values, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/ttColumn_edit_row_edit.php');

                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/ttColumn_edit_row_delete.php');
                });

            echo $table->render($rows->toDataSet());
        }
    }
}
