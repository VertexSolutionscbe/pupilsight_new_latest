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

$proceed = false;

if (isset($_SESSION[$guid]['username']) == false) {
    $enablePublicRegistration = getSettingByScope($connection2, 'Alumni', 'showPublicRegistration');
    if ($enablePublicRegistration == 'Y') {
        $proceed = true;
    }
}

if ($proceed == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('{orgName} Alumni Registration', [
        'orgName' => $_SESSION[$guid]['organisationNameShort'] ?? ''
    ]));

    $publicRegistrationMinimumAge = getSettingByScope($connection2, 'User Admin', 'publicRegistrationMinimumAge');

    $returns = array();
    $returns['error5'] = sprintf(__('Your request failed because you do not meet the minimum age for joining this site (%1$s years of age).'), $publicRegistrationMinimumAge);
    $returns['error7'] = __('Your request failed because the specified email address has already been registered');
    $returns['success0'] = __('Your registration was successfully submitted: a member of our alumni team will be in touch shortly.');
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/user_manage_edit.php&pupilsightPersonID='.$_GET['editID'].'&search='.$_GET['search'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, $returns);
    }
    ?>
	<p>
		<?php
        echo sprintf(__('This registration form is for former members of the %1$s community who wish to reconnect. Please fill in your details here, and someone from our alumni team will get back to you.'), $_SESSION[$guid]['organisationNameShort']);
    $facebookLink = getSettingByScope($connection2, 'Alumni', 'facebookLink');
    if ($facebookLink != '') {
        echo ' '.sprintf(__('Please don\'t forget to take a look at, and like, our alumni %1$sFacebook page%2$s.'), "<a href='".htmlPrep($facebookLink)."' target='_blank'>", '</a>');
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/Alumni/publicRegistrationProcess.php');
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
        $row->addTextField('officialName')->isRequired()->maxLength(150);

    $row = $form->addRow();
        $row->addLabel('email', __('Email'));
        $email = $row->addEmail('email')->isRequired()->maxLength(50);

    $row = $form->addRow();
        $row->addLabel('gender', __('Gender'));
        $row->addSelectGender('gender')->isRequired();

    $row = $form->addRow();
        $row->addLabel('dob', __('Date of Birth'));
        $row->addDate('dob')->isRequired();

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
        $row->addLabel('username2', __('Username'))->description(__('If you are young enough, this is how you logged into computers.'));
        $row->addTextField('username2')->maxLength(20);

    $row = $form->addRow();
        $row->addLabel('graduatingYear', __('Graduating Year'));
        $row->addSelect('graduatingYear')->fromArray(range(date('Y'), date('Y')-100, -1))->placeholder();

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

    $privacyStatement = getSettingByScope($connection2, 'User Admin', 'publicRegistrationPrivacyStatement');
    if (!empty($privacyStatement)) {
        $form->addRow()->addHeading(__('Privacy Statement'));
        $row = $form->addRow();
            $row->addContent($privacyStatement);
    }

    $agreement = getSettingByScope($connection2, 'User Admin', 'publicRegistrationAgreement');
    if (!empty($agreement)) {
        $form->addRow()->addHeading(__('Agreement'));
        $row = $form->addRow();
            $row->addContent($agreement);

        $row = $form->addRow();
            $row->addLabel('agreement', __('Do you agree to the above?'));
            $row->addCheckbox('agreement')->isRequired()->description(__('Yes'));
    }

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
