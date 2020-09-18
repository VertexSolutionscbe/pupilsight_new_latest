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
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Alumni/alumni_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
      ->add(__('Manage Alumni'), 'alumni_manage.php')
      ->add(__('Edit'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $graduatingYear = isset($_GET['graduatingYear'])? $_GET['graduatingYear'] : '';
    $alumniAlumnusID = isset($_GET['alumniAlumnusID'])? $_GET['alumniAlumnusID'] : '';

    if (empty($alumniAlumnusID)) { 
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('alumniAlumnusID' => $alumniAlumnusID);
            $sql = 'SELECT alumniAlumnus.* FROM alumniAlumnus WHERE alumniAlumnusID=:alumniAlumnusID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            if ($graduatingYear != '') {
                echo "<div class='linkTop'>";
                  echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Alumni/alumni_manage.php&graduatingYear='.$graduatingYear."'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            //Let's go!
            $values = $result->fetch();

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/alumni_manage_editProcess.php?alumniAlumnusID='.$alumniAlumnusID.'&graduatingYear='.$graduatingYear);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('Personal Details'));

            $row = $form->addRow();
                $row->addLabel('title', __('Title'));
                $row->addSelectTitle('title');

            $row = $form->addRow();
                $row->addLabel('firstName', __('First Name'));
                $row->addTextField('firstName')->isRequired()->maxLength(30);
                
            $row = $form->addRow();
                $row->addLabel('surname', __('Surname'));
                $row->addTextField('surname')->isRequired()->maxLength(30);

            $row = $form->addRow();
                $row->addLabel('officialName', __('Official Name'))->description(__('Full name as shown in ID documents.'));
                $row->addTextField('officialName')->maxLength(150);

            $row = $form->addRow();
                $row->addLabel('email', __('Email'));
                $email = $row->addEmail('email')->isRequired()->maxLength(50);

            $row = $form->addRow();
                $row->addLabel('gender', __('Gender'));
                $row->addSelectGender('gender')->isRequired();

            $row = $form->addRow();
                $row->addLabel('dob', __('Date of Birth'));
                $row->addDate('dob');
                
            $formerRoles = array(
                'Student' => __('Student'),
                'Staff' => __('Staff'),
                'Parent' => __('Parent'),
                'Other' => __('Other'),
            );
            $row = $form->addRow();
                $row->addLabel('formerRole', __('Main Role'))->description(__('In what way, primarily, were you involved with the school?'));
                $row->addSelect('formerRole')->fromArray($formerRoles)->isRequired()->placeholder();

            $form->addRow()->addHeading(__('Tell Us More About Yourself'));

            $row = $form->addRow();
                $row->addLabel('maidenName', __('Maiden Name'))->description(__('Your surname prior to marriage.'));
                $row->addTextField('maidenName')->maxLength(30);

            $row = $form->addRow();
                $row->addLabel('username', __('Username'))->description(__('If you are young enough, this is how you logged into computers.'));
                $row->addTextField('username')->maxLength(20);

            $row = $form->addRow();
                $row->addLabel('graduatingYear', __('Graduating Year'));
                $row->addSelect('graduatingYear')->fromArray(range(date('Y'), date('Y')-100, -1))->selected($graduatingYear)->placeholder();

            $row = $form->addRow();
                $row->addLabel('address1Country', __('Current Country of Residence'));
                $row->addSelectCountry('address1Country')->placeholder('');

            $row = $form->addRow();
                $row->addLabel('profession', __('Profession'));
                $row->addTextField('profession')->maxLength(30);

            $row = $form->addRow();
                $row->addLabel('employer', __('Employer'));
                $row->addTextField('employer')->maxLength(30);

            $row = $form->addRow();
                $row->addLabel('jobTitle', __('Job Title'));
                $row->addTextField('jobTitle')->maxLength(30);

            $form->addRow()->addHeading(__('Link To Pupilsight User'));

            $row = $form->addRow();
                $row->addLabel('pupilsightPersonID', __('Existing User'));
                $row->addSelectUsers('pupilsightPersonID')->placeholder();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
