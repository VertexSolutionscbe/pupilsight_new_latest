<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\IndividualNeeds\INGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/in_summary.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Individual Needs Summary'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
    }

    $pupilsightINDescriptorID = null;
    if (isset($_GET['pupilsightINDescriptorID'])) {
        $pupilsightINDescriptorID = $_GET['pupilsightINDescriptorID'];
    }
    $pupilsightAlertLevelID = null;
    if (isset($_GET['pupilsightAlertLevelID'])) {
        $pupilsightAlertLevelID = $_GET['pupilsightAlertLevelID'];
    }
    $pupilsightRollGroupID = null;
    if (isset($_GET['pupilsightRollGroupID'])) {
        $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'];
    }
    $pupilsightYearGroupID = null;
    if (isset($_GET['pupilsightYearGroupID'])) {
        $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
    }

    echo '<h3>';
    echo __('Filter');
    echo '</h3>';

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth standardForm');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    
    $form->addHiddenValue('q', '/modules/Individual Needs/in_summary.php');
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    //SELECT FROM ARRAY
    $sql = "SELECT pupilsightINDescriptorID as value, name FROM pupilsightINDescriptor ORDER BY sequenceNumber";
    $row = $form->addRow();
    	$row->addLabel('pupilsightINDescriptorID', __('Descriptor'));
        $row->addSelect('pupilsightINDescriptorID')->fromQuery($pdo, $sql)->selected($pupilsightINDescriptorID)->placeholder();

    $sql = "SELECT pupilsightAlertLevelID as value, name FROM pupilsightAlertLevel ORDER BY sequenceNumber";
    $row = $form->addRow();
        $row->addLabel('pupilsightAlertLevelID', __('Alert Level'));
        $row->addSelect('pupilsightAlertLevelID')->fromQuery($pdo, $sql)->selected($pupilsightAlertLevelID)->placeholder();

    $row = $form->addRow();
        $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
        $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightRollGroupID)->placeholder();
    
    $row = $form->addRow();
        $row->addLabel('pupilsightYearGroupID', __('Year Group'));
        $row->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->placeholder();
    
    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));
        
    echo $form->getOutput();

    echo '<h3>';
    echo __('Students With Records');
    echo '</h3>';
    echo '<p>';
    echo __('Students only show up in this list if they have an Individual Needs record with descriptors set. If a student does not show up here, check in Individual Needs Records.');
    echo '</p>';

    $individualNeedsGateway = $container->get(INGateway::class);

    $criteria = $individualNeedsGateway->newQueryCriteria()
        ->sortBy(['surname', 'preferredName'])
        ->filterBy('descriptor', $pupilsightINDescriptorID)
        ->filterBy('alert', $pupilsightAlertLevelID)
        ->filterBy('rollGroup', $pupilsightRollGroupID)
        ->filterBy('yearGroup', $pupilsightYearGroupID)
        ->fromPOST();

    $individualNeeds = $individualNeedsGateway->queryINBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);

    // DATA TABLE
    $table = DataTable::createPaginated('inSummary', $criteria);

    $table->modifyRows(function($student, $row) {
        if ($student['status'] != 'Full') $row->addClass('error');
        if (!($student['dateStart'] == '' || $student['dateStart'] <= date('Y-m-d'))) $row->addClass('error');
        if (!($student['dateEnd'] == '' || $student['dateEnd'] >= date('Y-m-d'))) $row->addClass('error');
        return $row;
    });

    $table->addMetaData('filterOptions', [
        'alert:003'    => __('Alert Level').': '.__('Low'),
        'alert:002' => __('Alert Level').': '.__('Medium'),
        'alert:001'   => __('Alert Level').': '.__('High'),
    ]);

    // COLUMNS
    $table->addColumn('student', __('Student'))
        ->sortable(['surname', 'preferredName'])
        ->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', true]));
    $table->addColumn('yearGroup', __('Year Group'));
    $table->addColumn('rollGroup', __('Roll Group'));

    $table->addActionColumn()
        ->addParam('pupilsightPersonID')
        ->addParam('pupilsightINDescriptorID', $pupilsightINDescriptorID)
        ->addParam('pupilsightAlertLevelID', $pupilsightAlertLevelID)
        ->addParam('pupilsightRollGroupID', $pupilsightRollGroupID)
        ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
        ->addParam('source', 'summary')
        ->format(function ($row, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Individual Needs/in_edit.php');
        });

    echo $table->render($individualNeeds);
}
