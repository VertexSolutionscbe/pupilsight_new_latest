<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;

$page->breadcrumbs
    ->add(__('Manage Groups'), 'groups_manage.php')
    ->add(__('Add Group'));

if (isActionAccessible($guid, $connection2, '/modules/Messenger/groups_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Messenger/groups_manage_edit.php&pupilsightGroupID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    //add extra filter
    $HelperGateway = $container->get(HelperGateway::class);

    $pupilsightPersonID_logged =   $_SESSION[$guid]['pupilsightPersonID'];
    $pupilsightRoleIDPrimary =$_SESSION[$guid]['pupilsightRoleIDPrimary'];
    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    if( $pupilsightRoleIDPrimary !='001')//for staff login
    {
        $staff_person_id=$pupilsightPersonID_logged;
        $sql1 = "SELECT p.pupilsightProgramID,p.name AS program,a.pupilsightYearGroupID FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN assignstaff_toclasssection b ON(a.pupilsightMappingID =b.pupilsightMappingID) LEFT JOIN pupilsightProgram AS p
    ON(p.pupilsightProgramID =a.pupilsightProgramID) WHERE b.pupilsightPersonID=".$staff_person_id."  GROUP By a.pupilsightYearGroupID ";//except Admin //0000002962
        $result1 = $connection2->query($sql1);
        $row1 = $result1->fetchAll();
        $progrm_id="Staff_program";
        $class_id="Staff_class";
        $section_id= "Staff_section";
        foreach ($row1 as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['program'];
        }
        $program = $program1 + $program2;
        $disable_cls= 'dsble_attr';
    }
    else
    {
        $staff_person_id= Null;
        $disable_cls= '';
        $progrm_id="pupilsightProgramID";
        $class_id="pupilsightYearGroupID";
        $section_id= "pupilsightRollGroupID";
        //  $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $sqlp = 'SELECT p.pupilsightProgramID, p.name FROM pupilsightProgram AS p RIGHT JOIN attn_settings AS a ON(p.pupilsightProgramID =a.pupilsightProgramID) ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program = $program1 + $program2;
    }

    $check_role='SELECT role.name FROM pupilsightPerson as p LEFT JOIN pupilsightRole as role ON p.pupilsightRoleIDAll = role.pupilsightRoleID 
    WHERE p.pupilsightPersonID ="'.$_SESSION[$guid]['pupilsightPersonID'].'" AND role.name="Administrator"';
    $check_role= $connection2->query($check_role);
    $role = $check_role->fetch();
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
    if($_GET){

        $pupilsightProgramID =  $_GET['pupilsightProgramID'];
        $pupilsightSchoolYearIDpost = $_GET['pupilsightSchoolYearID'];
        $pupilsightYearGroupID =  $_GET['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_GET['pupilsightRollGroupID'];
        $pupilsightPersonID =  $_GET['pupilsightPersonID'];
        $searchbyPost =  '';
        $search =  $_GET['search'];
        $stuId = $_GET['studentId'];
        $classes =  $HelperGateway->getClassByProgram_Attconfig($connection2, $pupilsightProgramID);
        $sections =  $HelperGateway->getSectionByProgram_attConfig($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);
    } else {
        $pupilsightProgramID =  '';

        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $pupilsightPersonID = '';
        $searchbyPost =  '';
        $search = '';
        $stuId = '0';
        $classes = array('');
        $sections = array('');
    }
    $sqls = 'SELECT a.*, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';
    $results = $connection2->query($sqls);
    $rowdatastd = $results->fetchAll();
    $student = array();
    $student1 = array(''=>'Select Student');
    $student2 = array();

    if(!empty($rowdatastd)){

        foreach ($rowdatastd as $st) {
            $student2[$st['pupilsightPersonID']] = $st['officialName'];
        }
    }
    $student = $student1 + $student2;
    $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
    if (isset($_GET['pupilsightProgramID'])) {
        $pupilsightProgramID = $_GET['pupilsightProgramID'];
    }
    if (isset($_GET['pupilsightYearGroupID'])) {
        $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
    }
    if (isset($_GET['pupilsightRollGroupID'])) {
        $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'];
    }
    if (isset($_GET['stuId'])) {
        $stuId = $_GET['stuId'];
    }
    if (isset($_GET['pupilsightPersonID'])) {
        $pupilsightPersonID = $_GET['pupilsightPersonID'];
    }












    
    $form = Form::create('groups', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/groups_manage_addProcess.php");
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->required()->setValue();

    $row = $form->addRow();
        $row->addLabel('members', __('Students Members'));
        $row->addSelectUsers('members', $_SESSION[$guid]['pupilsightSchoolYearID'], ['includeStudents' => true])
            ->setId('members')
            ->selectMultiple();

    $row = $form->addRow();
    $row->addLabel('staffmembers', __('Staff Members'));
    $row->addSelectUsers('staffmembers', $_SESSION[$guid]['pupilsightSchoolYearID'], ['includeStaff' => true])
        ->setId('staffmembers')
        ->selectMultiple();

    $row = $form->addRow();
    $row->addLabel('parentmembers', __('Parents Members'));
    $row->addSelectUsers('parentmembers', $_SESSION[$guid]['pupilsightSchoolYearID'], ['includeParent' => true])
        ->setId('parentmembers')
        ->selectMultiple();

    $row = $form->addRow();
    $row->addLabel('allmembers', __('All Members'));
    $row->addSelectUsers('allmembers', $_SESSION[$guid]['pupilsightSchoolYearID'], ['includeParent' => false])
        ->setId('allmembers')
        ->selectMultiple();

//extra filter
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes noEdit');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->placeholder();



    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->setId("pupilsightYearGroupIDA")->fromArray($classes)->selected($pupilsightYearGroupID)->addClass("load_configSession");
    $col->addTextField('pupilsightPersonID')->setId('staff_id')->addClass('nodisply')->setValue($staff_person_id);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->setId($section_id)->selected($pupilsightRollGroupID)->placeholder()->addClass('pupilsightRollGroupIDP');

    // $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->required()->selected($pupilsightPersonID)->placeholder();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightPersonID', __('Students'));
    $col->addSelect('pupilsightPersonID')->fromArray($student)->selected($pupilsightPersonID)->selectMultiple();



    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();
        
    echo $form->getOutput();
}
?>
<script type="text/javascript">

    $(document).on('change','#pupilsightProgramID',function(){
        var val = $(this).val();
        var type = "attendanceConfigCls";
        if(val != ""){
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: val,type:type },
                async: true,
                success: function(response)
                {
                    $("#pupilsightYearGroupIDA").html();
                    $("#pupilsightYearGroupIDA").html(response);

                }
            })
        }
    });

    $(document).on('change', '#pupilsightYearGroupIDA', function() {
        var id = $(this).val();
        var pid = $('#pupilsightProgramID').val();
        var type = 'getSection';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid },
            async: true,
            success: function(response) {
                $("#pupilsightRollGroupID").html();
                $("#pupilsightRollGroupID").html(response);
            }
        })
    });

    $(document).on('change', '.pupilsightRollGroupIDP', function() {
        var id = $("#pupilsightRollGroupID").val();
        var yid = $('#pupilsightSchoolYearID').val();
        var pid = $('#pupilsightProgramID').val();
        var cid = $('#pupilsightYearGroupIDA').val();
        var type = 'getStudent';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, yid: yid, pid: pid, cid: cid },
            async: true,
            success: function(response) {
                $("#pupilsightPersonID").html();
                $("#pupilsightPersonID").html(response);
            }
        });
    });

    $(document).on('change','.load_configSession',function(){
        var id = $('#pupilsightProgramID').val();
        var pupilsightYearGroupID = $('#pupilsightYearGroupIDA').val();
        var type = 'getsessionConfigured';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id,pupilsightYearGroupID:pupilsightYearGroupID, type: type },
            async: true,
            success: function(response) {
                $("#session").html();
                $("#session").html(response);
            }
        });
    });
</script>
<script type='text/javascript'>
    $(document).ready(function () {
        $('#members').select2();
    });
</script>
<script type='text/javascript'>
    $(document).ready(function () {
        $('#staffmembers').select2();
    });
</script>
<script type='text/javascript'>
    $(document).ready(function () {
        $('#parentmembers').select2();
    });
</script>
<script type='text/javascript'>
    $(document).ready(function () {
        $('#allmembers').select2();
    });
</script>
<script type='text/javascript'>
    $(document).ready(function () {
        $('#pupilsightPersonID').select2();
    });
</script>