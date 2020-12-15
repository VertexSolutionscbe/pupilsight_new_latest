<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\TimetableDayGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_exception.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightTTDayID = $_GET['pupilsightTTDayID'] ?? '';
    $pupilsightTTID = $_GET['pupilsightTTID'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'] ?? '';
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';

    if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' or $pupilsightTTColumnRowID == '' or $pupilsightCourseClassID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {

        $timetableDayGateway = $container->get(TimetableDayGateway::class);
        $values = $timetableDayGateway->getTTDayRowClassByID($pupilsightTTDayID, $pupilsightTTColumnRowID, $pupilsightCourseClassID);
//print_r($timetableDayGateway);die();
        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $pupilsightTTDayRowClassID = $values['pupilsightTTDayRowClassID'];

            $urlParams = [
                'pupilsightTTDayID' => $pupilsightTTDayID,
                'pupilsightTTID' => $pupilsightTTID,
                'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
                'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID
            ];

            $page->breadcrumbs
                ->add(__('Manage Timetables'), 'tt.php', $urlParams)
                ->add(__('Edit Timetable'), 'tt_edit.php', $urlParams)
                ->add(__('Edit Timetable Day'), 'tt_edit_day_edit.php', $urlParams)
                ->add(__('Classes in Period'), 'tt_edit_day_edit_class.php', $urlParams)
                ->add(__('Class List Exception'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $ttDayRowClassExceptions = $timetableDayGateway->selectTTDayRowClassExceptionsByID($pupilsightTTDayRowClassID);

            // DATA TABLE
            $table = DataTable::create('timetableDayRowClassExceptions');

            $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/Timetable Admin/tt_edit_day_edit_class_exception_add.php')
                ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                ->addParam('pupilsightTTID', $pupilsightTTID)
                ->addParam('pupilsightTTDayID', $pupilsightTTDayID)
                ->addParam('pupilsightTTColumnRowID', $pupilsightTTColumnRowID)
                ->addParam('pupilsightCourseClassID', $pupilsightCourseClassID)
                ->displayLabel();

            $table->addColumn('name', __('Name'))->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', true]));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                ->addParam('pupilsightTTID', $pupilsightTTID)
                ->addParam('pupilsightTTDayID', $pupilsightTTDayID)
                ->addParam('pupilsightTTColumnRowID', $pupilsightTTColumnRowID)
                ->addParam('pupilsightCourseClassID', $pupilsightCourseClassID)
                ->addParam('pupilsightTTDayRowClassID', $pupilsightTTDayRowClassID)
                ->addParam('pupilsightTTDayRowClassExceptionID')
                ->format(function ($values, $actions) {
                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/tt_edit_day_edit_class_exception_delete.php');
                });

            echo $table->render($ttDayRowClassExceptions->toDataSet());
        }
    }
}
