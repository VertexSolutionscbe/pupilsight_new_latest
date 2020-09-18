<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_staff_toClassSection_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $pupilsightSchoolYearID = '';
        if (isset($_GET['pupilsightSchoolYearID'])) {
            $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
        }

        if($_POST){        
            $pupilsightProgramID =  $_POST['pupilsightProgramID'];
            $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];         
          
            $stuId = $_POST['studentId'];
        } else {
            $pupilsightProgramID =  '';
            $pupilsightYearGroupID =  '';
            $pupilsightRollGroupID =  '';
          
            $stuId = '0';
        }
        
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
    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
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
        $page->breadcrumbs->add(__('Assign Staff to Class and Section'));
    }
    
    echo "<a style='display:none' id='clickStudentPage' href='fullscreen.php?q=/modules/Staff/select_staff_toAssign.php&width=1200'  class='thickbox '>Change Route</a>";   
    echo "<div style='height:50px;'><div class='float-left mb-2'><a  id='assignStudentPage' data-type='staff' class='btn btn-primary'>Assign</a>&nbsp;&nbsp;";  
    echo "</div><div class='float-none'></div></div>";
    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightProgramID', __('Program'));
        $col->addSelect('pupilsightProgramID')->fromArray($program)->required()->selected($pupilsightProgramID)->placeholder();

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightYearGroupID', __('Class'));
        $col->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->required();

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightRollGroupID', __('Section'));
        $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required()->selected($pupilsightRollGroupID);
        $col = $row->addColumn()->setClass('newdes');   
        
        $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');  
        echo $searchform->getOutput();

    $StaffGateway = $container->get(StaffGateway::class);
    $criteria = $StaffGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();

   $students = $StaffGateway->getStudentData($criteria, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID);

$table = DataTable::createPaginated('FeeStructureManage', $criteria);
    $table->addCheckboxColumn('stuid',__(''))
->setClass('chkbox')
->notSortable();
$table->addColumn('program', __('Program'));
$table->addColumn('yearGroup', __('Class'));
$table->addColumn('rollGroup', __('Section'));


echo $table->render($students);

    
}