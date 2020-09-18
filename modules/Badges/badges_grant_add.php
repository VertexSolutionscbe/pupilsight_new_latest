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
include './modules/'.$pupilsight->session->get('module').'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Badges/badges_grant_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';

    $page->breadcrumbs
        ->add(__('Grant Badges'), 'badges_grant.php&pupilsightSchoolYearID='.$pupilsightSchoolYearID)
        ->add(__('Add'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo "<div class='linkTop'>";

    //Get the pupilsight persion and badge IDs
    //Set '' for safety
    $pupilsightPersonID2 = '';
    $badgesBadgeID2 = '';
    if (isset($_GET['pupilsightPersonID2']) ||  isset($_GET['badgesBadgeID2'])) {
        //Only assign variable when it exists
        $pupilsightPersonID2 = $_GET['pupilsightPersonID2'] ?? '';
        $badgesBadgeID2 = $_GET['badgesBadgeID2'] ?? '';

        //Add a "Back to Results" link
        if (!empty($pupilsightPersonID2) OR !empty($badgesBadgeID2)) {
            echo "<a href='".$pupilsight->session->get('absoluteURL').'/index.php?q=/modules/Badges/badges_grant.php&pupilsightPersonID2='.$pupilsightPersonID2.'&badgesBadgeID2='.$badgesBadgeID2."'>".__('Back to Search Results').'</a>';
        }
    }
    echo '</div>';

    $form = Form::create('grantBadges', $pupilsight->session->get('absoluteURL').'/modules/'.$pupilsight->session->get('module').'/badges_grant_addProcess.php?pupilsightPersonID2='.$pupilsightPersonID2.'&badgesBadgeID2='.$badgesBadgeID2."&pupilsightSchoolYearID=$pupilsightSchoolYearID");

    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $pupilsight->session->get('address'));
    $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonIDMulti', __('Students'));
        $row->addSelectUsers('pupilsightPersonIDMulti', $pupilsight->session->get('pupilsightSchoolYearID'), ['includeStudents' => true])->selectMultiple()->isRequired();

    $sql = "SELECT badgesBadgeID as value, name, category FROM badgesBadge WHERE active='Y' ORDER BY category, name";
    $row = $form->addRow();
        $row->addLabel('badgesBadgeID', __('Badge'));
        $row->addSelect('badgesBadgeID')->fromQuery($pdo, $sql, [], 'category')->isRequired()->placeholder();

    $row = $form->addRow();
        $row->addLabel('date', __('Date'));
        $row->addDate('date')->setValue(date($pupilsight->session->get('i18n')['dateFormatPHP']))->isRequired();

    $col = $form->addRow()->addColumn();
        $col->addLabel('comment', __('Comment'));
        $col->addTextArea('comment')->setRows(8)->setClass('w-full');

    $row = $form->addRow();
        $row->addSubmit();

    echo $form->getOutput();
}
