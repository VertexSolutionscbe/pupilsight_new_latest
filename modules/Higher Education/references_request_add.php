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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_request_add.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Request References'), 'references_request.php')
        ->add(__('Request A Reference'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check for student enrolment
    if (studentEnrolment($_SESSION[$guid]['pupilsightPersonID'], $connection2) == false) {
        $page->addError(__('You have not been enrolled for higher education applications.'));
    } else {

        //START FORM
        $form = Form::create('requestReference', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/references_request_addProcess.php');
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $types = [
            'Composite Reference' =>__('Composite Reference'),
            'US Reference' => __('US Reference'),
        ];

        $row = $form->addRow();
            $row->addLabel('type', __('Type'));
            $row->addSelect('type')->fromArray($types)->placeholder()->isRequired();

        $form->toggleVisibilityByCLass('pupilsightPersonIDReferee')->onSelect('type')->when('US Reference');
        $row = $form->addRow()->addClass('pupilsightPersonIDReferee');
            $row->addLabel('pupilsightPersonIDReferee', __('Referee'));
            $row->addSelectStaff('pupilsightPersonIDReferee')->placeholder();

        $row = $form->addRow();
            $column = $row->addColumn();
                $column->addLabel('notes', __('Notes'))->description(__('Any information you need to share with your referee(s), that is not already in your general reference notes'));
                $column->addTextArea('notes')->setRows(4)->setClass('w-full');

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();
    }
}
