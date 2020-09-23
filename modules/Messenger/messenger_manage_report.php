<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\Prefab\BulkActionForm;

if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_manage_report.php")==FALSE) {
	//Acess denied
	print "<div class='alert alert-danger'>" ;
		print __("You do not have access to this action.") ;
	print "</div>" ;
}
else {
	//Get action with highest precendence
	$highestAction=getHighestGroupedAction($guid, $_GET["q"], $connection2) ;
	if ($highestAction==FALSE) {
		print "<div class='alert alert-danger'>" ;
		print __("The highest grouped action cannot be determined.") ;
		print "</div>" ;
	}
	else {
        $pupilsightMessengerID = isset($_GET['pupilsightMessengerID']) ? $_GET['pupilsightMessengerID'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : null;

        $page->breadcrumbs
            ->add(__('Manage Messages'), 'messenger_manage.php', ['search' => $search])
            ->add(__('View Send Report'));

		echo '<h2>';
		echo __('Report Data');
		echo '</h2>';
		
		$nonConfirm = 0;
		$noConfirm = 0;
		$yesConfirm = 0;

		try {
			$data = array('pupilsightMessengerID' => $pupilsightMessengerID);
			$sql = "SELECT pupilsightMessenger.* FROM pupilsightMessenger WHERE pupilsightMessengerID=:pupilsightMessengerID";
			$result = $connection2->prepare($sql);
			$result->execute($data);
		} catch (PDOException $e) {
			echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
		}

		if ($result->rowCount() < 1) {
			echo "<div class='alert alert-danger'>";
			echo __('The specified record cannot be found.');
			echo '</div>';
		}
		else {
			$row = $result->fetch();

			if ($row['emailReceiptText'] != '') {
				echo '<p>';
				echo "<b>".__('Receipt Confirmation Text') . "</b>: ".$row['emailReceiptText'];
				echo '</p>';
			}
			?>

			<script type='text/javascript'>
				$(function() {
					$( "#tabs" ).tabs({
						create: function( event, ui ) {
							action1.enable();
							action2.disable();
						},
						activate: function( event, ui ) {
							if (ui.newPanel.attr('id') == 'tabs1') {
								action1.enable();
								action2.disable();
							}
							else if (ui.newPanel.attr('id') == 'tabs2') {
								action1.disable();
								action2.enable();
							}
						},
						ajaxOptions: {
							error: function( xhr, status, index, anchor ) {
								$( anchor.hash ).html(
									"Couldn't load this tab." );
							}
						}
					});
				});
			</script>
			<?php

			if (isset($_GET['return'])) {
				returnProcess($guid, $_GET['return'], null, array('error2' => 'Some elements of your request failed, but others were successful.'));
			}

			// Create a reusable confirmation closure
			$icon = '<img src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/%1$s"/>';
			$confirmationIndicator = function($recipient) use ($icon) {
				if (is_null($recipient['key'])) return __('N/A');
				return sprintf($icon, $recipient['confirmed'] == 'Y'? 'iconTick.png' : 'iconCross.png');
			};

			$sender = false;
			if ($row['pupilsightPersonID'] == $_SESSION[$guid]['pupilsightPersonID'] || $highestAction == 'Manage Messages_all') {
				$sender = true;
			}

			echo "<div id='tabs' style='margin: 20px 0'>";
				//Tab links
				echo '<ul>';
				echo "<li><a href='#tabs1'>".__('By Roll Group').'</a></li>';
				echo "<li><a href='#tabs2'>".__('By Recipient').'</a></li>';
				echo '</ul>';

				//Tab content
				echo "<div id='tabs1'>";
					try {
						$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'today' => date('Y-m-d'));
						$sql = "SELECT pupilsightRollGroup.nameShort AS rollGroup, pupilsightPerson.pupilsightPersonID, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightFamilyChild.pupilsightFamilyID, parent1.email AS parent1email, parent1.surname AS parent1surname, parent1.preferredName AS parent1preferredName, parent1.pupilsightPersonID AS parent1pupilsightPersonID, parent2.email AS parent2email, parent2.surname AS parent2surname, parent2.preferredName AS parent2preferredName, parent2.pupilsightPersonID AS parent2pupilsightPersonID
							FROM pupilsightPerson
							JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
							JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
							LEFT JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
							LEFT JOIN pupilsightFamilyAdult AS parent1Fam ON (parent1Fam.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID AND parent1Fam.contactPriority=1)
							LEFT JOIN pupilsightPerson AS parent1 ON (parent1Fam.pupilsightPersonID=parent1.pupilsightPersonID AND parent1.status='Full' AND NOT parent1.surname IS NULL)
							LEFT JOIN pupilsightFamilyAdult AS parent2Fam ON (parent2Fam.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID AND parent2Fam.contactPriority=2 AND parent2Fam.contactEmail='Y')
							LEFT JOIN pupilsightPerson AS parent2 ON (parent2Fam.pupilsightPersonID=parent2.pupilsightPersonID AND parent2.status='Full' AND NOT parent2.surname IS NULL)
							WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
							AND pupilsightPerson.status='Full'
							AND (pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<=:today) AND (pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd>=:today)
							GROUP BY pupilsightPerson.pupilsightPersonID
							ORDER BY rollGroup, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightFamilyChild.pupilsightFamilyID";
						$result = $connection2->prepare($sql);
						$result->execute($data);
					} catch (PDOException $e) {
						echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
					}

					if ($result->rowCount() < 1) {
						echo "<div class='alert alert-danger'>";
						echo __('There are no records to display.');
						echo '</div>';
					} else {
						//Store receipt for this message data in an array
						try {
							$dataReceipts = array('pupilsightMessengerID' => $pupilsightMessengerID);
							$sqlReceipts = "SELECT pupilsightPersonID, pupilsightMessengerReceiptID, confirmed, `key` FROM pupilsightMessengerReceipt WHERE pupilsightMessengerID=:pupilsightMessengerID";
							$resultReceipts = $connection2->prepare($sqlReceipts);
							$resultReceipts->execute($dataReceipts);
						} catch (PDOException $e) {}
						$receipts = $resultReceipts->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

						$form = BulkActionForm::create('resendByRecipient', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/messenger_manage_report_processBulk.php?pupilsightMessengerID='.$pupilsightMessengerID.'&search='.$search);
						$form->addHiddenValue('address', $_SESSION[$guid]['address']);

						$row = $form->addBulkActionRow(array('resend' => __('Resend')));
							$row->addSubmit(__('Go'));

						$rollGroups = $result->fetchAll(\PDO::FETCH_GROUP);
						$countTotal = 0;

						foreach ($rollGroups as $rollGroupName => $recipients) {
							$count = 0;

							// Filter the array for only those individuals involved in the message (student or parent)
							$recipients = array_filter($recipients, function($recipient) use (&$receipts) {
								return array_key_exists($recipient['pupilsightPersonID'], $receipts)
									|| array_key_exists($recipient['parent1pupilsightPersonID'], $receipts)
									|| array_key_exists($recipient['parent2pupilsightPersonID'], $receipts);
							});

							// Skip this roll group if there's no involved individuals
							if (empty($recipients)) continue;

							$form->addRow()->addHeading($rollGroupName);
							$table = $form->addRow()->addTable()->setClass('colorOddEven fullWidth');

							$header = $table->addHeaderRow();
								$header->addContent(__('Total Count'));
								$header->addContent(__('Form Count'));
								$header->addContent(__('Student'))->addClass('mediumWidth');
								$header->addContent(__('Parent 1'))->addClass('mediumWidth');
								$header->addContent(__('Parent 2'))->addClass('mediumWidth');

							foreach ($recipients as $recipient) {
								$countTotal++;
								$count++;

								$studentName = formatName('', $recipient['preferredName'], $recipient['surname'], 'Student', true);
								$parent1Name = formatName('', $recipient['parent1preferredName'], $recipient['parent1surname'], 'Parent', true);
								$parent2Name = formatName('', $recipient['parent2preferredName'], $recipient['parent2surname'], 'Parent', true);

								//Tests for row completion, to set colour
								$studentReceived = isset($receipts[$recipient['pupilsightPersonID']]);
								if ($studentReceived) {
									$studentComplete = ($receipts[$recipient['pupilsightPersonID']]['confirmed'] == "Y");
								}
								else {
									$studentComplete = true;
								}
								$parentReceived = (isset($receipts[$recipient['parent1pupilsightPersonID']]) || isset($receipts[$recipient['parent2pupilsightPersonID']]));
								if ($parentReceived) {
									$parentComplete = ((isset($receipts[$recipient['parent1pupilsightPersonID']]) && $receipts[$recipient['parent1pupilsightPersonID']]['confirmed'] == "Y") || (isset($receipts[$recipient['parent2pupilsightPersonID']]) && $receipts[$recipient['parent2pupilsightPersonID']]['confirmed'] == "Y"));
								}
								else {
									$parentComplete = true;
								}
								$class = 'error';
								if ($studentComplete && $parentComplete) {
									$class = 'current';
								}

								$row = $table->addRow()->setClass($class);
									$row->addContent($countTotal);
									$row->addContent($count);

									$studentReceipt = isset($receipts[$recipient['pupilsightPersonID']])? $receipts[$recipient['pupilsightPersonID']] : null;
									$col = $row->addColumn();
										$col->addContent(!empty($studentName)? $studentName : __('N/A'));
										$col->addContent($confirmationIndicator($studentReceipt));
										$col->onlyIf($sender == true && !empty($studentReceipt) && $studentReceipt['confirmed'] == 'N')
											->addCheckbox('pupilsightMessengerReceiptIDs[]')
											->setValue($studentReceipt['pupilsightMessengerReceiptID'])
											->setClass('');

									$parent1Receipt = isset($receipts[$recipient['parent1pupilsightPersonID']])? $receipts[$recipient['parent1pupilsightPersonID']] : null;
									$col = $row->addColumn();
										$col->addContent(!empty($recipient['parent1surname'])? $parent1Name : __('N/A'));
										$col->addContent($confirmationIndicator($parent1Receipt));
										$col->onlyIf($sender == true && !empty($parent1Receipt) && $parent1Receipt['confirmed'] == 'N')
											->addCheckbox('pupilsightMessengerReceiptIDs[]')
											->setValue($parent1Receipt['pupilsightMessengerReceiptID'])
											->setClass('');

									$parent2Receipt = isset($receipts[$recipient['parent2pupilsightPersonID']])? $receipts[$recipient['parent2pupilsightPersonID']] : null;
									$col = $row->addColumn();
										$col->addContent(!empty($recipient['parent2surname'])? $parent2Name : __('N/A'));
										$col->addContent($confirmationIndicator($parent2Receipt));
										$col->onlyIf($sender == true && !empty($parent2Receipt) && $parent2Receipt['confirmed'] == 'N')
											->addCheckbox('pupilsightMessengerReceiptIDs[]')
											->setValue($parent2Receipt['pupilsightMessengerReceiptID'])
											->setClass('');
							}
						}

						if ($countTotal == 0) {
							$table = $form->addRow()->addTable()->setClass('colorOddEven fullWidth');
							$table->addRow()->addTableCell(__('There are no records to display.'))->colSpan(8);
						}

						echo $form->getOutput();
					}
				echo "</div>";
				echo "<div id='tabs2'>";
					if (!is_null($pupilsightMessengerID)) {
						try {
							$data = array('pupilsightMessengerID' => $pupilsightMessengerID);
							$sql = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightMessenger.*, pupilsightMessengerReceipt.*, pupilsightRole.category as roleCategory
								FROM pupilsightMessengerReceipt
								JOIN pupilsightMessenger ON (pupilsightMessengerReceipt.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
								LEFT JOIN pupilsightPerson ON (pupilsightMessengerReceipt.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
								LEFT JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary)
								WHERE pupilsightMessengerReceipt.pupilsightMessengerID=:pupilsightMessengerID ORDER BY FIELD(confirmed, 'Y','N',NULL), confirmedTimestamp, surname, preferredName, contactType";
							$result = $connection2->prepare($sql);
							$result->execute($data);
						} catch (PDOException $e) {
							echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
						}

						$form = BulkActionForm::create('resendByRecipient', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/messenger_manage_report_processBulk.php?pupilsightMessengerID='.$pupilsightMessengerID.'&search='.$search);

						$form->addHiddenValue('address', $_SESSION[$guid]['address']);

						$row = $form->addBulkActionRow(array('resend' => __('Resend')));
							$row->addSubmit(__('Go'));

						$table = $form->addRow()->addTable()->setClass('colorOddEven fullWidth');

						$header = $table->addHeaderRow();
							$header->addContent();
							$header->addContent(__('Recipient'));
							$header->addContent(__('Role'));
							$header->addContent(__('Contact Type'));
							$header->addContent(__('Contact Detail'));
							$header->addContent(__('Receipt Confirmed'));
							$header->addContent(__('Timestamp'));
							if ($sender == true) {
								$header->addCheckAll();
							}


						$recipients = $result->fetchAll();
						$recipientIDs = array_column($recipients, 'pupilsightPersonID');

						foreach ($recipients as $count => $recipient) {
							$row = $table->addRow();
								$row->addContent($count+1);
								$row->addContent(($recipient['preferredName'] != '' && $recipient['surname'] != '') ? formatName('', $recipient['preferredName'], $recipient['surname'], 'Student', true) : __('N/A'));
								$row->addContent($recipient['roleCategory']);
								$row->addContent($recipient['contactType']);
								$row->addContent($recipient['contactDetail']);
								$row->addContent($confirmationIndicator($recipient));
								$row->addContent(dateConvertBack($guid, substr($recipient['confirmedTimestamp'],0,10)).' '.substr($recipient['confirmedTimestamp'],11,5));

								if ($sender == true) {
									$row->onlyIf($recipient['confirmed'] == 'N')
										->addCheckbox('pupilsightMessengerReceiptIDs[]')
										->setValue($recipient['pupilsightMessengerReceiptID'])
										->setClass('textCenter');

									$row->onlyIf($recipient['confirmed'] != 'N')->addContent();
								}

							if (is_null($recipient['key'])) $nonConfirm++;
							else if ($recipient['confirmed'] == 'Y') $yesConfirm++;
							else if ($recipient['confirmed'] == 'N') $noConfirm++;
						}

						if (count($recipients) == 0) {
							$table->addRow()->addTableCell(__('There are no records to display.'))->colSpan(8);
						} else {
							$sendReport = '<b>'.__('Total Messages:')." ".count($recipients)."</b><br/>";
							$sendReport .= "<span>".__('Messages not eligible for confirmation of receipt:')." <b>$nonConfirm</b><br/>";
							$sendReport .= "<span>".__('Messages confirmed:').' <b>'.$yesConfirm.'</b><br/>';
							$sendReport .= "<span>".__('Messages not yet confirmed:').' <b>'.$noConfirm.'</b><br/>';

							$form->addRow()->addClass('right')->addAlert($sendReport, 'success');
						}

						echo $form->getOutput();
					}
				echo "</div>";
			}
		echo "</div>";
	}
}
