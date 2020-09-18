<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Students\StudentGateway;

if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/in_view.php') == false) {
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
        $page->breadcrumbs->add(__('View Student Records'));

        $studentGateway = $container->get(StudentGateway::class);

        $pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : '';
        $search = isset($_GET['search'])? $_GET['search'] : '';
        $allStudents = (isset($_GET['allStudents']) ? $_GET['allStudents'] : '');

        // CRITERIA
        $criteria = $studentGateway->newQueryCriteria()
            ->searchBy($studentGateway->getSearchableColumns(), $search)
            ->sortBy(['surname', 'preferredName'])
            ->filterBy('all', $allStudents)
            ->fromPOST();

        echo '<h2>';
        echo __('Search');
        echo '</h2>';

        $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/in_view.php');

        $row = $form->addRow();
            $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
            $row->addTextField('search')->setValue($criteria->getSearchText());

        $row = $form->addRow();
            $row->addLabel('allStudents', __('All Students'))->description(__('Include all students, regardless of status and current enrolment. Some data may not display.'));
            $row->addCheckbox('allStudents')->setValue('on')->checked($allStudents);

        $row = $form->addRow()
		->addClass('right_align');
            $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

        echo $form->getOutput();

        echo '<h2>';
        echo __('Choose A Student');
        echo '</h2>';
        echo '<p>';
        echo __('This page displays all students enroled in the school, including those who have not yet met their start date. With the right permissions, you can set Individual Needs status and Individual Education Plan details for any student.');
        echo '</p>';

        $students = $studentGateway->queryStudentsBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);

        // DATA TABLE
        $table = DataTable::createPaginated('inView', $criteria);

        $table->addMetaData('filterOptions', [
            'all:on'        => __('All Students')
        ]);

        $table->modifyRows($studentGateway->getSharedUserRowHighlighter());

        // COLUMNS
        $table->addColumn('student', __('Student'))
            ->sortable(['surname', 'preferredName'])
            ->format(function ($person) {
                return Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
            });
        $table->addColumn('yearGroup', __('Year Group'));
        $table->addColumn('rollGroup', __('Roll Group'));

        $table->addActionColumn()
            ->addParam('pupilsightPersonID')
            ->addParam('search', $criteria->getSearchText(true))
            ->format(function ($row, $actions) use ($highestAction) {
                if ($highestAction == 'Individual Needs Records_view') {
                    $actions->addAction('view', __('View Individual Needs Details'))
                            ->setURL('/modules/Individual Needs/in_edit.php');
                } else if ($highestAction == 'Individual Needs Records_viewEdit' or $highestAction == 'Individual Needs Records_viewContribute') {
                    $actions->addAction('edit', __('Edit'))
                            ->setURL('/modules/Individual Needs/in_edit.php');
                }
            });

        echo $table->render($students);
    }
}
