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

if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_staff_toClassSection_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $pupilsightSchoolYearID = '';
        if (isset($_GET['pupilsightSchoolYearID'])) {
            $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
        }
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

        $HelperGateway = $container->get(HelperGateway::class);

        if($_POST){        
            $pupilsightProgramID =  $_POST['pupilsightProgramID'];
            $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];  
            
            $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
            $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);
          
            $stuId = $_POST['studentId'];
        } else {
            $classes = array('' => 'Select Class');
            $sections = array('' => 'Select Section');
            $pupilsightProgramID =  '';
            $pupilsightYearGroupID =  '';
            $pupilsightRollGroupID =  '';
          
            $stuId = '0';
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
        $page->breadcrumbs->add(__('Assign Staff to Class and Section'));
    }
    
    echo "<a style='display:none' id='clickStudentPage' href='fullscreen.php?q=/modules/Staff/select_staff_toAssign.php&width=1200'  class='thickbox '>Change Route</a>";   
    echo "<div style='height:50px;'><div class='float-left mb-2'><a  id='assignStudentPage' data-type='staff' class='btn btn-primary'>Assign</a>&nbsp;&nbsp;";  
    echo "</div><div class='float-none'></div></div>";
    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightProgramID', __('Program'));
        $col->addSelect('pupilsightProgramID')->setID('getMultiClassByProgStaff')->fromArray($program)->required()->selected($pupilsightProgramID)->placeholder();

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightYearGroupID', __('Class'));
        $col->addSelect('pupilsightYearGroupID')->setID('showMultiClassByProgStaff')->fromArray($classes)->selected($pupilsightYearGroupID)->required();

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightRollGroupID', __('Section'))->addClass('dte');
        $col->addSelect('pupilsightRollGroupID')->setID('showMultiSecByProgClsStaff')->fromArray($sections)->required()->selected($pupilsightRollGroupID)->selectMultiple();
        
        $col = $row->addColumn()->setClass('newdes');   
        $col->addLabel('', __(''));
        $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');  
        echo $searchform->getOutput();

    $StaffGateway = $container->get(StaffGateway::class);
    $criteria = $StaffGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->pageSize('5000')
        ->fromPOST();

   $students = $StaffGateway->getStudentData($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID);

$table = DataTable::createPaginated('FeeStructureManage', $criteria);
    $table->addCheckboxColumn('stuid',__(''))
->setClass('chkbox')
->notSortable();
$table->addColumn('program', __('Program'));
$table->addColumn('yearGroup', __('Class'));
$table->addColumn('rollGroup', __('Section'));


echo $table->render($students);

    
}
?>

<script>

$(document).ready(function () {
    $('#showMultiSecByProgClsStaff').selectize({
        plugins: ['remove_button'],
    });
});

$(document).on('change', '#getMultiClassByProgStaff', function () {
    var id = $(this).val();
    var type = 'getClass';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type },
        async: true,
        success: function (response) {
            $("#showMultiClassByProgStaff").html();
            $("#showMultiClassByProgStaff").html(response);
        }
    });
});

$(document).on('change', '#showMultiClassByProgStaff', function () {
    var id = $(this).val();
    var pid = $('#getMultiClassByProgStaff').val();
    var type = 'getSection';
    $('#showMultiSecByProgClsStaff').selectize()[0].selectize.destroy();
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pid: pid },
        async: true,
        success: function (response) {
            $("#showMultiSecByProgClsStaff").html();
            $("#showMultiSecByProgClsStaff").html(response);
            $('#showMultiSecByProgClsStaff').selectize({
                plugins: ['remove_button'],
            });
        }
    });
});
</script>