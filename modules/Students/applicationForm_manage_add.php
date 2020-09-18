<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

//Module includes from User Admin (for custom fields)
include './modules/User Admin/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/applicationForm_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Applications'), 'applicationForm_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Add Form'));

    $form = Form::create('addApplication', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/applicationForm.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $types = array(
        'blank' => __('Blank Application'),
        'family' => __('Current').' '.__('Family'),
        'person' => __('Current').' '.__('User'),
    );

    $row = $form->addRow();
        $row->addLabel('applicationType', __('Type'));
        $row->addSelect('applicationType')->fromArray($types)->required();

    $sql = "SELECT pupilsightFamily.pupilsightFamilyID as value, CONCAT(pupilsightFamily.name, ' (', GROUP_CONCAT(DISTINCT CONCAT(pupilsightPerson.preferredName, ' ', pupilsightPerson.surname) SEPARATOR ', '), ')') as name FROM pupilsightFamily
        JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
        JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        GROUP BY pupilsightFamily.pupilsightFamilyID
        HAVING count(DISTINCT pupilsightFamilyAdult.pupilsightPersonID) > 0
        ORDER BY name";

    $form->toggleVisibilityByClass('typeFamily')->onSelect('applicationType')->when('family');

    $row = $form->addRow()->addClass('typeFamily');
        $row->addLabel('pupilsightFamilyID', __('Family'));
        $row->addSelect('pupilsightFamilyID')->fromQuery($pdo, $sql)->required();

    $sql = "SELECT pupilsightPersonID as value, CONCAT(pupilsightPerson.surname, ', ', pupilsightPerson.preferredName, ' (', pupilsightRole.category, ': ', pupilsightPerson.username, ')') as name
            FROM pupilsightPerson
            JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary)
            WHERE pupilsightRole.category <> 'Student'
            AND pupilsightPerson.status='Full'
            ORDER BY pupilsightPerson.surname, pupilsightPerson.preferredname";

    $form->toggleVisibilityByClass('typePerson')->onSelect('applicationType')->when('person');

    $row = $form->addRow()->addClass('typePerson');
        $row->addLabel('pupilsightPersonID', __('Person'));
        $row->addSelect('pupilsightPersonID')->fromQuery($pdo, $sql)->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
