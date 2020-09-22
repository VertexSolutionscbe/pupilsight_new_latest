<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\CourseEnrolmentGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/report_classEnrolment_byRollGroup.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Class Enrolment by Roll Group'));

    echo '<h2>';
    echo __('Choose Roll Group');
    echo '</h2>';

    $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : '';

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/report_classEnrolment_byRollGroup.php');

    $row = $form->addRow();
        $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
        $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightRollGroupID)->required()->placeholder();

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if ($pupilsightRollGroupID != '') {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        $courseGateway = $container->get(CourseEnrolmentGateway::class);

        $enrolment = $courseGateway->selectCourseEnrolmentByRollGroup($pupilsightRollGroupID);

        // DATA TABLE
        $table = DataTable::create('courseEnrolment');

        $table->addColumn('rollGroup', __('Roll Group'));
        $table->addColumn('student', __('Student'))
            ->sortable(['surname', 'preferredName'])
            ->format(function($person) use ($guid) {
                return Format::link($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable/tt_view.php&pupilsightPersonID='.$person['pupilsightPersonID'], Format::name('', $person['preferredName'], $person['surname'], 'Student', true) );
            });
        $table->addColumn('classCount', __('Class Count'));

        echo $table->render($enrolment->toDataSet());
    }
}
