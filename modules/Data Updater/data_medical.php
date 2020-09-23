<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Students\MedicalGateway;
use Pupilsight\Domain\DataUpdater\MedicalUpdateGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_medical.php') == false) {
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
        $page->breadcrumbs->add(__('Update Medical Data'));

        if ($highestAction == 'Update Medical Data_any') {
            echo '<p>';
            echo __('This page allows a user to request selected medical data updates for any student.');
            echo '</p>';
        } else {
            echo '<p>';
            echo __('This page allows any adult with data access permission to request medical data updates for any member of their family.');
            echo '</p>';
        }

        $customResponces = array();

        $success0 = __('Your request was completed successfully. An administrator will process your request as soon as possible. You will not see the updated data in the system until it has been processed and approved.');
        if ($_SESSION[$guid]['organisationDBAEmail'] != '' and $_SESSION[$guid]['organisationDBAName'] != '') {
            $success0 .= ' '.sprintf(__('Please contact %1$s if you have any questions.'), "<a href='mailto:".$_SESSION[$guid]['organisationDBAEmail']."'>".$_SESSION[$guid]['organisationDBAName'].'</a>');
        }
        $customResponces['success0'] = $success0;

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, $customResponces);
        }

        echo '<h2>';
        echo 'Choose User';
        echo '</h2>';

        $pupilsightPersonID = null;
        if (isset($_GET['pupilsightPersonID'])) {
            $pupilsightPersonID = $_GET['pupilsightPersonID'];
		}

		$pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : null;

		$form = Form::create('selectFamily', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
		$form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/data_medical.php');

		if ($highestAction == 'Update Medical Data_any') {
			$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, username, surname, preferredName FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Full' ORDER BY surname, preferredName";
		} else {
			$data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "SELECT pupilsightFamilyAdult.pupilsightFamilyID, pupilsightFamily.name as familyName, child.surname, child.preferredName, child.pupilsightPersonID
					FROM pupilsightFamilyAdult
					JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
					LEFT JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID)
					LEFT JOIN pupilsightPerson AS child ON (pupilsightFamilyChild.pupilsightPersonID=child.pupilsightPersonID)
					WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID
					AND pupilsightFamilyAdult.childDataAccess='Y' AND child.status='Full'
					ORDER BY pupilsightFamily.name, child.surname, child.preferredName";
		}

		$result = $pdo->executeQuery($data, $sql);
		$resultSet = ($result && $result->rowCount() > 0)? $result->fetchAll() : array();
		$people = array_reduce($resultSet, function($carry, $person) use ($highestAction) {
			$value = $person['pupilsightPersonID'];
			$carry[$value] = formatName('', htmlPrep($person['preferredName']), htmlPrep($person['surname']), 'Student', true);
			if ($highestAction == 'Update Medical Data_any') {
				$carry[$value] .= ' ('.$person['username'].')';
			}
			return $carry;
		}, array());

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
            if ($highestAction == 'Update Medical Data_any') {
                try {
                    $dataSelect = array();
                    $sqlSelect = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE status='Full' ORDER BY surname, preferredName";
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                }
                $checkCount = $resultSelect->rowCount();
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
                        $dataCheck2 = array('pupilsightFamilyID' => $rowCheck['pupilsightFamilyID'], 'pupilsightFamilyID2' => $rowCheck['pupilsightFamilyID']);
                        $sqlCheck2 = '(SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID) UNION (SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID FROM pupilsightFamilyAdult JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID2)';
                        $resultCheck2 = $connection2->prepare($sqlCheck2);
                        $resultCheck2->execute($dataCheck2);
                    } catch (PDOException $e) {
                    }
                    while ($rowCheck2 = $resultCheck2->fetch()) {
                        if ($pupilsightPersonID == $rowCheck2['pupilsightPersonID']) {
                            ++$checkCount;
                        }
                    }
                }
            }
            if ($checkCount < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
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
                    //Check if there is already a pending form for this user
                    $existing = false;
                    $proceed = false;
                    try {
                        $dataForm = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sqlForm = "SELECT * FROM pupilsightPersonMedicalUpdate WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightPersonIDUpdater=:pupilsightPersonID2 AND status='Pending'";
                        $resultForm = $connection2->prepare($sqlForm);
                        $resultForm->execute($dataForm);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultForm->rowCount() > 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('Your request failed due to a database error.');
                        echo '</div>';
                    } elseif ($resultForm->rowCount() == 1) {
                        $existing = true;
                        echo "<div class='alert alert-warning'>";
                        echo __('You have already submitted a form, which is pending approval by an administrator. If you wish to make changes, please edit the data below, but remember your data will not appear in the system until it has been approved.');
                        echo '</div>';
                        $proceed = true;
                    } else {
                        //Get user's data
                        try {
                            $dataForm = array('pupilsightPersonID' => $pupilsightPersonID);
                            $sqlForm = 'SELECT * FROM pupilsightPersonMedical WHERE pupilsightPersonID=:pupilsightPersonID';
                            $resultForm = $connection2->prepare($sqlForm);
                            $resultForm->execute($dataForm);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($result->rowCount() == 1) {
                            $proceed = true;
                        }
                    }

                    if ($proceed == true) {
						$values = $resultForm->fetch();

						$form = Form::create('updateFamily', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/data_medicalProcess.php?pupilsightPersonID='.$pupilsightPersonID);
						$form->setFactory(DatabaseFormFactory::create($pdo));

						$form->addHiddenValue('address', $_SESSION[$guid]['address']);
						$form->addHiddenValue('pupilsightPersonMedicalID', $values['pupilsightPersonMedicalID']);
						$form->addHiddenValue('existing', isset($values['pupilsightPersonMedicalUpdateID'])? $values['pupilsightPersonMedicalUpdateID'] : 'N');

						$row = $form->addRow();
							$row->addLabel('bloodType', __('Blood Type'));
							$row->addSelectBloodType('bloodType')->placeholder();

						$row = $form->addRow();
							$row->addLabel('longTermMedication', __('Long-Term Medication?'));
							$row->addYesNo('longTermMedication')->placeholder();

						$form->toggleVisibilityByClass('longTermMedicationDetails')->onSelect('longTermMedication')->when('Y');

						$row = $form->addRow()->addClass('longTermMedicationDetails');
							$row->addLabel('longTermMedicationDetails', __('Medication Details'));
							$row->addTextArea('longTermMedicationDetails')->setRows(5);

						$row = $form->addRow();
							$row->addLabel('tetanusWithin10Years', __('Tetanus Within Last 10 Years?'));
							$row->addYesNo('tetanusWithin10Years')->placeholder();

                        $row = $form->addRow();
							$row->addLabel('comment', __('Comment'));
							$row->addTextArea('comment')->setRows(6);

						// EXISTING CONDITIONS
						$count = 0;
						if ($values['pupilsightPersonMedicalID'] != '' or $existing == true) {

                            if ($existing == true) {
                                $medicalUpdateGateway = $container->get(MedicalUpdateGateway::class);
                                $conditions = $medicalUpdateGateway->selectMedicalConditionUpdatesByID($values['pupilsightPersonMedicalUpdateID'])->fetchAll();
                            } else {
                                $medicalGateway = $container->get(MedicalGateway::class);
                                $conditions = $medicalGateway->selectMedicalConditionsByID($values['pupilsightPersonMedicalID'])->fetchAll();
                            }

                            foreach ($conditions as $rowCond) {
								$form->addHiddenValue('pupilsightPersonMedicalConditionID'.$count, $rowCond['pupilsightPersonMedicalConditionID']);
								$form->addHiddenValue('pupilsightPersonMedicalConditionUpdateID'.$count, $existing ? $rowCond['pupilsightPersonMedicalConditionUpdateID'] : 0);

								$form->addRow()->addHeading(__('Medical Condition').' '.($count+1) );

								$sql = "SELECT name AS value, name FROM pupilsightMedicalCondition ORDER BY name";
								$row = $form->addRow();
									$row->addLabel('name'.$count, __('Condition Name'));
									$row->addSelect('name'.$count)->fromQuery($pdo, $sql)->required()->placeholder()->selected($rowCond['name']);

								$row = $form->addRow();
									$row->addLabel('pupilsightAlertLevelID'.$count, __('Risk'));
									$row->addSelectAlert('pupilsightAlertLevelID'.$count)->required()->selected($rowCond['pupilsightAlertLevelID']);

								$row = $form->addRow();
									$row->addLabel('triggers'.$count, __('Triggers'));
									$row->addTextField('triggers'.$count)->maxLength(255)->setValue($rowCond['triggers']);

								$row = $form->addRow();
									$row->addLabel('reaction'.$count, __('Reaction'));
									$row->addTextField('reaction'.$count)->maxLength(255)->setValue($rowCond['reaction']);

								$row = $form->addRow();
									$row->addLabel('response'.$count, __('Response'));
									$row->addTextField('response'.$count)->maxLength(255)->setValue($rowCond['response']);

								$row = $form->addRow();
									$row->addLabel('medication'.$count, __('Medication'));
									$row->addTextField('medication'.$count)->maxLength(255)->setValue($rowCond['medication']);

								$row = $form->addRow();
									$row->addLabel('lastEpisode'.$count, __('Last Episode Date'));
									$row->addDate('lastEpisode'.$count)->setValue(dateConvertBack($guid, $rowCond['lastEpisode']) );

								$row = $form->addRow();
									$row->addLabel('lastEpisodeTreatment'.$count, __('Last Episode Treatment'));
									$row->addTextField('lastEpisodeTreatment'.$count)->maxLength(255)->setValue($rowCond['lastEpisodeTreatment']);

								$row = $form->addRow();
									$row->addLabel('commentCond'.$count, __('Comment'));
									$row->addTextArea('commentCond'.$count)->setValue($rowCond['comment']);

								$count++;
							}

							$form->addHiddenValue('count', $count);
						}

						// ADD NEW CONDITION
						$form->addRow()->addHeading(__('Add Medical Condition'));

						$form->toggleVisibilityByClass('addConditionRow')->onCheckbox('addCondition')->when('Yes');

						$row = $form->addRow();
							$row->addCheckbox('addCondition')->setValue('Yes')->description(__('Check the box to add a new medical condition'));

						$sql = "SELECT name AS value, name FROM pupilsightMedicalCondition ORDER BY name";
						$row = $form->addRow()->addClass('addConditionRow');
							$row->addLabel('name', __('Condition Name'));
							$row->addSelect('name')->fromQuery($pdo, $sql)->required()->placeholder();

						$row = $form->addRow()->addClass('addConditionRow');
							$row->addLabel('pupilsightAlertLevelID', __('Risk'));
							$row->addSelectAlert('pupilsightAlertLevelID')->required();

						$row = $form->addRow()->addClass('addConditionRow');
							$row->addLabel('triggers', __('Triggers'));
							$row->addTextField('triggers')->maxLength(255);

						$row = $form->addRow()->addClass('addConditionRow');
							$row->addLabel('reaction', __('Reaction'));
							$row->addTextField('reaction')->maxLength(255);

						$row = $form->addRow()->addClass('addConditionRow');
							$row->addLabel('response', __('Response'));
							$row->addTextField('response')->maxLength(255);

						$row = $form->addRow()->addClass('addConditionRow');
							$row->addLabel('medication', __('Medication'));
							$row->addTextField('medication')->maxLength(255);

						$row = $form->addRow()->addClass('addConditionRow');
							$row->addLabel('lastEpisode', __('Last Episode Date'));
							$row->addDate('lastEpisode');

						$row = $form->addRow()->addClass('addConditionRow');
							$row->addLabel('lastEpisodeTreatment', __('Last Episode Treatment'));
							$row->addTextField('lastEpisodeTreatment')->maxLength(255);

						$row = $form->addRow()->addClass('addConditionRow');
							$row->addLabel('commentCond', __('Comment'));
							$row->addTextArea('commentCond');

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
}
