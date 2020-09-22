<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_medical_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = isset($_REQUEST['pupilsightSchoolYearID'])? $_REQUEST['pupilsightSchoolYearID'] : $_SESSION[$guid]['pupilsightSchoolYearID'];

    $urlParams = ['pupilsightSchoolYearID' => $pupilsightSchoolYearID];
    
    $page->breadcrumbs
        ->add(__('Medical Data Updates'), 'data_medical_manage.php', $urlParams)
        ->add(__('Edit Request'));

    //Check if school year specified
    $pupilsightPersonMedicalUpdateID = $_GET['pupilsightPersonMedicalUpdateID'];
    if ($pupilsightPersonMedicalUpdateID == 'Y') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID);
            $sql = "SELECT pupilsightPersonMedical.* FROM pupilsightPersonMedicalUpdate
                    LEFT JOIN pupilsightPersonMedical ON (pupilsightPersonMedical.pupilsightPersonID=pupilsightPersonMedicalUpdate.pupilsightPersonID)
                    WHERE pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID);
            $sql = "SELECT pupilsightPersonMedicalUpdate.* FROM pupilsightPersonMedicalUpdate
                    LEFT JOIN pupilsightPersonMedical ON (pupilsightPersonMedical.pupilsightPersonID=pupilsightPersonMedicalUpdate.pupilsightPersonID)
                    WHERE pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID";
            $newResult = $pdo->executeQuery($data, $sql);

            //Let's go!
            $oldValues = $result->fetch();
            $newValues = $newResult->fetch();

            // Provide a link back to edit the associated record
            if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_edit.php') == true && !empty($oldValues['pupilsightPersonMedicalID'])) {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/medicalForm_manage_edit.php&pupilsightPersonMedicalID=".$oldValues['pupilsightPersonMedicalID']."&search='>".__('Edit Medical Form')."<img style='margin: 0 0 -4px 5px' title='".__('Edit Medical Form')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                echo '</div>';
            }

            $compare = array(
                'bloodType'                 => __('Blood Type'),
                'longTermMedication'        => __('Long-Term Medication?'),
                'longTermMedicationDetails' => __('Medication Details'),
                'tetanusWithin10Years'      => __('Tetanus Within Last 10 Years?'),
                'comment'      => __('Comment'),
            );

            $compareCondition = array(
                'name'                 => __('Condition Name'),
                'pupilsightAlertLevelID'   => __('Risk'),
                'triggers'             => __('Triggers'),
                'reaction'             => __('Reaction'),
                'response'             => __('Response'),
                'medication'           => __('Medication'),
                'lastEpisode'          => __('Last Episode Date'),
                'lastEpisodeTreatment' => __('Last Episode Treatment'),
                'comment'              => __('Comment'),
            );

            $sql = "SELECT pupilsightMedicalConditionID AS value, name FROM pupilsightMedicalCondition ORDER BY name";
            $result = $pdo->executeQuery(array(), $sql);
            $conditions = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_KEY_PAIR) : array();

            $sql = "SELECT pupilsightAlertLevelID AS value, name FROM pupilsightAlertLevel ORDER BY sequenceNumber";
            $result = $pdo->executeQuery(array(), $sql);
            $alerts = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_KEY_PAIR) : array();

            $form = Form::create('updateMedical', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/data_medical_manage_editProcess.php?pupilsightPersonMedicalUpdateID='.$pupilsightPersonMedicalUpdateID);

            $form->setClass('fullWidth colorOddEven');
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightPersonID', $newValues['pupilsightPersonID']);
            $form->addHiddenValue('formExists', !empty($oldValues['pupilsightPersonMedicalID']));

            $row = $form->addRow()->setClass('head heading');
                $row->addContent(__('Field'));
                $row->addContent(__('Current Value'));
                $row->addContent(__('New Value'));
                $row->addContent(__('Accept'));

            // Create a reusable function for adding comparisons to the form
            $comparisonFields = function ($form, $oldValues, $newValues, $fieldName, $label, $count = '') use ($guid, $conditions, $alerts) {
                $oldValue = isset($oldValues[$fieldName])? $oldValues[$fieldName] : '';
                $newValue = isset($newValues[$fieldName])? $newValues[$fieldName] : '';
                $isMatching = ($oldValue != $newValue);

                if ($fieldName == 'name') {
                    $oldValue = isset($conditions[$oldValue])? $conditions[$oldValue] : $oldValue;
                    $newValue = isset($conditions[$newValue])? $conditions[$newValue] : $newValue;
                }

                if ($fieldName == 'pupilsightAlertLevelID') {
                    $oldValue = isset($alerts[$oldValue])? $alerts[$oldValue] : $oldValue;
                    $newValue = isset($alerts[$newValue])? $alerts[$newValue] : $newValue;
                }

                if ($fieldName == 'lastEpisode') {
                    $oldValue = dateConvertBack($guid, $oldValue);
                    $newValue = dateConvertBack($guid, $newValue);
                }

                $row = $form->addRow();
                $row->addLabel($fieldName.'On'.$count, $label);
                $row->addContent($oldValue);
                $row->addContent($newValue)->addClass($isMatching ? 'matchHighlightText' : '');

                if ($isMatching) {
                    $row->addCheckbox($fieldName.'On'.$count)->checked(true)->setClass('textCenter');
                    $form->addHiddenValue($fieldName.$count, $newValues[$fieldName]);
                } else {
                    $row->addContent();
                }
            };

            // Basic Medical Form
            $form->addRow()->addHeading(__('Basic Information'));

            foreach ($compare as $fieldName => $label) {
                $comparisonFields($form, $oldValues, $newValues, $fieldName, $label);
            }

            // Existing Conditions
            $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID);
            $sql = "SELECT * FROM pupilsightPersonMedicalConditionUpdate
                    WHERE pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID
                    AND NOT pupilsightPersonMedicalConditionID IS NULL
                    ORDER BY pupilsightPersonMedicalConditionUpdateID";
            $result = $pdo->executeQuery($data, $sql);

            $count = 0;
            if ($result->rowCount() > 0) {
                while ($newValues = $result->fetch()) {
                    $data = array('pupilsightPersonMedicalConditionID' => $newValues['pupilsightPersonMedicalConditionID']);
                    $sql = "SELECT * FROM pupilsightPersonMedicalCondition
                            WHERE pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID";
                    $oldResult = $pdo->executeQuery($data, $sql);
                    $oldValues = $oldResult->fetch();

                    $form->addRow()->addHeading(__('Existing Condition').' '.($count+1));
                    $form->addHiddenValue('pupilsightPersonMedicalConditionID'.$count, $newValues['pupilsightPersonMedicalConditionID']);

                    foreach ($compareCondition as $fieldName => $label) {
                        $comparisonFields($form, $oldValues, $newValues, $fieldName, $label, $count);
                    }

                    $count++;
                }
            }

            // New Conditions
            $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID);
            $sql = "SELECT * FROM pupilsightPersonMedicalConditionUpdate
                    WHERE pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID
                    AND pupilsightPersonMedicalConditionID IS NULL ORDER BY name";
            $result = $pdo->executeQuery($data, $sql);

            $count2 = 0;
            if ($result->rowCount() > 0) {
                while ($newValues = $result->fetch()) {
                    $count2++;

                    $form->addRow()->addHeading(__('New Condition').' '.$count2);
                    $form->addHiddenValue('pupilsightPersonMedicalConditionUpdateID'.($count+$count2), $newValues['pupilsightPersonMedicalConditionUpdateID']);

                    foreach ($compareCondition as $fieldName => $label) {
                        $comparisonFields($form, array(), $newValues, $fieldName, $label, $count+$count2);
                    }
                }
            }

            $form->addHiddenValue('count', $count);
            $form->addHiddenValue('count2', $count2);

            $row = $form->addRow();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
