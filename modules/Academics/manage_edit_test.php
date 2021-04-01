<?php
/*
Pupilsight, Flexible & Open School System
 */

use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_edit_test.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Edit Test'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo '<h3>';
    echo __('Manage Test Edit');
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


    $HelperGateway = $container->get(HelperGateway::class);
    $roleId = $_SESSION[$guid]['pupilsightRoleIDPrimary'];

    if ($_POST) {

        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
        $searchbyPost = '';

        //  $search =  $_POST['search'];
        $stuId = $_POST['studentId'];

        $uid = $_SESSION[$guid]['pupilsightPersonID'];

        if ($roleId == '2') {
            $classes =  $HelperGateway->getClassByProgramForTeacher($connection2, $pupilsightProgramID, $uid);
            $sections =  $HelperGateway->getSectionByProgramForTeacher($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $uid);
        } else {
            $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
            $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearIDpost);
        }
    } else {
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $pupilsightProgramID =  '';
        $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
        $pupilsightYearGroupID = '';
        $pupilsightRollGroupID = '';
        $searchbyPost = '';
        $search = '';
        $stuId = '0';

        if (isset($_GET["pid"])) {
            $pupilsightProgramID =  $_GET['pid'];
            $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
            $pupilsightYearGroupID = $_GET['cid'];
            $pupilsightRollGroupID = $_GET['sid'];
            if ($roleId == '2') {
                $classes =  $HelperGateway->getClassByProgramForTeacher($connection2, $pupilsightProgramID, $uid);
                $sections =  $HelperGateway->getSectionByProgramForTeacher($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $uid);
            } else {
                $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
                $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearIDpost);
            }
        }
    }


    echo "<a  id='Add_test_class_section_wise' data-type='test' href='index.php?q=/modules/Academics/test_create_with_section.php' class='btn btn-primary'>Add Test</a>&nbsp;&nbsp;";

    //  echo "<a style='display:none' id='clickmodify_test' href='fullscreen.php?q=/modules/Academics/modify_test_class_section_wise.php&width=800&class_name=$pupilsightYearGroupID&section_name=$pupilsightRollGroupID'  class='thickbox '></a>";   
    //  echo "<a  id='modify_test_btn' data-type='test' class='btn btn-primary'>Modify Test</a>&nbsp;&nbsp;";  
    //  echo "<a  id='delete_test' data-type='test' class='btn btn-primary'>Delete Test</a>&nbsp;&nbsp;";
    echo "<a style='display:none' id='clickcopy_test_to_sections' href='fullscreen.php?q=/modules/Academics/copy_test_to_sections.php&width=800&class_name=$pupilsightYearGroupID&section_name=$pupilsightRollGroupID'  class='thickbox '></a>";
    echo "<a  id='copy_test_class_section_wise' data-type='test' class='btn btn-primary'>Copy Test</a>&nbsp;&nbsp;";
    echo  "<div style='height:10px'></div>";


    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost)->required()->placeholder('Select Academic');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightYearGroupID)->required()->placeholder('Select Class');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->selected($pupilsightRollGroupID)->placeholder('Select Section');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));

    //$col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Go</button>');
    $col->addSearchSubmit($pupilsight->session, __('Clear Search'));
    echo $searchform->getOutput();
    echo  "<div style='height:10px'></div>";
    $CurriculamGateway = $container->get(CurriculamGateway::class);

    // QUERY
    $criteria = $CurriculamGateway->newQueryCriteria()
        ->pageSize(5000)
        ->sortBy('id')
        ->fromPOST();

    $general_tests = $CurriculamGateway->getAllgeneraltest($criteria, $pupilsightSchoolYearIDpost, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID);
    // print_r($general_tests);
    // DATA TABLE
    $table = DataTable::createPaginated('managetestedit', $criteria);

    //$table->addColumn('serial_number',__('Sl No'))
    //->notSortable();
    $table->addCheckBoxColumn('id', __(''));
    $table->addColumn('name', __('Test Name'));
    $table->addColumn('academic_year', __('Academic Year'));
    $table->addColumn('progname', __('Program'));
    $table->addColumn('classname', __('Class'));

    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($ac_remarks, $actions) {
            // $actions->addAction('copynew', __('Copy'))
            //         ->setURL('/modules/Finance/fee_structure_manage_copy.php');

            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Academics/modify_test_class_section_wise.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Academics/test_exam_delete.php');
        });

    echo $table->render($general_tests);
}
