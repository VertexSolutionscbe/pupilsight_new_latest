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
    ->add(__('Renew Item'));


if (isActionAccessible($guid, $connection2, '/modules/Library/library_lending_item_renew.php') == false) {
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

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/library_lending_item_renewProcess.php?pupilsightLibraryItemEventID=$pupilsightLibraryItemEventID&pupilsightLibraryItemID=$pupilsightLibraryItemID&name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status']);
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

            $form->addHiddenValue('pupilsightPersonIDStatusResponsible', $values['pupilsightPersonIDStatusResponsible']);
            $row = $form->addRow();
                $row->addLabel('pupilsightPersonIDStatusResponsiblename', __('Responsible User'));
                $row->addTextField('pupilsightPersonIDStatusResponsiblename')->setValue(formatName('', htmlPrep($values['preferredName']), htmlPrep($values['surname']), 'Student', true))->readonly()->required();

            $loanLength = getSettingByScope($connection2, 'Library', 'defaultLoanLength');
            $loanLength = (is_numeric($loanLength) == false or $loanLength < 0) ? 7 : $loanLength ;
            $row = $form->addRow();
                $row->addLabel('returnExpected', __('Expected Return Date'))->description(sprintf(__('Default renew length is today plus %1$s day(s)'), $loanLength));
                $row->addDate('returnExpected')->setValue(date($_SESSION[$guid]['i18n']['dateFormatPHP'], time() + ($loanLength * 60 * 60 * 24)))->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
