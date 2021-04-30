<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_student_toStaff.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Assign Student to Staff'));
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

        $HelperGateway = $container->get(HelperGateway::class);
        if($_POST){
            
            $pupilsightProgramID =  $_POST['pupilsightProgramID'];
            $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
            $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
            $pupilsightDepartmentID =  $_POST['pupilsightDepartmentID'];
            $searchbyPost =  '';
            $search =  $_POST['search'];
            $stuId = $_POST['studentId'];

            echo "<a style='display:none' id='clickstaffassign' href='fullscreen.php?q=/modules/Staff/assigned_student_toStaff_add.php&aid=".$pupilsightSchoolYearIDpost."&pid=".$pupilsightProgramID."&cid=".$pupilsightYearGroupID."&sid=".$pupilsightRollGroupID."&width=800'  class='thickbox '>Assign Staff</a>"; 
            echo "<a style='display:none' id='clk_remove' href='fullscreen.php?q=/modules/Staff/remove_staff_fromstudent.php'  class='thickbox '>Change status</a>";   
            echo "<div style='height:50px;'><div class='float-left mb-2'><a  id='assignstaff_st' data-type='staff' class='btn btn-primary'>Assign Staff</a>&nbsp;&nbsp;";  
            echo "<a  id='unassignStudentstaff'  class='btn btn-primary'>Remove Staff</a>&nbsp;&nbsp;";  
            echo "</div><div class='float-none'></div></div>";

            $uid = $_SESSION[$guid]['pupilsightPersonID'];

            if ($roleId == '2') {
                $classes =  $HelperGateway->getClassByProgramForTeacher($connection2, $pupilsightProgramID, $uid);
                $sections =  $HelperGateway->getSectionByProgramForTeacher($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $uid);
            } else {
                $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID, $pupilsightSchoolYearIDpost);
                $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearIDpost);
            }

            $sqld = 'SELECT pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum WHERE pupilsightSchoolYearID ="'.$pupilsightSchoolYearIDpost.'" AND pupilsightProgramID ="'.$pupilsightProgramID.'" AND pupilsightYearGroupID ="'.$pupilsightYearGroupID.'"   order by subject_display_name asc ';
            $resultd = $connection2->query($sqld);
            $rowdatadept = $resultd->fetchAll();
            $subjects=array();  
            $subject2=array();  
            // $subject1=array(''=>'Select Subjects');
            foreach ($rowdatadept as $dt) {
                $subject2[$dt['pupilsightDepartmentID']] = $dt['subject_display_name'];
            }
            $subjects=  $subject2; 

            $sqlchk = 'SELECT subject_type from subjectToClassCurriculum WHERE pupilsightSchoolYearID ='.$pupilsightSchoolYearIDpost.' AND pupilsightProgramID ='.$pupilsightProgramID.' AND pupilsightYearGroupID = '.$pupilsightYearGroupID.' AND pupilsightDepartmentID = '.$pupilsightDepartmentID.'  ';
            $resultchk = $connection2->query($sqlchk);
            $subChk = $resultchk->fetch();
            $subType = $subChk['subject_type'];
        } else {
            $classes = array('' => 'Select Class');
            $sections = array('' => 'Select Section');
            $subjects= array('' => 'Select Subject');
            $subType = '';
            $pupilsightProgramID =  '';
            $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
            $pupilsightYearGroupID =  '';
            $pupilsightRollGroupID =  '';
            $pupilsightDepartmentID =  '';
            $searchbyPost =  '';
            $search = '';
            $stuId = '0';
        }
       
        // echo '<button id="" href="modules/Transport/assign_route.php"  class=" btn btn-primary" style="margin:5px">Assign route</button>';
        // echo '<button id=""  class=" btn btn-primary" style="margin:5px">Unassign route</button>';
        // echo '<button id=""  class=" btn btn-primary" style="margin:5px">Change route</button>';
    
        $searchform = Form::create('searchForm','');
        $searchform->setFactory(DatabaseFormFactory::create($pdo));
        $searchform->addHiddenValue('studentId', '0');
        $row = $searchform->addRow();
        
    
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost);    

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'));
            $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();
            
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'));
            $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightYearGroupID)->required();
    
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightRollGroupID', __('Section'));
            $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->selected($pupilsightRollGroupID)->required();

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightDepartmentID', __('Subjects'));
            $col->addSelect('pupilsightDepartmentID')->fromArray($subjects)->required()->selected($pupilsightDepartmentID)->placeholder()->required();
    
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
    
       
        
$StaffGateway = $container->get(StaffGateway::class);
$criteria = $StaffGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();

$students = $StaffGateway->getstdData($criteria, $pupilsightProgramID, $pupilsightSchoolYearIDpost, $pupilsightYearGroupID, $pupilsightRollGroupID , $pupilsightDepartmentID, $search, $subType);

$table = DataTable::createPaginated('FeeStructureManage', $criteria);

// $table->addColumn('serial_number', __('SL No'));
$table->addCheckboxColumn('stuid',__(''))
    ->setClass('chkbox')
    ->notSortable()
    ->format(function ($students) {
        return "<input id='stuid' name='stuid[]' type='checkbox' value='" . $students['stuid'] . "'  data-stfid='" . $students['staff_ids'] . "'>";
    });

$table->addColumn('student_name', __('Name'));
$table->addColumn('staff_name', __('Staff Name'));
//$table->addColumn('staff_id', __('Staff Id'));



    
// ACTIONS
// $table->addActionColumn()
//     ->addParam('id')
//     ->format(function ($facilities, $actions) use ($guid) {
//         $actions->addAction('copynew', __('Copy'))
//                 ->setURL('/modules/Transport/transport_route_copy.php');

//         $actions->addAction('edit', __('Edit'))
//                 ->setURL('/modules/Transport/transport_route_edit.php');

//         $actions->addAction('delete', __('Delete'))
//                 ->setURL('/modules/Transport/transport_route_delete.php');
        
//         $actions->addAction('assign', __('Assign to Class'))
//                 ->setURL('/modules/Transport/transport_route_assign.php');        
//     });
// echo '<pre>';
//         print_r($students);
//         echo '</pre>';
       // die();
if($_POST){
echo $table->render($students);
}

}

?>

<script>
    $("#pupilsightYearGroupID").change(function() {
        loadSubjects();
    });
    

    function loadSubjects() {
        var pupilsightProgramID = $("#pupilsightProgramID").val();
        var pupilsightYearGroupID = $("#pupilsightYearGroupID").val();
        //var roleid= $("#roleid").val();
        if (pupilsightYearGroupID) {
            var type = "getSubjectbasedonclassNew";
            try {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: {
                        val: pupilsightYearGroupID,
                        type: type,
                        pupilsightProgramID:pupilsightProgramID

                    },
                    async: true,
                    success: function(response) {
                        $("#pupilsightDepartmentID").html(response);
                        if (reloadCall) {
                            $("#pupilsightDepartmentID").val(_pupilsightDepartmentID);
                            
                        }
                    }
                });
            } catch (ex) {
                reloadCall = false;
            }
        }
    }
</script>