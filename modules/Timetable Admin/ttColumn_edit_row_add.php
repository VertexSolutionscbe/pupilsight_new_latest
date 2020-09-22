<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttColumn_edit_row_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightTTColumnID = $_GET['pupilsightTTColumnID'] ?? '';

    if ($pupilsightTTColumnID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightTTColumnID' => $pupilsightTTColumnID);
            $sql = 'SELECT name AS columnName FROM pupilsightTTColumn WHERE pupilsightTTColumnID=:pupilsightTTColumnID';
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
            $values = $result->fetch();

            $page->breadcrumbs
                ->add(__('Manage Columns'), 'ttColumn.php')
                ->add(__('Edit Column'), 'ttColumn_edit.php', ['pupilsightTTColumnID' => $pupilsightTTColumnID])
                ->add(__('Add Column Row'));

            $editLink = '';
            if (isset($_GET['editID'])) {
                $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable Admin/ttColumn_edit_row_edit.php&pupilsightTTColumnRowID='.$_GET['editID'].'&pupilsightTTColumnID='.$_GET['pupilsightTTColumnID'];
            }
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], $editLink, null);
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/ttColumn_edit_row_addProcess.php');

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightTTColumnID', $pupilsightTTColumnID);

            $row = $form->addRow();
                $row->addLabel('columnName', __('Column'));
                $row->addTextField('columnName')->maxLength(30)->required()->readonly()->setValue($values['columnName']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique for this school year.'));
                $row->addTextField('name')->maxLength(12)->required();

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique for this school year.'));
                $row->addTextField('nameShort')->maxLength(4)->required();

            $row = $form->addRow();
                $row->addLabel('timeStart', __('Start Time'));
                $row->addTime('timeStart')->required();

            $row = $form->addRow();
                $row->addLabel('timeEnd', __('End Time'));
                $row->addTime('timeEnd')->required()->chainedTo('timeStart');

            $types = array(
                'Lesson' => __('Lesson'),
                'Pastoral' => __('Pastoral'),
                'Sport' => __('Sport'),
                'Break' => __('Break'),
                'Service' => __('Service'),
                'Other' => __('Other'));
            $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addSelect('type')->fromArray($types)->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
