<?php
/*
Pupilsight, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/student_manage_add.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Student Enrolment'), 'student_manage.php');
    $page->breadcrumbs->add(__('Add Student Enrolment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('students', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/student_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('Members', __('Students'));
        $row->addSelectStudent('Members', $_SESSION[$guid]['pupilsightSchoolYearID'], array('byRoll' => true))->selectMultiple()->isRequired();

    $sql = "SELECT pupilsightPerson.pupilsightPersonID as value, CONCAT(surname, ', ', preferredName) as name FROM pupilsightPerson JOIN higherEducationStaff ON (pupilsightPerson.pupilsightPersonID=higherEducationStaff.pupilsightPersonID) WHERE status='Full' ORDER BY surname, preferredName";
    $row = $form->addRow();
        $row->addLabel('pupilsightPersonIDAdvisor', __('Advisor'));
        $row->addSelect('pupilsightPersonIDAdvisor')->fromQuery($pdo, $sql)->placeholder();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
