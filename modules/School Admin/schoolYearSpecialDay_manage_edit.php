<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearSpecialDay_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightSchoolYearSpecialDayID = $_GET['pupilsightSchoolYearSpecialDayID'] ?? '';
    $pupilsightSchoolYearTermID = $_GET['pupilsightSchoolYearTermID'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Special Days'), 'schoolYearSpecialDay_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Edit Special Day'));

    if (empty($pupilsightSchoolYearSpecialDayID) && empty($pupilsightSchoolYearID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearSpecialDayID' => $pupilsightSchoolYearSpecialDayID);
            $sql = 'SELECT * FROM pupilsightSchoolYearSpecialDay WHERE pupilsightSchoolYearSpecialDayID=:pupilsightSchoolYearSpecialDayID';
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

            $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/schoolYearSpecialDay_manage_editProcess.php?pupilsightSchoolYearSpecialDayID='.$pupilsightSchoolYearSpecialDayID.'&pupilsightSchoolYearID='.$pupilsightSchoolYearID);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
            $form->addHiddenValue('pupilsightSchoolYearTermID', $pupilsightSchoolYearTermID);

            $row = $form->addRow();
                $row->addLabel('dateDisplay', __('Date'))->description(__('Must be unique.'));
                $row->addTextField('dateDisplay')->readonly()->setValue(dateConvertBack($guid, $values['date']));

            $types = array(
                'School Closure' => __('School Closure'),
                'Timing Change' => __('Timing Change'),
            );

            $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addSelect('type')->fromArray($types)->required()->placeholder();

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->required()->maxLength(20);

            $row = $form->addRow();
                $row->addLabel('description', __('Description'));
                $row->addTextField('description')->maxLength(255);

            $form->toggleVisibilityByClass('timingChange')->onSelect('type')->when('Timing Change');

            $hoursArray = array_map(function($num) { return str_pad($num, 2, '0', STR_PAD_LEFT); }, range(0, 23));
            $hours = implode(',', $hoursArray);

            $minutesArray = array_map(function($num) { return str_pad($num, 2, '0', STR_PAD_LEFT); }, range(0, 59));
            $minutes = implode(',', $minutesArray);

            if (!empty($values['schoolOpen']) && stripos($values['schoolOpen'], ':') !== false) {
                list($values['schoolOpenH'], $values['schoolOpenM'], $sec) = explode(':', $values['schoolOpen']);
            }

            $row = $form->addRow()->addClass('timingChange');
                $row->addLabel('schoolOpen', __('School Opens'));
                $col = $row->addColumn()->addClass('right inline');
                $col->addSelect('schoolOpenH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
                $col->addSelect('schoolOpenM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

            if (!empty($values['schoolStart']) && stripos($values['schoolStart'], ':') !== false) {
                list($values['schoolStartH'], $values['schoolStartM'], $sec) = explode(':', $values['schoolStart']);
            }

            $row = $form->addRow()->addClass('timingChange');
                $row->addLabel('schoolStart', __('School Starts'));
                $col = $row->addColumn()->addClass('right inline');
                $col->addSelect('schoolStartH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
                $col->addSelect('schoolStartM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

            if (!empty($values['schoolEnd']) && stripos($values['schoolEnd'], ':') !== false) {
                list($values['schoolEndH'], $values['schoolEndM'], $sec) = explode(':', $values['schoolEnd']);
            }

            $row = $form->addRow()->addClass('timingChange');
                $row->addLabel('schoolEnd', __('School Ends'));
                $col = $row->addColumn()->addClass('right inline');
                $col->addSelect('schoolEndH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
                $col->addSelect('schoolEndM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

            if (!empty($values['schoolClose']) && stripos($values['schoolClose'], ':') !== false) {
                list($values['schoolCloseH'], $values['schoolCloseM'], $sec) = explode(':', $values['schoolClose']);
            }

            $row = $form->addRow()->addClass('timingChange');
                $row->addLabel('schoolClose', __('School Closes'));
                $col = $row->addColumn()->addClass('right inline');
                $col->addSelect('schoolCloseH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
                $col->addSelect('schoolCloseM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
