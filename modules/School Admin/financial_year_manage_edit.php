<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/financial_year_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Financial Year'), 'financial_year_manage.php')
        ->add(__('Edit Financial Year'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightSchoolFinanceYearID = $_GET['pupilsightSchoolFinanceYearID'];
    if ($pupilsightSchoolFinanceYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID);
            $sql = 'SELECT * FROM pupilsightSchoolFinanceYear WHERE pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID';
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

            $form = Form::create('schoolYear', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/financial_year_manage_editProcess.php?pupilsightSchoolFinanceYearID='.$pupilsightSchoolFinanceYearID);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $statuses = array(
                'Past'     => __('Past'),
                'Current'  => __('Current'),
                'Upcoming' => __('Upcoming'),
            );

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->required()->maxLength(9)->setValue($values['name']);

            if ($values['status'] == 'Current') {
                $form->addHiddenValue('status', $values['status']);
                $row = $form->addRow();
                    $row->addLabel('status', __('Status'));
                    $row->addTextField('status')->readOnly()->setValue($values['status']);
            } else {
                $row = $form->addRow();
                    $row->addLabel('status', __('Status'));
                    $row->addSelect('status')->fromArray($statuses)->required()->selected($values['status']);

                    $form->toggleVisibilityByClass('statusChange')->onSelect('status')->when('Current');
                    $direction = ($values['sequenceNumber'] < $_SESSION[$guid]['pupilsightSchoolFinanceYearSequenceNumberCurrent'])? __('Upcoming') : __('Past');

                    // Display an alert to warn users that changing this will have an impact on their system.
                    $row = $form->addRow()->setClass('statusChange');
                    // $row->addAlert(sprintf(__('Setting the status of this school year to Current will change the current school year %1$s to %2$s. Adjustments to the Academic Year can affect the visibility of vital data in your system. It\'s recommended to use the Rollover tool in User Admin to advance school years rather than changing them here. PROCEED WITH CAUTION!'), $_SESSION[$guid]['pupilsightSchoolFinanceYearNameCurrent'], $direction) );
            }

            $row = $form->addRow();
                $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
                $row->addSequenceNumber('sequenceNumber', 'pupilsightSchoolFinanceYear', $values['sequenceNumber'])->required()->maxLength(3)->setValue($values['sequenceNumber']);

            $row = $form->addRow();
                $row->addLabel('firstDay', __('Start Day'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
                $row->addDate('firstDay')->required()->setValue(dateConvertBack($guid, $values['firstDay']));

            $row = $form->addRow();
                $row->addLabel('lastDay', __('End Day'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
                $row->addDate('lastDay')->required()->setValue(dateConvertBack($guid, $values['lastDay']));

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit()->addClass('submit_align submt');

            echo $form->getOutput();
        }
    }
}
