<?php
/*
Pupilsight, Flexible & Open School System
*/


use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;
//use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Domain\Attendance\AttendanceLogPersonGateway;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Helper\HelperGateway;


//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Attendance Summary by Date'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_report_by_date_and_percentage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h2>';
    echo __('Attendance Summary By Date & Percentage');
    echo '</h2>';
    $HelperGateway = $container->get(HelperGateway::class); 
    if ($_POST) {
        $staff_person_id=Null;
        $start_date = $_POST["start_date"];// => 01/08/2018 
        $end_date = $_POST["end_date"];// => 13/04/2020 
        $pupilsightProgramID= $_POST["pupilsightProgramID"];
        $pupilsightYearGroupID = $_POST["pupilsightYearGroupID"];// => 001 
        $pupilsightRollGroupID = $_POST["pupilsightRollGroupID"];// => 00143 
        $pupilsightDepartmentID = $_POST["pupilsightDepartmentID"];// => 0005  //subject
        $minpercentage = $_POST["minpercentage"];// => 1 
        $maxpercentage = $_POST["maxpercentage"];// => 10
        $classes =  $HelperGateway->getClassByProgram_staff($connection2, $pupilsightProgramID,$staff_person_id);
            $sections =  $HelperGateway->getSectionByProgram_staff($connection2, $pupilsightYearGroupID,  $pupilsightProgramID,$staff_person_id);
    } else {
        $start_date = "";// => 01/08/2018 
        $end_date = "";// => 13/04/2020 
        $pupilsightProgramID= "";
        $pupilsightYearGroupID = "";// => 001 
        $pupilsightRollGroupID = "";// => 00143 
        $pupilsightDepartmentID = "";// => 0005  //subject
        $minpercentage = "";// => 1 
        $maxpercentage = "";// => 10
        $classes = array('');
        $sections = array(''); 
    }


    $today = date('Y-m-d');
    $pupilsightSchoolYearID = '';
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];

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
    $percentage_min = array(
        '0'     => __('0%'),
        '10'     => __('10%'),
        '20'  => __('20%'),
        '30' => __('30%'),
        '40' => __('40%'),
        '50' => __('50%'),
        '60' => __('60%'),
        '70' => __('70%'),
        '80' => __('80%'),
        '90' => __('90%'),
        '100' => __('100%'),
    );
    $percentage = array(
        '10'     => __('10%'),
        '20'  => __('20%'),
        '30' => __('30%'),
        '40' => __('40%'),
        '50' => __('50%'),
        '60' => __('60%'),
        '70' => __('70%'),
        '80' => __('80%'),
        '90' => __('90%'),
        '100' => __('100%'),
    );


    //select subjects from department
    $sqld = 'SELECT d.name,c.pupilsightDepartmentID FROM pupilsightTT AS a LEFT JOIN pupilsightTTDay AS b ON a.pupilsightTTID = b.pupilsightTTID LEFT JOIN pupilsightTTDayRowClass AS c ON b.pupilsightTTDayID = c.pupilsightTTDayID LEFT JOIN pupilsightDepartment as d ON c.pupilsightDepartmentID = d.pupilsightDepartmentID WHERE a.pupilsightProgramID = "'.$pupilsightProgramID.'" AND a.pupilsightYearGroupIDList = "'.$pupilsightYearGroupID.'"';
     if(!empty($pupilsightRollGroupID)){
            $sqld.= 'AND a.pupilsightRollGroupIDList = "'.$pupilsightRollGroupID.'"';
            }
    $resultd = $connection2->query($sqld);
    $rowdatadept = $resultd->fetchAll();

    $subjects = array();
    $subject2 = array();
    $subject1=array(''=>'Select Subjects');
    foreach ($rowdatadept as $dt) {
        $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
    }
    $subjects = array_merge($subject1, $subject2);
    //$subjects =  $subject1 + $subject2;

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

    $today = date('Y-m-d');
    $startDate = isset($_POST['start_date'])? dateConvert($guid, $_POST['start_date']) : $today;
    $endDate = isset($_POST['end_date'])? dateConvert($guid, $_POST['end_date']) : $today;

    $form = Form::create('searchForm', '');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('start_date', __('Start Date'))->addClass('dte');
    $col->addDate('start_date')->addClass('txtfield')->required()->readonly()->setValue(dateConvertBack($guid, $startDate));

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('end_date', __('End Date'))->addClass('dte');
    $col->addDate('end_date')->addClass('txtfield')->required()->readonly()->setValue(dateConvertBack($guid, $endDate));
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required();
   
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->required()->fromArray($classes)->selected($pupilsightYearGroupID)->placeholder()->addClass('getSubjectsFromPeriod');
    //$col->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->required()->addClass('getSubjectsFromPeriod');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->addClass('getSubjectsFromPeriod')->selected($pupilsightRollGroupID);
    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightDepartmentID', __('Subjects'));
    $col->addSelect('pupilsightDepartmentID')->fromArray($subjects)->selected($pupilsightDepartmentID);


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('minpercentage', __('Min Percentage'));
    $col->addSelect('minpercentage')->fromArray($percentage_min)->required()->selected($minpercentage);


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('maxpercentage', __('Max Percentage'));
    $col->addSelect('maxpercentage')->fromArray($percentage)->required()->selected($maxpercentage);

    $col = $row->addColumn()->setClass('newdes');
    
    $col->addSubmit(__('Go'))->setClass('mrgin_tp ');


    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes ');
    
    $col->addTextField('')->addClass('txtfield nodsply');

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');


    //$row = $form->addRow()->setID('attendance_rep');
    $row->addFooter();


    echo $form->getOutput();
    //change gateway

    $highestAction = 'View Student Profile_full';
    $canViewFullProfile = ($highestAction == 'View Student Profile_full' or $highestAction == 'View Student Profile_fullNoNotes');
    $canViewBriefProfile = isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php', 'View Student Profile_brief');

    $attnLog = $container->get(AttendanceLogPersonGateway::class);
    $resLog = $attnLog->getUserLog($_POST);

    $search = isset($_GET['search']) ? $_GET['search'] : '';

    /*
        $searchColumns = $canViewFullProfile
            ? array_merge($studentGateway->getSearchableColumns(), ['parent1.email', 'parent1.emailAlternate', 'parent2.email', 'parent2.emailAlternate'])
            : $studentGateway->getSearchableColumns();
    */
    $criteria = $attnLog->newQueryCriteria()->fromPOST();

    echo "<div class='linkTop'>";
   
    echo "<a style=' margin-bottom:10px;' href=''  data-toggle='modal' data-target='#large-modal-stud_attendance_rprt' data-noti='2'  class='sendButton_attendance_rprt btn btn-primary' id='sendSMS'>Send SMS</a>";
    echo '</div>';
    // DATA TABLE
    $table = DataTable::createPaginated('students_attendance', $criteria);

    $canViewFullProfile = "";

    // COLUMNS
    $table->addCheckboxColumn('student_id', __(''))
        ->setClass('chkbox')
        ->notSortable();

    $table->addColumn('serial_number', __('SI No'));

    $table->addColumn('officialName', __('Name'));

    $table->addColumn('pupilsightPersonID', __(' ID'));
    $table->addColumn('total', __('Total'));
    $table->addColumn('present', __('Present'));
    $table->addColumn('percentage', __('%'))->setClass('percentage_td');
    echo $table->render($resLog);
}

echo "<style>
.newdes
{
border: 1px #ffff !important;
}
</style>";

?>
<script>
$(document).ready(function() {
$('#expore_tbl tr ').find("td:last").each(function() {
if($(this).text().trim() == ''){
$(this).closest('tr').hide();
}
});
});
$(document).on('change','.getSubjectsFromPeriod',function(){
    var val = $('#pupilsightProgramID ').val();
    var cls = $('#pupilsightYearGroupID').val();   
    var sec = $('#pupilsightRollGroupID').val();
    var type ="getSubjectsFromPeriod";
    if(cls != '' && val != '' ){
        $.ajax({
        url: 'attendanceSwitch.php',
        type: 'post',
        data: {
        val:val,
        cls: cls,
        sec:sec,
        type: type
        },
        async: true,
        success: function(response) {                          
        $("#pupilsightDepartmentID").html('');
        $("#pupilsightDepartmentID").html(response);
        }
        });
    }
});
 </script>   
