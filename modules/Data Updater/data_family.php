<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_family.php') == false) {
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
        $page->breadcrumbs->add(__('Update Family Data'));
        
        if ($highestAction == 'Update Personal Data_any') {
            echo '<p>';
            echo __('This page allows a user to request selected family data updates for any family.');
            echo '</p>';
        } else {
            echo '<p>';
            echo __('This page allows any adult with data access permission to request selected family data updates for their family.');
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
        echo __('Choose Family');
        echo '</h2>';

        $pupilsightFamilyID = isset($_GET['pupilsightFamilyID'])? $_GET['pupilsightFamilyID'] : null;

        $form = Form::create('selectFamily', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/data_family.php');
    
        if ($highestAction == 'Update Family Data_any') {
            $data = array();
            $sql = "SELECT pupilsightFamily.pupilsightFamilyID as value, name FROM pupilsightFamily ORDER BY name";
        } else {
            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "SELECT pupilsightFamily.pupilsightFamilyID as value, name FROM pupilsightFamily JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y' ORDER BY name";
        }
        $row = $form->addRow();
            $row->addLabel('pupilsightFamilyID', __('Family'));
            $row->addSelect('pupilsightFamilyID')
                ->fromQuery($pdo, $sql, $data)
                ->required()
                ->selected($pupilsightFamilyID)
                ->placeholder();
        
        $row = $form->addRow()->addClass('right_align');
            $row->addSubmit();
        
        echo $form->getOutput();                   

        if ($pupilsightFamilyID != '') {
            echo '<h2>';
            echo __('Update Data');
            echo '</h2>';

            //Check access to person
            if ($highestAction == 'Update Family Data_any') {
                try {
                    $dataCheck = array('pupilsightFamilyID' => $pupilsightFamilyID);
                    $sqlCheck = 'SELECT name, pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily WHERE pupilsightFamilyID=:pupilsightFamilyID';
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                }
            } else {
                try {
                    $dataCheck = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlCheck = "SELECT name, pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y' AND pupilsightFamily.pupilsightFamilyID=:pupilsightFamilyID";
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }
            }

            if ($resultCheck->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                //Check if there is already a pending form for this user
                $existing = false;
                $proceed = false;
                try {
                    $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT * FROM pupilsightFamilyUpdate WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater AND status='Pending'";
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
                    $proceed = true;
                } else {
                    //Get user's data
                    try {
                        $data = array('pupilsightFamilyID' => $pupilsightFamilyID);
                        $sql = 'SELECT * FROM pupilsightFamily WHERE pupilsightFamilyID=:pupilsightFamilyID';
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
                        $proceed = true;
                    }
                }

                if ($proceed == true) {
                    //Let's go!
                    $values = $result->fetch(); 

                    $required = ($highestAction != 'Update Family Data_any');
                    
                    $form = Form::create('updateFamily', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/data_familyProcess.php?pupilsightFamilyID='.$pupilsightFamilyID);
                    $form->setFactory(DatabaseFormFactory::create($pdo));

                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                    $form->addHiddenValue('existing', isset($values['pupilsightFamilyUpdateID'])? $values['pupilsightFamilyUpdateID'] : 'N');

                    $row = $form->addRow();
                        $row->addLabel('nameAddress', __('Address Name'))->description(__('Formal name to address parents with.'));
                        $row->addTextField('nameAddress')->maxLength(100)->setRequired($required);

                    $row = $form->addRow();
                        $row->addLabel('homeAddress', __('Home Address'))->description(__('Unit, Building, Street'));
                        $row->addTextArea('homeAddress')->maxLength(255)->setRequired($required)->setRows(2);

                    $row = $form->addRow();
                        $row->addLabel('homeAddressDistrict', __('Home Address (District)'))->description(__('County, State, District'));
                        $row->addTextFieldDistrict('homeAddressDistrict')->setRequired($required);

                    $row = $form->addRow();
                        $row->addLabel('homeAddressCountry', __('Home Address (Country)'));
                        $row->addSelectCountry('homeAddressCountry')->setRequired($required);

                    $row = $form->addRow();
                        $row->addLabel('languageHomePrimary', __('Home Language - Primary'));
                        $row->addSelectLanguage('languageHomePrimary')->setRequired($required);

                    $row = $form->addRow();
                        $row->addLabel('languageHomeSecondary', __('Home Language - Secondary'));
                        $row->addSelectLanguage('languageHomeSecondary');

                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit()->setClass('submit_align submt');

                    $form->loadAllValuesFrom($values);

                    echo $form->getOutput();
                }
            }
        }
    }
}
