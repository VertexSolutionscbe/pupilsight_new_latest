<?php
/*
Pupilsight, Flexible & Open School System
 */
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_test_marks_not_entered.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Marks Not Entered'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo '<h3>';
    echo __('Marks Not Entered Info');
    echo '</h3>';
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
    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program = $program1 + $program2;

    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $academic = array();
    $ayear = '';
    if (!empty($rowdata)) {
        $ayear = $rowdata[0]['name'];
        foreach ($rowdata as $dt) {
            $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
        }
    } 
    if ($_POST) {

        // $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        // $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
        $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
        $searchbyPost = '';
      
        // $search =  $_POST['search'];
        $stuId = $_POST['studentId'];
    } else {
        //  $pupilsightProgramID =  '';
        // $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
        $pupilsightYearGroupID = '';
        $pupilsightRollGroupID = '';
        $pupilsightDepartmentID='';
        $searchbyPost = '';
        $search = '';
        $stuId = '0';
       
    }
    $sql_rmk = 'SELECT id, description FROM acRemarks ';
    $result_rmk = $connection2->query($sql_rmk);
    $rowdata_rmk = $result_rmk->fetchAll();
    $remarks=array();  
    $remark2=array();  
    // $subject1=array(''=>'Select Subjects');
    foreach ($rowdata_rmk as $dr) {
        $subject2[$dr['id']] = $dr['description'];
    }
    $remarks=  $remark2;  
    //select subjects from department
    $sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ';
    $resultd = $connection2->query($sqld);
    $rowdatadept = $resultd->fetchAll();
    $subjects=array();  
    $subject2=array();  
    // $subject1=array(''=>'Select Subjects');
    foreach ($rowdatadept as $dt) {
        $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
    }
    $subjects=  $subject2;  
    $test_types = array(__('Select') => array ('Term1' => __('Term 1'), 'Term2' => __('Term 2')));
    $tests =  array(__('Select') => array('Test1' => __('Test1'),
    'Test2' => __('Test2'),
    'Test3' => __('Test3')));

    $sqlsk = 'SELECT ID, name FROM ac_manage_skill ';
    $resultsk = $connection2->query($sqlsk);
    $skilldata = $resultsk->fetchAll();
    $skills=array();  
    $skills2=array();  
    foreach ($skilldata as $sk) {
        $skills2[$sk['ID']] = $sk['name'];
    }
    $skills=  $skills2;  

    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->setClass('noIntBorder fullWidth');
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
    $col = $row->addColumn()->setClass('newdes ');
    $col->addLabel('test_type', __('Test Type'));
    $col->addSelect('test_type')->fromArray($test_types)->required();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('selecttest', __(' Test Name'));
    $col->addSelect('selecttest')->fromArray($tests)->required();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->required()->placeholder('Select Class');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->selected($pupilsightRollGroupID)->required()->placeholder('Select Section');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightDepartmentID', __('Subjects'));
    $col->addSelect('pupilsightDepartmentID')->fromArray($subjects)->required()->selected($pupilsightDepartmentID)->placeholder();    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('skill', 'Skill');
    $col->addSelect('skill')->fromArray($skills)->placeholder(' Skill')->required();   
    $col = $row->addColumn()->setClass('newdes');   
    $col->addContent('<div style="width:90px;margin-top: 30px;"><button type="submit"  class=" btn btn-primary">Go</button>&nbsp;&nbsp;
   </div>');    
    echo $searchform->getOutput();
    echo  "<div style='height:20px'></div>";
    echo "<a  id='marks_not_enterd_send_sms' data-type='test' class='btn btn-primary'>SEND SMS</a>&nbsp;&nbsp;";
    echo "<a  id='marks_not_enterd_send_email' data-type='test' class='btn btn-primary'>SEND EMAIL</a>&nbsp;&nbsp;";         
    echo  "<div style='height:10px'></div>";
    $CurriculamGateway = $container->get(CurriculamGateway::class);
    // QUERY
    $criteria = $CurriculamGateway->newQueryCriteria()
        ->sortBy('id')
        ->fromPOST();
    $general_tests = $CurriculamGateway->getAllgeneraltest($criteria);
    // DATA TABLE
    $table = DataTable::createPaginated('managetestmarknotentered', $criteria);
  $table->addCheckboxColumn('id',__(''))
  
   ->notSortable();
    $table->addColumn('class', __('Class'));
    $table->addColumn('section', __('Section'));
    $table->addColumn('name', __('Test '));
    $table->addColumn('subject', __('Subject'));
    $table->addColumn('skill', __('Skill'));
    $table->addColumn('marks_entered', __('Marks Entered'));
    $table->addColumn('total_student', __('Total Student'));
    $table->addColumn('staff', __('Staff'));
    $table->addColumn('status', __('Status'));
    echo $table->render($general_tests);
}
?>
<style>
 .text_cntr 
 {
    text-align: center;
 }   
 .bdr_right 
 {
    border-right: 2px solid #dee2e6;
 }
 .textfield_wdth 
 {
     width:75px;
 }
 .td_texfield 
 {
    width: 9%;
 }
 .rmk_width 
 {
    width: 250px;
 }
 
</style>

