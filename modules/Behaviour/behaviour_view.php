<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_view.php') == false) {
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
        $page->breadcrumbs->add(__('View Behaviour Records'));

        $search = isset($_GET['search'])? $_GET['search'] : '';

        if ($highestAction == 'View Behaviour Records_all') {
            $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
            $form->setTitle(__('Search'));
            $form->setClass('noIntBorder fullWidth');

            $form->addHiddenValue('q', '/modules/Behaviour/behaviour_view.php');

            $row = $form->addRow();
                $row->addLabel('search',__('Search For'))->description('Preferred, surname, username.');
                $row->addTextField('search')->setValue($search)->maxLength(30);

            $row = $form->addRow();
                $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

            echo $form->getOutput();
        }

        $studentGateway = $container->get(StudentGateway::class);

        // DATA TABLE
        if ($highestAction == 'View Behaviour Records_all') {
            
            $criteria = $studentGateway->newQueryCriteria()
                ->searchBy($studentGateway->getSearchableColumns(), $search)
                ->sortBy(['surname', 'preferredName'])
                ->fromPOST();

            $students = $studentGateway->queryStudentsBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID'], false);

            $table = DataTable::createPaginated('behaviour', $criteria);
            $table->setTitle(__('Choose A Student'));

        } else if ($highestAction == 'View Behaviour Records_myChildren') {
            $students = $studentGateway->selectActiveStudentsByFamilyAdult($_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID'])->toDataSet();

            $table = DataTable::create('behaviour');
            $table->setTitle( __('My Children'));
        } else {
            return;
        }

        // COLUMNS
        $table->addColumn('student', __('Student'))
            ->sortable(['surname', 'preferredName'])
            ->format(function ($person) use ($guid) {
                $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&subpage=Behaviour&pupilsightPersonID='.$person['pupilsightPersonID'].'&search=&allStudents=&sort=surname,preferredName';
                return Format::link($url, Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true));
            });
        $table->addColumn('yearGroup', __('Year Group'));
        $table->addColumn('rollGroup', __('Roll Group'));

        $table->addActionColumn()
            ->addParam('pupilsightPersonID')
            ->addParam('search', $search)
            ->format(function ($row, $actions) {
                $actions->addAction('view', __('View Details'))
                    ->setURL('/modules/Behaviour/behaviour_view_details.php');
            });

        echo $table->render($students);
    }
}
