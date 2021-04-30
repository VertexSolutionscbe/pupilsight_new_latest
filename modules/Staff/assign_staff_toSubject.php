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

    $searchby = array('' => 'Search By', 'stu_name' => 'Student Name', 'stu_id' => 'Student Id', 'adm_id' => 'Admission Id', 'father_name' => 'Father Name', 'father_email' => 'Father Email', 'mother_name' => 'Mother Name', 'mother_email' => 'Mother Email');
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    //die();
    $HelperGateway = $container->get(HelperGateway::class);
    if ($_POST) {

        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        //$pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        // $searchbyPost =  '';
        // $search =  $_POST['search'];
        // $stuId = $_POST['studentId'];

        $uid = $_SESSION[$guid]['pupilsightPersonID'];

        if ($roleId == '2') {
            $classes =  $HelperGateway->getClassByProgramForTeacher($connection2, $pupilsightProgramID, $uid);
            $sections =  $HelperGateway->getSectionByProgramForTeacher($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $uid);
        } else {
            $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID, $pupilsightSchoolYearID);
            $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);
        }

        $sqlp = 'SELECT GROUP_CONCAT(pupilsightMappingID) AS mappingIds FROM pupilsightProgramClassSectionMapping WHERE pupilsightSchoolYearID = ' . $pupilsightSchoolYearID . ' AND pupilsightProgramID = ' . $pupilsightProgramID . ' AND pupilsightYearGroupID = ' . $pupilsightYearGroupID . ' AND pupilsightRollGroupID = ' . $pupilsightRollGroupID . ' ';
        $resultp = $connection2->query($sqlp);
        $getMapData = $resultp->fetch();

        $sqlp = 'SELECT GROUP_CONCAT(a.pupilsightStaffID) AS staffIds   FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN assignstaff_toclasssection AS c ON a.pupilsightPersonID = c.pupilsightPersonID  WHERE c.pupilsightMappingID IN (' . $getMapData['mappingIds'] . ') ';
        $resultp = $connection2->query($sqlp);
        $getstaff = $resultp->fetch();

        if (!empty($getstaff['staffIds'])) {
            $staffIds = $getstaff['staffIds'];
        } else {
            $staffIds = 0;
        }
    } else {
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $pupilsightProgramID =  '';
        //$pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $staffIds = '0';
        // $searchbyPost =  '';
        // $search = '';
        // $stuId = '0';
    }
    // echo "<a style='display:none' id='clickstaffassign' href='fullscreen.php?q=/modules/Staff/assigned_student_toStaff_add.php&width=800'  class='thickbox '>Assign Staff</a>"; 
    // echo "<a style='display:none' id='clk_remove' href='fullscreen.php?q=/modules/Staff/remove_staff_fromstudent.php'  class='thickbox '>Change status</a>";   
    // echo "<div style='height:50px;'><div class='float-left mb-2'><a  id='assignstaff_st' data-type='staff' class='btn btn-primary'>Assign Staff</a>&nbsp;&nbsp;";  
    // echo "<a  id='unassignStudentstaff'  class='btn btn-primary'>Remove Staff</a>&nbsp;&nbsp;";  
    // echo "</div><div class='float-none'></div></div>";
    echo "<a style='display:none' id='clickstaffunassign' href='fullscreen.php?q=/modules/Staff/remove_assigned_staffSub.php&width=600'  class='thickbox '> Unassign Staff</a>";
    echo '<div  ><a id="unassignsubj" style="height: 34px;  margin-left: 10px; float: right;"class=" btn btn-primary">Unassign</a>&nbsp;&nbsp;<a href="index.php?q=/modules/Staff/select_staff_sub.php" class= "btn btn-primary" style="height: 34px;  margin-left: 10px; float: right;"class=" btn btn-primary">Assign Staff To Subject</a></div>';

    $form = Form::create('studentViewSearch', '');

    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPP')->fromArray($program)->selected($pupilsightProgramID)->placeholder('Select Program')->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPP')->fromArray($classes)->selected($pupilsightYearGroupID)->placeholder('Select Class')->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDbyPP')->fromArray($sections)->selected($pupilsightRollGroupID)->placeholder('Select Section')->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
    $col->addSearchSubmit($pupilsight->session, __('Clear Search'));



    echo $form->getOutput();

    $StaffGateway = $container->get(StaffGateway::class);
    $criteria = $StaffGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->pageSize(1000)
        ->fromPOST();

    $getselstaff = $StaffGateway->getStaffByFilter($criteria, $staffIds);
    $table = DataTable::createPaginated('FeeStructureManage', $criteria);

    $table->addCheckboxColumn('st_id', __(''))
        ->setClass('chkbox')
        ->notSortable();
    $table->addColumn('fname', __('Staff'));
    $table->addColumn('dep_name', __('Subject'));


    echo $table->render($getselstaff);
}
