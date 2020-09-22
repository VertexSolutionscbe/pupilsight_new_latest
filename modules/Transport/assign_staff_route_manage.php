<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/assign_staff_route_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Assign route'));

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
    $TransportGateway = $container->get(TransportGateway::class);


    if ($_POST) {

        // $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        // $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        // $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        // $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        // $searchbyPost =  '';

        if (!empty($_POST['search'])) {
            $search =  $_POST['search'];
        } else {
            $search =  '';
        }
        if (!empty($_POST['studentId'])) {
            $stuId = $_POST['studentId'];
        } else {
            $stuId = '0';
        }
    } else {
        // $pupilsightProgramID =  '';
        // $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
        // $pupilsightYearGroupID =  '';
        // $pupilsightRollGroupID =  '';
        // $searchbyPost =  '';
        $search = '';
        $stuId = '0';
    }
    echo "<a style='display:none' id='clickStudentroute' href='fullscreen.php?q=/modules/Transport/assign_route_to_staff_add.php&width=800'  class='thickbox '>Assign Route</a>";
    echo "<a style='display:none' id='changeClickStudentroute' href='fullscreen.php?q=/modules/Transport/assign_route_change_to_staff_add.php'  class='thickbox '>Change Route</a>";
    echo "<div style='height:50px;'><div class='float-left mb-2'><a  id='assignStudentroute' data-type='staff' class='btn btn-primary'>Assign Route</a>&nbsp;&nbsp;";
    echo "<a  id='unassignStudentroute' data-type='staff' class='btn btn-primary'>UnAssign Route</a>&nbsp;&nbsp;";
    echo "<a  id='changeStudentroute' data-type='staff'  class='btn btn-primary'>Change Route</a></div><div class='float-none'></div></div>";
    // echo '<button id="" href="modules/Transport/assign_route.php"  class=" btn btn-primary" style="margin:5px">Assign route</button>';
    // echo '<button id=""  class=" btn btn-primary" style="margin:5px">Unassign route</button>';
    // echo '<button id=""  class=" btn btn-primary" style="margin:5px">Change route</button>';

    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightProgramID', __('Program'));
    //     $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();

    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    //     $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost);    

    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightYearGroupID', __('Class'));
    //     $col->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID);

    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightRollGroupID', __('Section'));
    //     $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->selected($pupilsightRollGroupID);

    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('searchby', __('Search By'));
    //     $col->addSelect('searchby')->fromArray($searchby)->selected($searchbyPost)->required();    

    $col = $row->addColumn()->setClass('newdes');
    // $col->addLabel('search', __('Search'));
    $col->addTextField('search')->addClass('txtfield')->placeholder('Search Staff')->setValue($search);

    $col = $row->addColumn()->setClass('newdes');
    

    $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');
    echo $searchform->getOutput();



    $criteria = $TransportGateway->newQueryCriteria()
        ->sortBy(['pupilsightPersonID'])
        ->fromPOST();

    $staff = $TransportGateway->getStaffData($criteria, $search, $pupilsightSchoolYearID);

    $table = DataTable::createPaginated('FeeStructureManage', $criteria);

    // $table->addColumn('id', __('SL No'));
    /*
    $table->addCheckboxColumn('stuid', __(''))
        ->setClass('chkbox')
        ->notSortable();
    */

    $table->addCheckboxColumn('stuid', __(''))
        ->setClass('chkbox')
        ->notSortable()
        ->format(function ($staff) {
            return "<input type='checkbox' routeid='" . $staff['route_id'] . "' name='stuid[]' id='stuid" . $staff["stuid"] . "' value='" . $staff["stuid"] . "'>";
        });


    $table->addColumn('student_name', __('Staff Name'));
    // $table->addColumn('academicyear', __('Academic Year'));
    // $table->addColumn('class', __('Class'));
    // $table->addColumn('section', __('Section'));
    $table->addColumn('onward_route_name', __('Onward Route'));
    $table->addColumn('onward_stop_name', __('Onward Stop'));
    $table->addColumn('return_route_name', __('Return Route'));
    $table->addColumn('return_stop_name', __('Return Stop'));


    echo $table->render($staff);

    echo '<script> $(".sorting").removeClass("sortable");</script>';
}
