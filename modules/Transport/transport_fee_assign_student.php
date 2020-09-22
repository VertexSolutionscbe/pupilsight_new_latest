<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/TransportFee/generation_by_student.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('generation Invoice By student'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
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

    $searchby = array(''=>'Search By', 'stu_name'=>'Student Name', 'stu_id'=>'Student Id', 'adm_id'=>'Admission Id', 'father_name'=>'Father Name', 'father_email'=>'Father Email', 'mother_name'=>'Mother Name', 'mother_email'=>'Mother Email');
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    //die();
    if($_POST){
        
        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        $searchbyPost =  '';
        $search =  $_POST['search'];
        $stuId = $_POST['studentId'];
    } else {
        $pupilsightProgramID =  '';
        $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $searchbyPost =  '';
        $search = '';
        $stuId = '0';
    }

    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
    $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightProgramID', __('Program'));
        $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();

    $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
        $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost);    
        
    $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightYearGroupID', __('Class'));
        $col->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID);

    $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightRollGroupID', __('Section'));
        $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->selected($pupilsightRollGroupID);

    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('searchby', __('Search By'));
    //     $col->addSelect('searchby')->fromArray($searchby)->selected($searchbyPost)->required();    

    $col = $row->addColumn()->setClass('newdes');    
        $col->addLabel('search', __('Search'));
        $col->addTextField('search')->addClass('txtfield')->setValue($search);

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    
    $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');  
    echo $searchform->getOutput();

   
        
    $TransportGateway = $container->get(TransportGateway::class);
    $criteria = $TransportGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

   $students = $TransportGateway->getStudentData($criteria, $pupilsightProgramID, $pupilsightSchoolYearIDpost, $pupilsightYearGroupID, $pupilsightRollGroupID, $search);

$table = DataTable::createPaginated('FeeStructureManage', $criteria);



// $table->addColumn('serial_number', __('SL No'));
$table->addCheckboxColumn('stuid',__(''))
->setClass('chkbox')
->notSortable();
$table->addColumn('student_name', __('Name'));
//$table->addColumn('id', __('ID'));
$table->addColumn('class', __('Class'));
$table->addColumn('section', __('Section'));


$table->addColumn('academic_year', __('Academic Year'));


echo $table->render($students);

}