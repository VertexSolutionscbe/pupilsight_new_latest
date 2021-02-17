<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$pupilsightLibraryItemEventID = trim($_GET['pupilsightLibraryItemEventID']) ?? '';
$pupilsightLibraryItemID = trim($_GET['pupilsightLibraryItemID']) ?? '';

$page->breadcrumbs
    ->add(__('Lending & Activity Log'), 'library_lending.php')
    ->add(__('View Item'), 'library_lending_item.php', ['pupilsightLibraryItemID' => $pupilsightLibraryItemID])
    ->add(__('Edit Item'));

if (isActionAccessible($guid, $connection2, '/modules/Library/library_lending_item_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // check if school year specified
    if (empty($pupilsightLibraryItemEventID) or empty($pupilsightLibraryItemID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID, 'pupilsightLibraryItemEventID' => $pupilsightLibraryItemEventID);
            $sql = 'SELECT pupilsightLibraryItemEvent.*, pupilsightLibraryItem.name AS name, pupilsightLibraryItem.id, pupilsightPersonID, surname, preferredName
                FROM pupilsightLibraryItem
                    JOIN pupilsightLibraryItemEvent ON (pupilsightLibraryItem.pupilsightLibraryItemID=pupilsightLibraryItemEvent.pupilsightLibraryItemID)
                    JOIN pupilsightPerson ON (pupilsightLibraryItemEvent.pupilsightPersonIDStatusResponsible=pupilsightPerson.pupilsightPersonID)
                WHERE pupilsightLibraryItemEvent.pupilsightLibraryItemID=:pupilsightLibraryItemID
                    AND pupilsightLibraryItemEvent.pupilsightLibraryItemEventID=:pupilsightLibraryItemEventID';
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
            //Let's go!
            $values = $result->fetch();

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
            }

            if ($_GET['name'] != '' or $_GET['pupilsightLibraryTypeID'] != '' or $_GET['pupilsightSpaceID'] != '' or $_GET['status'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Library/library_lending_item.php&name='.$_GET['name']."&pupilsightLibraryItemEventID=$pupilsightLibraryItemEventID&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryTypeID=".$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status']."'>".__('Back').'</a>';
                echo '</div>';
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/library_lending_item_editProcess.php?pupilsightLibraryItemEventID=$pupilsightLibraryItemEventID&pupilsightLibraryItemID=$pupilsightLibraryItemID&name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status']);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('Item Details'));

            $row = $form->addRow();
                $row->addLabel('id', __('ID'));
                $row->addTextField('id')->setValue($values['id'])->readonly()->required();

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->setValue($values['name'])->readonly()->required();

            $row = $form->addRow();
                $row->addLabel('statusCurrent', __('Current Status'));
                $row->addTextField('statusCurrent')->setValue($values['status'])->readonly()->required();

            $form->addRow()->addHeading(__('This Event'));

            $statuses = array(
                'On Loan' => __('On Loan'),
                'Reserved' => __('Reserved'),
                'Decommissioned' => __('Decommissioned'),
                'Lost' => __('Lost'),
                'Repair' => __('Repair')
            );
            $row = $form->addRow();
                $row->addLabel('status', __('New Status'));
                $row->addSelect('status')->fromArray($statuses)->required()->selected($values['status'])->placeholder();

            $form->addHiddenValue('pupilsightPersonIDStatusResponsible', $values['pupilsightPersonIDStatusResponsible']);
            $row = $form->addRow();
                $row->addLabel('pupilsightPersonIDStatusResponsiblename', __('Responsible User'));
                $row->addTextField('pupilsightPersonIDStatusResponsiblename')->setValue(formatName('', htmlPrep($values['preferredName']), htmlPrep($values['surname']), 'Student', true))->readonly()->required();

            $row = $form->addRow();
                $row->addLabel('returnExpected', __('Expected Return Date'));
                $row->addDate('returnExpected')->setValue(dateConvertBack($guid, $values['returnExpected']))->required();


            $row = $form->addRow()->addHeading(__('On Return'));

            $actions = array(
                'Reserve' => __('Reserve'),
                'Decommission' => __('Decommission'),
                'Repair' => __('Repair')
            );
            $row = $form->addRow();
                $row->addLabel('returnAction', __('Action'))->description(__('What to do when item is next returned.'));
                $row->addSelect('returnAction')->fromArray($actions)->selected($values['returnAction'])->placeholder();

            //USER SELECT
            $people = array();

            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => date('Y-m-d'));
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, username, pupilsightRollGroup.name AS rollGroupName
                FROM pupilsightPerson
                    JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                WHERE status='Full'
                    AND (dateStart IS NULL OR dateStart<=:date)
                    AND (dateEnd IS NULL  OR dateEnd>=:date)
                    AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
                ORDER BY name, surname, preferredName";
            $result = $pdo->executeQuery($data, $sql);

            if ($result->rowCount() > 0) {
                $people['--'.__('Enrolable Students').'--'] = array_reduce($result->fetchAll(), function ($group, $item) {
                    $group[$item['pupilsightPersonID']] = $item['rollGroupName'].' - '.formatName('', htmlPrep($item['preferredName']), htmlPrep($item['surname']), 'Student', true).' ('.$item['username'].')';
                    return $group;
                }, array());
            }

            $sql = "SELECT pupilsightPersonID, surname, preferredName, status, username FROM pupilsightPerson WHERE status='Full' OR status='Expected' ORDER BY surname, preferredName";
            $result = $pdo->executeQuery(array(), $sql);

            if ($result->rowCount() > 0) {
                $people['--'.__('All Users').'--'] = array_reduce($result->fetchAll(), function($group, $item) {
                    $expected = ($item['status'] == 'Expected')? '('.__('Expected').')' : '';
                    $group[$item['pupilsightPersonID']] = formatName('', htmlPrep($item['preferredName']), htmlPrep($item['surname']), 'Student', true).' ('.$item['username'].')'.$expected;
                    return $group;
                }, array());
            }

            
			$sql = 'SELECT a.type, b.pupilsightPersonID, b.officialName FROM pupilsightStaff AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE b.officialName != "" ';
			$result = $connection2->query($sql);
			$staffs = $result->fetchAll();
			$owner1 = array('' => 'Please Select ');
			
			foreach ($staffs as $dt) {
				$owner2[$dt['pupilsightPersonID']] = $dt['officialName'];
			}
			$owner = $owner1 + $owner2;
			

            $row = $form->addRow();
                $row->addLabel('pupilsightPersonIDReturnAction', __('Responsible User'))->description(__('Who will be responsible for the future status?'));
                //$row->addSelect('pupilsightPersonIDReturnAction')->fromArray($people)->placeholder();
                $row->addSelect('pupilsightPersonIDReturnAction')->fromArray($owner)->placeholder()->selected($values['pupilsightPersonIDReturnAction']);
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
