<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Rubrics\RubricGateway;
use Pupilsight\Domain\Students\StudentGateway;

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('View Rubrics'));

    // Register scripts available to the core, but not included by default
    $page->scripts->add('chart');

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = isset($_REQUEST['search'])? $_REQUEST['search'] : '';
    $department = isset($_POST['filter2'])? $_POST['filter2'] : '';
    $yearGroups = getYearGroups($connection2);

    $rubricGateway = $container->get(RubricGateway::class);

    // QUERY
    $criteria = $rubricGateway->newQueryCriteria()
        ->searchBy($rubricGateway->getSearchableColumns(), $search)
        ->sortBy(['scope', 'category', 'name'])
        ->filterBy('department', $department)
        ->fromPOST();

    // SEARCH
    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/rubrics_view.php');
    $form->setTitle(__('Filter'));
    $form->setClass('noIntBorder fullWidth');

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__('Rubric name.'));
        $row->addTextField('search')->setValue($criteria->getSearchText());

    $sql = "SELECT pupilsightDepartmentID as value, name FROM pupilsightDepartment WHERE type='Learning Area' ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('filter2', __('Learning Areas'));
        $row->addSelect('filter2')
            ->fromArray(array('' => __('All Learning Areas')))
            ->fromQuery($pdo, $sql)
            ->selected($department);

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

    echo $form->getOutput();

    // If the current user is a student, limit the results to their year group
    $pupilsightYearGroupID = null;
    $roleCategory = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
    if ($roleCategory == 'Student') {
        $studentGateway = $container->get(StudentGateway::class);
        $enrolment = $studentGateway->selectActiveStudentByPerson($_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID'])->fetch();

        if (!empty($enrolment)) {
            $pupilsightYearGroupID = $enrolment['pupilsightYearGroupID'];
        }
    }

    $rubrics = $rubricGateway->queryRubrics($criteria, 'Y', $pupilsightYearGroupID);

    // DATA TABLE
    $table = DataTable::createPaginated('rubrics', $criteria);
    $table->setTitle(__('Rubrics'));

    // COLUMNS
    $table->addExpandableColumn('description');
    $table->addColumn('scope', __('Scope'))
        ->context('primary')
        ->width('15%')
        ->format(function($rubric) {
            if ($rubric['scope'] == 'School') {
                return '<strong>'.__('School').'</strong>';
            } else {
                return '<strong>'.__('Learning Area').'</strong><br/>'.Format::small($rubric['learningArea']);
            }
        });
    $table->addColumn('category', __('Category'))->width('15%');
    $table->addColumn('name', __('Name'))
        ->context('primary')
        ->width('35%');
    $table->addColumn('yearGroups', __('Year Groups'))
        ->format(function($activity) use ($yearGroups) {
            return ($activity['yearGroupCount'] >= count($yearGroups)/2)? '<i>'.__('All').'</i>' : $activity['yearGroups'];
        });

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightRubricID')
        ->format(function ($rubric, $actions) {
            $actions->addAction('view', __('View'))
                ->setURL('/modules/Rubrics/rubrics_view_full.php')
                ->modalWindow(1100, 550);
        });

    echo $table->render($rubrics);
}
