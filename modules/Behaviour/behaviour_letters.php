<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Behaviour\BehaviourGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_letters.php') == false) {
    //Access denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('View Behaviour Letters'));

    $pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : '';

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL']."/index.php", 'get', 'noIntBorder fullWidth standardForm');
    $form->setTitle(__('Filter'));
    $form->addHiddenValue('q', '/modules/Behaviour/behaviour_letters.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Student'));
        $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightPersonID)->placeholder();

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

    echo $form->getOutput();

    echo '<h3>';
    echo __('Behaviour Letters');
    echo '</h3>';
    echo '<p>';
    echo __('This interface displays automated behaviour letters that have been issued within the current school year.');
    echo '</p>';

    $behaviourGateway = $container->get(BehaviourGateway::class);

    // CRITERIA
    $criteria = $behaviourGateway->newQueryCriteria()
        ->sortBy('timestamp', 'DESC')
        ->filterBy('student', $pupilsightPersonID)
        ->fromPOST();

    $letters = $behaviourGateway->queryBehaviourLettersBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);

    // DATA TABLE
    $table = DataTable::createPaginated('behaviourLetters', $criteria);

    // COLUMNS
    $table->addExpandableColumn('comment')
        ->format(function($letter) {
            $output = '';
            if (!empty($letter['body'])) {
                $output .= '<b>'.__('Letter Body').'</b><br/>';
                $output .= nl2brr($letter['body']).'<br/><br/>';
            }
            if (!empty($letter['recipientList'])) {
                $output .= '<b>'.__('Recipients').'</b><br/>';
                $reipients = array_map('trim', explode(',', $letter['recipientList']));
                $output .= implode('<br/>', $reipients);
            }
            return $output;
        });

    $table->addColumn('student', __('Student'))
        ->description(__('Roll Group'))
        ->sortable(['surname', 'preferredName'])
        ->width('25%')
        ->format(function($person) use ($guid) {
            $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$person['pupilsightPersonID'].'&subpage=Behaviour&search=&allStudents=&sort=surname,preferredName';
            return '<b>'.Format::link($url, Format::name('', $person['preferredName'], $person['surname'], 'Student', true)).'</b>'
                  .'<br/><small><i>'.$person['rollGroup'].'</i></small>';
        });

    $table->addColumn('timestamp', __('Date'))
        ->format(Format::using('date', 'timestamp'));
    $table->addColumn('letterLevel', __('Letter'));
    $table->addColumn('status', __('Status'));

    echo $table->render($letters);
}
