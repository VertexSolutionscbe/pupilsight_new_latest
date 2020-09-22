<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
include './modules/User Admin/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_personal_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = isset($_REQUEST['pupilsightSchoolYearID'])? $_REQUEST['pupilsightSchoolYearID'] : $_SESSION[$guid]['pupilsightSchoolYearID'];

    $urlParams = ['pupilsightSchoolYearID' => $pupilsightSchoolYearID];
    
    $page->breadcrumbs
        ->add(__('Personal Data Updates'), 'data_personal_manage.php', $urlParams)
        ->add(__('Edit Request'));    

    //Check if school year specified
    $pupilsightPersonUpdateID = $_GET['pupilsightPersonUpdateID'];
    if ($pupilsightPersonUpdateID == 'Y') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightPersonUpdateID' => $pupilsightPersonUpdateID);
            $sql = 'SELECT pupilsightPerson.* FROM pupilsightPersonUpdate JOIN pupilsightPerson ON (pupilsightPersonUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPersonUpdateID=:pupilsightPersonUpdateID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $data = array('pupilsightPersonUpdateID' => $pupilsightPersonUpdateID);
            $sql = "SELECT pupilsightPersonUpdate.* FROM pupilsightPersonUpdate JOIN pupilsightPerson ON (pupilsightPersonUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPersonUpdateID=:pupilsightPersonUpdateID";
            $newResult = $pdo->executeQuery($data, $sql);
            
            //Let's go!
            $oldValues = $result->fetch();
            $newValues = $newResult->fetch();

            // Provide a link back to edit the associated record
            echo "<div class='linkTop'>";
            if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage_edit.php') == true) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/User Admin/user_manage_edit.php&pupilsightPersonID=".$oldValues['pupilsightPersonID']."'>".__('Edit User')."<img style='margin: 0 0 -4px 5px' title='".__('Edit User')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
            }
            if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php') == true && getRoleCategory($oldValues['pupilsightRoleIDPrimary'], $connection2) == 'Student') {
                echo "&nbsp;&nbsp;&nbsp;<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=".$oldValues['pupilsightPersonID']."'>".__('View Student')."<img style='margin: 0 0 -4px 5px' title='".__('View Student')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
            }
            echo '</div>';

            //Get categories
            $staff = false;
            $student = false;
            $parent = false;
            $other = false;
            $roles = explode(',', $oldValues['pupilsightRoleIDAll']);
            foreach ($roles as $role) {
                $roleCategory = getRoleCategory($role, $connection2);
                $staff = $staff || ($roleCategory == 'Staff');
                $student = $student || ($roleCategory == 'Student');
                $parent = $parent || ($roleCategory == 'Parent');
                $other = $other || ($roleCategory == 'Other');
            }
            
            // An array of common fields to compare in each data set, and the field label
            $compare = array(
                'title'                  => __('Title'),
                'surname'                => __('Surname'),
                'firstName'              => __('First Name'),
                'preferredName'          => __('Preferred Name'),
                'officialName'           => __('Official Name'),
                'nameInCharacters'       => __('Name In Characters'),
                'dob'                    => __('Date of Birth'),
                'email'                  => __('Email'),
                'emailAlternate'         => __('Alternate Email'),
                'address1'               => __('Address 1'),
                'address1District'       => __('Address 1 District'),
                'address1Country'        => __('Address 1 Country'),
                'address2'               => __('Address 2'),
                'address2District'       => __('Address 2 District'),
                'address2Country'        => __('Address 2 Country'),
                'phone1Type'             => __('Phone').' 1 '.__('Type'),
                'phone1CountryCode'      => __('Phone').' 1 '.__('Country Code'),
                'phone1'                 => __('Phone').' 1 ',
                'phone2Type'             => __('Phone').' 2 '.__('Type'),
                'phone2CountryCode'      => __('Phone').' 2 '.__('Country Code'),
                'phone2'                 => __('Phone').' 2 ',
                'phone3Type'             => __('Phone').' 3 '.__('Type'),
                'phone3CountryCode'      => __('Phone').' 3 '.__('Country Code'),
                'phone3'                 => __('Phone').' 3 ',
                'phone4Type'             => __('Phone').' 4 '.__('Type'),
                'phone4CountryCode'      => __('Phone').' 4 '.__('Country Code'),
                'phone4'                 => __('Phone').' 4 ',
                'languageFirst'          => __('First Language'),
                'languageSecond'         => __('Second Language'),
                'languageThird'          => __('Third Language'),
                'countryOfBirth'         => __('Country of Birth'),
                'ethnicity'              => __('Ethnicity'),
                'religion'               => __('Religion'),
                'citizenship1'           => __('Citizenship 1'),
                'citizenship1Passport'   => __('Citizenship 1 Passport Number'),
                'citizenship2'           => __('Citizenship 2'),
                'citizenship2Passport'   => __('Citizenship 2 Passport Number'),
                'nationalIDCardNumber'   => __('National ID Card Number'),
                'residencyStatus'        => __('Residency/Visa Type'),
                'visaExpiryDate'         => __('Visa Expiry Date'),
                'vehicleRegistration'    => __('Vehicle Registration'),
            );

            //Adjust country in field label
            if (!empty($_SESSION[$guid]['country'])) {
                $compare['nationalIDCardNumber'] = $_SESSION[$guid]['country'].' '.__('ID Card Number');
                $compare['residencyStatus'] = $_SESSION[$guid]['country'].' '.__('Residency/Visa Type');
                $compare['visaExpiryDate'] = $_SESSION[$guid]['country'].' '.__('Visa Expiry Date');
            }

            if ($student || $staff) {
                $compare['emergency1Name']         = __('Emergency 1 Name');
                $compare['emergency1Number1']      = __('Emergency 1 Number 1');
                $compare['emergency1Number2']      = __('Emergency 1 Number 2');
                $compare['emergency1Relationship'] = __('Emergency 1 Relationship');
                $compare['emergency2Name']         = __('Emergency 2 Name');
                $compare['emergency2Number1']      = __('Emergency 2 Number 1');
                $compare['emergency2Number2']      = __('Emergency 2 Number 2');
                $compare['emergency2Relationship'] = __('Emergency 2 Relationship');
            }

            if ($student) {
                $compare['privacy'] = __('Privacy');
            }

            if ($parent) {
                $compare['profession'] = __('Profession');
                $compare['employer']   = __('Employer');
                $compare['jobTitle']   = __('Job Title');
            }

            $form = Form::create('updatePerson', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/data_personal_manage_editProcess.php?pupilsightPersonUpdateID='.$pupilsightPersonUpdateID);
            
            $form->setClass('fullWidth colorOddEven');
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightPersonID', $oldValues['pupilsightPersonID']);

            $row = $form->addRow()->setClass('head heading');
                $row->addContent(__('Field'));
                $row->addContent(__('Current Value'));
                $row->addContent(__('New Value'));
                $row->addContent(__('Accept'));

            foreach ($compare as $fieldName => $label) {
                $oldValue = isset($oldValues[$fieldName])? $oldValues[$fieldName] : '';
                $newValue = isset($newValues[$fieldName])? $newValues[$fieldName] : '';
                $isMatching = ($oldValue != $newValue);
                $isNonUnique = false;

                if ($fieldName == 'dob' || $fieldName == 'visaExpiryDate') {
                    $oldValue = Format::date($oldValue);
                    $newValue = Format::date($newValue);
                }

                if ($fieldName == 'email') {
                    $uniqueEmailAddress = getSettingByScope($connection2, 'User Admin', 'uniqueEmailAddress');
                    if ($uniqueEmailAddress == 'Y') {
                        $data = array('pupilsightPersonID' => $oldValues['pupilsightPersonID'], 'email' => $newValues['email']);
                        $sql = "SELECT COUNT(*) FROM pupilsightPerson WHERE email=:email AND pupilsightPersonID<>:pupilsightPersonID";
                        $result = $pdo->executeQuery($data, $sql);
                        $isNonUnique = ($result && $result->rowCount() == 1)? $result->fetchColumn(0) > 0 : false;
                    }
                }

                $row = $form->addRow();
                $row->addLabel('new'.$fieldName.'On', $label);
                $row->addContent($oldValue);
                $row->addContent($newValue)->addClass($isMatching ? 'matchHighlightText' : '');
                
                if ($isNonUnique) {
                    $row->addContent(__('Must be unique.'));
                } else if ($isMatching) {
                    $row->addCheckbox('new'.$fieldName.'On')->checked(true)->setClass('textCenter');
                    $form->addHiddenValue('new'.$fieldName, $newValues[$fieldName]);
                } else {
                    $row->addContent();
                }
            }

            // CUSTOM FIELDS
			$oldFields = !empty($oldValues['fields'])? unserialize($oldValues['fields']) : array();
            $newFields = !empty($newValues['fields'])? unserialize($newValues['fields']) : array();
            $resultFields = getCustomFields($connection2, $guid, $student, $staff, $parent, $other, null, true);
            if ($resultFields->rowCount() > 0) {
                while ($rowFields = $resultFields->fetch()) {
                    $fieldName = $rowFields['pupilsightPersonFieldID'];
                    $label = __($rowFields['name']);

                    $oldValue = isset($oldFields[$fieldName])? $oldFields[$fieldName] : '';
                    $newValue = isset($newFields[$fieldName])? $newFields[$fieldName] : '';

                    $isMatching = ($oldValue != $newValue);

                    $row = $form->addRow();
                    $row->addLabel('new'.$fieldName.'On', $label);
                    $row->addContent($oldValue);
                    $row->addContent($newValue)->addClass($isMatching ? 'matchHighlightText' : '');

                    if ($isMatching) {
                        $row->addCheckbox('newcustom'.$fieldName.'On')->checked(true)->setClass('textCenter');
                        $form->addHiddenValue('newcustom'.$fieldName, $newValue);
                    } else {
                        $row->addContent();
                    }
                }
            }
            
            $row = $form->addRow();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
?>
