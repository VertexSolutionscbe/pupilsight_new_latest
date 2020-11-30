<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\TimetableDayGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightTTDayID = $_GET['pupilsightTTDayID'];
    $pupilsightTTID = $_GET['pupilsightTTID'];
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    $pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'];
    $pupilsightProgramID = $_GET['pupilsightProgramID'];
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
   //print_r($pupilsightTTColumnRowID);
    if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' or $pupilsightTTColumnRowID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        //Timetable, day, period

        $timetableDayGateway = $container->get(TimetableDayGateway::class);
        $values = $timetableDayGateway->getTTDayRowByID($pupilsightTTDayID, $pupilsightTTColumnRowID);

        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            $urlParams = ['pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$pupilsightYearGroupID,'pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTID' => $pupilsightTTID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID];

            $page->breadcrumbs
                ->add(__('Manage Timetables'), 'tt.php', $urlParams)
                ->add(__('Edit Timetable'), 'tt_edit.php', $urlParams)
                ->add(__('Edit Timetable Day'), 'tt_edit_day_edit.php', $urlParams)
                ->add(__('Classes in Period'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            echo "<table class='table'>";
            echo '<tr>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Timetable').'</span><br/>';
            echo $values['ttName'];
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Day').'</span><br/>';
            echo $values['dayName'];
            echo '</td>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Period').'</span><br/>';
            echo $values['rowName'];
            echo '</td>';
            echo '</tr>';
            echo '</table>';

            $ttDayRowClasses = $timetableDayGateway->selectTTDayRowClassesByID($pupilsightTTDayID, $pupilsightTTColumnRowID);

            // DATA TABLE
            $table = DataTable::create('timetableDayRowClasses');

            $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/Timetable Admin/tt_edit_day_edit_class_add.php')
                ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                ->addParam('pupilsightTTID', $pupilsightTTID)
                ->addParam('pupilsightTTDayID', $pupilsightTTDayID)
                ->addParam('pupilsightProgramID', $pupilsightProgramID)
                ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
                ->addParam('pupilsightTTColumnRowID', $pupilsightTTColumnRowID)
                ->displayLabel();

            $table->addColumn('subname', __('Subject'));
            $table->addColumn('pupilsightTTDayRowClassID', __('ID'));
            $table->addColumn('staffname', __('Staff'));
            $table->addColumn('location', __('Location'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                ->addParam('pupilsightTTID', $pupilsightTTID)
                ->addParam('pupilsightTTDayID', $pupilsightTTDayID)
                ->addParam('pupilsightTTColumnRowID', $pupilsightTTColumnRowID)
                ->addParam('pupilsightProgramID', $pupilsightProgramID)
                ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
                ->addParam('pupilsightCourseClassID', $pupilsightCourseClassID)
                ->addParam('pupilsightTTDayRowClassID')

               
                ->format(function ($values, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/tt_edit_day_edit_class_edit.php');
                        
                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/tt_edit_day_edit_class_delete.php');

                    $actions->addAction('exceptions', __('Exceptions'))
                        ->setIcon('attendance')
                        ->setURL('/modules/Timetable Admin/tt_edit_day_edit_class_exception.php');
                });

            echo $table->render($ttDayRowClasses->toDataSet());
        }
    }
}
