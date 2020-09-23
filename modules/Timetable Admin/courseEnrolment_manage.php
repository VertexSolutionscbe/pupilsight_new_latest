<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Timetable\CourseGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Course Enrolment by Class'));

    $pupilsightSchoolYearID = isset($_GET['pupilsightSchoolYearID'])? $_GET['pupilsightSchoolYearID'] : '';

    if (empty($pupilsightSchoolYearID) || $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    } else {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID";
        $result = $pdo->executeQuery($data, $sql);
        
        $pupilsightSchoolYearName = ($result->rowCount() > 0)? $result->fetchColumn(0) : '';
    }

    if (empty($pupilsightSchoolYearID) || empty($pupilsightSchoolYearName)) {
        echo '<div class="alert alert-danger">';
        echo __('The specified record does not exist.');
        echo '</div>';
    } else {
        echo '<h2>';
        echo $pupilsightSchoolYearName;
        echo '</h2>';

        echo "<div class='linkTop'>";
            //Print year picker
            if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_manage.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
        echo ' | ';
        if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_manage.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
        } else {
            echo __('Next Year').' ';
        }
        echo '</div>';

        $search = (isset($_GET['search']))? $_GET['search'] : '';
        $pupilsightYearGroupID = (isset($_GET['pupilsightYearGroupID']))? $_GET['pupilsightYearGroupID'] : '';

        $courseGateway = $container->get(CourseGateway::class);
        
        // CRITERIA
        $criteria = $courseGateway->newQueryCriteria()
            ->searchBy($courseGateway->getSearchableColumns(), $search)
            ->sortBy(['pupilsightCourse.nameShort', 'pupilsightCourse.name'])
            ->filterBy('yearGroup', $pupilsightYearGroupID)
            ->pageSize(0)
            ->fromPOST();

        echo '<h3>';
        echo __('Filters');
        echo '</h3>'; 
        
        $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_manage.php');
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $row = $form->addRow();
            $row->addLabel('search', __('Search For'));
            $row->addTextField('search')->setValue($criteria->getSearchText());

        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID', __('Year Group'));
            $row->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID);


        $row = $form->addRow();
            $row->addSearchSubmit($pupilsight->session, __('Clear Search'), array('pupilsightSchoolYearID'));

        echo $form->getOutput();

        // QUERY
        $courses = $courseGateway->queryCoursesBySchoolYear($criteria, $pupilsightSchoolYearID);

        if (count($courses) == 0) {
            echo '<div class="alert alert-danger">';
            echo __('There are no records to display.');
            echo '</div>';
            return;
        }

        foreach ($courses as $course) {
            echo '<h3>';
            echo $course['nameShort'].' ('.$course['name'].')';
            echo '</h3>';

            $classes = $courseGateway->selectClassesByCourseID($course['pupilsightCourseID']);

            // DATA TABLE
            $table = DataTable::create('courseClassEnrolment');

            $table->addColumn('name', __('Name'));
            $table->addColumn('nameShort', __('Short Name'));
            $table->addColumn('participantsActive', __('Participants'))->description(__('Active'));
            $table->addColumn('participantsExpected', __('Participants'))->description(__('Expected'));
            $table->addColumn('participantsTotal', __('Participants'))->description(__('Total'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                ->addParam('pupilsightCourseID')
                ->addParam('pupilsightCourseClassID')
                ->format(function ($class, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_class_edit.php');
                });

            echo $table->render($classes->toDataSet());
        }
    }
}
