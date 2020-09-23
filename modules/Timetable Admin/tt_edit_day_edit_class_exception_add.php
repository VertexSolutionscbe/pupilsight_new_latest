<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Timetable\TimetableDayGateway;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_exception_add.php') == false) {
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
        $urlParams = [
            'pupilsightTTDayID' => $pupilsightTTDayID,
            'pupilsightTTID' => $pupilsightTTID,
            'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
            'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID,
            'pupilsightCourseClassID' => $pupilsightCourseClassID,
        ];

        $page->breadcrumbs
            ->add(__('Manage Timetables'), 'tt.php', $urlParams)
            ->add(__('Edit Timetable'), 'tt_edit.php', $urlParams)
            ->add(__('Edit Timetable Day'), 'tt_edit_day_edit.php', $urlParams)
            ->add(__('Classes in Period'), 'tt_edit_day_edit_class.php', $urlParams)
            ->add(__('Class List Exception'), 'tt_edit_day_edit_class_exception.php', $urlParams)
            ->add(__('Add Exception'));

        $timetableDayGateway = $container->get(TimetableDayGateway::class);
        $values = $timetableDayGateway->getTTDayRowClassByID($pupilsightTTDayID, $pupilsightTTColumnRowID, $pupilsightCourseClassID);

        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $pupilsightTTDayRowClassID = $values['pupilsightTTDayRowClassID'];

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/tt_edit_day_edit_class_exception_addProcess.php?pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTDayRowClass=$pupilsightTTDayRowClassID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightTTDayRowClassID=$pupilsightTTDayRowClassID");

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightTTID', $pupilsightTTID);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

            $participants = array();
            try {
                $dataSelect = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightTTDayRowClassID' => $pupilsightTTDayRowClassID);
                $sqlSelect = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname
                    FROM pupilsightPerson
                        JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                        LEFT JOIN pupilsightTTDayRowClassException ON (pupilsightTTDayRowClassException.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightTTDayRowClassException.pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID)
                    WHERE pupilsightCourseClassID=:pupilsightCourseClassID
                        AND NOT role='Student - Left'
                        AND NOT role='Teacher - Left'
                        AND NOT pupilsightPerson.status='Left'
                        AND pupilsightTTDayRowClassExceptionID IS NULL
                    ORDER BY surname, preferredName";
                $resultSelect = $connection2->prepare($sqlSelect);
                $resultSelect->execute($dataSelect);
            } catch (PDOException $e) { echo $e->getMessage();}
            while ($rowSelect = $resultSelect->fetch()) {
                $participants[$rowSelect['pupilsightPersonID']] = Format::name('', htmlPrep($rowSelect['preferredName']), htmlPrep($rowSelect['surname']), 'Student', true);
            }

            $row = $form->addRow();
                $row->addLabel('Members', __('Participants'));
                $row->addSelect('Members')->fromArray($participants)->selectMultiple()->required()->setSize(8);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
