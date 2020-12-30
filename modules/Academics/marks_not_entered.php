<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Tables\DataTable;

if (isActionAccessible($guid, $connection2, '/modules/Academics/marks_not_entered.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    /*$sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $academic = array();
    $ayear = '';
    if (!empty($rowdata)) {
    $ayear = $rowdata[0]['name'];
    foreach ($rowdata as $dt) {
    $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }
    }*/

    $page->breadcrumbs->add(__('Marks Not Entered'));
    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

     $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }
    ?>
<style type="text/css">
    .text-xxs{
        display: none;
    }
</style>
    <?php
    //print_r($_POST);
    $search = array();
    if ($_POST) {
     $test_id=$_POST['test_id'];
     $pupilsightYearGroupIDT=$_POST['pupilsightYearGroupIDT'];
     $pupilsightYearGroupIDT_str=implode(',',$_POST['pupilsightYearGroupIDT']);
     $pupilsightProgramIDBytest =  $_POST['pupilsightProgramIDBytest'];
     $search['pupilsightProgramIDBytest']=$pupilsightProgramIDBytest;
     $search["test_id"] = isset($_POST["test_id"])?$_POST["test_id"]:"";
     $search["pupilsightDepartmentID"]=isset($_POST["pupilsightDepartmentID"])?$_POST["pupilsightDepartmentID"]:"";
     $search["pupilsightRollGroupID"] = isset($_POST["pupilsightRollGroupID"])?$_POST["pupilsightRollGroupID"]:""; 
     $search['pupilsightYearGroupIDT']=isset($_POST["pupilsightYearGroupIDT"])?$_POST["pupilsightYearGroupIDT"]:""; //test id
    }
    else
    {
    $pupilsightYearGroupIDT_str='';
    $pupilsightProgramIDBytest =  $pupilsightProgramIDBytest;
    $pupilsightYearGroupIDT = "";
    $pupilsightDepartmentID = "";
    $pupilsightRollGroupID = "";
    
    $skill_id = "";
    $test_id = "";
    }
    $curriculamGateway  = $container->get(CurriculamGateway::class);
    /*$sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ';
    $resultd = $connection2->query($sqld);
    $rowdatadept = $resultd->fetchAll();
    $subjects=array('' => __('Select'));    
    $subject2=array();  
    // $subject1=array(''=>'Select Subjects');
    foreach ($rowdatadept as $dt) {
        $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
    }
    $subjects+=  $subject2; */ 
    //program load
    $psql = 'SELECT p.pupilsightProgramID,p.name
            FROM pupilsightProgram as p
            LEFT JOIN examinationTestAssignClass as examTAC 
            ON p.pupilsightProgramID = examTAC.pupilsightProgramID
            WHERE examTAC.test_master_id = "'.$test_id.'" GROUP BY examTAC.pupilsightProgramID';
    $psql = $connection2->query($psql);
    $pdata = $psql->fetchAll();
    $program=array('' => __('Select'));    
    $program1=array();  
    // $subject1=array(''=>'Select Subjects');
    foreach ($pdata as $dt) {
    $program1[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program+=  $program1;  
   //ends here 
    //class data 
        $clsql = 'SELECT g.pupilsightYearGroupID,g.name
        FROM pupilsightYearGroup as g 
        LEFT JOIN examinationTestAssignClass as examTAC
        ON g.pupilsightYearGroupID = examTAC.pupilsightYearGroupID 
        WHERE examTAC.test_master_id ="'.$test_id.'" AND examTAC.pupilsightProgramID="'.$pupilsightProgramIDBytest.'"  GROUP BY examTAC.pupilsightYearGroupID ORDER bY g.name ASC';
        $class_res = $connection2->query($clsql);
        $class_data = $class_res->fetchAll();
        $class_option=array('' => __('Select class'));    
        $class_option1=array();  
        // $subject1=array(''=>'Select Subjects');
        foreach ($class_data as $dt) {
        $class_option1[$dt['pupilsightYearGroupID']] = $dt['name'];
        }
        $class_option+=  $class_option1;  
    //ends class data
    //subject data
   $subjects=array();
   $subject1=array('' => __('Select subject'));  
   if($pupilsightYearGroupIDT_str!="")  {
        $s_sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightYearGroupID IN(".$pupilsightYearGroupIDT_str.")  GROUP BY subject_display_name order by subject_display_name ASC";
        $s_sq_res = $connection2->query($s_sq);
        $subjectD = $s_sq_res->fetchAll();
        foreach ($subjectD as $dt) {
        $subject1[$dt['pupilsightDepartmentID']] = $dt['subject_display_name'];
        }
        }
        $subjects+=  $subject1;  
   //ends subject data
    //section data
        $sectionData=array();
        $sectionData1=array('' => __('Select section')); 
        if($pupilsightYearGroupIDT_str!=""){
      $section_sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramIDBytest . '" AND a.pupilsightYearGroupID IN(' .$pupilsightYearGroupIDT_str.') GROUP BY a.pupilsightRollGroupID';
        $section_res = $connection2->query($section_sql);
        $section_data = $section_res->fetchAll();
         foreach ($section_data as $dt) {
        $sectionData1[$dt['pupilsightRollGroupID']] = $dt['name'];
        }
    }
    $sectionData+=$sectionData1;
    //section_data ends
    $skills=array ('' => __('Select'));  
    /*$skills2=array();  

    foreach ($skilldata as $sk) {
        $skills2[$sk['id']] = $sk['name'];
    }
    $skills+=  $skills2; */ 
  
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Marks Not Entered');
    echo '</h3>';

    $form = Form::create('descriptiveIndicatorConfig', "");
    $form->setFactory(DatabaseFormFactory::create($pdo));
    if ($_POST) {
    $marksNotEnterted = $curriculamGateway->getExamMarksNotEntered($search);
    }else{
    $marksNotEnterted = $curriculamGateway->getExamMarksNotEntered();
    }
   
    $row = $form->addRow();
    $test_sql = 'SELECT  examinationTestMaster.id,examinationTestMaster.name FROM `examinationTestMaster` LEFT JOIN `pupilsightSchoolYear` ON `examinationTestMaster`.`pupilsightSchoolYearID`=`pupilsightSchoolYear`.`pupilsightSchoolYearID` WHERE `examinationTestMaster`.`pupilsightSchoolYearID` = "'.$pupilsightSchoolYearID.'" ORDER BY examinationTestMaster.name ASC';
    $test_res = $connection2->query($test_sql);
    $tests = $test_res->fetchAll();
    $testList = array();
    $testList = array(''=> ('Select Test'));
    foreach ($tests as $val) {
        $testList[$val['id']]=$val['name'];
    }
    /*$col=$row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearID)->required()->placeholder('Select Academic');*/

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('test_id', __('Test Name'));
    $col->addSelect('test_id')->fromArray($testList)->selected($search["test_id"])->required();
   

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramIDBytest', __('Program'));
    $col->addSelect('pupilsightProgramIDBytest')->fromArray($program)->selected($pupilsightProgramIDBytest)->required()->placeholder('Select Program');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupIDT', __('Class'));
    $col->addSelect('pupilsightYearGroupIDT')->setId('pupilsightYearGroupIDT')->fromArray($class_option)->selected($pupilsightYearGroupIDT)->setClass('test_lngth')->required()->selectMultiple();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    
    $col->addSelect('pupilsightRollGroupID')->fromArray($sectionData)->selected($search["pupilsightRollGroupID"])->placeholder('Select Section')->required();

    $sub[""] = "";
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightDepartmentID', __('Subject'));
    $col->addSelect('pupilsightDepartmentID')->fromArray($subjects)->selected($search["pupilsightDepartmentID"]);

    

    $skill[""] = "";
   /* $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('skill_id', __('Skill'));
    $col->addSelect('skill_id')->fromArray($skills)->selected($search["skill_id"]);*/


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
    $col->addSubmit();
    echo $form->getOutput();

?>
<script>
 $(document).on('change','#test_id',function(){
   var test_id = $(this).val();
   var type = "loadTestByProgram";
        $.ajax({
        url: 'ajaxSwitch.php',
        type: 'post',
        data: { test_id: test_id,type:type},
        async: true,
        success: function(response) {
           $("#pupilsightProgramIDBytest").html(response);
           $("#pupilsightYearGroupIDT").html('<option value="">Select class</option>');
           $("#pupilsightRollGroupID").html('<option value="">Select section</option>');
           $("#pupilsightDepartmentID").html('<option value="">Select subject</option>');
        }
        });
 });  
/* $(document).on('change','#pupilsightSchoolYearID',function(){
   var pupilsightSchoolYearID = $(this).val();
   var type = "loadYearByTests";
        $.ajax({
        url: 'ajaxSwitch.php',
        type: 'post',
        data: { pupilsightSchoolYearID: pupilsightSchoolYearID,type:type},
        async: true,
        success: function(response) {
           $("#test_id").html(response);
        }
        });
 }); */  
 $(document).on('change','#pupilsightProgramIDBytest',function(){
    var pupilsightProgramID=$(this).val();
    var test_id = $("#test_id").val();
    $('#pupilsightYearGroupIDT').selectize()[0].selectize.destroy();
     var type = "loadClassesByTest";
        $.ajax({
        url: 'ajaxSwitch.php',
        type: 'post',
        data: { test_id:test_id,pupilsightProgramID: pupilsightProgramID,type:type},
        async: true,
        success: function(response) {
            $("#pupilsightYearGroupIDT").html(response);
            $("#pupilsightRollGroupID").html('<option value="">Select section</option>');
            $("#pupilsightDepartmentID").html('<option value="">Select subject</option>');
            $('#pupilsightYearGroupIDT').selectize({
                plugins: ['remove_button'],
            });
        }
    });
 });   
 $(document).on('change','#pupilsightYearGroupIDT',function(){
  var   pupilsightYearGroup=$(this).val();
  var pupilsightProgramID =$("#pupilsightProgramIDBytest").val();
  loadSubjects();
   var type = "getSectionM";
        $.ajax({
        url: 'ajaxSwitch.php',
        type: 'post',
        data: { pupilsightYearGroup:pupilsightYearGroup,pupilsightProgramID:pupilsightProgramID,type:type},
        async: true,
        success: function(response) {
           $("#pupilsightRollGroupID").html(response);
        }
        });
 });
function loadSubjects() {
        var pupilsightYearGroupID = $("#pupilsightYearGroupIDT").val();
        var type = "getSubjectbasedonclassByM";
        $.ajax({
        url: 'ajaxSwitch.php',
        type: 'post',
        data: { pupilsightYearGroupID:pupilsightYearGroupID,type:type},
        async: true,
        success: function(response) {
        $("#pupilsightDepartmentID").html(response);
        }
        });
    }
    $('td:last').addClass('red');
</script>
<?php
    echo "<div style='height:50px;'><div class='float-left mb-2 right_align'><a  id='export_not_marks_xl' data-type='student' class='btn btn-primary'>Export To Excel</a>&nbsp;&nbsp;</div></div>";
  
    $table = DataTable::create('marksNotEntered');
    $table->addColumn('pupilsightYearGroup', __('Class'));
    $table->addColumn('pupilsightRollGroup', __('Section'));
    $table->addColumn('name', __('Test'));
    $table->addColumn('pupilsightDepartment', __('Subject'));
    $table->addColumn('skill', __('Skill'));
    
    $table->addColumn('marksEntered', __('Marks Entered'));
    $table->addColumn('totalStudents', __('Total Students'));
    $table->addColumn('staff', __('Staff'));
    $table->addColumn('marksStatus', __('Status'))
    ->notSortable();

    echo "<br>" . $table->render($marksNotEnterted);

echo "<div id='marksNotEntered_excel' class='downloadExcel' style='display:none'>";
$table = DataTable::create('marksNotEnteredExcel');
$table->addColumn('pupilsightYearGroup', __('Class'));
$table->addColumn('pupilsightRollGroup', __('Section'));
$table->addColumn('name', __('Test'));
$table->addColumn('pupilsightDepartment', __('Subject'));
$table->addColumn('skill', __('Skill'));

$table->addColumn('marksEntered', __('Marks Entered'));
$table->addColumn('totalStudents', __('Total Students'));
$table->addColumn('staff', __('Staff'));


echo "<br>" . $table->render($marksNotEnterted);
echo "</div>";

}


?>

<script>
    $(document).ready(function () {
      	$('#pupilsightYearGroupIDT').selectize({
      		plugins: ['remove_button'],
      	});
    });
</script>