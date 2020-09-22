<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\DataUpdater\PersonUpdateGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/report_student_dataUpdaterHistory.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Student Data Updater History'));
    echo '<p>';
    echo __('This report allows a user to select a range of students and check whether or not they have had their personal and medical data updated after a specified date.');
    echo '</p>';

    echo '<h2>';
    echo __('Choose Students');
    echo '</h2>';

    $cutoffDate = getSettingByScope($connection2, 'Data Updater', 'cutoffDate');
    $cutoffDate = !empty($cutoffDate)? Format::date($cutoffDate) : Format::dateFromTimestamp(time() - (604800 * 26)); 

    $choices = isset($_POST['members'])? $_POST['members'] : array();
    $nonCompliant = isset($_POST['nonCompliant'])? $_POST['nonCompliant'] : '';
    $date = isset($_POST['date'])? $_POST['date'] : $cutoffDate;

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/report_student_dataUpdaterHistory.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    
    $row = $form->addRow();
        $row->addLabel('members', __('Students'));
        $row->addSelectStudent('members', $_SESSION[$guid]['pupilsightSchoolYearID'], array('byRoll' => true, 'byName' => true))
            ->selectMultiple()
            ->required()
            ->selected($choices);

    $row = $form->addRow();
        $row->addLabel('date', __('Date'))->description(__('Earliest acceptable update'));
        $row->addDate('date')->setValue($date)->required();

    $row = $form->addRow();
        $row->addLabel('nonCompliant', __('Show Only Non-Compliant?'))->description(__('If not checked, show all. If checked, show only non-compliant students.'));
        $row->addCheckbox('nonCompliant')->setValue('Y')->checked($nonCompliant);
    
    $row = $form->addRow();
        $row->addSubmit();
    
    echo $form->getOutput();

    if (count($choices) > 0) {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        $gateway = $container->get(PersonUpdateGateway::class);

        // QUERY
        $criteria = $gateway->newQueryCriteria()
            ->sortBy(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
            ->filterBy('cutoff', $nonCompliant == 'Y'? Format::dateConvert($date) : '')
            ->fromPOST();

        $dataUpdates = $gateway->queryStudentUpdaterHistory($criteria, $_SESSION[$guid]['pupilsightSchoolYearID'], $choices);
        
        // Join a set of parent emails per student
        $people = $dataUpdates->getColumn('pupilsightPersonID');
        $parentEmails = $gateway->selectParentEmailsByPersonID($people)->fetchGrouped();
        $dataUpdates->joinColumn('pupilsightPersonID', 'parentEmails', $parentEmails);

        // Function to display the updated date based on the cutoff date
        $dateCutoff = DateTime::createFromFormat('Y-m-d H:i:s', Format::dateConvert($date).' 00:00:00');
        $dataChecker = function($dateUpdated) use ($dateCutoff) {
            $dateDisplay = !empty($dateUpdated)? Format::dateTime($dateUpdated) : __('No data');
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateUpdated);

            return empty($dateUpdated) || $dateCutoff > $date
                ? '<span style="color: #ff0000; font-weight: bold">'.$dateDisplay.'</span>'
                : $dateDisplay;
        };

        // DATA TABLE
        $table = DataTable::createPaginated('studentUpdaterHistory', $criteria);
        $table->addMetaData('post', ['members' => $choices]);

        $count = $dataUpdates->getPageFrom();
        $table->addColumn('count', '')
            ->notSortable()
            ->format(function ($row) use (&$count) {
                return $count++;
            });

        $table->addColumn('student', __('Student'))
            ->sortable(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
            ->format(function ($row) use ($guid) {
                $name = Format::name('', $row['preferredName'], $row['surname'], 'Student', true);
                return Format::link($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$row['pupilsightPersonID'], $name);
            });

        $table->addColumn('rollGroupName', __('Roll Group'));

        $table->addColumn('personalUpdate', __('Personal Data'))
            ->format(function($row) use ($dataChecker) {
                return $dataChecker($row['personalUpdate']);
            });

        $table->addColumn('medicalUpdate', __('Medical Data'))
            ->format(function($row) use ($dataChecker) {
                return $dataChecker($row['medicalUpdate']);
            });

        $table->addColumn('parentEmails', __('Parent Emails'))
            ->notSortable()
            ->format(function ($row) {
                return is_array($row['parentEmails'])? implode('<br/>', array_column($row['parentEmails'], 'email')) : '';
            });

        echo $table->render($dataUpdates);
    }
}
