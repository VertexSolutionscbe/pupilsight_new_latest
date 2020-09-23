<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Domain\Students\MedicalGateway;
use Pupilsight\Domain\RollGroups\RollGroupGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_students_byRollGroup.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $pupilsightRollGroupID = (isset($_GET['pupilsightRollGroupID']) ? $_GET['pupilsightRollGroupID'] : null);
    $view = isset($_GET['view']) ? $_GET['view'] : 'basic';
    $viewMode = isset($_REQUEST['format']) ? $_REQUEST['format'] : '';

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Students by Roll Group'));

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setTitle(__('Choose Roll Group'))
            ->setFactory(DatabaseFormFactory::create($pdo))
            ->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_students_byRollGroup.php");

        $row = $form->addRow();
            $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
            $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'], true)->selected($pupilsightRollGroupID)->placeholder()->required();

        $row = $form->addRow();
            $row->addLabel('view', __('View'));
            $row->addSelect('view')->fromArray(array('basic' => __('Basic'), 'extended' =>__('Extended')))->selected($view)->required();

        $row = $form->addRow();
            $row->addFooter();
            $row->addSearchSubmit($pupilsight->session);

        echo $form->getOutput();
    }

    // Cancel out early if there's no roll group selected
    if (empty($pupilsightRollGroupID)) return;

    $rollGroupGateway = $container->get(RollGroupGateway::class);
    $studentGateway = $container->get(StudentGateway::class);
    $medicalGateway = $container->get(MedicalGateway::class);

    // QUERY
    $criteria = $studentGateway->newQueryCriteria()
        ->sortBy(['rollGroup', 'surname', 'preferredName'])
        ->pageSize(!empty($viewMode) ? 0 : 50)
        ->filterBy('view', $view)
        ->fromArray($_POST);
    
    $students = $studentGateway->queryStudentEnrolmentByRollGroup($criteria, $pupilsightRollGroupID != '*' ? $pupilsightRollGroupID : null);

    // DATA TABLE
    $table = ReportTable::createPaginated('studentsByRollGroup', $criteria)->setViewMode($viewMode, $pupilsight->session);
    $table->setTitle(__('Report Data'));
    $table->setDescription(function () use ($pupilsightRollGroupID, $rollGroupGateway) {
        $output = '';

        if ($pupilsightRollGroupID == '*') return $output;
        
        if ($rollGroup = $rollGroupGateway->getRollGroupByID($pupilsightRollGroupID)) {
            $output .= '<b>'.__('Roll Group').'</b>: '.$rollGroup['name'];
        }
        if ($tutors = $rollGroupGateway->selectTutorsByRollGroup($pupilsightRollGroupID)->fetchAll()) {
            $output .= '<br/><b>'.__('Tutors').'</b>: '.Format::nameList($tutors, 'Staff');
        }

        return $output;
    });

    $table->addMetaData('filterOptions', [
        'view:basic'    => __('View').': '.__('Basic'),
        'view:extended' => __('View').': '.__('Extended'),
    ]);

    $table->addColumn('rollGroup', __('Roll Group'))->width('5%');
    $table->addColumn('student', __('Student'))
        ->sortable(['surname', 'preferredName'])
        ->format(function ($person) {
            return Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
        });

    if ($criteria->hasFilter('view', 'extended')) {
        $table->addColumn('gender', __('Gender'));
        $table->addColumn('dob', __('Age').'<br/>'.Format::small('DOB'))
            ->format(function ($values) {
                return !empty($values['dob'])
                    ? Format::age($values['dob'], true).'<br/>'.Format::small(Format::date($values['dob']))
                    : '';
            });
        $table->addColumn('citizenship1', __('Nationality'))
            ->format(function ($values) {
                $output = '';
                if (!empty($values['citizenship1'])) {
                    $output .= $values['citizenship1'].'<br/>';
                }
                if (!empty($values['citizenship2'])) {
                    $output .= $values['citizenship2'].'<br/>';
                }
                return $output;
            });
        $table->addColumn('transport', __('Transport'));
        $table->addColumn('house', __('House'));
        $table->addColumn('lockerNumber', __('Locker'));
        $table->addColumn('longTermMedication', __('Medical'))->format(function ($values) use ($medicalGateway) {
            $output = '';

            if (!empty($values['longTermMedication'])) {
                if ($values['longTermMedication'] == 'Y') {
                    $output .= '<b><i>'.__('Long Term Medication').'</i></b>: '.$values['longTermMedicationDetails'].'<br/>';
                }

                if ($values['conditionCount'] > 0) {
                    $conditions = $medicalGateway->selectMedicalConditionsByID($values['pupilsightPersonMedicalID'])->fetchAll();

                    foreach ($conditions as $index => $condition) {
                        $output .= '<b><i>'.__('Condition').' '.($index+1).'</i></b>: '.$condition['name'];
                        $output .= ' <span style="color: #'.$condition['alertColor'].'; font-weight: bold">('.__($condition['risk']).' '.__('Risk').')</span>';
                        $output .= '<br/>';
                    }
                }
            } else {
                $output = '<i>'.__('No medical data').'</i>';
            }

            return $output;
        });
    }
    
    echo $table->render($students);
}
