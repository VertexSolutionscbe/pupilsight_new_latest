<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearSpecialDay_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $addtype = $_GET['addtype'];
    if($addtype == '1'){
    //Proceed!
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
        $dateStamp = $_GET['dateStamp'] ?? '';
        $pupilsightSchoolYearTermID = $_GET['pupilsightSchoolYearTermID'] ?? '';
        $firstDay = $_GET['firstDay'] ?? '';
        $lastDay = $_GET['lastDay'] ?? '';

        $page->breadcrumbs
            ->add(__('Manage Special Days'), 'schoolYearSpecialDay_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
            ->add(__('Add Special Day'));

        if ($pupilsightSchoolYearID == '' or $dateStamp == '' or $pupilsightSchoolYearTermID == '' or $firstDay == '' or $lastDay == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record does not exist.');
                echo '</div>';
            } elseif ($dateStamp < $firstDay or $dateStamp > $lastDay) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified date is outside of the allowed range.');
                echo '</div>';
            } else {

                $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/schoolYearSpecialDay_manage_addProcess.php');

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
                $form->addHiddenValue('pupilsightSchoolYearTermID', $pupilsightSchoolYearTermID);
                $form->addHiddenValue('dateStamp', $dateStamp);
                $form->addHiddenValue('firstDay', $firstDay);
                $form->addHiddenValue('lastDay', $lastDay);
                $form->addHiddenValue('f_date', 'single');

                $row = $form->addRow();
                    $row->addLabel('date', __('Date'))->description(__('Must be unique.'));
                    $row->addTextField('date')->readonly()->setValue(dateConvertBack($guid, date('Y-m-d', $dateStamp)));

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

                $row = $form->addRow()->addClass('timingChange');
                    $row->addLabel('schoolOpen', __('School Opens'));
                    $col = $row->addColumn()->addClass('right inline');
                    $col->addSelect('schoolOpenH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
                    $col->addSelect('schoolOpenM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

                $row = $form->addRow()->addClass('timingChange');
                    $row->addLabel('schoolStart', __('School Starts'));
                    $col = $row->addColumn()->addClass('right inline');
                    $col->addSelect('schoolStartH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
                    $col->addSelect('schoolStartM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

                $row = $form->addRow()->addClass('timingChange');
                    $row->addLabel('schoolEnd', __('School Ends'));
                    $col = $row->addColumn()->addClass('right inline');
                    $col->addSelect('schoolEndH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
                    $col->addSelect('schoolEndM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

                $row = $form->addRow()->addClass('timingChange');
                    $row->addLabel('schoolClose', __('School Closes'));
                    $col = $row->addColumn()->addClass('right inline');
                    $col->addSelect('schoolCloseH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
                    $col->addSelect('schoolCloseM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                echo $form->getOutput();
            }
        }
    } else {
        $sqlq = 'SELECT * FROM pupilsightSchoolYear ORDER BY sequenceNumber';
        $resultval = $connection2->query($sqlq);
        $rowdata = $resultval->fetchAll();
        $academic=array();
        $ayear = '';
        if(!empty($rowdata)){
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }
        //print_r($schoolyear);


        $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/schoolYearSpecialDay_manage_addProcess.php');

        // $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        // $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
        // $form->addHiddenValue('pupilsightSchoolYearTermID', $pupilsightSchoolYearTermID);
        // $form->addHiddenValue('dateStamp', $dateStamp);
        // $form->addHiddenValue('firstDay', $firstDay);
        // $form->addHiddenValue('lastDay', $lastDay);

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('dateStamp', '0');
        $form->addHiddenValue('firstDay', '0');
        $form->addHiddenValue('lastDay', '0');

        $row = $form->addRow();
            $row->addLabel('Academic Year', __('Academic Year'));
            $row->addSelect('pupilsightSchoolYearID')->setId('academic_year')->fromArray($academic)->required()->placeholder();

        $row = $form->addRow();
            $row->addLabel('Term', __('Term'));
            $row->addSelect('pupilsightSchoolYearTermID')->setId('schoolterm')->required()->placeholder();    

        // $row = $form->addRow();
        //     $row->addLabel('date', __('Date'))->description(__('Must be unique.'));
        //     $row->addTextField('date')->readonly()->setValue(dateConvertBack($guid, date('Y-m-d', $dateStamp)));
        
        $row = $form->addRow();
            $row->addLabel('startdate', __('Start Date'));
            $row->addDate('f_date')->required()->placeholder();    

        $row = $form->addRow();
            $row->addLabel('enddate', __('End Date'));
            $row->addDate('l_date')->required()->placeholder();    

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

        $row = $form->addRow()->addClass('timingChange');
            $row->addLabel('schoolOpen', __('School Opens'));
            $col = $row->addColumn()->addClass('right inline');
            $col->addSelect('schoolOpenH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
            $col->addSelect('schoolOpenM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

        $row = $form->addRow()->addClass('timingChange');
            $row->addLabel('schoolStart', __('School Starts'));
            $col = $row->addColumn()->addClass('right inline');
            $col->addSelect('schoolStartH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
            $col->addSelect('schoolStartM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

        $row = $form->addRow()->addClass('timingChange');
            $row->addLabel('schoolEnd', __('School Ends'));
            $col = $row->addColumn()->addClass('right inline');
            $col->addSelect('schoolEndH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
            $col->addSelect('schoolEndM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

        $row = $form->addRow()->addClass('timingChange');
            $row->addLabel('schoolClose', __('School Closes'));
            $col = $row->addColumn()->addClass('right inline');
            $col->addSelect('schoolCloseH')->fromString($hours)->setClass('shortWidth')->placeholder(__('Hours'));
            $col->addSelect('schoolCloseM')->fromString($minutes)->setClass('shortWidth')->placeholder(__('Minutes'));

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit()->addClass('');

        echo $form->getOutput();

    }
}
