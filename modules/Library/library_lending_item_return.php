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
    ->add(__('Return Item'));

if (isActionAccessible($guid, $connection2, '/modules/Library/library_lending_item_return.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    if (empty($pupilsightLibraryItemEventID) or empty($pupilsightLibraryItemID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID, 'pupilsightLibraryItemEventID' => $pupilsightLibraryItemEventID);
            $sql = 'SELECT pupilsightLibraryItemEvent.*, pupilsightLibraryItem.name AS name, pupilsightLibraryItem.id FROM pupilsightLibraryItem JOIN pupilsightLibraryItemEvent ON (pupilsightLibraryItem.pupilsightLibraryItemID=pupilsightLibraryItemEvent.pupilsightLibraryItemID) WHERE pupilsightLibraryItemEvent.pupilsightLibraryItemID=:pupilsightLibraryItemID AND pupilsightLibraryItemEvent.pupilsightLibraryItemEventID=:pupilsightLibraryItemEventID';
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
                returnProcess($guid, $_GET['return'], null, null);
            }

            if ($_GET['name'] != '' or $_GET['pupilsightLibraryTypeID'] != '' or $_GET['pupilsightSpaceID'] != '' or $_GET['status'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Library/library_lending_item.php&name='.$_GET['name']."&pupilsightLibraryItemEventID=$pupilsightLibraryItemEventID&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryTypeID=".$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status']."'>".__('Back').'</a>';
                echo '</div>';
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/library_lending_item_returnProcess.php?pupilsightLibraryItemEventID=$pupilsightLibraryItemEventID&pupilsightLibraryItemID=$pupilsightLibraryItemID&name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status']);
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

            $row = $form->addRow()->addHeading(__('On Return'));
                $row->append(__('The new status will be set to "Returned" unless the fields below are completed:'));

            $actions = array(
                'Reserve' => __('Reserve'),
                'Decommission' => __('Decommission'),
                'Repair' => __('Repair')
            );

            $row = $form->addRow();
                $row->addLabel('returnAction', __('Action'));
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
                $people['--'.__('Students By Roll Group').'--'] = array_reduce($result->fetchAll(), function ($group, $item) {
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

            $row = $form->addRow();
                $row->addLabel('pupilsightPersonIDReturnAction', __('Responsible User'))->description(__('Who will be responsible for the future status?'));
                $row->addSelect('pupilsightPersonIDReturnAction')->fromArray($people)->placeholder()->selected($values['pupilsightPersonIDReturnAction']);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
