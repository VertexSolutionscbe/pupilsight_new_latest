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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_delete.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage References'), 'references_manage.php', [
        'pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID'] ?? '',
        'higherEducationReferenceID' => $_GET['higherEducationReferenceID'] ?? '',
    ]);
    $page->breadcrumbs->add(__('Delete Reference'));

    $role = staffHigherEducationRole($_SESSION[$guid]['pupilsightPersonID'], $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
        $higherEducationReferenceID = $_GET['higherEducationReferenceID'];
        if ($higherEducationReferenceID == '' or $pupilsightSchoolYearID == '') {
            $page->addError(__('You have not specified a reference.'));
        } else {
            try {
                $data = array('higherEducationReferenceID' => $higherEducationReferenceID);
                $sql = 'SELECT * FROM higherEducationReference WHERE higherEducationReferenceID=:higherEducationReferenceID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The selected reference does not exist.'));
            } else {
                //Let's go!
                $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/references_manage_deleteProcess.php?higherEducationReferenceID=$higherEducationReferenceID&pupilsightSchoolYearID=$pupilsightSchoolYearID");
                $form->addHiddenValue('higherEducationReferenceID', $higherEducationReferenceID);
                echo $form->getOutput();
            }
        }
    }
}
