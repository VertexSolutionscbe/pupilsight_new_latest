<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\FirstAidGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/firstAidRecord.php') == false) {
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
        $page->breadcrumbs->add(__('First Aid Records'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        $pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : null;
        $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : null;
        $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])? $_GET['pupilsightYearGroupID'] : null;

        echo '<h3>';
        echo __('Filter');
        echo '</h3>';

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/firstAidRecord.php");

        $row = $form->addRow();
            $row->addLabel('pupilsightPersonID', __('Student'));
            $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->placeholder()->selected($pupilsightPersonID);

        $row = $form->addRow();
            $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
            $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightRollGroupID);

        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID', __('Year Group'));
            $row->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID);

        $row = $form->addRow();
            $row->addFooter();
            $row->addSearchSubmit($pupilsight->session);

        echo $form->getOutput();

        echo '<h3>';
        echo __('First Aid Records');
        echo '</h3>';

        $firstAidGateway = $container->get(FirstAidGateway::class);

        $criteria = $firstAidGateway->newQueryCriteria()
            ->sortBy(['date', 'timeIn'], 'DESC')
            ->filterBy('student', $pupilsightPersonID)
            ->filterBy('rollGroup', $pupilsightRollGroupID)
            ->filterBy('yearGroup', $pupilsightYearGroupID)
            ->fromPOST();

        $firstAidRecords = $firstAidGateway->queryFirstAidBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);

        // DATA TABLE
        $table = DataTable::createPaginated('firstAidRecords', $criteria);

        $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/Students/firstAidRecord_add.php')
            ->addParam('pupilsightRollGroupID', $pupilsightRollGroupID)
            ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
            ->displayLabel();

        // COLUMNS
        $table->addExpandableColumn('details')->format(function($person) {
            $output = '';
            if ($person['description'] != '') $output .= '<b>'.__('Description').'</b><br/>'.nl2brr($person['description']).'<br/><br/>';
            if ($person['actionTaken'] != '') $output .= '<b>'.__('Action Taken').'</b><br/>'.nl2brr($person['actionTaken']).'<br/><br/>';
            if ($person['followUp'] != '') $output .= '<b>'.__('Follow Up').'</b><br/>'.nl2brr($person['followUp']);
            return $output;
        });

        $table->addColumn('patient', __('Student'))
            ->description(__('Roll Group'))
            ->sortable(['surnamePatient', 'preferredNamePatient'])
            ->format(function($person) use ($guid) {
                $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$person['pupilsightPersonIDPatient'].'&subpage=Medical&search=&allStudents=&sort=surname,preferredName';
                return Format::link($url, Format::name('', $person['preferredNamePatient'], $person['surnamePatient'], 'Student', true))
                      .'<br/><small><i>'.$person['rollGroup'].'</i></small>';
            });

        $table->addColumn('firstAider', __('First Aider'))
            ->sortable(['surnameFirstAider', 'preferredNameFirstAider'])
            ->format(Format::using('name', ['', 'preferredNameFirstAider', 'surnameFirstAider', 'Staff', false, true]));

        $table->addColumn('date', __('Date'))
            ->format(Format::using('date', ['date']));

        $table->addColumn('time', __('Time'))
            ->sortable(['timeIn', 'timeOut'])
            ->format(Format::using('timeRange', ['timeIn', 'timeOut']));

        $table->addActionColumn()
            ->addParam('pupilsightPersonID', $pupilsightPersonID)
            ->addParam('pupilsightRollGroupID', $pupilsightRollGroupID)
            ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
            ->addParam('pupilsightFirstAidID')
            ->format(function ($person, $actions) {
                $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Students/firstAidRecord_edit.php');
            });
        
        echo $table->render($firstAidRecords);
    }
}
