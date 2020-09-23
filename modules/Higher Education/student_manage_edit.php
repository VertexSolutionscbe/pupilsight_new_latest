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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/student_manage_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Student Enrolment'), 'student_manage.php');
    $page->breadcrumbs->add(__('Edit Student Enrolment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $higherEducationStudentID = $_GET['higherEducationStudentID'];
    if ($higherEducationStudentID == 'Y') {
        $page->addError(__('You have not specified an activity.'));
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'higherEducationStudentID' => $higherEducationStudentID);
            $sql = "SELECT higherEducationStudentID, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, pupilsightPersonIDAdvisor FROM higherEducationStudent JOIN pupilsightPerson ON (higherEducationStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (higherEducationStudent.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) LEFT JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND higherEducationStudentID=:higherEducationStudentID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError(__('The student cannot be edited due to a database error.'));
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('The selected activity does not exist.'));
        } else {
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('students', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/student_manage_editProcess.php?higherEducationStudentID='.$higherEducationStudentID);
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('name', __('Student'));
                $row->addTextField('name')->isRequired()->readonly()->setValue(formatName('', $values['preferredName'], $values['surname'], 'Student', true, true));

            $sql = "SELECT pupilsightPerson.pupilsightPersonID as value, CONCAT(surname, ', ', preferredName) as name FROM pupilsightPerson JOIN higherEducationStaff ON (pupilsightPerson.pupilsightPersonID=higherEducationStaff.pupilsightPersonID) WHERE status='Full' ORDER BY surname, preferredName";
            $row = $form->addRow();
                $row->addLabel('pupilsightPersonIDAdvisor', __('Advisor'));
                $row->addSelect('pupilsightPersonIDAdvisor')->fromQuery($pdo, $sql)->placeholder();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
