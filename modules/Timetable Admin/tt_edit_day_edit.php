<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\TimetableDayGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'] ?? '';
    //print_r($pupilsightYearGroupID);die();
    $pupilsightProgramID = $_GET['pupilsightProgramID'] ?? '';
    $pupilsightTTDayID = $_GET['pupilsightTTDayID'] ?? '';
    $pupilsightTTID = $_GET['pupilsightTTID'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';   
    if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' or $pupilsightProgramID=='' or $pupilsightYearGroupID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {

        $timetableDayGateway = $container->get(TimetableDayGateway::class);
        $values = $timetableDayGateway->getTTDayByID($pupilsightTTDayID);

        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $page->breadcrumbs
                ->add(__('Manage Timetables'), 'tt.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
                ->add(__('Edit Timetable'), 'tt_edit.php', ['pupilsightTTID' => $pupilsightTTID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID])
                ->add(__('Edit Timetable Day'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/tt_edit_day_editProcess.php?pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID");

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightTTID', $pupilsightTTID);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
            $form->addHiddenValue('pupilsightTTColumnID', $values['pupilsightTTColumnID']);

            $row = $form->addRow();
                $row->addLabel('schoolYear', __('School Year'));
                $row->addTextField('schoolYear')->maxLength(20)->required()->readonly()->setValue($values['schoolYear']);

            $row = $form->addRow();
                $row->addLabel('ttName', __('Timetable'));
                $row->addTextField('ttName')->maxLength(20)->required()->readonly()->setValue($values['ttName']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique for this school year.'));
                $row->addTextField('name')->maxLength(12)->required();

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique for this school year.'));
                $row->addTextField('nameShort')->maxLength(4)->required();

            $row = $form->addRow();
                $row->addLabel('color', __('Header Background Colour'))->description(__('RGB Hex value, without leading #.'));
                $row->addTextField('color')->maxLength(6);

            $row = $form->addRow();
                $row->addLabel('fontColor', __('Header Font Colour'))->description(__('RGB Hex value, without leading #.'));
                $row->addTextField('fontColor')->maxLength(6);

            $row = $form->addRow();
                $row->addLabel('columnName', __('Timetable Column'));
                $row->addTextField('columnName')->maxLength(30)->required()->readonly();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();

            echo '<h2>';
            echo __('Edit Classes by Period');
            echo '</h2>';

            $ttDayRows = $timetableDayGateway->selectTTDayRowsByID($pupilsightTTDayID);

            // DATA TABLE
            $table = DataTable::create('timetableDayRows');

            $table->addColumn('name', __('Name'));
            $table->addColumn('nameShort', __('Short Name'));
            $table->addColumn('time', __('Time'))->format(Format::using('timeRange', ['timeStart', 'timeEnd']));
            $table->addColumn('type', __('Type'));
            $table->addColumn('classCount', __('Classes'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                ->addParam('pupilsightTTID', $pupilsightTTID)
                ->addParam('pupilsightTTDayID', $pupilsightTTDayID)
                ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
                ->addParam('pupilsightProgramID', $pupilsightProgramID)
                ->addParam('pupilsightTTColumnRowID')
                ->format(function ($values, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/tt_edit_day_edit_class.php');
                });

            echo $table->render($ttDayRows->toDataSet());
        }
    }
}
