<?php
/*
Pupilsight, Flexible & Open School System
 */
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
if (isActionAccessible($guid, $connection2, '/modules/Academics/test_marks_upload.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Upload Marks'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo '<h3>';
    echo __('Test Mark Upload');
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
        //$pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
        $searchbyPost = '';     
        //  $search =  $_POST['search'];
        $stuId = $_POST['studentId'];
    } else {
        // $pupilsightProgramID =  '';
        // $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
        $pupilsightYearGroupID = '';
        $pupilsightRollGroupID = '';
        $searchbyPost = '';
        $search = '';
        $stuId = '0';     
    }    
    //  echo "<a  id='download_template_subject' data-type='test'  class='btn btn-primary'>Download template(Subject)</a>&nbsp;&nbsp;";  
     echo "<a  id='download_template_test' data-type='test'  class='btn btn-primary'>Download template(Test)</a>&nbsp;&nbsp;";   
     echo "<a  id='upload_template_test' data-type='test'  class='btn btn-primary thickbox'  href='fullscreen.php?q=/modules/Transport/assign_route_student_add.php&width=800'>Upload/Import Marks</a>&nbsp;&nbsp;";   
   //  echo "<a style='display:none' id='clickmodify_test' href='fullscreen.php?q=/modules/Academics/modify_test_class_section_wise.php&width=800&class_name=$pupilsightYearGroupID&section_name=$pupilsightRollGroupID'  class='thickbox '></a>";   
  //   echo "<a  id='modify_test_btn' data-type='test' class='btn btn-primary'>Modify Test</a>&nbsp;&nbsp;";       
     echo  "<div style='height:10px'></div>";
    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program')->setID('pupilsightProgramIDInUpload');
    /*$col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID_check', __('Class'));
    $col->addSelectYearGroup('pupilsightYearGroupID_check_td')->required()->placeholder('Select Class');*/
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->required()->placeholder('Select Class')->addClass('pupilsightYearGroupID_check_td');

    $col = $row->addColumn()->setClass('newdes');
    /*$col->addLabel('sortby', __('Sort By'));
    $col->addSelect('sortby')->fromArray('')->required();
    $col = $row->addColumn()->setClass('newdes');*/
    $col->addLabel('', __(''));  
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
  //  $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Go</button>');
    echo $searchform->getOutput();
    echo  "<div style='height:10px'></div>";
    $CurriculamGateway = $container->get(CurriculamGateway::class);
    // QUERY
    $criteria = $CurriculamGateway->newQueryCriteria()
        ->sortBy('id')
        ->fromPOST();
    $general_tests = $CurriculamGateway->getAllgeneraltest($criteria, Null, Null, Null, Null);
    // DATA TABLE
}
?>
<form id="downloadTemplate" name="test_mark_upload_form" method="post" action="index.php?q=/modules/Academics/marks_upload_template.php">
<input type="hidden" name="pupilsightProgramID" id="programId" value="">
<input type="hidden" name="pupilsightYearGroupID" id="classId" value="">
<table class="table" cellspacing="0" >
<tbody>
    <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
        <td colspan="3" class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                <table class="table tablewidth" border="0" >
                    <thead>
                        <tr>
                            <th><input type="checkbox" name="checkall" id="checkall_sections" value="on" class="floatNone checkall"></th>
                            <th>Section</th>
                        </tr>
                    </thead>
                    <tbody id="pupilsightRollGroupID_check_td">
                    </tbody>
                </table>
        </td>
        <td colspan='2' class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                <table class="table" border="0" >
                    <thead>
                        <tr>
                            <th><input type="checkbox" name="checkall" id="checkall_tests" value="on" class="floatNone checkall"></th>
                            <th>Test</th>
                        <tr>
                        </thead>               
                        <tbody id="tests_display">
                        </tbody>
                    </table>
        </td>
        <td colspan="3" class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                    <table class="table" border="0" >
                        <thead>
                            <tr>
                            <th><input type="checkbox" name="checkall" id="checkall_subject" value="on" class="floatNone checkall"></th>
                            <th>Subject</th>
                            <th>Skill</th>
                            <tr> 
                        </thead>
                        <tbody id="subject_display">
                        </tbody>       
                    </table>
        </td>
    </tr>
</tbody>
</table>
</form>
<script type="text/javascript">
$(document).on('change', '#pupilsightProgramIDInUpload', function() {
var id = $(this).val();
$("#programId").val(id);
var type = 'getClass';
$.ajax({
    url: 'ajax_data.php',
    type: 'post',
    data: { val: id, type: type },
    async: true,
    success: function(response) {
        $("#pupilsightYearGroupID").html('');
        $("#pupilsightYearGroupID").html(response);
        $("#pupilsightRollGroupID_check_td").html('');
        $("#tests_display").html('');
        $("#subject_display").html('');
        $("#checkall_sections"). prop("checked", false);
        $("#checkall_tests"). prop("checked", false);
        $("#checkall_subject"). prop("checked", false);
    }
});
});
$(document).on('change', '.pupilsightYearGroupID_check_td', function() {
var id = $(this).val();
var pupilsightProgramID = $("#pupilsightProgramIDInUpload").val();

var type = 'getSection_checkbox_td';
if(id!=""){
    $("#classId").val(id);
$.ajax({
url: 'ajax_data.php',
type: 'post',
data: { val: id,pupilsightProgramID:pupilsightProgramID,type: type },
async: true,
success: function(response) {
$("#pupilsightRollGroupID_check_td").html('');
$("#pupilsightRollGroupID_check_td").html(response);
$("#tests_display").html('');
$("#subject_display").html('');
$("#checkall_sections"). prop("checked", false);
$("#checkall_tests"). prop("checked", false);
$("#checkall_subject"). prop("checked", false);
}
});
}
});
$(document).on('click','.check_mrgin',function(){
 load_test();
});
$(document).on('change','#checkall_sections',function(){
load_test();
});
$(document).on('change','#checkall_tests',function(){
load_subject();
});
$(document).on('click','.slt_test',function(){
 load_subject();
});
function load_test(){
    var favorite = [];
    var pupilsightYearGroupID = $("#pupilsightYearGroupID").val();
    var pupilsightProgramID = $("#pupilsightProgramIDInUpload").val();
    $.each($("input[name='pupilsightRollGroupID[]']:checked"), function() {
    favorite.push($(this).val());
    });
    var type ="load_test_groups";
    $.ajax({
            url: 'ajaxSwitch.php',
            type: 'post',
            data: { pupilsightRollGroupID:favorite,pupilsightProgramID:pupilsightProgramID,pupilsightYearGroupID:pupilsightYearGroupID,type: type },
            async: true,
            success: function(response) {
            $("#tests_display").html('');
            $("#tests_display").html(response);
            $("#subject_display").html('');
            $("#checkall_tests"). prop("checked", false);
            $("#checkall_subject"). prop("checked", false);
            }
    });
}
function load_subject(){
    var testID = [];
    $.each($("input[name='testID[]']:checked"), function() {
    testID.push($(this).val());
    });
    var type ="load_tests_subjects";
    $.ajax({
            url: 'ajaxSwitch.php',
            type: 'post',
            data: { testID:testID,type: type},
            async: true,
            success: function(response) {
            $("#subject_display").html('');
            $("#subject_display").html(response);
            $("#checkall_subject"). prop("checked", false);
            }
    });
}


$(document).on('click','#download_template_test', function(){
    $("#downloadTemplate").submit();
})
</script>