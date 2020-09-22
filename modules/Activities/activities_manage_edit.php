<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Activities'), 'activities_manage.php')
        ->add(__('Edit Activity'));
    
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('error3' => 'Your request failed due to an attachment error.'));
    }

    //Check if school year specified
    $pupilsightActivityID = $_GET['pupilsightActivityID'];
    if ($pupilsightActivityID == 'Y') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT * FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
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
            //Let's go!
			$values = $result->fetch();

			$search = isset($_GET['search'])? $_GET['search'] : '';
			$pupilsightSchoolYearTermID = isset($_GET['pupilsightSchoolYearTermID'])? $_GET['pupilsightSchoolYearTermID'] : '';

            if ($search != '' || $pupilsightSchoolYearTermID != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Activities/activities_manage.php&search='.$search."&pupilsightSchoolYearTermID=".$pupilsightSchoolYearTermID."'>".__('Back to Search Results').'</a>';
                echo '</div>';
			}

			$form = Form::create('activity', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/activities_manage_editProcess.php?pupilsightActivityID='.$pupilsightActivityID.'&search='.$search.'&pupilsightSchoolYearTermID='.$pupilsightSchoolYearTermID);
			$form->setFactory(DatabaseFormFactory::create($pdo));

			$form->addHiddenValue('address', $_SESSION[$guid]['address']);

			$form->addRow()->addHeading(__('Basic Information'));

			$row = $form->addRow();
				$row->addLabel('name', __('Name'));
				$row->addTextField('name')->required()->maxLength(40);

			$row = $form->addRow();
				$row->addLabel('provider', __('Provider'));
				$row->addSelect('provider')->required()->fromArray(array('School' => $_SESSION[$guid]['organisationNameShort'], 'External' => __('External')));

			$activityTypes = getSettingByScope($connection2, 'Activities', 'activityTypes');
			if (!empty($activityTypes)) {
				$row = $form->addRow();
					$row->addLabel('type', __('Type'));
					$row->addSelect('type')->fromString($activityTypes)->placeholder();
			}

			$row = $form->addRow();
				$row->addLabel('active', __('Active'));
				$row->addYesNo('active')->required();

			$row = $form->addRow();
				$row->addLabel('registration', __('Registration'))->description(__('Assuming system-wide registration is open, should this activity be open for registration?'));
				$row->addYesNo('registration')->required();

			$dateType = getSettingByScope($connection2, 'Activities', 'dateType');
			$form->addHiddenValue('dateType', $dateType);
			if ($dateType != 'Date') {
				$row = $form->addRow();
					$row->addLabel('pupilsightSchoolYearTermIDList', __('Terms'))->description(__('Terms in which the activity will run.'));
					$row->addCheckboxSchoolYearTerm('pupilsightSchoolYearTermIDList', $_SESSION[$guid]['pupilsightSchoolYearID'])->loadFromCSV($values);
			} else {
				$row = $form->addRow();
					$row->addLabel('listingStart', __('Listing Start Date'))->description(__('Default: 2 weeks before the end of the current term.'));
					$row->addDate('listingStart')->required()->setValue(dateConvertBack($guid, $values['listingStart']));

				$row = $form->addRow();
					$row->addLabel('listingEnd', __('Listing End Date'))->description(__('Default: 2 weeks after the start of next term.'));
					$row->addDate('listingEnd')->required()->setValue(dateConvertBack($guid, $values['listingEnd']));

				$row = $form->addRow();
					$row->addLabel('programStart', __('Program Start Date'))->description(__('Default: first day of next term.'));
					$row->addDate('programStart')->required()->setValue(dateConvertBack($guid, $values['programStart']));

				$row = $form->addRow();
					$row->addLabel('programEnd', __('Program End Date'))->description(__('Default: last day of the next term.'));
					$row->addDate('programEnd')->required()->setValue(dateConvertBack($guid, $values['programEnd']));
			}

			$row = $form->addRow();
				$row->addLabel('pupilsightYearGroupIDList', __('Year Groups'));
				$row->addCheckboxYearGroup('pupilsightYearGroupIDList')->addCheckAllNone()->loadFromCSV($values);

			$row = $form->addRow();
				$row->addLabel('maxParticipants', __('Max Participants'));
				$row->addNumber('maxParticipants')->required()->maxLength(4);

			$column = $form->addRow()->addColumn();
				$column->addLabel('description', __('Description'));
				$column->addEditor('description', $guid)->setRows(10)->showMedia();

			$payment = getSettingByScope($connection2, 'Activities', 'payment');
			if ($payment != 'None' && $payment != 'Single') {
				$form->addRow()->addHeading(__('Cost'));

				$row = $form->addRow();
					$row->addLabel('payment', __('Cost'));
					$row->addCurrency('payment')->required()->maxLength(9);

				$costTypes = array(
					'Entire Programme' => __('Entire Programme'),
					'Per Session'      => __('Per Session'),
					'Per Week'         => __('Per Week'),
					'Per Term'         => __('Per Term'),
				);

				$row = $form->addRow();
					$row->addLabel('paymentType', __('Cost Type'));
					$row->addSelect('paymentType')->required()->fromArray($costTypes);

				$costStatuses = array(
					'Finalised' => __('Finalised'),
					'Estimated' => __('Estimated'),
				);

				$row = $form->addRow();
					$row->addLabel('paymentFirmness', __('Cost Status'));
					$row->addSelect('paymentFirmness')->required()->fromArray($costStatuses);
			}

			$form->addRow()->addHeading(__('Current Time Slots'));

            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = "SELECT pupilsightActivitySlot.*, pupilsightDaysOfWeek.name, pupilsightSpace.name as locationInternal FROM pupilsightActivitySlot
					JOIN pupilsightDaysOfWeek ON (pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID)
					LEFT JOIN pupilsightSpace ON (pupilsightSpace.pupilsightSpaceID=pupilsightActivitySlot.pupilsightSpaceID)
					WHERE pupilsightActivityID=:pupilsightActivityID ORDER BY pupilsightDaysOfWeek.pupilsightDaysOfWeekID";

            $results = $pdo->executeQuery($data, $sql);

            if ($results->rowCount() == 0) {
                $form->addRow()->addAlert(__('There are no records to display.'), 'error');
            } else {
                $form->addRow()->addContent('<b>'.__('Warning').'</b>: '.__('If you delete a time slot, any unsaved changes to this record will be lost!'))->wrap('<i>', '</i>');

                $table = $form->addRow()->addTable()->addClass('colorOddEven');

                $header = $table->addHeaderRow();
                $header->addContent(__('Day'));
                $header->addContent(__('Time'));
                $header->addContent(__('Location'));
                $header->addContent(__('Action'));

                while ($slot = $results->fetch()) {
                    $row = $table->addRow();

                    $row->addContent(__($slot['name']));
                    $row->addContent(substr($slot['timeStart'], 0, 5).' - '.substr($slot['timeEnd'], 0, 5));
                    $row->addContent(!empty($slot['locationInternal'])? $slot['locationInternal'] : $slot['locationExternal']);
                    $row->addWebLink('<img title="'.__('Delete').'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/garbage.png"/></a>')
                            ->setURL($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/activities_manage_edit_slot_deleteProcess.php')
                            ->addParam('address', $_GET['q'])
                            ->addParam('pupilsightActivitySlotID', $slot['pupilsightActivitySlotID'])
                            ->addParam('pupilsightActivityID', $pupilsightActivityID)
                            ->addParam('search', $search)
                            ->addParam('pupilsightSchoolYearTermID', $pupilsightSchoolYearTermID)
                            ->addConfirmation(__('Are you sure you wish to delete this record?'));
                }
            }

            $form->addRow()->addHeading(__('New Time Slots'));

            $sqlWeekdays = "SELECT pupilsightDaysOfWeekID as value, name FROM pupilsightDaysOfWeek ORDER BY sequenceNumber";
            $sqlSpaces = "SELECT pupilsightSpaceID as value, name FROM pupilsightSpace ORDER BY name";
            $locations = array(
                    'Internal' => __('Internal'),
                    'External' => __('External'),
            );

            for ($i = 1; $i <= 2; ++$i) {
				$form->addRow()->addSubheading(__('Slot').' '.$i)->addClass("slotRow{$i}");
           
                $row = $form->addRow()->addClass("slotRow{$i}");
					$row->addLabel("pupilsightDaysOfWeekID{$i}", sprintf(__('Slot %1$d Day'), $i));
                        $row->addSelect("pupilsightDaysOfWeekID{$i}")->fromQuery($pdo, $sqlWeekdays)->placeholder();

                $row = $form->addRow()->addClass("slotRow{$i}");
					$row->addLabel('timeStart'.$i, sprintf(__('Slot %1$d Start Time'), $i));
					$row->addTime('timeStart'.$i);

                $row = $form->addRow()->addClass("slotRow{$i}");
                        $row->addLabel("timeEnd{$i}", sprintf(__('Slot %1$d End Time'), $i));
					$row->addTime("timeEnd{$i}")->chainedTo('timeStart'.$i);

                $row = $form->addRow()->addClass("slotRow{$i}");
					$row->addLabel("slot{$i}Location", sprintf(__('Slot %1$d Location'), $i));
                        $row->addRadio("slot{$i}Location")->fromArray($locations)->inline();

                $form->toggleVisibilityByClass("slotRow{$i}Internal")->onRadio("slot{$i}Location")->when('Internal');
                $row = $form->addRow()->addClass("slotRow{$i}Internal");
                        $row->addSelect("pupilsightSpaceID{$i}")->fromQuery($pdo, $sqlSpaces)->placeholder();

                $form->toggleVisibilityByClass("slotRow{$i}External")->onRadio("slot{$i}Location")->when('External');
                $row = $form->addRow()->addClass("slotRow{$i}External");
                        $row->addTextField("location{$i}External")->maxLength(50);

                if ($i == 1) {
                        $form->toggleVisibilityByClass("slot{$i}ButtonRow")->onRadio("slot{$i}Location")->when(array('Internal', 'External'));
                        $row = $form->addRow()->addClass("slotRow{$i} slot{$i}ButtonRow");
                        $row->addButton(__('Add Another Slot'))
                                ->onClick("$('.slotRow2').show();$('.slot1ButtonRow').hide();")
                                ->addClass('right buttonAsLink');
                }
            }

            $form->addRow()->addHeading(__('Current Staff'));

            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = "SELECT preferredName, surname, pupilsightActivityStaff.* FROM pupilsightActivityStaff JOIN pupilsightPerson ON (pupilsightActivityStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPerson.status='Full' ORDER BY surname, preferredName";

            $results = $pdo->executeQuery($data, $sql);

            if ($results->rowCount() == 0) {
                $form->addRow()->addAlert(__('There are no records to display.'), 'error');
            } else {
                $form->addRow()->addContent('<b>'.__('Warning').'</b>: '.__('If you delete a member of staff, any unsaved changes to this record will be lost!'))->wrap('<i>', '</i>');

                $table = $form->addRow()->addTable()->addClass('colorOddEven');

                $header = $table->addHeaderRow();
                $header->addContent(__('Name'));
                $header->addContent(__('Role'));
                $header->addContent(__('Action'));

                while ($staff = $results->fetch()) {
                    $row = $table->addRow();
                        $row->addContent(formatName('', $staff['preferredName'], $staff['surname'], 'Staff', true, true));
			$row->addContent(__($staff['role']));
			$row->addWebLink('<img title="'.__('Delete').'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/garbage.png"/></a>')
			    ->setURL($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/activities_manage_edit_staff_deleteProcess.php')
			    ->addParam('address', $_GET['q'])
			    ->addParam('pupilsightActivityStaffID', $staff['pupilsightActivityStaffID'])
			    ->addParam('pupilsightActivityID', $pupilsightActivityID)
			    ->addParam('search', $search)
			    ->addParam('pupilsightSchoolYearTermID', $pupilsightSchoolYearTermID)
			    ->addConfirmation(__('Are you sure you wish to delete this record?'));
                }
            }

            $form->addRow()->addHeading(__('New Staff'));

			$row = $form->addRow();
				$row->addLabel('staff', __('Staff'));
				$row->addSelectUsers('staff', $_SESSION[$guid]['pupilsightSchoolYearID'], array('includeStaff' => true))->selectMultiple();
			
			$staffRoles = array(
				'Organiser' => __('Organiser'),
				'Coach'     => __('Coach'),
				'Assistant' => __('Assistant'),
				'Other'     => __('Other'),
			);

			$row = $form->addRow();
				$row->addLabel('role', __('Role'));
				$row->addSelect('role')->fromArray($staffRoles);

			$row = $form->addRow();
				$row->addFooter();
				$row->addSubmit();

			$form->loadAllValuesFrom($values);

			echo $form->getOutput();
			?>

			<script type="text/javascript">
			$(document).ready(function(){
				$('.slotRow2').hide();
			});
			</script>

			<?php
        }
    }
}
