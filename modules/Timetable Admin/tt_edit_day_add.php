<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightTTID = $_GET['pupilsightTTID'] ?? '';
    $pupilsightProgramID = $_GET['pupilsightProgramID'] ?? '';


    if ($pupilsightSchoolYearID == '' or $pupilsightTTID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightTTID' => $pupilsightTTID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT pupilsightTTID, pupilsightSchoolYear.name AS schoolYear, pupilsightTT.name AS ttName FROM pupilsightTT JOIN pupilsightSchoolYear ON (pupilsightTT.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightTTID=:pupilsightTTID AND pupilsightTT.pupilsightSchoolYearID=:pupilsightSchoolYearID';
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
                ->add(__('Manage Timetables'), 'tt.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
                ->add(__('Edit Timetable'), 'tt_edit.php', ['pupilsightTTID' => $pupilsightTTID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID])
                ->add(__('Add Timetable Day'));
        
            $editLink = '';
            if (isset($_GET['editID'])) {
                $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable Admin/tt_edit_day_edit.php&pupilsightTTDayID='.$_GET['editID'].'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'].'&pupilsightTTID='.$_GET['pupilsightTTID'];
            }
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], $editLink, null);
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/tt_edit_day_addProcess.php');

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightTTID', $pupilsightTTID);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

            $row = $form->addRow();
                $row->addLabel('schoolYear', __('School Year'));
                $row->addTextField('schoolYear')->maxLength(20)->required()->readonly()->setValue($values['schoolYear']);

            $row = $form->addRow();
                $row->addLabel('ttName', __('Timetable'));
                $row->addTextField('ttName')->maxLength(20)->required()->readonly()->setValue($values['ttName']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique for this school year.'));
                $row->addTextField('name')->maxLength(12)->required();

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique for this school year.'));
                $row->addTextField('nameShort')->maxLength(4)->required();

            $row = $form->addRow();
                $row->addLabel('color', __('Header Background Colour'))->description(__('RGB Hex value, without leading #.'));
                $row->addTextField('color')->maxLength(6);

            $row = $form->addRow();
                $row->addLabel('fontColor', __('Header Font Colour'))->description(__('RGB Hex value, without leading #.'));
                $row->addTextField('fontColor')->maxLength(6);

            $data = array();
            $sql = "SELECT pupilsightTTColumnID as value, name FROM pupilsightTTColumn ORDER BY name";
            $row = $form->addRow();
                $row->addLabel('pupilsightTTColumnID', __('Timetable Column'));
                $row->addSelect('pupilsightTTColumnID')->fromQuery($pdo, $sql, $data)->required()->placeholder();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
