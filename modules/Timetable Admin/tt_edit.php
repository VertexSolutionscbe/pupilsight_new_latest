<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\TimetableGateway;
use Pupilsight\Domain\Timetable\TimetableDayGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Timetables'), 'tt.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Edit Timetable'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $timetableGateway = $container->get(TimetableGateway::class);

    //Check if school year specified
    $pupilsightTTID = $_GET['pupilsightTTID'];
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    if ($pupilsightTTID == '' || $pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $values = $timetableGateway->getTTByID($pupilsightTTID);
//print_r($values);die();
        $sqlroll = 'SELECT a.pupilsightRollGroupID, a.name FROM pupilsightRollGroup AS a LEFT JOIN pupilsightProgramClassSectionMapping AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE b.pupilsightYearGroupID = '.$values['pupilsightYearGroupIDList'].' ';
        $resultroll = $connection2->query($sqlroll);
        $rollGroupListData = $resultroll->fetchAll();
        if(!empty($rollGroupListData)){
            $rollGroupList = array();
            $rollGroupList2=array();  
            $rollGroupList1=array(''=>'Select Section');
            foreach ($rollGroupListData as $dt) {
               $rollGroupList2[$dt['pupilsightRollGroupID']] = $dt['name'];
            }
            $rollGroupList= $rollGroupList1 + $rollGroupList2;
        }

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
 
         $yearGroupsOptions2 = $timetableGateway->getNonTimetabledYearGroups($pupilsightSchoolYearID, $pupilsightTTID);
         $yearGroupsOptions1=array(''=>'Select Class');
         $yearGroupsOptions = $yearGroupsOptions1 + $yearGroupsOptions2;

        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/tt_editProcess.php');

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightTTID', $pupilsightTTID);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
            $form->addHiddenValue('pupilsightProgramID', $pupilsightProgramID);
            $form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);

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
                $row->addSelect('pupilsightProgramID')->selected($pupilsightProgramID)->fromArray($program)->required()->placeholder()->setValue($values['pupilsightProgramID']);
            $row = $form->addRow();
                $row->addLabel('pupilsightYearGroupID', __('Class'));
                $row->addSelect('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->required()->fromArray($yearGroupsOptions)->selected($values['pupilsightYearGroupIDList']);

            $row = $form->addRow();
                $row->addLabel('pupilsightRollGroupID', __('Section'));
                 $pupilsightRollGroupIDList = explode(',', $values['pupilsightRollGroupIDList']);
                    $checked = array_filter(array_keys($rollGroupList), function ($item) use ($pupilsightRollGroupIDList) {
                        return in_array($item, $pupilsightRollGroupIDList);
                    });
                $row->addSelect('pupilsightRollGroupID')->fromArray($rollGroupList)->required()->selected($pupilsightRollGroupIDList);    

            $form->addHiddenValue('count', count($yearGroupsOptions));

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();

            echo '<h2>';
            echo __('Edit Timetable Days');
            echo '</h2>';

            $timetableDayGateway = $container->get(TimetableDayGateway::class);
            $ttDays = $timetableDayGateway->selectTTDaysByID($pupilsightTTID);

            // DATA TABLE
            $table = DataTable::create('timetableDays');

            $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/Timetable Admin/tt_edit_day_add.php')
                ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                ->addParam('pupilsightTTID', $pupilsightTTID)
                ->addParam('pupilsightProgramID', $pupilsightProgramID)
                ->addParam('pupilsightYearGroupID', $pupilsightYearGroupID)
                ->displayLabel();

            $table->addColumn('name', __('Name'));
            $table->addColumn('nameShort', __('Short Name'));
            $table->addColumn('columnName', __('Column'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                ->addParam('pupilsightTTID', $pupilsightTTID)
                ->addParam('pupilsightProgramID', $values['pupilsightProgramID'])
                ->addParam('pupilsightYearGroupID', $values['pupilsightYearGroupIDList'])
                
                ->addParam('pupilsightTTDayID')
                ->format(function ($values, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/tt_edit_day_edit.php');

                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/tt_edit_day_delete.php');
                });

            echo $table->render($ttDays->toDataSet());
        }
    }
}
