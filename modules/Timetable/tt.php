<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Domain\Staff\StaffGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $page->breadcrumbs->add(__('View Timetable by Person'));

        $pupilsightPersonID = isset($_GET['pupilsightPersonID']) ? $_GET['pupilsightPersonID'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $allUsers = (isset($_GET['allUsers']) && $_SESSION[$guid]['pupilsightRoleIDCurrentCategory'] == 'Staff') ? $_GET['allUsers'] : '';

        $studentGateway = $container->get(StudentGateway::class);
        $staffGateway = $container->get(StaffGateway::class);

        $canViewAllTimetables = $highestAction == 'View Timetable by Person' || $highestAction == 'View Timetable by Person_allYears';

        if ($canViewAllTimetables) {
            $criteria = $studentGateway->newQueryCriteria()
                ->searchBy($studentGateway->getSearchableColumns(), $search)
                ->sortBy(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
                ->filterBy('all', $allUsers)
                ->fromPOST();


            $form = Form::create('ttView', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
            $form->setClass('noIntBorder fullWidth');
            $form->setTitle(__('Search'));

            $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/tt.php');

            $row = $form->addRow();
                $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
                $row->addTextField('search')->setValue($criteria->getSearchText());

            if ($_SESSION[$guid]['pupilsightRoleIDCurrentCategory'] == 'Staff') {
                $row = $form->addRow();
                    $row->addLabel('allUsers', __('All Users'))->description(__('Include non-staff, non-student users.'));
                    $row->addCheckbox('allUsers')->checked($allUsers);
            }

            $row = $form->addRow()
				->addClass('right_align');
                $row->addSearchSubmit($pupilsight->session);

            echo $form->getOutput();
        }

        echo '<h2>';
        echo __('Choose A Person');
        echo '</h2>';

        if ($highestAction == 'View Timetable by Person_my') {
            $role = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDPrimary'], $connection2);
            if ($role == 'Student') {
                $result = $studentGateway->selectActiveStudentByPerson($_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID']);
            } else {
                $result = $staffGateway->selectStaffByID($_SESSION[$guid]['pupilsightPersonID'], 'Teaching');
            }
            $users = $result->toDataSet();

            $table = DataTable::create('timetables');

        } else if ($highestAction == 'View Timetable by Person_myChildren') {
            $result = $studentGateway->selectActiveStudentsByFamilyAdult($_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID']);
            $users = $result->toDataSet();

            $table = DataTable::create('timetables');

        } else if ($canViewAllTimetables) {

            $users = $studentGateway->queryStudentsAndTeachersBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);

            $table = DataTable::createPaginated('timetables', $criteria);

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
        }

        if (!$canViewAllTimetables && count($users) == 0) {
            echo '<div class="alert alert-danger">';
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
            return;
        }

        // COLUMNS
        $table->addColumn('name', __('Name'))
            ->sortable(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
            ->format(function ($person) {
                $roleCategory = ($person['roleCategory'] == 'Student' || !empty($person['yearGroup']))? 'Student' : 'Staff';
                return Format::name('', $person['preferredName'], $person['surname'], $roleCategory, true, true);
            });
        if ($canViewAllTimetables) {
            $table->addColumn('roleCategory', __('Role Category'))
                ->format(function($person) {
                    return __($person['roleCategory']) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
                });
        }
        $table->addColumn('yearGroup', __('Year Group'));
        $table->addColumn('rollGroup', __('Roll Group'));

        $actions = $table->addActionColumn()
            ->addParam('pupilsightPersonID')
            ->format(function ($person, $actions) {
                $actions->addAction('view', __('View Details'))
                    ->setURL('/modules/Timetable/tt_view.php');
            });

        if ($canViewAllTimetables) {
            $actions->addParam('search', $criteria->getSearchText(true))
                    ->addParam('allUsers', $criteria->getFilterValue('all'));
        }

        echo $table->render($users);
    }
}
