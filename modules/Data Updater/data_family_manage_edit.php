<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_family_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = isset($_REQUEST['pupilsightSchoolYearID'])? $_REQUEST['pupilsightSchoolYearID'] : $_SESSION[$guid]['pupilsightSchoolYearID'];
    $urlParams = ['pupilsightSchoolYearID' => $pupilsightSchoolYearID];
    
    $page->breadcrumbs
        ->add(__('Family Data Updates'), 'data_family_manage.php', $urlParams)
        ->add(__('Edit Request'));
    
    //Check if school year specified
    $pupilsightFamilyUpdateID = $_GET['pupilsightFamilyUpdateID'];
    if ($pupilsightFamilyUpdateID == 'Y') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFamilyUpdateID' => $pupilsightFamilyUpdateID);
            $sql = 'SELECT pupilsightFamily.* FROM pupilsightFamilyUpdate JOIN pupilsightFamily ON (pupilsightFamilyUpdate.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightFamilyUpdateID=:pupilsightFamilyUpdateID';
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
			
			$data = array('pupilsightFamilyUpdateID' => $pupilsightFamilyUpdateID);
			$sql = 'SELECT pupilsightFamilyUpdate.* FROM pupilsightFamilyUpdate JOIN pupilsightFamily ON (pupilsightFamilyUpdate.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightFamilyUpdateID=:pupilsightFamilyUpdateID';
			$newResult = $pdo->executeQuery($data, $sql);

            //Let's go!
			$oldValues = $result->fetch(); 
			$newValues = $newResult->fetch();
            
            // Provide a link back to edit the associated record
            if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit.php') == true) {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/User Admin/family_manage_edit.php&pupilsightFamilyID=".$oldValues['pupilsightFamilyID']."'>".__('Edit Family')."<img style='margin: 0 0 -4px 5px' title='".__('Edit Family')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                echo '</div>';
            }

			$compare = array(
				'nameAddress'           => __('Address Name'),
				'homeAddress'           => __('Home Address'),
				'homeAddressDistrict'   => __('Home Address (District)'),
				'homeAddressCountry'    => __('Home Address (Country)'),
				'languageHomePrimary'   => __('Home Language - Primary'),
				'languageHomeSecondary' => __('Home Language - Secondary'),
			);

			$form = Form::create('updateFamily', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/data_family_manage_editProcess.php?pupilsightFamilyUpdateID='.$pupilsightFamilyUpdateID);
			
			$form->setClass('fullWidth colorOddEven');
			$form->addHiddenValue('address', $_SESSION[$guid]['address']);
			$form->addHiddenValue('pupilsightFamilyID', $oldValues['pupilsightFamilyID']);

			$row = $form->addRow()->setClass('head heading');
				$row->addContent(__('Field'));
				$row->addContent(__('Current Value'));
				$row->addContent(__('New Value'));
				$row->addContent(__('Accept'));

			foreach ($compare as $fieldName => $label) {
				$isMatching = ($oldValues[$fieldName] != $newValues[$fieldName]);

				$row = $form->addRow();
					$row->addLabel('new'.$fieldName.'On', $label);
					$row->addContent($oldValues[$fieldName]);
					$row->addContent($newValues[$fieldName])->addClass($isMatching ? 'matchHighlightText' : '');
				
				if ($isMatching) {
					$row->addCheckbox('new'.$fieldName.'On')->checked(true)->setClass('textCenter');
					$form->addHiddenValue('new'.$fieldName, $newValues[$fieldName]);
				} else {
					$row->addContent();
				}
			}
			
			$row = $form->addRow();
				$row->addSubmit();

			echo $form->getOutput();
        }
    }
}
