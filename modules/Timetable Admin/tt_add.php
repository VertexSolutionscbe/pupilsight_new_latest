<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;

use Pupilsight\Domain\Timetable\TimetableGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Timetables'), 'tt.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Add Timetable'));
        //program arry
        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();
        $program=array();  
        $program2=array();  
        $program1=array(''=>'Select Program');
        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program= $program1 + $program2; 
//get All class
        $sqlc = 'SELECT pupilsightYearGroupID,name FROM pupilsightYearGroup';
        $resultc = $connection2->query($sqlc);
        $rowdatacls = $resultc->fetchAll();
      


        $classes=array(); 
        $classes1=array(''=> 'Select Class'); 
        $classes2=array();  
        foreach ($rowdatacls as $dt) {
            $classes2[$dt['pupilsightYearGroupID']] = $dt['name'];
        }
        $classes = $classes1 + $classes2;

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable Admin/tt_edit.php&pupilsightTTID='.$_GET['editID'].'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    if ($pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT name AS schoolYear FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
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

            $timetableGateway = $container->get(TimetableGateway::class);

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/tt_addProcess.php');

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

            $row = $form->addRow();
                $row->addLabel('schoolYear', __('School Year'));
                $row->addTextField('schoolYear')->maxLength(20)->required()->readonly()->setValue($values['schoolYear']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique for this school year.'));
                $row->addTextField('name')->maxLength(30)->required();

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'));
                $row->addTextField('nameShort')->maxLength(12)->required();

            $row = $form->addRow();
                $row->addLabel('nameShortDisplay', __('Day Column Name'));
                $row->addSelect('nameShortDisplay')->fromArray(array('Day Of The Week' => __('Day Of The Week'), 'Timetable Day Short Name' => __('Timetable Day Short Name')))->required();

            $row = $form->addRow();
                $row->addLabel('active', __('Active'));
                $row->addYesNo('active')->required();
            $row = $form->addRow();
                $row->addLabel('pupilsightProgramID', __('Program'));
                $row->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();
            $row = $form->addRow();
                $row->addLabel('pupilsightYearGroupID', __('Class'));
                $row->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightYearGroupID)->required();

            $row = $form->addRow();
                $row->addLabel('pupilsightRollGroupID', __('Section'));
                $row->addSelect('pupilsightRollGroupID', $pupilsightSchoolYearID)->required()->selected($pupilsightRollGroupID);

                // $col = $row->addColumn()->setClass('newdes');
                // $col->addLabel('pupilsightRollGroupID', __('Section'));
                // $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required()->selected($pupilsightRollGroupID);
            // $yearGroupsOptions2 = $timetableGateway->getNonTimetabledYearGroups($pupilsightSchoolYearID);
            // $yearGroupsOptions1=array(''=>'Select Class');
            // $yearGroupsOptions = $yearGroupsOptions1 + $yearGroupsOptions2;

            // $row = $form->addRow();
            //     $row->addLabel('active', __('Class'))->description(__('Class not in an active TT this year.'));
            //     if (empty($yearGroupsOptions)) {
            //         $row->addContent('<i>'.__('No Class available.').'</i>')->addClass('right');
            //     } else {
            //         $row->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDTimeTable')->fromArray($yearGroupsOptions);
            //     }

            // $row = $form->addRow();
            //     $row->addLabel('active', __('Section'));
            //     $row->addSelect('pupilsightRollGroupID')->selectMultiple();
                
            $form->addHiddenValue('count',count($yearGroupsOptions));

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
