<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_byPerson.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Course Enrolment by Person'));

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
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_manage_byPerson.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
			echo ' | ';
			if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
				echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_manage_byPerson.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
			} else {
				echo __('Next Year').' ';
			}
        echo '</div>';

        $allUsers = isset($_GET['allUsers'])? $_GET['allUsers'] : '';
        $search = isset($_GET['search'])? $_GET['search'] : '';

        // CRITERIA
        $studentGateway = $container->get(StudentGateway::class);

        $criteria = $studentGateway->newQueryCriteria()
            ->searchBy($studentGateway->getSearchableColumns(), $search)
            ->sortBy(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
            ->filterBy('all', $allUsers)
            ->fromPOST();

        echo '<h3>';
        echo __('Filters');
        echo '</h3>'; 
        
        $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_manage_byPerson.php');
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $row = $form->addRow();
            $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
            $row->addTextField('search')->setValue($criteria->getSearchText());

        $row = $form->addRow();
            $row->addLabel('allUsers', __('All Users'))->description(__('Include non-staff, non-student users.'));
            $row->addCheckbox('allUsers')->setValue('on')->checked($allUsers);

        $row = $form->addRow();
            $row->addSearchSubmit($pupilsight->session, __('Clear Search'), array('pupilsightSchoolYearID'));

        echo $form->getOutput();

        echo '<h3>';
        echo __('View');
        echo '</h3>';
            
        $users = $studentGateway->queryStudentsAndTeachersBySchoolYear($criteria, $pupilsightSchoolYearID);

        // DATA TABLE
        $table = DataTable::createPaginated('courseEnrolmentByPerson', $criteria);

        $table->modifyRows($studentGateway->getSharedUserRowHighlighter());

        $table->addMetaData('filterOptions', [
            'all:on'          => __('All Users'),
            'role:student'    => __('Role').': '.__('Student'),
            'role:staff'      => __('Role').': '.__('Staff'),
        ]);

        if ($criteria->hasFilter('all')) {
            $table->addMetaData('filterOptions', [
                'status:full'     => __('Status').': '.__('Full'),
                'status:expected' => __('Status').': '.__('Expected'),
                'date:starting'   => __('Before Start Date'),
                'date:ended'      => __('After End Date'),
            ]);
        }

        // COLUMNS
        $table->addColumn('name', __('Name'))
            ->sortable(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
            ->format(function ($person) {
                $roleCategory = ($person['roleCategory'] == 'Student' || !empty($person['yearGroup']))? 'Student' : 'Staff';
                return Format::name('', $person['preferredName'], $person['surname'], $roleCategory, true, true);
            });
        $table->addColumn('roleCategory', __('Role Category'))
            ->format(function($person) {
                return __($person['roleCategory']) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
            });
        $table->addColumn('yearGroup', __('Year Group'));
        $table->addColumn('rollGroup', __('Roll Group'));

        $actions = $table->addActionColumn()
            ->addParam('search', $criteria->getSearchText(true))
            ->addParam('allUsers', $allUsers)
            ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->addParam('pupilsightPersonID')
            ->format(function ($person, $actions) {
                $actions->addAction('edit', __('Edit'))
                        ->addParam('type', $person['roleCategory'])
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php');
            });

        echo $table->render($users);
    }
}
