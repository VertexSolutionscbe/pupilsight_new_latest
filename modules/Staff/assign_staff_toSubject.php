<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_student_toStaff.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Assign Staff To Subject'));
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
        // echo "<a style='display:none' id='clickstaffassign' href='fullscreen.php?q=/modules/Staff/assigned_student_toStaff_add.php&width=800'  class='thickbox '>Assign Staff</a>"; 
        // echo "<a style='display:none' id='clk_remove' href='fullscreen.php?q=/modules/Staff/remove_staff_fromstudent.php'  class='thickbox '>Change status</a>";   
        // echo "<div style='height:50px;'><div class='float-left mb-2'><a  id='assignstaff_st' data-type='staff' class='btn btn-primary'>Assign Staff</a>&nbsp;&nbsp;";  
        // echo "<a  id='unassignStudentstaff'  class='btn btn-primary'>Remove Staff</a>&nbsp;&nbsp;";  
        // echo "</div><div class='float-none'></div></div>";
        echo "<a style='display:none' id='clickstaffunassign' href='fullscreen.php?q=/modules/Staff/remove_assigned_staffSub.php&width=600'  class='thickbox '> Unassign Staff</a>";
        echo'<div  ><a id="unassignsubj" style="height: 34px;  margin-left: 10px; float: right;"class=" btn btn-primary">Unassign</a>&nbsp;&nbsp;<a href="fullscreen.php?q=/modules/Staff/select_staff_sub.php&width=650" class= "btn btn-primary thickbox" style="height: 34px;  margin-left: 10px; float: right;"class=" btn btn-primary">Assign Staff To Subject</a></div>';
    
    $StaffGateway = $container->get(StaffGateway::class);
    $criteria = $StaffGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();

$getselstaff = $StaffGateway->getselectedStaff($criteria);
    $table = DataTable::createPaginated('FeeStructureManage', $criteria);

    $table->addCheckboxColumn('st_id',__(''))
    ->setClass('chkbox')
    ->notSortable();
    $table->addColumn('fname', __('Staff'));
    $table->addColumn('dep_name', __('Subject'));
   
    
echo $table->render($getselstaff);

}