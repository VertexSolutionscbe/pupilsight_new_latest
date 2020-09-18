<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Behaviour\BehaviourGateway;
use Pupilsight\Domain\Students\StudentGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$enableDescriptors = getSettingByScope($connection2, 'Behaviour', 'enableDescriptors');
$enableLevels = getSettingByScope($connection2, 'Behaviour', 'enableLevels');

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Find Behaviour Patterns'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
    }

    $descriptor = isset($_GET['descriptor'])? $_GET['descriptor'] : '';
    $level = isset($_GET['level'])? $_GET['level'] : '';
    $fromDate = isset($_GET['fromDate'])? $_GET['fromDate'] : '';
    $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : '';
    $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])? $_GET['pupilsightYearGroupID'] : '';
    $minimumCount = isset($_GET['minimumCount'])? $_GET['minimumCount'] : 1;

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setTitle(__('Filter'));
    $form->setClass('noIntBorder fullWidth');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('q', "/modules/Behaviour/behaviour_pattern.php");

    if ($enableDescriptors == 'Y') {
        $negativeDescriptors = getSettingByScope($connection2, 'Behaviour', 'negativeDescriptors');
        $negativeDescriptors = array_map('trim', explode(',', $negativeDescriptors));
        
        $row = $form->addRow();
            $row->addLabel('descriptor', __('Descriptor'));
            $row->addSelect('descriptor')->fromArray($negativeDescriptors)->placeholder()->selected($descriptor);
    }

    if ($enableLevels == 'Y') {
        $optionsLevels = getSettingByScope($connection2, 'Behaviour', 'levels');
        if ($optionsLevels != '') {
            $optionsLevels = explode(',', $optionsLevels);
        }
        $row = $form->addRow();
            $row->addLabel('level', __('Level'));
            $row->addSelect('level')->fromArray($optionsLevels)->placeholder()->selected($level);
    }

    $row = $form->addRow();
        $row->addLabel('date', __('Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
        $row->addDate('fromDate')->setValue(dateConvertBack($guid, $fromDate));

    $row = $form->addRow();
        $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
        $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightRollGroupID)->placeholder();

    $row = $form->addRow();
        $row->addLabel('pupilsightYearGroupID', __('Year Group'));
        $row->addLabel('pupilsightYearGroupID',__('Year Group'));
        $row->addSelectYearGroup('pupilsightYearGroupID')->placeholder()->selected($pupilsightYearGroupID);

    $row = $form->addRow();
        $row->addLabel('minimumCount', __('Minimum Count'));
        $row->addSelect('minimumCount')->fromArray(array(0,1,2,3,4,5,10,25,50))->selected($minimumCount);

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

    echo $form->getOutput();

    echo '<h3>';
    echo __('Behaviour Records');
    echo '</h3>';
    echo '<p>';
    echo __('The students listed below match the criteria above, for negative behaviour records in the current school year. The count is updated according to the criteria above.');
    echo '</p>';

    $behaviourGateway = $container->get(BehaviourGateway::class);
    $studentGateway = $container->get(StudentGateway::class);

    // CRITERIA
    $criteria = $behaviourGateway->newQueryCriteria()
        ->sortBy('count', 'DESC')
        ->sortBy('rollGroup')
        ->sortBy(['surname', 'preferredName'])
        ->filterBy('descriptor', $descriptor)
        ->filterBy('level', $level)
        ->filterBy('fromDate', dateConvert($guid, $fromDate))
        ->filterBy('rollGroup', $pupilsightRollGroupID)
        ->filterBy('yearGroup', $pupilsightYearGroupID)
        ->filterBy('minimumCount', $minimumCount)
        ->fromPOST();

    $records = $behaviourGateway->queryBehaviourPatternsBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);

    // DATA TABLE
    $table = DataTable::createPaginated('behaviourPatterns', $criteria);

    $table->modifyRows($studentGateway->getSharedUserRowHighlighter());

    // COLUMNS
    $table->addColumn('student', __('Student'))
        ->sortable(['surname', 'preferredName'])
        ->format(function ($person) use ($guid) {
            $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&subpage=Behaviour&pupilsightPersonID='.$person['pupilsightPersonID'].'&search=&allStudents=&sort=surname,preferredName';
            return Format::link($url, Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true))
                . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
        });
    $table->addColumn('count', __('Negative Count'))->description(__('(Current Year Only)'));
    $table->addColumn('yearGroup', __('Year Group'));
    $table->addColumn('rollGroup', __('Roll Group'));

    $table->addActionColumn()
        ->addParam('pupilsightPersonID')
        ->addParam('search', '')
        ->format(function ($row, $actions) {
            $actions->addAction('view', __('View Details'))
                ->setURL('/modules/Behaviour/behaviour_view_details.php');
        });

    echo $table->render($records);
}
