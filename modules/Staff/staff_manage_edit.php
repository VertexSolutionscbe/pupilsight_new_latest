<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
         ->add(__('Manage Staff'), 'staff_view.php')
         ->add(__('Edit Staff'));

    $returns = array();
    $returns['warning1'] = __('Your request was completed successfully, but one or more images were the wrong size and so were not saved.');
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, $returns);
    }

    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);

    //Check if school year specified
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    if ($pupilsightPersonID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
			$result->execute($data);
			

			$sqls = "SELECT * FROM pupilsightStaff WHERE pupilsightPersonID = " . $pupilsightPersonID . " ";
            $results = $connection2->query($sqls);
			$stfdata = $results->fetch();
			//print_r($stfdata);
            $pupilsightStaffID = $stfdata['pupilsightStaffID'];
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            //Get categories
            $staff = false;
            $student = false;
            $parent = false;
            $other = false;
            $roles = explode(',', $values['pupilsightRoleIDAll']);
            foreach ($roles as $role) {
                $roleCategory = getRoleCategory($role, $connection2);
				$staff = $staff || ($roleCategory == 'Staff');
				$student = $student || ($roleCategory == 'Student');
				$parent = $parent || ($roleCategory == 'Parent');
				$other = $other || ($roleCategory == 'Other');
            }

            $search = (isset($_GET['search']))? $_GET['search'] : '';

            if (!empty($search)) {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/staff_view.php&search='.$search."'>".__('Back to Search Results').'</a>';
                echo '</div>';
			}

			echo '<div class="alert alert-warning">';
			echo __('Note that certain fields are hidden or revealed depending on the role categories (Staff, Student, Parent) that a user is assigned to. For example, parents do not get Emergency Contact fields, and students/staff do not get Employment fields.');
			echo '</div>';

			$form = Form::create('addUser', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/staff_manage_editProcess.php?pupilsightPersonID='.$pupilsightPersonID.'&search='.$search);
			$form->setFactory(DatabaseFormFactory::create($pdo));

			$form->addHiddenValue('address', $_SESSION[$guid]['address']);
			$form->addHiddenValue('signature_path', $values['signature_path']);

			// BASIC INFORMATION
			$form->addRow()->addHeading(__('Basic Information'));


			$data = array();
			$sql = "SELECT pupilsightRoleID, pupilsightRoleID, name, restriction FROM pupilsightRole ORDER BY name";
			$result = $pdo->executeQuery($data, $sql);

			// Get all roles
			$allRoles = ($result && $result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

			// Put together an array of this user's current roles
			$currentUserRoles = (is_array($_SESSION[$guid]['pupilsightRoleIDAll'])) ? array_column($_SESSION[$guid]['pupilsightRoleIDAll'], 0) : array();
			$currentUserRoles[] = $_SESSION[$guid]['pupilsightRoleIDPrimary'];

			// Filter all roles based on role restrictions
			$availableRoles = array_reduce($allRoles, function ($carry, $item) use (&$currentUserRoles) {
				if ($item['restriction'] == 'Admin Only') {
					if (!in_array('001', $currentUserRoles)) {
						return $carry;
					}
				} elseif ($item['restriction'] == 'Same Role') {
					if (!in_array($item['pupilsightRoleID'], $currentUserRoles) && !in_array('001', $currentUserRoles)) {
						return $carry;
					}
				}
				$carry[$item['pupilsightRoleID']] = $item['name'];
				return $carry;
			}, array());

			// Get info on the user role being edited
			$roleRestriction = null;
			if (isset($allRoles[$values['pupilsightRoleIDPrimary']])) {
				$roleDetails = $allRoles[$values['pupilsightRoleIDPrimary']];
            	$roleRestriction = $roleDetails['restriction'];
			}

			// Display a readonly field if the current role cannot be changed
			if (empty($roleRestriction) || ($roleRestriction == 'Admin Only' && !in_array('001', $currentUserRoles)) || ($roleRestriction == 'Same Role' && !in_array($values['pupilsightRoleIDPrimary'], $currentUserRoles) && !in_array('001', $currentUserRoles)) ) {
				$row = $form->addRow();
				$row->addLabel('pupilsightRoleIDPrimaryName', __('Primary Role'))->description(__('Controls what a user can do and see.'));
				$row->addTextField('pupilsightRoleIDPrimaryName')->readOnly()->setValue($roleDetails['name']);
				$form->addHiddenValue('pupilsightRoleIDPrimary', $values['pupilsightRoleIDPrimary']);
			} else {
                $row = $form->addRow();
                $row->addLabel('pupilsightRoleIDPrimary', __('Primary Role'))->description(__('Controls what a user can do and see.'));
                $row->addSelect('pupilsightRoleIDPrimary')->fromArray($availableRoles)->required()->placeholder();
			}

			$row = $form->addRow();
				$row->addLabel('title', __('Title'));
				$row->addSelectTitle('title');

			$row = $form->addRow();
				$row->addLabel('surname', __('Surname'))->description(__('Family name as shown in ID documents.'));
				$row->addTextField('surname')->required()->maxLength(60);

			$row = $form->addRow();
				$row->addLabel('firstName', __('First Name'))->description(__('First name as shown in ID documents.'));
				$row->addTextField('firstName')->required()->maxLength(60);

			$row = $form->addRow();
				$row->addLabel('preferredName', __('Preferred Name'))->description(__('Most common name, alias, nickname, etc.'));
				$row->addTextField('preferredName')->required()->maxLength(60);

			$row = $form->addRow();
				$row->addLabel('officialName', __('Official Name'))->description(__('Full name as shown in ID documents.'));
				$row->addTextField('officialName')->required()->maxLength(150)->setTitle(__('Please enter full name as shown in ID documents'));

			$row = $form->addRow();
				$row->addLabel('nameInCharacters', __('Name In Characters'))->description(__('Chinese or other character-based name.'));
				$row->addTextField('nameInCharacters')->maxLength(60);

			$types = array(__('Basic') => array('Teaching' => __('Teaching'), 'Support' => __('Support')));
			$sql = "SELECT name as value, name FROM pupilsightRole WHERE category='Staff' ORDER BY name";
			$result = $pdo->executeQuery(array(), $sql);
			$types[__('System Roles')] = ($result->rowCount() > 0) ? $result->fetchAll(\PDO::FETCH_KEY_PAIR) : array();
			$row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addSelect('type')->fromArray($types)->selected($stfdata['type'])->placeholder()->required();
				
			
			$row = $form->addRow();
                $row->addLabel('jobTitle_staff', __('Job Title'));
                $row->addTextField('jobTitle_staff')->setValue($stfdata['jobTitle'])->maxlength(100);

			$row = $form->addRow();
				$row->addLabel('gender', __('Gender'));
				$row->addSelectGender('gender')->required();

			$row = $form->addRow();
				$row->addLabel('dob', __('Date of Birth'));
				$row->addDate('dob');

			$row = $form->addRow();
				$row->addLabel('file1', __('User Photo'))
					->description(__('Displayed at 240px by 320px.'))
					->description(__('Accepts images up to 360px by 480px.'))
					->description(__('Accepts aspect ratio between 1:1.2 and 1:1.4.'));
				$row->addFileUpload('file1')
					->accepts('.jpg,.jpeg,.gif,.png')
					->setAttachment('attachment1', $_SESSION[$guid]['absoluteURL'], $values['image_240'])
					->setMaxUpload(false);

			// SYSTEM ACCESS
			$form->addRow()->addHeading(__('System Access'));

			

			// Grab the selected roles, and break apart into selectable roles and restricted roles
			$selectedRoles = explode(',', $values['pupilsightRoleIDAll']);
			$selectableRoles = array_intersect(array_keys($availableRoles), $selectedRoles);
			unset($values['pupilsightRoleIDAll']);

			$restrictedRoles = array_diff($selectedRoles, $selectableRoles);
			$restrictedRoles = array_intersect_key($allRoles, array_flip($restrictedRoles));

			$row = $form->addRow();
				$row->addLabel('pupilsightRoleIDAll', __('All Roles'))->description(__('Controls what a user can do and see.'));
				$row->addSelect('pupilsightRoleIDAll')->fromArray($availableRoles)->selectMultiple()->selected($selectableRoles);

			if (!empty($restrictedRoles)) {
				$restrictedRolesList = implode(', ', array_column($restrictedRoles, 'name'));

				$row = $form->addRow();
					$row->addLabel('pupilsightRoleIDRestricted', __('Restricted Roles'));
					$row->addTextField('pupilsightRoleIDRestricted')->readOnly()->setValue($restrictedRolesList)->setClass('standardWidth');
			}

            $row = $form->addRow();
                $row->addLabel('username', __('Username'))->description(__('System login name.'));
                $row->addUsername('username')
                    ->required()
                    ->setValue($values['username']);

			$row = $form->addRow();
				$row->addLabel('status', __('Status'))->description(__('This determines visibility within the system.'));
				$row->addSelectStatus('status')->required();

			$row = $form->addRow();
				$row->addLabel('canLogin', __('Can Login?'));
				$row->addYesNo('canLogin')->required();

			$row = $form->addRow();
				$row->addLabel('passwordForceReset', __('Force Reset Password?'))->description(__('User will be prompted on next login.'));
				$row->addYesNo('passwordForceReset')->required();

			// CONTACT INFORMATION
			$form->addRow()->addHeading(__('Contact Information'));

			$row = $form->addRow();
                $emailLabel = $row->addLabel('email', __('Email'));
                $email = $row->addEmail('email');

			$uniqueEmailAddress = getSettingByScope($connection2, 'User Admin', 'uniqueEmailAddress');
			if ($uniqueEmailAddress == 'Y') {
				$email->uniqueField('./modules/User Admin/user_manage_emailAjax.php', array('pupilsightPersonID' => $pupilsightPersonID));
			}

			$row = $form->addRow();
				$row->addLabel('emailAlternate', __('Alternate Email'));
				$row->addEmail('emailAlternate');

			$row = $form->addRow();
			$row->addAlert(__('Address information for an individual only needs to be set under the following conditions:'), 'warning')
				->append('<ol>')
				->append('<li>'.__('If the user is not in a family.').'</li>')
				->append('<li>'.__('If the user\'s family does not have a home address set.').'</li>')
				->append('<li>'.__('If the user needs an address in addition to their family\'s home address.').'</li>')
				->append('</ol>');

			$addressSet = ($values['address1'] != '' or $values['address1District'] != '' or $values['address1Country'] != '' or $values['address2'] != '' or $values['address2District'] != '' or $values['address2Country'] != '')? 'Yes' : '';

			$row = $form->addRow();
				$row->addLabel('showAddresses', __('Enter Personal Address?'));
				$row->addCheckbox('showAddresses')->setValue('Yes')->checked($addressSet);

			$form->toggleVisibilityByClass('address')->onCheckbox('showAddresses')->when('Yes');

			$row = $form->addRow()->addClass('address');
				$row->addLabel('address1', __('Address 1'))->description(__('Unit, Building, Street'));
				$row->addTextArea('address1')->maxLength(255)->setRows(2);

			$row = $form->addRow()->addClass('address');
				$row->addLabel('address1District', __('Address 1 District'))->description(__('County, State, District'));
				$row->addTextFieldDistrict('address1District');

			$row = $form->addRow()->addClass('address');
				$row->addLabel('address1Country', __('Address 1 Country'));
				$row->addSelectCountry('address1Country');

			if ($values['address1'] != '') {
				try {
					$dataAddress = array('pupilsightPersonID' => $values['pupilsightPersonID'], 'addressMatch' => '%'.strtolower(preg_replace('/ /', '%', preg_replace('/,/', '%', $values['address1']))).'%');
					$sqlAddress = "SELECT pupilsightPersonID, title, preferredName, surname, category FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE status='Full' AND address1 LIKE :addressMatch AND NOT pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
					$resultAddress = $connection2->prepare($sqlAddress);
					$resultAddress->execute($dataAddress);
				} catch (PDOException $e) {
					echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
				}

				if ($resultAddress->rowCount() > 0) {
					$addressCount = 0;

					$row = $form->addRow()->addClass('address  matchHighlight');
					$row->addLabel('matchAddress', __('Matching Address 1'))->description(__('These users have similar Address 1. Do you want to change them too?'));
					$table = $row->addTable()->setClass('standardWidth');

                    while ($rowAddress = $resultAddress->fetch()) {
                        $adressee = formatName($rowAddress['title'], $rowAddress['preferredName'], $rowAddress['surname'], $rowAddress['category']).' ('.$rowAddress['category'].')';

                        $row = $table->addRow()->addClass('address');
                        $row->addTextField($addressCount.'-matchAddressLabel')->readOnly()->setValue($adressee)->setClass('fullWidth');
                        $row->addCheckbox($addressCount.'-matchAddress')->setValue($rowAddress['pupilsightPersonID']);

                        $addressCount++;
					}

					$form->addHiddenValue('matchAddressCount', $addressCount);
				}
			}

			$row = $form->addRow()->addClass('address');
				$row->addLabel('address2', __('Address 2'))->description(__('Unit, Building, Street'));
                $row->addTextArea('address2')->maxLength(255)->setRows(2);

			$row = $form->addRow()->addClass('address');
				$row->addLabel('address2District', __('Address 2 District'))->description(__('County, State, District'));
				$row->addTextFieldDistrict('address2District');

			$row = $form->addRow()->addClass('address');
				$row->addLabel('address2Country', __('Address 2 Country'));
				$row->addSelectCountry('address2Country');

			for ($i = 1; $i < 5; ++$i) {
				$row = $form->addRow();
				$row->addLabel('phone'.$i, __('Phone').' '.$i)->description(__('Type, country code, number.'));
				$row->addPhoneNumber('phone'.$i);
			}

			$row = $form->addRow();
				$row->addLabel('website', __('Website'))->description(__('Include http://'));
				$row->addURL('website');

			// SCHOOL INFORMATION
			$form->addRow()->addHeading(__('School Information'));

            if ($student) {
                $dayTypeOptions = getSettingByScope($connection2, 'User Admin', 'dayTypeOptions');
                if (!empty($dayTypeOptions)) {
                    $dayTypeText = getSettingByScope($connection2, 'User Admin', 'dayTypeText');
                    $row = $form->addRow();
                    $row->addLabel('dayType', __('Day Type'))->description($dayTypeText);
                    $row->addSelect('dayType')->fromString($dayTypeOptions)->placeholder();
                }
            }

            if ($student || $staff) {
                $sql = "SELECT DISTINCT lastSchool FROM pupilsightPerson ORDER BY lastSchool";
                $result = $pdo->executeQuery(array(), $sql);
                $schools = ($result && $result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN) : array();

                $row = $form->addRow();
                $row->addLabel('lastSchool', __('Last School'));
                $row->addTextField('lastSchool')->autocomplete($schools);
            }

			$row = $form->addRow();
				$row->addLabel('dateStart', __('Start Date'))->description(__("Users's first day at school."));
				$row->addDate('dateStart');

			$row = $form->addRow();
                $row->addLabel('dateEnd', __('End Date'))->description(__("Users's last day at school."));
                $row->addDate('dateEnd');

            if ($student) {
                $row = $form->addRow();
                	$row->addLabel('pupilsightSchoolYearIDClassOf', __('Class Of'))->description(__('When is the student expected to graduate?'));
                	$row->addSelectSchoolYear('pupilsightSchoolYearIDClassOf');
			}

			if ($student || $staff) {
                $sql = "SELECT DISTINCT nextSchool FROM pupilsightPerson ORDER BY lastSchool";
                $result = $pdo->executeQuery(array(), $sql);
                $schools = ($result && $result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN) : array();

                $row = $form->addRow();
                $row->addLabel('nextSchool', __('Next School'));
                $row->addTextField('nextSchool')->autocomplete($schools);

				$departureReasonsList = getSettingByScope($connection2, 'User Admin', 'departureReasons');

				$row = $form->addRow();
				$row->addLabel('departureReason', __('Departure Reason'));
				if (!empty($departureReasonsList)) {
					$row->addSelect('departureReason')->fromString($departureReasonsList)->placeholder();
				} else {
					$row->addTextField('departureReason')->maxLength(30);
				}
			}

			// BACKGROUND INFORMATION
			$form->addRow()->addHeading(__('Background Information'));

			$row = $form->addRow();
				$row->addLabel('languageFirst', __('First Language'));
				$row->addSelectLanguage('languageFirst');

			$row = $form->addRow();
				$row->addLabel('languageSecond', __('Second Language'));
				$row->addSelectLanguage('languageSecond');

			$row = $form->addRow();
				$row->addLabel('languageThird', __('Third Language'));
				$row->addSelectLanguage('languageThird');

			$row = $form->addRow();
				$row->addLabel('countryOfBirth', __('Country of Birth'));
				$row->addSelectCountry('countryOfBirth');

			$row = $form->addRow();
				$row->addLabel('birthCertificateScan', __('Birth Certificate Scan'))->description(__('Less than 1440px by 900px').'. '.__('Accepts PDF files.'));
				$row->addFileUpload('birthCertificateScan')
					->accepts('.jpg,.jpeg,.gif,.png,.pdf')
					->setMaxUpload(false)
					->setAttachment('birthCertificateScanCurrent', $_SESSION[$guid]['absoluteURL'], $values['birthCertificateScan']);

			$ethnicities = getSettingByScope($connection2, 'User Admin', 'ethnicity');
			$row = $form->addRow();
				$row->addLabel('ethnicity', __('Ethnicity'));
				if (!empty($ethnicities)) {
					$row->addSelect('ethnicity')->fromString($ethnicities)->placeholder();
				} else {
					$row->addTextField('ethnicity')->maxLength(255);
				}

			$religions = getSettingByScope($connection2, 'User Admin', 'religions');
			$row = $form->addRow();
				$row->addLabel('religion', __('Religion'));
				if (!empty($religions)) {
					$row->addSelect('religion')->fromString($religions)->placeholder();
				} else {
					$row->addTextField('religion')->maxLength(30);
				}

			$nationalityList = getSettingByScope($connection2, 'User Admin', 'nationality');
			$row = $form->addRow();
				$row->addLabel('citizenship1', __('Citizenship 1'));
				if (!empty($nationalityList)) {
					$row->addSelect('citizenship1')->fromString($nationalityList)->placeholder();
				} else {
					$row->addSelectCountry('citizenship1');
				}

			$row = $form->addRow();
				$row->addLabel('citizenship1Passport', __('Citizenship 1 Passport Number'));
				$row->addTextField('citizenship1Passport')->maxLength(30);

			$row = $form->addRow();
				$row->addLabel('citizenship1PassportScan', __('Citizenship 1 Passport Scan'))->description(__('Less than 1440px by 900px').'. '.__('Accepts PDF files.'));
				$row->addFileUpload('citizenship1PassportScan')
					->accepts('.jpg,.jpeg,.gif,.png,.pdf')
					->setMaxUpload(false)
					->setAttachment('citizenship1PassportScanCurrent', $_SESSION[$guid]['absoluteURL'], $values['citizenship1PassportScan']);

			$row = $form->addRow();
				$row->addLabel('citizenship2', __('Citizenship 2'));
				if (!empty($nationalityList)) {
					$row->addSelect('citizenship2')->fromString($nationalityList)->placeholder();
				} else {
					$row->addSelectCountry('citizenship2');
				}

			$row = $form->addRow();
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

			$row = $form->addRow();
				$row->addLabel('nationalIDCardNumber', $nationalIDCardNumberLabel);
				$row->addTextField('nationalIDCardNumber')->maxLength(30);

			$row = $form->addRow();
				$row->addLabel('nationalIDCardScan', $nationalIDCardScanLabel)->description(__('Less than 1440px by 900px').'. '.__('Accepts PDF files.'));
				$row->addFileUpload('nationalIDCardScan')
					->accepts('.jpg,.jpeg,.gif,.png,.pdf')
					->setMaxUpload(false)
					->setAttachment('nationalIDCardScanCurrent', $_SESSION[$guid]['absoluteURL'], $values['nationalIDCardScan']);

			$residencyStatusList = getSettingByScope($connection2, 'User Admin', 'residencyStatus');

			$row = $form->addRow();
				$row->addLabel('residencyStatus', $residencyStatusLabel);
				if (!empty($residencyStatusList)) {
					$row->addSelect('residencyStatus')->fromString($residencyStatusList)->placeholder();
				} else {
					$row->addTextField('residencyStatus')->maxLength(30);
				}

			$row = $form->addRow();
				$row->addLabel('visaExpiryDate', $visaExpiryDateLabel)->description(__('If relevant.'));
				$row->addDate('visaExpiryDate');

			// EMPLOYMENT
			if ($parent) {
				$form->addRow()->addHeading(__('Employment'));

				$row = $form->addRow();
					$row->addLabel('profession', __('Profession'));
					$row->addTextField('profession')->maxLength(90);

				$row = $form->addRow();
					$row->addLabel('employer', __('Employer'));
					$row->addTextField('employer')->maxLength(90);

				// $row = $form->addRow();
				// 	$row->addLabel('jobTitle', __('Job Title'));
				// 	$row->addTextField('jobTitle')->maxLength(90);
			}

			// EMERGENCY CONTACTS
			if ($student || $staff) {
				$form->addRow()->addHeading(__('Emergency Contacts'));

				$form->addRow()->addContent(__('These details are used when immediate family members (e.g. parent, spouse) cannot be reached first. Please try to avoid listing immediate family members.'));

				$row = $form->addRow();
					$row->addLabel('emergency1Name', __('Contact 1 Name'));
					$row->addTextField('emergency1Name')->maxLength(90);

				$row = $form->addRow();
					$row->addLabel('emergency1Relationship', __('Contact 1 Relationship'));
					$row->addSelectEmergencyRelationship('emergency1Relationship');

				$row = $form->addRow();
					$row->addLabel('emergency1Number1', __('Contact 1 Number 1'));
					$row->addTextField('emergency1Number1')->maxLength(30);

				$row = $form->addRow();
					$row->addLabel('emergency1Number2', __('Contact 1 Number 2'));
					$row->addTextField('emergency1Number2')->maxLength(30);

				$row = $form->addRow();
					$row->addLabel('emergency2Name', __('Contact 2 Name'));
					$row->addTextField('emergency2Name')->maxLength(90);

				$row = $form->addRow();
					$row->addLabel('emergency2Relationship', __('Contact 2 Relationship'));
					$row->addSelectEmergencyRelationship('emergency2Relationship');

				$row = $form->addRow();
					$row->addLabel('emergency2Number1', __('Contact 2 Number 1'));
					$row->addTextField('emergency2Number1')->maxLength(30);

				$row = $form->addRow();
					$row->addLabel('emergency2Number2', __('Contact 2 Number 2'));
					$row->addTextField('emergency2Number2')->maxLength(30);
			}

            $form->addRow()->addHeading(__('First Aid'));

            $row = $form->addRow();
            $row->addLabel('firstAidQualified', __('First Aid Qualified?'));
            $row->addYesNo('firstAidQualified')->selected($stfdata['firstAidQualified'])->placeHolder();

            $form->toggleVisibilityByClass('firstAid')->onSelect('firstAidQualified')->when('Y');

            $row = $form->addRow()->addClass('firstAid');
            $row->addLabel('firstAidExpiry', __('First Aid Expiry'));
            $row->addDate('firstAidExpiry')->setValue($stfdata['firstAidExpiry']);

            $form->addRow()->addHeading(__('Biography'));

            $row = $form->addRow();
            $row->addLabel('countryOfOrigin', __('Country Of Origin'));
            $row->addSelectCountry('countryOfOrigin')->selected($stfdata['countryOfOrigin'])->placeHolder();

            $row = $form->addRow();
            $row->addLabel('qualifications', __('Qualifications'));
            $row->addTextField('qualifications')->setValue($stfdata['qualifications'])->maxlength(80);

            $row = $form->addRow();
            $row->addLabel('biographicalGrouping', __('Grouping'))->description(__('Used to group staff when creating a staff directory.'));
            $row->addTextField('biographicalGrouping')->setValue($stfdata['biographicalGrouping'])->maxlength(100);

            $row = $form->addRow();
            $row->addLabel('biographicalGroupingPriority', __('Grouping Priority'))->description(__('Higher numbers move teachers up the order within their grouping.'));
            $row->addNumber('biographicalGroupingPriority')->decimalPlaces(0)->maximum(99)->maxLength(2)->setValue('0');

            $row = $form->addRow();
            $row->addLabel('biography', __('Biography'));
            $row->addTextArea('biography')->setRows(10)->setValue($stfdata['biography']);

            $row = $form->addRow("Principle?");
            $row->addLabel('is_principle', __('Principle?'));
            $row->addCheckBox('is_principle')->setValue('1')->checked($stfdata['is_principle']);

            $row = $form->addRow("Signature");
            $row->addLabel('file', __('Signature'));
            $row->addFileUpload('file')
                ->accepts('.jpg,.jpeg,.gif,.png')
                ->setMaxUpload(false);


			// CUSTOM FIELDS
			$existingFields = (isset($values['fields']))? unserialize($values['fields']) : null;
			$resultFields = getCustomFields($connection2, $guid, $student, $staff, $parent, $other);
			if ($resultFields->rowCount() > 0) {
				$heading = $form->addRow()->addHeading(__('Custom Fields'));
				
				while ($rowFields = $resultFields->fetch()) {
					$name = 'custom'.$rowFields['pupilsightPersonFieldID'];
					$value = (isset($existingFields[$rowFields['pupilsightPersonFieldID']]))? $existingFields[$rowFields['pupilsightPersonFieldID']] : '';
					
					$row = $form->addRow();
						$row->addLabel($name, $rowFields['name'])->description($rowFields['description']);
						$row->addCustomField($name, $rowFields)->setValue($value);
				}
			}

			$row = $form->addRow();
				$row->addFooter()->append('<small>'.getMaxUpload($guid, true).'</small>');
				$row->addSubmit();

			$form->loadAllValuesFrom($values);

            echo $form->getOutput();
            

            echo '<h3>' . __('Facilities') . '</h3>';
                try {
                    $data = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightPersonID2' => $pupilsightPersonID, 'pupilsightPersonID3' => $pupilsightPersonID, 'pupilsightPersonID4' => $pupilsightPersonID, 'pupilsightPersonID5' => $pupilsightPersonID, 'pupilsightPersonID6' => $pupilsightPersonID, 'pupilsightSchoolYearID1' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = '(SELECT pupilsightSpace.*, pupilsightSpacePersonID, usageType, NULL AS \'exception\' FROM pupilsightSpacePerson JOIN pupilsightSpace ON (pupilsightSpacePerson.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightPersonID=:pupilsightPersonID1)
                    UNION
                    (SELECT DISTINCT pupilsightSpace.*, NULL AS pupilsightSpacePersonID, \'Roll Group\' AS usageType, NULL AS \'exception\' FROM pupilsightRollGroup JOIN pupilsightSpace ON (pupilsightRollGroup.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE (pupilsightPersonIDTutor=:pupilsightPersonID2 OR pupilsightPersonIDTutor2=:pupilsightPersonID3 OR pupilsightPersonIDTutor3=:pupilsightPersonID4) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID1)
                    UNION
                    (SELECT DISTINCT pupilsightSpace.*, NULL AS pupilsightSpacePersonID, \'Timetable\' AS usageType, pupilsightTTDayRowClassException.pupilsightPersonID AS \'exception\' FROM pupilsightSpace JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) LEFT JOIN pupilsightTTDayRowClassException ON (pupilsightTTDayRowClassException.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID AND (pupilsightTTDayRowClassException.pupilsightPersonID=:pupilsightPersonID6 OR pupilsightTTDayRowClassException.pupilsightPersonID IS NULL)) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID5)
                    ORDER BY name';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }

                echo "<div class='linkTop'>";
                // echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/staff_manage_edit_facility_add.php&pupilsightPersonID=$pupilsightPersonID&pupilsightStaffID=$pupilsightStaffID&search=$search'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";


                echo "<div style='height:50px;'><div class='float-right mb-2'>";
                echo "&nbsp;&nbsp;<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . "/staff_manage_edit_facility_add.php&pupilsightPersonID=$pupilsightPersonID&pupilsightStaffID=$pupilsightStaffID&search=$search' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";

                echo '</div>';

                if ($result->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    echo "<table cellspacing='0' style='width: 100%'>";
                    echo "<tr class='head'>";
                    echo '<th>';
                    echo __('Name');
                    echo '</th>';
                    echo '<th>';
                    echo __('Usage') . '<br/>';
                    echo '</th>';
                    echo '<th>';
                    echo __('Actions');
                    echo '</th>';
                    echo '</tr>';

                    $count = 0;
                    $rowNum = 'odd';
                    while ($row = $result->fetch()) {
                        if ($row['exception'] == null) {
                            if ($count % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }
                            ++$count;

                            echo "<tr class=$rowNum>";
                            echo '<td>';
                            echo $row['name'];
                            echo '</td>';
                            echo '<td>';
                            echo $row['usageType'];
                            echo '</td>';
                            echo '<td>';
                            if ($row['usageType'] != 'Roll Group' and $row['usageType'] != 'Timetable') {
                                echo "<a class='thickbox' href='" . $_SESSION[$guid]['absoluteURL'] . '/fullscreen.php?q=/modules/' . $_SESSION[$guid]['module'] . '/staff_manage_edit_facility_delete.php&pupilsightSpacePersonID=' . $row['pupilsightSpacePersonID'] . "&pupilsightStaffID=$pupilsightStaffID&search=$search&width=650&height=135'><img title='" . __('Delete') . "' src='./themes/" . $_SESSION[$guid]['pupilsightThemeName'] . "/img/garbage.png'/></a> ";
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    echo '</table>';
                }


                if ($highestAction == 'Manage Staff_confidential') {
                    echo '<h3>' . __('Contracts') . '</h3>';
                    try {
                        $data = array('pupilsightStaffID' => $pupilsightStaffID);
                        $sql = 'SELECT * FROM pupilsightStaffContract WHERE pupilsightStaffID=:pupilsightStaffID ORDER BY dateStart DESC';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }

                    echo "<div class='linkTop'>";
                    // echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/staff_manage_edit_contract_add.php&pupilsightStaffID=$pupilsightStaffID&search=$search'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";

                    echo "<div style='height:50px;'><div class='float-right mb-2'>";
                    echo "&nbsp;&nbsp;<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . "/staff_manage_edit_contract_add.php&pupilsightStaffID=$pupilsightStaffID&search=$search' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";
                    echo '</div>';

                    if ($result->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        echo "<table cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo '<th>';
                        echo __('Title');
                        echo '</th>';
                        echo '<th>';
                        echo __('Status') . '<br/>';
                        echo '</th>';
                        echo '<th>';
                        echo __('Dates');
                        echo '</th>';
                        echo '<th>';
                        echo __('Actions');
                        echo '</th>';
                        echo '</tr>';

                        $count = 0;
                        $rowNum = 'odd';
                        while ($row = $result->fetch()) {
                            if ($count % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }
                            ++$count;

                            echo "<tr class=$rowNum>";
                            echo '<td>';
                            echo $row['title'];
                            echo '</td>';
                            echo '<td>';
                            echo $row['status'];
                            echo '</td>';
                            echo '<td>';
                            if ($row['dateEnd'] == '') {
                                echo dateConvertBack($guid, $row['dateStart']);
                            } else {
                                echo dateConvertBack($guid, $row['dateStart']) . ' - ' . dateConvertBack($guid, $row['dateEnd']);
                            }
                            echo '</td>';
                            echo '<td>';
                            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/staff_manage_edit_contract_edit.php&pupilsightStaffContractID=' . $row['pupilsightStaffContractID'] . "&pupilsightStaffID=$pupilsightStaffID&search=$search'><img title='" . __('Edit') . "' src='./themes/" . $_SESSION[$guid]['pupilsightThemeName'] . "/img/config.png'/></a> ";
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                }
            ?>

			<!-- CONTROLS FOR STATUS -->
			<script type="text/javascript">
				$(document).ready(function(){
					$("#status").change(function(){
						if ($('#status').val()=="Left" ) {
							alert("As you have marked this person as left, please consider setting the End Date field.") ;
						}
						else if ($('#status').val()=="Full" ) {
							alert("As you have marked this person as full, please consider setting the Start Date field.") ;
						}
						else if ($('#status').val()=="Expected" ) {
							alert("As you have marked this person as expected, please consider setting the Start Date field.") ;
						}
						});
				});
			</script>

			<?php
        }
    }
}
