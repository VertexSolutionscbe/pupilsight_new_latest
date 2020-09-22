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

$enableDescriptors = getSettingByScope($connection2, 'Behaviour', 'enableDescriptors');
$enableLevels = getSettingByScope($connection2, 'Behaviour', 'enableLevels');

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage.php') == false) {
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
        $page->breadcrumbs->add(__('Manage Behaviour Records'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        $pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : '';
        $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : '';
        $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])? $_GET['pupilsightYearGroupID'] : '';
        $type = isset($_GET['type'])? $_GET['type'] : '';

        $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setTitle(__('Filter'));
        $form->setClass('noIntBorder fullWidth');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('q', "/modules/Behaviour/behaviour_manage.php");

        $row = $form->addRow();
            $row->addLabel('pupilsightPersonID',__('Student'));
            $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightPersonID)->placeholder();

        $row = $form->addRow();
            $row->addLabel('pupilsightRollGroupID',__('Roll Group'));
            $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightRollGroupID)->placeholder();

        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID',__('Year Group'));
            $row->addSelectYearGroup('pupilsightYearGroupID')->placeholder()->selected($pupilsightYearGroupID);

        $row = $form->addRow();
            $row->addLabel('type',__('Type'));
            $row->addSelect('type')->fromArray(array('Positive', 'Negative'))->selected($type)->placeholder();


        $row = $form->addRow()->addClass('right_align');
            $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

        echo $form->getOutput();

        $behaviourGateway = $container->get(BehaviourGateway::class);

        // CRITERIA
        $criteria = $behaviourGateway->newQueryCriteria()
            ->sortBy('timestamp', 'DESC')
            ->filterBy('student', $pupilsightPersonID)
            ->filterBy('rollGroup', $pupilsightRollGroupID)
            ->filterBy('yearGroup', $pupilsightYearGroupID)
            ->filterBy('type', $type)
            ->fromPOST();

        
        if ($highestAction == 'Manage Behaviour Records_all') {
            $records = $behaviourGateway->queryBehaviourBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);
        } else if ($highestAction == 'Manage Behaviour Records_my') {
            $records = $behaviourGateway->queryBehaviourBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID']);
        } else {
            return;
        }

        // DATA TABLE
        $table = DataTable::createPaginated('behaviourManage', $criteria);
        $table->setTitle(__('Behaviour Records'));

        $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/Behaviour/behaviour_manage_add.php')
            ->addParam('pupilsightPersonID', $pupilsightPersonID)
            ->addParam('pupilsightRollGroupID', $pupilsightRollGroupID)
            ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
            ->addParam('type', $type)
            ->displayLabel()
            ->append('&nbsp; &nbsp;');

        $table->addHeaderAction('addMultiple', __('Add Multiple'))
            ->setURL('/modules/Behaviour/behaviour_manage_addMulti.php')
            ->addParam('pupilsightPersonID', $pupilsightPersonID)
            ->addParam('pupilsightRollGroupID', $pupilsightRollGroupID)
            ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
            ->addParam('type', $type)
            ->displayLabel();

        $policyLink = getSettingByScope($connection2, 'Behaviour', 'policyLink');
        if (!empty($policyLink)) {
            $table->addHeaderAction('policy', __('View Behaviour Policy'))
                ->setExternalURL($policyLink)
                ->displayLabel()
                ->prepend('&nbsp|&nbsp');
        }

        $table->addExpandableColumn('comment')
            ->format(function($beahviour) {
                $output = '';
                if (!empty($beahviour['comment'])) {
                    $output .= '<strong>'.__('Incident').'</strong><br/>';
                    $output .= nl2brr($beahviour['comment']).'<br/>';
                }
                if (!empty($beahviour['followup'])) {
                    $output .= '<br/><strong>'.__('Follow Up').'</strong><br/>';
                    $output .= nl2brr($beahviour['followup']).'<br/>';
                }
                return $output;
            });

        $table->addColumn('student', __('Student'))
            ->description(__('Roll Group'))
            ->sortable(['student.surname', 'student.preferredName'])
            ->width('25%')
            ->format(function($person) use ($guid) {
                $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$person['pupilsightPersonID'].'&subpage=Behaviour&search=&allStudents=&sort=surname,preferredName';
                return '<b>'.Format::link($url, Format::name('', $person['preferredName'], $person['surname'], 'Student', true)).'</b>'
                      .'<br/><small><i>'.$person['rollGroup'].'</i></small>';
            });

        $table->addColumn('date', __('Date'))
            ->format(function($beahviour) {
                if (substr($beahviour['timestamp'], 0, 10) > $beahviour['date']) {
                    return __('Updated:').' '.Format::date($beahviour['timestamp']).'<br/>'
                         . __('Incident:').' '.Format::date($beahviour['date']).'<br/>';
                } else {
                    return Format::date($beahviour['timestamp']);
                }
            });
            
        $table->addColumn('type', __('Type'))->addClass('font_align')
          
            ->format(function($beahviour) use ($guid) {
                if ($beahviour['type'] == 'Negative') {
                    return "<i class='mdi mdi-close mdi-24px px-4 x_icon'></i> ";
                } elseif ($beahviour['type'] == 'Positive') {
                    return "<i class='mdi mdi-check mdi-24px px-4 yes_icon'></i>";
                }
            });

        if ($enableDescriptors == 'Y') {
            $table->addColumn('descriptor', __('Descriptor'));
        }

        if ($enableLevels == 'Y') {
            $table->addColumn('level', __('Level'))->width('15%');
        }

        $table->addColumn('teacher', __('Teacher'))
            ->sortable(['preferredNameCreator', 'surnameCreator'])
            ->width('25%')
            ->format(function($person) {
                return Format::name($person['titleCreator'], $person['preferredNameCreator'], $person['surnameCreator'], 'Staff');
            });

        $table->addActionColumn()
            ->addParam('pupilsightPersonID', $pupilsightPersonID)
            ->addParam('pupilsightRollGroupID', $pupilsightRollGroupID)
            ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
            ->addParam('type', $type)
            ->addParam('pupilsightBehaviourID')
            ->format(function ($person, $actions) {
                $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Behaviour/behaviour_manage_edit.php');

                $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Behaviour/behaviour_manage_delete.php');
            });

        echo $table->render($records);
    }
}
