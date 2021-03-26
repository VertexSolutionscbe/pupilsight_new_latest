<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/marks_by_student.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // echo '<pre>';
    //  print_r($_POST);
    //  echo '</pre>';die();
    //Proceed!
    $HelperGateway = $container->get(HelperGateway::class);
    $page->breadcrumbs->add(__('Marks Entry By Student'));
    
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    $sqlterm = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ORDER BY pupilsightSchoolYearTermID ASC';
    $resultterm = $connection2->query($sqlterm);
    $termdata = $resultterm->fetchAll();

    $term = array();
    $term1 = array(''=>'Please Select');
    $term2 = array();
    foreach($termdata as $trm){
        $term2[$trm['pupilsightSchoolYearTermID']] = $trm['name'];
    }
    if(!empty($term2)){
        $term = $term1 + $term2;
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
     
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
            
    
        if($_POST){    
            $pupilsightProgramID = $_POST['pupilsightProgramID'];
            $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];  
            $test_id  =  $_POST['test_id'];
            $testId  =  $_POST['test_id'];
    
            $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
            $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);
            $test_type=$_POST['test_type'];

            $sql_tst = 'SELECT b.id, b.name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id  WHERE a.pupilsightSchoolYearID= "'.$pupilsightSchoolYearID.'" AND a.pupilsightProgramID = "'.$pupilsightProgramID.'" AND a.pupilsightYearGroupID ="'.$pupilsightYearGroupID.'" AND a.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'"';
            $result_test = $connection2->query($sql_tst);
            $tests = $result_test->fetchAll();
            $testarr=array ('' => __('Select'));  
            $test2=array();  
        
            foreach ($tests as $ts) {
                $test2[$ts['id']] = $ts['name'];
            }
            $testarr+=  $test2; 
        } else {      
            if(!empty($_GET['pid'])){
                $pupilsightProgramID = $_GET['pid'];
                $pupilsightYearGroupID =  $_GET['cid'];
                $pupilsightRollGroupID =  $_GET['sid'];  
                $test_id  =  explode(',',$_GET['tid']);
                $testId  =  explode(',',$_GET['tid']);
                
                $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
                $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);
                //$test_type=$_POST['test_type'];

                $sql_tst = 'SELECT b.id, b.name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id  WHERE a.pupilsightSchoolYearID= "'.$pupilsightSchoolYearID.'" AND a.pupilsightProgramID = "'.$pupilsightProgramID.'" AND a.pupilsightYearGroupID ="'.$pupilsightYearGroupID.'" AND a.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'"';
                $result_test = $connection2->query($sql_tst);
                $tests = $result_test->fetchAll();
                $testarr=array ('' => __('Select'));  
                $test2=array();  
            
                foreach ($tests as $ts) {
                    $test2[$ts['id']] = $ts['name'];
                }
                $testarr+=  $test2; 
            } else {
                $classes = array('' => 'Select Class');
                $sections = array('' => 'Select Section');
                $pupilsightProgramID = '';
                $pupilsightYearGroupID =  '';
                $pupilsightRollGroupID = '';
                $test_id  = '';
                $test_type='';
                $testarr = array();
            }
        }

    
      
    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPP')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
     $col->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPP')->fromArray($classes)->selected($pupilsightYearGroupID)->required()->placeholder('Select Class');

     
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDbyPP')->fromArray($sections)->required()->selected($pupilsightRollGroupID)->placeholder('Select Section');

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('test_type', __('Test Type'));
    $col->addSelect('test_type')->setId('termId')->fromArray($term)->selected($test_type)->placeholder();
   
   
    $col = $row->addColumn()->setClass('newdes'); 
    $col->addLabel('test_id', __('Test Name'));  
    $col->addSelect('test_id')->setId('testId')->selectMultiple()->fromArray($testarr)->selected($test_id)->required()->setClass('test_lngth');

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('<button id=""  class="btn btn-primary">Go</button>'); 

     
    echo $searchform->getOutput();
    $CurriculamGateway = $container->get(CurriculamGateway::class);
    // QUERY
    $criteria = $CurriculamGateway->newQueryCriteria()
        ->pageSize(1000)
        ->fromPOST();
        //echo $test_id;
 $manage_test = $CurriculamGateway->getstdDataNew($criteria,$pupilsightProgramID,$pupilsightYearGroupID, $pupilsightRollGroupID,$test_id);
//  $entrymarks = $CurriculamGateway->getsStdWiseExcel($criteria,$pupilsightProgramID,$pupilsightYearGroupID, $pupilsightRollGroupID,$test_id);
//   echo '<pre>';
//  print_r($manage_test);

    echo "<div style='height:50px;'><div class='float-left mb-2 right_align'><a  id='expore_marks_xl' data-type='student' class='btn btn-primary'>Export To Excel</a>&nbsp;&nbsp;";   
  
             
    
    echo "<a  id='marksentry'  href='index.php?q=/modules/Academics/entry_marks_byStudent.php' data-type='student' class='btn btn-primary' style='display:none'>Mark Entry</a>&nbsp;&nbsp;"; 
    
    echo "<a  id='studentMarksEntry'   data-type='student' class='btn btn-primary'>Mark Entry</a>&nbsp;&nbsp;";  
    echo "</div><div class='float-none'></div></div>";

    echo "<div style='display:none' id='marks_studentExcel'></div>";
    // DATA TABLE
    $table = DataTable::createPaginated('marks', $criteria);
     $table->addCheckboxColumn('stuid',__(''))  
   ->notSortable();
   $table->addColumn('serial_number', __('Sl No'));
  //      $table->addColumn('roll_no', __('Roll No'));
    $table->addColumn('admission_no', __('Admission No'))->translatable();
    $table->addColumn('student_name', __('Student Name')); 

    if($_POST || !empty($_GET['pid'])){
        echo $table->render($manage_test);
    }
    
}
?>

<style>
  .text-xxs 
 {
     display: none !important;
 }  
 #testId
 {
    min-height: 35px!important;
    width: 166px;
 }
</style>

<script>

    $(document).ready(function () {
      	$('#testId').selectize({
      		plugins: ['remove_button'],
      	});
    });

    $(document).on('change', '#pupilsightRollGroupIDbyPP', function() {
        var id = $(this).val();
        var cid = $('#pupilsightYearGroupIDbyPP').val();
        var pid = $('#pupilsightProgramIDbyPP').val();
        var type = 'getTestBySection';
        $('#testId').selectize()[0].selectize.destroy();
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, cid: cid, pid: pid },
            async: true,
            success: function(response) {
                $("#testId").html();
                $("#testId").html(response);
                $("#testId").parent().children('.LV_validation_message').remove();
                $('#testId').selectize({
                    plugins: ['remove_button'],
                });
            }
        });
    });
</script>