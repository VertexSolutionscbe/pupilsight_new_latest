<?php
/*
Pupilsight, Flexible & Open School System
*/

use Psr\Container\ContainerInterface;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Behaviour\BehaviourGateway;
use Pupilsight\Domain\Students\StudentGateway;

function getBehaviourRecord(ContainerInterface $container, $pupilsightPersonID)
{
    $output = '';

    $guid = $container->get('config')->getConfig('guid');
    $connection2 = $container->get('db')->getConnection();

    $enableDescriptors = getSettingByScope($connection2, 'Behaviour', 'enableDescriptors');
    $enableLevels = getSettingByScope($connection2, 'Behaviour', 'enableLevels');

    $behaviourGateway = $container->get(BehaviourGateway::class);
    $studentGateway = $container->get(StudentGateway::class);

    $schoolYears = $studentGateway->selectAllStudentEnrolmentsByPerson($pupilsightPersonID)->fetchAll();

    if (empty($schoolYears)) {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('There are no records to display.');
        $output .= '</div>';
    } else {

        foreach ($schoolYears as $schoolYear) {

            // CRITERIA
            $criteria = $behaviourGateway->newQueryCriteria()
                ->sortBy('timestamp', 'DESC')
                ->pageSize(0)
                ->fromPOST($schoolYear['pupilsightSchoolYearID']);

            $behaviourRecords = $behaviourGateway->queryBehaviourRecordsByPerson($criteria, $schoolYear['pupilsightSchoolYearID'], $pupilsightPersonID);

            $table = DataTable::createPaginated('behaviour'.$schoolYear['pupilsightSchoolYearID'], $criteria);
            $table->setTitle($schoolYear['name']);

            if ($schoolYear['pupilsightSchoolYearID'] == $_SESSION[$guid]['pupilsightSchoolYearID']) {
                if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage.php')) {
                    $table->addHeaderAction('add', __('Add'))
                        ->setURL('/modules/Behaviour/behaviour_manage_add.php')
                        ->addParam('pupilsightPersonID', $pupilsightPersonID)
                        ->addParam('pupilsightRollGroupID', '')
                        ->addParam('pupilsightYearGroupID', '')
                        ->addParam('type', '')
                        ->displayLabel();
                }

                $policyLink = getSettingByScope($connection2, 'Behaviour', 'policyLink');
                if (!empty($policyLink)) {
                    $table->addHeaderAction('policy', __('View Behaviour Policy'))
                        ->setExternalURL($policyLink)
                        ->displayLabel()
                        ->prepend('&nbsp|&nbsp');
                }
            }

            $table->addMetaData('hidePagination', true);

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

            $table->addColumn('date', __('Date'))
                ->format(function($beahviour) {
                    if (substr($beahviour['timestamp'], 0, 10) > $beahviour['date']) {
                        return __('Updated:').' '.Format::date($beahviour['timestamp']).'<br/>'
                            . __('Incident:').' '.Format::date($beahviour['date']).'<br/>';
                    } else {
                        return Format::date($beahviour['timestamp']);
                    }
                });
            
            $table->addColumn('type', __('Type'))
                ->width('5%')
                ->format(function($beahviour) use ($guid) {
                    if ($beahviour['type'] == 'Negative') {
                        return "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/> ";
                    } elseif ($beahviour['type'] == 'Positive') {
                        return "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/> ";
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

            if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage.php') && $schoolYear['pupilsightSchoolYearID'] == $_SESSION[$guid]['pupilsightSchoolYearID']) {
                $highestAction = getHighestGroupedAction($guid, '/modules/Behaviour/behaviour_manage.php', $connection2);
                
                $table->addActionColumn()
                    ->addParam('pupilsightPersonID', $pupilsightPersonID)
                    ->addParam('pupilsightRollGroupID', '')
                    ->addParam('pupilsightYearGroupID', '')
                    ->addParam('type', '')
                    ->addParam('pupilsightBehaviourID')
                    ->format(function ($person, $actions) use ($guid, $highestAction) {
                        if ($highestAction == 'Manage Behaviour Records_all'
                        || ($highestAction == 'Manage Behaviour Records_my' && $person['pupilsightPersonIDCreator'] == $_SESSION[$guid]['pupilsightPersonID'])) {
                            $actions->addAction('edit', __('Edit'))
                                ->setURL('/modules/Behaviour/behaviour_manage_edit.php');
                        }
                    });
            }

            $output .= $table->render($behaviourRecords);
        }
    }
    return $output;
}
