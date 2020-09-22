<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/in_archive.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Archive Records'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
    }
    
    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
    $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightRollGroup.nameShort as rollGroup
            FROM pupilsightPerson 
            JOIN pupilsightIN ON (pupilsightIN.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
            JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID)
            WHERE status='Full' ORDER BY surname, preferredName";
    $result = $pdo->executeQuery($data, $sql);

    $students = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();
    $students = array_map(function($item) {
        return Format::name('', $item['preferredName'], $item['surname'], 'Student', true).' ('.$item['rollGroup'].')';
    }, $students);
    
    if (empty($students)) {
        $page->addError(__('There are no records to display.'));
        return;
    }
    
    $form = Form::create('courseEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/in_archiveProcess.php');
                
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('deleteCurrentPlans', __('Delete Current Plans?'))->description(__('Deletes Individual Education Plan fields only, not Individual Needs Status fields.'));
        $row->addYesNo('deleteCurrentPlans')->required()->selected('N');

    $row = $form->addRow();
        $row->addLabel('title', __('Archive Title'));
        $row->addTextField('title')->required()->maxLength(50);
                        
    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Students'));
        $row->addCheckbox('pupilsightPersonID')->fromArray($students)->addCheckAllNone();

    $row = $form->addRow();
        $row->addSubmit();

    echo $form->getOutput();
}
