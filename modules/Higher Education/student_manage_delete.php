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

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/student_manage_delete.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Student Enrolment'), 'student_manage.php');
    $page->breadcrumbs->add(__('Delete Student Enrolment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $higherEducationStudentID = $_GET['higherEducationStudentID'];
    if ($higherEducationStudentID == '') {
        $page->addError(__('You have not specified a student member.'));
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'higherEducationStudentID' => $higherEducationStudentID);
            $sql = "SELECT higherEducationStudentID, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup FROM higherEducationStudent JOIN pupilsightPerson ON (higherEducationStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (higherEducationStudent.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) LEFT JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND higherEducationStudentID=:higherEducationStudentID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('The selected student member does not exist.'));
        } else {
            //Let's go!
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/student_manage_deleteProcess.php?higherEducationStudentID=$higherEducationStudentID");
            $form->addHiddenValue('higherEducationStudentID', $higherEducationStudentID);
            echo $form->getOutput();
        }
    }
}
