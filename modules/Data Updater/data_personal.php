<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
include './modules/User Admin/moduleFunctions.php'; //for User Admin (for custom fields)

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_personal.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        $page->breadcrumbs->add(__('Update Personal Data'));

        if ($highestAction == 'Update Personal Data_any') {
            echo '<p>';
            echo __('This page allows a user to request selected personal data updates for any user.');
            echo '</p>';
        } else {
            echo '<p>';
            echo __('This page allows any adult with data access permission to request selected personal data updates for any member of their family.');
            echo '</p>';
        }

        $customResponces = array();
        $error3 = __('Your request was successful, but some data was not properly saved. An administrator will process your request as soon as possible. <u>You will not see the updated data in the system until it has been processed and approved.</u>');
        if ($_SESSION[$guid]['organisationDBAEmail'] != '' and $_SESSION[$guid]['organisationDBAName'] != '') {
            $error3 .= ' '.sprintf(__('Please contact %1$s if you have any questions.'), "<a href='mailto:".$_SESSION[$guid]['organisationDBAEmail']."'>".$_SESSION[$guid]['organisationDBAName'].'</a>');
        }
        $customResponces['error3'] = $error3;

        $success0 = __('Your request was completed successfully. An administrator will process your request as soon as possible. You will not see the updated data in the system until it has been processed and approved.');
        if ($_SESSION[$guid]['organisationDBAEmail'] != '' and $_SESSION[$guid]['organisationDBAName'] != '') {
            $success0 .= ' '.sprintf(__('Please contact %1$s if you have any questions.'), "<a href='mailto:".$_SESSION[$guid]['organisationDBAEmail']."'>".$_SESSION[$guid]['organisationDBAName'].'</a>');
        }
        $customResponces['success0'] = $success0;

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, $customResponces);
        }

        echo '<h2>';
        echo __('Choose User');
        echo '</h2>';
        
        $pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : null;

        $form = Form::create('selectPerson', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/data_personal.php');
    
        if ($highestAction == 'Update Personal Data_any') {
            $data = array();
            $sql = "SELECT username, surname, preferredName, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE status='Full' ORDER BY surname, preferredName";
        } else {
            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "(SELECT pupilsightFamilyAdult.pupilsightFamilyID, pupilsightFamily.name as familyName, child.surname, child.preferredName, child.pupilsightPersonID
                    FROM pupilsightFamilyAdult 
                    JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) 
                    JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
                    JOIN pupilsightPerson as child ON (pupilsightFamilyChild.pupilsightPersonID=child.pupilsightPersonID) 
                    WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID 
                    AND pupilsightFamilyAdult.childDataAccess='Y' AND child.status='Full') 
                UNION (SELECT pupilsightFamily.pupilsightFamilyID, pupilsightFamily.name as familyName, adult.surname, adult.preferredName, adult.pupilsightPersonID 
                    FROM pupilsightFamilyAdult 
                    JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
                    JOIN pupilsightFamilyAdult as familyAdult ON (familyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
                    JOIN pupilsightPerson as adult ON (familyAdult.pupilsightPersonID=adult.pupilsightPersonID) 
                    WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID AND adult.status='Full')
                ORDER BY surname, preferredName";
        }
        $result = $pdo->executeQuery($data, $sql);
        $resultSet = ($result && $result->rowCount() > 0)? $result->fetchAll() : array();

        // Collect a list of people with formatted names, add username for Data_any access
        $people = array_reduce($resultSet, function($carry, $person) use ($highestAction) {
            $id = str_pad($person['pupilsightPersonID'], 10, '0', STR_PAD_LEFT);
            $carry[$id] = formatName('', htmlPrep($person['preferredName']), htmlPrep($person['surname']), 'Student', true);
            if ($highestAction == 'Update Personal Data_any') {
                $carry[$id] .= ' ('.$person['username'].')';
            }
            return $carry;
        }, array());

        // Add self to people if not in the list
        if (array_key_exists($_SESSION[$guid]['pupilsightPersonID'], $people) == false) {
            $people[$_SESSION[$guid]['pupilsightPersonID']] = formatName('', htmlPrep($_SESSION[$guid]['preferredName']), htmlPrep($_SESSION[$guid]['surname']), 'Student', true);
        }
        
        $row = $form->addRow();
            $row->addLabel('pupilsightPersonID', __('Person'));
            $row->addSelect('pupilsightPersonID')
                ->fromArray($people)
                ->required()
                ->selected($pupilsightPersonID)
                ->placeholder();
        
     
        $row = $form->addRow()->addClass('right_align');
            $row->addSubmit();
        
        echo $form->getOutput();    

        if ($pupilsightPersonID != '') {
            echo '<h2>';
            echo __('Update Data');
            echo '</h2>';

            //Check access to person
            $checkCount = 0;
            $self = false;
            if ($highestAction == 'Update Personal Data_any') {
                try {
                    $dataSelect = array();
                    $sqlSelect = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE status='Full' ORDER BY surname, preferredName";
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                }
                $checkCount = $resultSelect->rowCount();
                $self = true;
            } else {
                try {
                    $dataCheck = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlCheck = "SELECT pupilsightFamilyAdult.pupilsightFamilyID, name FROM pupilsightFamilyAdult JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y' ORDER BY name";
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                }
                while ($rowCheck = $resultCheck->fetch()) {
                    try {
                        $dataCheck2 = array('pupilsightFamilyID1' => $rowCheck['pupilsightFamilyID'], 'pupilsightFamilyID2' => $rowCheck['pupilsightFamilyID']);
                        $sqlCheck2 = "(SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND pupilsightFamilyID=:pupilsightFamilyID1) UNION (SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID FROM pupilsightFamilyAdult JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND pupilsightFamilyID=:pupilsightFamilyID2)";
                        $resultCheck2 = $connection2->prepare($sqlCheck2);
                        $resultCheck2->execute($dataCheck2);
                    } catch (PDOException $e) {
                    }
                    while ($rowCheck2 = $resultCheck2->fetch()) {
                        if ($pupilsightPersonID == $rowCheck2['pupilsightPersonID']) {
                            ++$checkCount;
                        }
                        //Check for self
                        if ($rowCheck2['pupilsightPersonID'] == $_SESSION[$guid]['pupilsightPersonID']) {
                            $self = true;
                        }
                    }
                }
            }

            if ($self == false and $pupilsightPersonID == $_SESSION[$guid]['pupilsightPersonID']) {
                ++$checkCount;
            }

            if ($checkCount < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                //Get categories
                try {
                    $dataSelect = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sqlSelect = 'SELECT pupilsightRoleIDAll, pupilsightRoleIDPrimary FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                }
                if ($resultSelect->rowCount() == 1) {
                    $rowSelect = $resultSelect->fetch();
                    //Get categories
                    $staff = $student = $parent = $other = false;
                    $roles = explode(',', $rowSelect['pupilsightRoleIDAll']);
                    $primaryRoleCategory = getRoleCategory($rowSelect['pupilsightRoleIDPrimary'], $connection2);
                    $roleCategories = [];
                    foreach ($roles as $role) {
                        $roleCategory = getRoleCategory($role, $connection2);
                        $staff = $staff || ($roleCategory == 'Staff');
                        $student = $student || ($roleCategory == 'Student');
                        $parent = $parent || ($roleCategory == 'Parent');
                        $other = $other || ($roleCategory == 'Other');
                        $roleCategories[$roleCategory] = $roleCategory;
                    }
                }

                //Check if there is already a pending form for this user
                $existing = false;
                $proceed = false;
                $requiredFields = [];
                
                if ($highestAction != 'Update Personal Data_any') {
                    $requiredFieldsSetting = unserialize(getSettingByScope($connection2, 'User Admin', 'personalDataUpdaterRequiredFields'));
                    if (is_array($requiredFieldsSetting)) {
                        if (!isset($requiredFieldsSetting[$primaryRoleCategory])) {
                            // If there's no per-role settings then handle the original required field Y/N settings
                            $requiredFields = array_map(function ($item) {
                                return $item == 'Y'? 'required' : '';
                            }, $requiredFieldsSetting);
                        } elseif (is_array($roleCategories) && count($roleCategories) > 1) {
                            // Flip the array from role=>field=>value to field=>role=>value
                            // Loop by only the roles categories this user has.
                            foreach ($roleCategories as $roleCategory) {
                                $fields = $requiredFieldsSetting[$roleCategory] ?? [];
                                foreach ($fields as $name => $value) {
                                    $requiredFields[$name][$roleCategory] = $value;
                                }
                            }
                            // Reduce each field to the setting with the greatest priority.
                            // Eg: required by at least one role = a required field.
                            $requiredFields = array_map(function ($field) {
                                if (in_array('required', $field)) return 'required';
                                if (in_array('', $field, true)) return '';
                                if (in_array('readonly', $field)) return 'readonly';
                                if (in_array('hidden', $field)) return 'hidden';
                                return '';
                            }, $requiredFields);
                        } else {
                            // Grab the required fields for the users primary roles
                            $requiredFields = $requiredFieldsSetting[$primaryRoleCategory];
                        }
                    }
                }

                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT * FROM pupilsightPersonUpdate WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater AND status='Pending'";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() > 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('Your request failed due to a database error.');
                    echo '</div>';
                } elseif ($result->rowCount() == 1) {
                    $existing = true;
                    echo "<div class='alert alert-warning'>";
                    echo __('You have already submitted a form, which is pending approval by an administrator. If you wish to make changes, please edit the data below, but remember your data will not appear in the system until it has been approved.');
                    echo '</div>';

                    if ($highestAction != 'Update Personal Data_any') {
                        $proceed = is_array($requiredFields);
                    } else {
                        $proceed = true;
                    }
                } else {
                    //Get user's data
                    try {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID);
                        $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($result->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The specified record cannot be found.');
                        echo '</div>';
                    } else {
                        if ($highestAction != 'Update Personal Data_any') {
                            $proceed = is_array($requiredFields);
                        } else {
                            $proceed = true;
                        }
                    }
                }

                if ($proceed == true) {
                    //Let's go!
                    $values = $result->fetch();

                    // Closure: Check if a field is visible.
                    $isVisible = function ($name) use ($requiredFields) {
                        return empty($requiredFields[$name]) || $requiredFields[$name] != 'hidden';
                    };

                    // Closure: check if any field in a given array are visible.
                    // Useful to hide headings in sections if not needed.
                    $anyVisible = function ($names) use ($requiredFields) {
                        if (empty($requiredFields)) return true;
                        $fields = array_intersect_key($requiredFields, array_flip($names));
                        $visible = array_filter($fields, function ($item) {
                            return empty($item) || $item != 'hidden';
                        });

                        return count($visible) > 0;
                    };

                    $form = Form::create('updateFinance', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/data_personalProcess.php?pupilsightPersonID='.$pupilsightPersonID);
                    $form->setFactory(DatabaseFormFactory::create($pdo));

                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                    $form->addHiddenValue('existing', isset($values['pupilsightPersonUpdateID'])? $values['pupilsightPersonUpdateID'] : 'N');
                    
                    // BASIC INFORMATION
                    $form->addRow()->addHeading(__('Basic Information'));

                    $row = $form->addRow()->onlyIf($isVisible('title'));
                        $row->addLabel('title', __('Title'));
                        $row->addSelectTitle('title');

                    $row = $form->addRow()->onlyIf($isVisible('surname'));
                        $row->addLabel('surname', __('Surname'))->description(__('Family name as shown in ID documents.'));
                        $row->addTextField('surname')->maxLength(60);

                    $row = $form->addRow()->onlyIf($isVisible('firstName'));
                        $row->addLabel('firstName', __('First Name'))->description(__('First name as shown in ID documents.'));
                        $row->addTextField('firstName')->maxLength(60);

                    $row = $form->addRow()->onlyIf($isVisible('preferredName'));
                        $row->addLabel('preferredName', __('Preferred Name'))->description(__('Most common name, alias, nickname, etc.'));
                        $row->addTextField('preferredName')->maxLength(60);

                    $row = $form->addRow()->onlyIf($isVisible('officialName'));
                        $row->addLabel('officialName', __('Official Name'))->description(__('Full name as shown in ID documents.'));
                        $row->addTextField('officialName')->maxLength(150)->setTitle(__('Please enter full name as shown in ID documents'));

                    $row = $form->addRow()->onlyIf($isVisible('nameInCharacters'));
                        $row->addLabel('nameInCharacters', __('Name In Characters'))->description(__('Chinese or other character-based name.'));
                        $row->addTextField('nameInCharacters')->maxLength(60);

                    $row = $form->addRow()->onlyIf($isVisible('dob'));
                        $row->addLabel('dob', __('Date of Birth'));
                        $row->addDate('dob');

                    // EMERGENCY CONTACTS
                    if ($student || $staff) {
                        $form->addRow()
                            ->onlyIf($anyVisible(['emergency1Name', 'emergency1Relationship', 'emergency1Number1', 'emergency1Number2', 'emergency2Name', 'emergency2Relationship', 'emergency2Number1', 'emergency2Number2']))
                            ->addHeading(__('Emergency Contacts'));

                        $form->addRow()->addContent(__('These details are used when immediate family members (e.g. parent, spouse) cannot be reached first. Please try to avoid listing immediate family members.'));

                        $row = $form->addRow()->onlyIf($isVisible('emergency1Name'));
                        $row->addLabel('emergency1Name', __('Contact 1 Name'));
                        $row->addTextField('emergency1Name')->maxLength(90);

                        $row = $form->addRow()->onlyIf($isVisible('emergency1Relationship'));
                        $row->addLabel('emergency1Relationship', __('Contact 1 Relationship'));
                        $row->addSelectEmergencyRelationship('emergency1Relationship');

                        $row = $form->addRow()->onlyIf($isVisible('emergency1Number1'));
                        $row->addLabel('emergency1Number1', __('Contact 1 Number 1'));
                        $row->addTextField('emergency1Number1')->maxLength(30);

                        $row = $form->addRow()->onlyIf($isVisible('emergency1Number2'));
                        $row->addLabel('emergency1Number2', __('Contact 1 Number 2'));
                        $row->addTextField('emergency1Number2')->maxLength(30);

                        $row = $form->addRow()->onlyIf($isVisible('emergency2Name'));
                        $row->addLabel('emergency2Name', __('Contact 2 Name'));
                        $row->addTextField('emergency2Name')->maxLength(90);

                        $row = $form->addRow()->onlyIf($isVisible('emergency2Relationship'));
                        $row->addLabel('emergency2Relationship', __('Contact 2 Relationship'));
                        $row->addSelectEmergencyRelationship('emergency2Relationship');

                        $row = $form->addRow()->onlyIf($isVisible('emergency2Number1'));
                        $row->addLabel('emergency2Number1', __('Contact 2 Number 1'));
                        $row->addTextField('emergency2Number1')->maxLength(30);

                        $row = $form->addRow()->onlyIf($isVisible('emergency2Number2'));
                        $row->addLabel('emergency2Number2', __('Contact 2 Number 2'));
                        $row->addTextField('emergency2Number2')->maxLength(30);
                    }

                    // CONTACT INFORMATION
                    $form->addRow()->addHeading(__('Contact Information'));

                    $row = $form->addRow()->onlyIf($isVisible('email'));
                        $row->addLabel('email', __('Email'));
                        $email = $row->addEmail('email');

                    $uniqueEmailAddress = getSettingByScope($connection2, 'User Admin', 'uniqueEmailAddress');
                    if ($uniqueEmailAddress == 'Y') {
                        $email->uniqueField('./modules/User Admin/user_manage_emailAjax.php', array('pupilsightPersonID' => $pupilsightPersonID));
                    }

                    $row = $form->addRow()->onlyIf($isVisible('emailAlternate'));
                        $row->addLabel('emailAlternate', __('Alternate Email'));
                        $row->addEmail('emailAlternate');

                    $addressSet = ($values['address1'] != '' or $values['address1District'] != '' or $values['address1Country'] != '' or $values['address2'] != '' or $values['address2District'] != '' or $values['address2Country'] != '')? 'Yes' : '';

                    $row = $form->addRow();
                        $row->addLabel('showAddresses', __('Enter Personal Address?'));
                        $row->addCheckbox('showAddresses')->setValue('Yes')->checked($addressSet);
                    
                    $form->toggleVisibilityByClass('address')->onCheckbox('showAddresses')->when('Yes');

                    $row = $form->addRow()->addClass('address');
                    $row->addAlert(__('Address information for an individual only needs to be set under the following conditions:'), 'warning')
                        ->append('<ol>')
                        ->append('<li>'.__('If the user is not in a family.').'</li>')
                        ->append('<li>'.__('If the user\'s family does not have a home address set.').'</li>')
                        ->append('<li>'.__('If the user needs an address in addition to their family\'s home address.').'</li>')
                        ->append('</ol>');

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
                            $dataAddress = array(
                                'pupilsightPersonID' => $values['pupilsightPersonID'], 
                                'addressMatch' => '%'.strtolower(preg_replace('/ /', '%', preg_replace('/,/', '%', $values['address1']))).'%',
                                'pupilsightFamilyPeople' => implode(',', array_keys($people)),
                            );
                            $sqlAddress = "SELECT pupilsightPersonID, title, preferredName, surname, category 
                                FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) 
                                WHERE status='Full' AND address1 LIKE :addressMatch 
                                AND FIND_IN_SET(pupilsightPersonID, :pupilsightFamilyPeople) AND NOT pupilsightPersonID=:pupilsightPersonID 
                                ORDER BY surname, preferredName";
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
                        $row = $form->addRow()->onlyIf($isVisible('phone'.$i));
                        $row->addLabel('phone'.$i, __('Phone').' '.$i)->description(__('Type, country code, number.'));
                        $row->addPhoneNumber('phone'.$i);
                    }

                    // BACKGROUND INFORMATION
                    $form->addRow()->addHeading(__('Background Information'));

                    $row = $form->addRow()->onlyIf($isVisible('languageFirst'));
                        $row->addLabel('languageFirst', __('First Language'));
                        $row->addSelectLanguage('languageFirst');

                    $row = $form->addRow()->onlyIf($isVisible('languageSecond'));
                        $row->addLabel('languageSecond', __('Second Language'));
                        $row->addSelectLanguage('languageSecond');

                    $row = $form->addRow()->onlyIf($isVisible('languageThird'));
                        $row->addLabel('languageThird', __('Third Language'));
                        $row->addSelectLanguage('languageThird');

                    $row = $form->addRow()->onlyIf($isVisible('countryOfBirth'));
                        $row->addLabel('countryOfBirth', __('Country of Birth'));
                        $row->addSelectCountry('countryOfBirth');

                    $ethnicities = getSettingByScope($connection2, 'User Admin', 'ethnicity');
                    $row = $form->addRow()->onlyIf($isVisible('ethnicity'));
                        $row->addLabel('ethnicity', __('Ethnicity'));
                        if (!empty($ethnicities)) {
                            $row->addSelect('ethnicity')->fromString($ethnicities)->placeholder();
                        } else {
                            $row->addTextField('ethnicity')->maxLength(255);
                        }

                    $religions = getSettingByScope($connection2, 'User Admin', 'religions');
                    $row = $form->addRow()->onlyIf($isVisible('religion'));
                        $row->addLabel('religion', __('Religion'));
                        if (!empty($religions)) {
                            $row->addSelect('religion')->fromString($religions)->placeholder();
                        } else {
                            $row->addTextField('religion')->maxLength(30);
                        }

                    $nationalityList = getSettingByScope($connection2, 'User Admin', 'nationality');
                    $row = $form->addRow()->onlyIf($isVisible('citizenship1'));
                        $row->addLabel('citizenship1', __('Citizenship 1'));
                        if (!empty($nationalityList)) {
                            $row->addSelect('citizenship1')->fromString($nationalityList)->placeholder();
                        } else {
                            $row->addSelectCountry('citizenship1');
                        }

                    $row = $form->addRow()->onlyIf($isVisible('citizenship1Passport'));
                        $row->addLabel('citizenship1Passport', __('Citizenship 1 Passport Number'));
                        $row->addTextField('citizenship1Passport')->maxLength(30);

                    $row = $form->addRow()->onlyIf($isVisible('citizenship2'));
                        $row->addLabel('citizenship2', __('Citizenship 2'));
                        if (!empty($nationalityList)) {
                            $row->addSelect('citizenship2')->fromString($nationalityList)->placeholder();
                        } else {
                            $row->addSelectCountry('citizenship2');
                        }

                    $row = $form->addRow()->onlyIf($isVisible('citizenship2Passport'));
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

                    $row = $form->addRow()->onlyIf($isVisible('nationalIDCardNumber'));
                        $row->addLabel('nationalIDCardNumber', $nationalIDCardNumberLabel);
                        $row->addTextField('nationalIDCardNumber')->maxLength(30);

                    $residencyStatusList = getSettingByScope($connection2, 'User Admin', 'residencyStatus');
                    $row = $form->addRow()->onlyIf($isVisible('residencyStatus'));
                        $row->addLabel('residencyStatus', $residencyStatusLabel);
                        if (!empty($residencyStatusList)) {
                            $row->addSelect('residencyStatus')->fromString($residencyStatusList)->placeholder();
                        } else {
                            $row->addTextField('residencyStatus')->maxLength(30);
                        }

                    $row = $form->addRow()->onlyIf($isVisible('visaExpiryDate'));
                        $row->addLabel('visaExpiryDate', $visaExpiryDateLabel)->description(__('If relevant.'));
                        $row->addDate('visaExpiryDate');

                    // EMPLOYMENT
                    if ($parent) {
                        $form->addRow()
                            ->onlyIf($anyVisible(['profession', 'employer', 'jobTitle']))
                            ->addHeading(__('Employment'));

                        $row = $form->addRow()->onlyIf($isVisible('profession'));
                            $row->addLabel('profession', __('Profession'));
                            $row->addTextField('profession')->maxLength(90);

                        $row = $form->addRow()->onlyIf($isVisible('employer'));
                            $row->addLabel('employer', __('Employer'));
                            $row->addTextField('employer')->maxLength(90);

                        $row = $form->addRow()->onlyIf($isVisible('jobTitle'));
                            $row->addLabel('jobTitle', __('Job Title'));
                            $row->addTextField('jobTitle')->maxLength(90);
                    }

                    // MISCELLANEOUS
                    $form->addRow()
                        ->onlyIf($anyVisible(['vehicleRegistration']))
                        ->addHeading(__('Miscellaneous'));
                    
                    $row = $form->addRow()->onlyIf($isVisible('vehicleRegistration'));
                        $row->addLabel('vehicleRegistration', __('Vehicle Registration'));
                        $row->addTextField('vehicleRegistration')->maxLength(20);

                    if ($student) {
                        $privacySetting = getSettingByScope($connection2, 'User Admin', 'privacy');
                        $privacyBlurb = getSettingByScope($connection2, 'User Admin', 'privacyBlurb');
                        $privacyOptions = getSettingByScope($connection2, 'User Admin', 'privacyOptions');

                        if ($privacySetting == 'Y' && !empty($privacyBlurb) && !empty($privacyOptions)) {

                            $form->addRow()->addSubheading(__('Privacy'))->append($privacyBlurb);

                            $options = array_map(function($item) { return trim($item); }, explode(',', $privacyOptions));
                            $values['privacyOptions'] = $values['privacy'];

                            $row = $form->addRow();
                                $row->addLabel('privacyOptions[]', __('Privacy Options'));
                                $row->addCheckbox('privacyOptions[]')->fromArray($options)->loadFromCSV($values)->addClass('md:max-w-lg');
                        }
                    }

                    // CUSTOM FIELDS
                    $existingFields = (isset($values['fields']))? unserialize($values['fields']) : null;
                    $resultFields = getCustomFields($connection2, $guid, $student, $staff, $parent, $other, false, true);
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
                        $row->addFooter();
                        $row->addSubmit()->setClass('submit_align submt');
                        
                    $form->loadStateFrom('required', array_filter($requiredFields, function ($item) {
                        return $item == 'required';
                    }));

                    $form->loadStateFrom('readonly', array_filter($requiredFields, function ($item) {
                        return $item == 'readonly';
                    }));
                    
                    $form->loadAllValuesFrom($values);

                    echo $form->getOutput();
                }
            }
        }
    }
}
