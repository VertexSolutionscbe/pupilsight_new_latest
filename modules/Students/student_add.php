<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Students/student_add.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
         ->add(__('Manage Students'), 'students.php')
         ->add(__('Add Student'));

    $returns = array();
    $returns['error5'] = __('Your request failed because your passwords did not match.');
    $returns['error6'] = __('Your request failed due to an attachment error.');
    $returns['error7'] = __('Your request failed because your password does not meet the minimum requirements for strength.');
    $returns['warning1'] = __('Your request was completed successfully, but one or more images were the wrong size and so were not saved.');
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/student_edit.php&pupilsightPersonID='.$_GET['editID'].'&search='.$_GET['search'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, $returns);
    }

    $search = (isset($_GET['search']))? $_GET['search'] : '';

    if (!empty($search)) {
        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/students.php&search='.$search."'>".__('Back to Search Results').'</a>';
        echo '</div>';
    }

    $sqlp = 'SELECT id,name FROM fee_category WHERE status="1"';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();
    $fee_category=array();  
    $fee_category2=array();  
    $fee_category1=array(''=>'Select fee category');
    foreach ($rowdataprog as $dt) {
    $fee_category2[$dt['id']] = $dt['name'];
    }
    $fee_category= $fee_category1 + $fee_category2; 
    // echo "<div style='height:50px;'><div class='float-left mb-2'><a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/User Admin/student_add.php' class='btn btn-primary active'>Student</a>";  
    // echo "&nbsp;&nbsp;<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/User Admin/parent_add.php' class='btn btn-primary'>Parent</a></div><div class='float-none'></div></div>"; 

    $form = Form::create('addUser', $_SESSION[$guid]['absoluteURL'].'/modules/User Admin'.'/student_addProcess.php?search='.$search);
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    // BASIC INFORMATION
    $form->addRow("basic_information")->addHeading(__('Basic Information'));

    
    // Put together an array of this user's current roles
    $currentUserRoles = (is_array($_SESSION[$guid]['pupilsightRoleIDAll'])) ? array_column($_SESSION[$guid]['pupilsightRoleIDAll'], 0) : array();
    $currentUserRoles[] = $_SESSION[$guid]['pupilsightRoleIDPrimary'];

    $data = array();
    $sql = "SELECT * FROM pupilsightRole WHERE pupilsightRoleID = '003' ORDER BY name";
    $result = $pdo->executeQuery($data, $sql);

    // Get all roles and filter roles based on role restrictions
    $availableRoles = ($result && $result->rowCount() > 0)? $result->fetchAll() : array();
    $availableRoles = array_reduce($availableRoles, function ($carry, $item) use (&$currentUserRoles) {
        if ($item['restriction'] == 'Admin Only') {
            if (!in_array('001', $currentUserRoles)) return $carry;
        } else if ($item['restriction'] == 'Same Role') {
            if (!in_array($item['pupilsightRoleID'], $currentUserRoles) && !in_array('001', $currentUserRoles)) return $carry;
        }
        $carry[$item['pupilsightRoleID']] = $item['name'];
        return $carry;
    }, array());

    $row = $form->addRow("row_pupilsightRoleIDPrimary");
        $row->addLabel('pupilsightRoleIDPrimary', __('Primary Role'))->description(__('Controls what a user can do and see.'));
        $row->addSelect('pupilsightRoleIDPrimary')->fromArray($availableRoles)->required();


    $row = $form->addRow("row_title");
        $row->addLabel('title', __('Title'));
        $row->addSelectTitle('title');

    $row = $form->addRow("row_surname");
        $row->addLabel('surname', __('Surname'))->description(__('Family name as shown in ID documents.'));
        $row->addTextField('surname')->maxLength(60);

    $row = $form->addRow("row_firstName");
        $row->addLabel('firstName', __('First Name'))->description(__('First name as shown in ID documents.'));
        $row->addTextField('firstName')->maxLength(60);

    $row = $form->addRow("row_preferredName");
        $row->addLabel('preferredName', __('Preferred Name'))->description(__('Most common name, alias, nickname, etc.'));
        $row->addTextField('preferredName')->maxLength(60);

    $row = $form->addRow("row_officialName");
        $row->addLabel('officialName', __('Official Name'))->description(__('Full name as shown in ID documents.'));
        $row->addTextField('officialName')->required()->maxLength(150)->setTitle(__('Please enter full name as shown in ID documents'));

    $row = $form->addRow("row_nameInCharacters");
        $row->addLabel('nameInCharacters', __('Name In Characters'))->description(__('Chinese or other character-based name.'));
        $row->addTextField('nameInCharacters')->maxLength(60);

    $row = $form->addRow("row_gender");
        $row->addLabel('gender', __('Gender'));
        $row->addSelectGender('gender');

    $row = $form->addRow("row_dob");
        $row->addLabel('dob', __('Date of Birth'));
        $row->addDate('dob');

    $row = $form->addRow("row_file1");
        $row->addLabel('file1', __('User Photo'))
            ->description(__('Displayed at 240px by 320px.'))
            ->description(__('Accepts images up to 360px by 480px.'))
            ->description(__('Accepts aspect ratio between 1:1.2 and 1:1.4.'));
        $row->addFileUpload('file1')
            ->accepts('.jpg,.jpeg,.gif,.png')
            ->setMaxUpload(false);

    // SYSTEM ACCESS
    $form->addRow("system_access")->addHeading(__('System Access'));


    $row = $form->addRow("row_username");
        $row->addLabel('username', __('Username'))->description(__('System login name.'));
        $row->addUsername('username')
            ->required()
            ->addGenerateUsernameButton($form);

    $policy = getPasswordPolicy($guid, $connection2);
    if ($policy != false) {
        $form->addRow()->addAlert($policy, 'warning');
    }
    $row = $form->addRow("row_passwordNew");
        $row->addLabel('passwordNew', __('Password'));
        $row->addPassword('passwordNew')
            ->addPasswordPolicy($pdo)
            ->addGeneratePasswordButton($form)
            ->required()
            ->maxLength(30);

    $row = $form->addRow("row_passwordConfirm");
        $row->addLabel('passwordConfirm', __('Confirm Password'));
        $row->addPassword('passwordConfirm')
            ->addConfirmation('passwordNew')
            ->required()
            ->maxLength(30);

    $row = $form->addRow("row_status");
        $row->addLabel('status', __('Status'))->description(__('This determines visibility within the system.'));
        $row->addSelectStatus('status')->required();

    $row = $form->addRow("row_canLogin");
        $row->addLabel('canLogin', __('Can Login?'));
        $row->addYesNo('canLogin')->required();

    $row = $form->addRow("row_passwordForceReset");
        $row->addLabel('passwordForceReset', __('Force Reset Password?'))->description(__('User will be prompted on next login.'));
        $row->addYesNo('passwordForceReset')->required();

$row = $form->addRow("row_fee_category");
        $row->addLabel('fee_category_id', __('Fee Category'));
        $row->addSelect('fee_category_id')->fromArray($fee_category)->placeholder();
    // CONTACT INFORMATION
    $form->addRow("contact_information")->addHeading(__('Contact Information'));

    $row = $form->addRow("row_email");
        $emailLabel = $row->addLabel('email', __('Email'));
        $email = $row->addEmail('email');

    $uniqueEmailAddress = getSettingByScope($connection2, 'User Admin', 'uniqueEmailAddress');
    if ($uniqueEmailAddress == 'Y') {
        $email->uniqueField($_SESSION[$guid]['absoluteURL'].'/modules/User Admin/user_manage_emailAjax.php');
    }

    $row = $form->addRow("row_emailAlternate");
        $row->addLabel('emailAlternate', __('Alternate Email'));
        $row->addEmail('emailAlternate');

    $row = $form->addRow("");
    $row->addAlert(__('Address information for an individual only needs to be set under the following conditions:'), 'warning')
        ->append('<ol>')
        ->append('<li>'.__('If the user is not in a family.').'</li>')
        ->append('<li>'.__('If the user\'s family does not have a home address set.').'</li>')
        ->append('<li>'.__('If the user needs an address in addition to their family\'s home address.').'</li>')
        ->append('</ol>');

    $row = $form->addRow("row_showAddresses");
        $row->addLabel('showAddresses', __('Enter Personal Address?'));
        $row->addCheckbox('showAddresses')->setValue('Yes');

    $form->toggleVisibilityByClass('address')->onCheckbox('showAddresses')->when('Yes');

    $row = $form->addRow("row_address1")->addClass('address');
        $row->addLabel('address1', __('Address 1'))->description(__('Unit, Building, Street'));
        $row->addTextField('address1')->maxLength(255);

    $row = $form->addRow("row_address1District")->addClass('address');
        $row->addLabel('address1District', __('Address 1 District'))->description(__('County, State, District'));
        $row->addTextFieldDistrict('address1District');

    $row = $form->addRow("row_address1Country")->addClass('address');
        $row->addLabel('address1Country', __('Address 1 Country'));
        $row->addSelectCountry('address1Country');

    $row = $form->addRow("row_address2")->addClass('address');
        $row->addLabel('address2', __('Address 2'))->description(__('Unit, Building, Street'));
        $row->addTextField('address2')->maxLength(255);

    $row = $form->addRow("row_address2District")->addClass('address');
        $row->addLabel('address2District', __('Address 2 District'))->description(__('County, State, District'));
        $row->addTextFieldDistrict('address2District');

    $row = $form->addRow("row_address2Country")->addClass('address');
        $row->addLabel('address2Country', __('Address 2 Country'));
        $row->addSelectCountry('address2Country');

    for ($i = 1; $i < 5; ++$i) {
        $row = $form->addRow("row_phone".$i);
        $row->addLabel('phone'.$i, __('Phone').' '.$i)->description(__('Type, country code, number.'));
        $row->addPhoneNumber('phone'.$i);
    }

    $row = $form->addRow("row_website");
        $row->addLabel('website', __('Website'))->description(__('Include http://'));
        $row->addURL('website');

    // SCHOOL INFORMATION
    $form->addRow("school_information")->addHeading(__('School Information'));

    $dayTypeOptions = getSettingByScope($connection2, 'User Admin', 'dayTypeOptions');
    if (!empty($dayTypeOptions)) {
        $dayTypeText = getSettingByScope($connection2, 'User Admin', 'dayTypeText');
        $row = $form->addRow("row_dayType");
            $row->addLabel('dayType', __('Day Type'))->description($dayTypeText);
            $row->addSelect('dayType')->fromString($dayTypeOptions)->placeholder();
    }

    $sql = "SELECT DISTINCT lastSchool FROM pupilsightPerson ORDER BY lastSchool";
    $result = $pdo->executeQuery(array(), $sql);
    $schools = ($result && $result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN) : array();

    $row = $form->addRow("row_lastSchool");
        $row->addLabel('lastSchool', __('Last School'));
        $row->addTextField('lastSchool')->autocomplete($schools);

    $row = $form->addRow("row_dateStart");
        $row->addLabel('dateStart', __('Start Date'))->description(__("Users's first day at school."));
        $row->addDate('dateStart');

    $row = $form->addRow("row_pupilsightSchoolYearIDClassOf");
        $row->addLabel('pupilsightSchoolYearIDClassOf', __('Class Of'))->description(__('When is the student expected to graduate?'));
        $row->addSelectSchoolYear('pupilsightSchoolYearIDClassOf');

    // BACKGROUND INFORMATION
    $form->addRow("background_information")->addHeading(__('Background Information'));

    $row = $form->addRow("row_languageFirst");
        $row->addLabel('languageFirst', __('First Language'));
        $row->addSelectLanguage('languageFirst');

    $row = $form->addRow("row_languageSecond");
        $row->addLabel('languageSecond', __('Second Language'));
        $row->addSelectLanguage('languageSecond');

    $row = $form->addRow("row_languageThird");
        $row->addLabel('languageThird', __('Third Language'));
        $row->addSelectLanguage('languageThird');

    $row = $form->addRow("row_countryOfBirth");
        $row->addLabel('countryOfBirth', __('Country of Birth'));
        $row->addSelectCountry('countryOfBirth');

    $row = $form->addRow("row_birthCertificateScan");
        $row->addLabel('birthCertificateScan', __('Birth Certificate Scan'))->description(__('Less than 1440px by 900px').'. '.__('Accepts PDF files.'));
        $row->addFileUpload('birthCertificateScan')->accepts('.jpg,.jpeg,.gif,.png,.pdf')->setMaxUpload(false);

    $ethnicities = getSettingByScope($connection2, 'User Admin', 'ethnicity');
    $row = $form->addRow("row_ethnicity");
        $row->addLabel('ethnicity', __('Ethnicity'));
        if (!empty($ethnicities)) {
            $row->addSelect('ethnicity')->fromString($ethnicities)->placeholder();
        } else {
            $row->addTextField('ethnicity')->maxLength(255);
        }

    $religions = getSettingByScope($connection2, 'User Admin', 'religions');
    $row = $form->addRow("row_religion");
        $row->addLabel('religion', __('Religion'));
        if (!empty($religions)) {
            $row->addSelect('religion')->fromString($religions)->placeholder();
        } else {
            $row->addTextField('religion')->maxLength(30);
        }

    $nationalityList = getSettingByScope($connection2, 'User Admin', 'nationality');
    $row = $form->addRow("row_citizenship1");
        $row->addLabel('citizenship1', __('Citizenship 1'));
        if (!empty($nationalityList)) {
            $row->addSelect('citizenship1')->fromString($nationalityList)->placeholder();
        } else {
            $row->addSelectCountry('citizenship1');
        }

    $row = $form->addRow("row_citizenship1Passport");
        $row->addLabel('citizenship1Passport', __('Citizenship 1 Passport Number'));
        $row->addTextField('citizenship1Passport')->maxLength(30);

    $row = $form->addRow("row_citizenship1PassportScan");
        $row->addLabel('citizenship1PassportScan', __('Citizenship 1 Passport Scan'))->description(__('Less than 1440px by 900px').'. '.__('Accepts PDF files.'));
        $row->addFileUpload('citizenship1PassportScan')->accepts('.jpg,.jpeg,.gif,.png,.pdf')->setMaxUpload(false);

    $row = $form->addRow("row_citizenship2");
        $row->addLabel('citizenship2', __('Citizenship 2'));
        if (!empty($nationalityList)) {
            $row->addSelect('citizenship2')->fromString($nationalityList)->placeholder();
        } else {
            $row->addSelectCountry('citizenship2');
        }

    $row = $form->addRow("row_citizenship2Passport");
        $row->addLabel('citizenship2Passport', __('Citizenship 2 Passport Number'));
        $row->addTextField('citizenship2Passport')->maxLength(30);

    if (!empty($_SESSION[$guid]['country'])) {
        $nationalIDCardNumberLabel = $_SESSION[$guid]['country'].' '.__('ID Card Number');
        $nationalIDCardScanLabel = $_SESSION[$guid]['country'].' '.__('ID Card Scan');
        $residencyStatusLabel = $_SESSION[$guid]['country'].' '.__('Residency/Visa Type');
        $visaExpiryDateLabel = $_SESSION[$guid]['country'].' '.__('Visa Expiry Date');
    } else {
        $nationalIDCardNumberLabel = __('National ID Card Number');
        $nationalIDCardScanLabel = __('National ID Card Scan');
        $residencyStatusLabel = __('Residency/Visa Type');
        $visaExpiryDateLabel = __('Visa Expiry Date');
    }

    $row = $form->addRow("row_nationalIDCardNumber");
        $row->addLabel('nationalIDCardNumber', $nationalIDCardNumberLabel);
        $row->addTextField('nationalIDCardNumber')->maxLength(30);

    $row = $form->addRow("row_nationalIDCardScan");
        $row->addLabel('nationalIDCardScan', $nationalIDCardScanLabel)->description(__('Less than 1440px by 900px').'. '.__('Accepts PDF files.'));
        $row->addFileUpload('nationalIDCardScan')->accepts('.jpg,.jpeg,.gif,.png,.pdf')->setMaxUpload(false);

    $residencyStatusList = getSettingByScope($connection2, 'User Admin', 'residencyStatus');

    $row = $form->addRow("row_residencyStatus");
        $row->addLabel('residencyStatus', $residencyStatusLabel);
        if (!empty($residencyStatusList)) {
            $row->addSelect('residencyStatus')->fromString($residencyStatusList)->placeholder();
        } else {
            $row->addTextField('residencyStatus')->maxLength(30);
        }

    $row = $form->addRow("row_visaExpiryDate");
        $row->addLabel('visaExpiryDate', $visaExpiryDateLabel)->description(__('If relevant.'));
        $row->addDate('visaExpiryDate');

    // EMPLOYMENT
    // $form->addRow("employment")->addHeading(__('Employment'));

    // $row = $form->addRow("row_profession");
    //     $row->addLabel('profession', __('Profession'));
    //     $row->addTextField('profession')->maxLength(90);

    // $row = $form->addRow("row_employer");
    //     $row->addLabel('employer', __('Employer'));
    //     $row->addTextField('employer')->maxLength(90);

    // $row = $form->addRow("row_jobTitle");
    //     $row->addLabel('jobTitle', __('Job Title'));
    //     $row->addTextField('jobTitle')->maxLength(90);

    // EMERGENCY CONTACTS
    $form->addRow("emergency_contacts")->addHeading(__('Emergency Contacts'));

    $form->addRow()->addContent(__('These details are used when immediate family members (e.g. parent, spouse) cannot be reached first. Please try to avoid listing immediate family members.'));

    $row = $form->addRow("row_emergency1Name");
        $row->addLabel('emergency1Name', __('Contact 1 Name'));
        $row->addTextField('emergency1Name')->maxLength(90);

    $row = $form->addRow("row_emergency1Relationship");
        $row->addLabel('emergency1Relationship', __('Contact 1 Relationship'));
        $row->addSelectEmergencyRelationship('emergency1Relationship');

    $row = $form->addRow("row_emergency1Number1");
        $row->addLabel('emergency1Number1', __('Contact 1 Number 1'));
        $row->addTextField('emergency1Number1')->maxLength(30);

    $row = $form->addRow("row_emergency1Number2");
        $row->addLabel('emergency1Number2', __('Contact 1 Number 2'));
        $row->addTextField('emergency1Number2')->maxLength(30);

    $row = $form->addRow("row_emergency2Name");
        $row->addLabel('emergency2Name', __('Contact 2 Name'));
        $row->addTextField('emergency2Name')->maxLength(90);

    $row = $form->addRow("row_emergency2Relationship");
        $row->addLabel('emergency2Relationship', __('Contact 2 Relationship'));
        $row->addSelectEmergencyRelationship('emergency2Relationship');

    $row = $form->addRow("row_emergency2Number1");
        $row->addLabel('emergency2Number1', __('Contact 2 Number 1'));
        $row->addTextField('emergency2Number1')->maxLength(30);

    $row = $form->addRow("row_emergency2Number2");
        $row->addLabel('emergency2Number2', __('Contact 2 Number 2'));
        $row->addTextField('emergency2Number2')->maxLength(30);

    // MISCELLANEOUS
    $form->addRow("miscellaneous")->addHeading(__('Miscellaneous'));

    $sql = "SELECT pupilsightHouseID as value, name FROM pupilsightHouse ORDER BY name";
    $row = $form->addRow("row_pupilsightHouseID");
        $row->addLabel('pupilsightHouseID', __('House'));
        $row->addSelect('pupilsightHouseID')->fromQuery($pdo, $sql)->placeholder();

    $row = $form->addRow("row_studentID");
        $row->addLabel('studentID', __('Student ID'))->description(__('Must be unique if set.'));
        $row->addTextField('studentID')->maxLength(10);

    $sql = "SELECT DISTINCT transport FROM pupilsightPerson
            JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE status='Current')
            ORDER BY transport";
    $result = $pdo->executeQuery(array(), $sql);
    $transport = ($result && $result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN) : array();

    $row = $form->addRow("row_transport");
        $row->addLabel('transport', __('Transport'));
        $row->addTextField('transport')->maxLength(255)->autocomplete($transport);

    $row = $form->addRow("row_transportNotes");
        $row->addLabel('transportNotes', __('Transport Notes'));
        $row->addTextArea('transportNotes')->setRows(4);

    $row = $form->addRow("row_lockerNumber");
        $row->addLabel('lockerNumber', __('Locker Number'));
        $row->addTextField('lockerNumber')->maxLength(20);

    $row = $form->addRow("row_vehicleRegistration");
        $row->addLabel('vehicleRegistration', __('Vehicle Registration'));
        $row->addTextField('vehicleRegistration')->maxLength(20);

    $privacySetting = getSettingByScope($connection2, 'User Admin', 'privacy');
    $privacyOptions = getSettingByScope($connection2, 'User Admin', 'privacyOptions');

    if ($privacySetting == 'Y' && !empty($privacyOptions)) {
        $options = array_map(function($item) { return trim($item); }, explode(',', $privacyOptions));

        $row = $form->addRow("row_privacyOptions");
            $row->addLabel('privacyOptions[]', __('Privacy'))->description(__('Check to indicate which privacy options are required.'));
            $row->addCheckbox('privacyOptions[]')->fromArray($options)->addClass('md:max-w-lg');
    }

    $studentAgreementOptions = getSettingByScope($connection2, 'School Admin', 'studentAgreementOptions');
    if (!empty($studentAgreementOptions)) {
        $options = array_map(function($item) { return trim($item); }, explode(',', $studentAgreementOptions));

        $row = $form->addRow("row_studentAgreements[]");
        $row->addLabel('studentAgreements[]', __('Student Agreements'))->description(__('Check to indicate that student has signed the relevant agreement.'));
        $row->addCheckbox('studentAgreements[]')->fromArray($options);
    }

    $row = $form->addRow("");
        $row->addFooter()->append('<small>'.getMaxUpload($guid, true).'</small>');
        $row->addSubmit();

    echo $form->getOutput();
}
