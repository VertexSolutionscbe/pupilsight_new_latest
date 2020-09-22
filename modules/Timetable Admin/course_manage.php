<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\CourseGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Courses & Classes'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    if ($pupilsightSchoolYearID != $_SESSION[$guid]['pupilsightSchoolYearID']) {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    if ($pupilsightSchoolYearID != '') {
        echo '<h2>';
        echo $pupilsightSchoolYearName;
        echo '</h2>';

        echo "<div class='linkTop'>";
            //Print year picker
            $previousYear = getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2);
			$nextYear = getNextSchoolYearID($pupilsightSchoolYearID, $connection2);
			if ($previousYear != false) {
				echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/course_manage.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
			} else {
				echo __('Previous Year').' ';
			}
			echo ' | ';
			if ($nextYear != false) {
				echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/course_manage.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
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
            ->fromPOST();

        echo '<h3>';
        echo __('Filters');
        echo '</h3>';

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/course_manage.php");
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $row = $form->addRow();
            $row->addLabel('search', __('Search For'));
            $row->addTextField('search')->setValue($criteria->getSearchText());

        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID', __('Year Group'));
            $row->addSelectYearGroup('pupilsightYearGroupID')->selected($criteria->getFilterValue('yearGroup'));

        $row = $form->addRow();
            $row->addSearchSubmit($pupilsight->session, __('Clear Filters'), array('pupilsightSchoolYearID'));

        echo $form->getOutput();

        echo '<h3>';
        echo __('View');
        echo '</h3>';

        $courses = $courseGateway->queryCoursesBySchoolYear($criteria, $pupilsightSchoolYearID);

        // DATA TABLE
        $table = DataTable::createPaginated('courseManage', $criteria);

        if ($nextYear != false) {
            $table->addHeaderAction('Duplicate', __('Copy All To Next Year'))
                ->setURL('/modules/Timetable Admin/course_manage_copyProcess.php')
                ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                ->addParam('pupilsightSchoolYearIDNext', $nextYear)
                ->addParam('search', $search)
                ->setIcon('Duplicate')
                ->onCLick('return confirm("'.__('Are you sure you want to do this? All courses and classes, but not their participants, will be copied.').'");')
                ->displayLabel()
                ->directLink()
                ->append(' | ');
        }

        $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/Timetable Admin/course_manage_add.php')
            ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->addParam('search', $search)
            ->displayLabel();

        // COLUMNS
        $table->addColumn('nameShort', __('Short Name'));
        $table->addColumn('name', __('Name'));
        $table->addColumn('department', __('Learning Area'));
        $table->addColumn('classCount', __('Classes'));

        // ACTIONS
        $table->addActionColumn()
            ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->addParam('pupilsightCourseID')
            ->addParam('search', $criteria->getSearchText(true))
            ->format(function ($course, $actions) {
                $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/course_manage_edit.php');

                $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/course_manage_delete.php');
            });

        echo $table->render($courses);
    }
}
