<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Domain\Helper\HelperGateway;
if (isActionAccessible($guid, $connection2, '/modules/Students/student_tc_history.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Student TC History'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $studentGateway = $container->get(StudentGateway::class);

    // QUERY
    $criteria = $studentGateway->newQueryCriteria()
        ->pageSize(1000)
        ->sortBy(['id'])
        ->fromPOST();

    $classes = array('' => 'Select Class');
    $sections = array('' => 'Select Section');

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

    $HelperGateway = $container->get(HelperGateway::class);
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    if ($_POST) {
        $input = $_POST;
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        $search = $_POST['search'];

        $roleId = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
        $uid = $_SESSION[$guid]['pupilsightPersonID'];

        if ($roleId == '2') {
            $classes =  $HelperGateway->getClassByProgramForTeacher($connection2, $pupilsightProgramID, $uid);
            $sections =  $HelperGateway->getSectionByProgramForTeacher($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $uid);
        } else {
            $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
            $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);
        }

        // if (empty($pupilsightProgramID)) {
        //     unset($_SESSION['student_search']);
        // }
    } else {
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $pupilsightProgramID = '';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID = '';
        $search = '';
        $input = '';
        // unset($_SESSION['student_search']);
    }
    // if (!empty($pupilsightProgramID)) {
    //     $_SESSION['student_search'] = $input;
    // }
    
    $tcHistory = $studentGateway->getTCHistoryByAdmin($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID, $search);

    $form = Form::create('studentViewSearch', '');

    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPP')->fromArray($program)->selected($pupilsightProgramID)->placeholder('Select Program');


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPP')->fromArray($classes)->selected($pupilsightYearGroupID)->placeholder('Select Class');


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDbyPP')->fromArray($sections)->selected($pupilsightRollGroupID)->placeholder('Select Section');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('search', __('Search For'))
        ->description($searchDescription);
    $col->addTextField('search')->setValue($search);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
    $col->addSearchSubmit($pupilsight->session, __('Clear Search'));



    echo $form->getOutput();


    // DATA TABLE

    
    $table = DataTable::createPaginated('programManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/School Admin/leaveReason_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a id='revertTC' class='btn btn-primary'>Revert</a></div><div class='float-none'></div></div>";  

    
    $table->addCheckBoxColumn('pupilsightPersonID', __(''));
    $table->addColumn('serial_number', __('Sl No'));
    $table->addColumn('program', __('Program'));
    $table->addColumn('class', __('Class'));
    $table->addColumn('section', __('Section'));
    $table->addColumn('studentName', __('Student Name'));
    $table->addColumn('file_path', __('File'))
        ->format(function ($tcHistory) {
            if (!empty($tcHistory['file_path'])) {
                return '<a href="public/student_tc/'.$tcHistory['file_path'].'" download><i title="Download" class="mdi mdi-file-download mdi-24px download_icon"></i></a>';
            } 
            return $tcHistory['file_path'];
        });
    $table->addColumn('cdt', __('Date'));
    
    echo $table->render($tcHistory);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
?>
<script>
    $(document).on('click', '#revertTC', function() {
        var favorite = [];
        $.each($("input[name='pupilsightPersonID[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var stuId = favorite.join(",");
        //alert(subid);
        if (stuId) {
            if (confirm("Do you want to Revert TC?")) {
                var val = stuId;
                var type = 'revertTC';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: val,
                            type: type
                        },
                        async: true,
                        success: function(response) {
                            alert('TC Revert Successfully!');
                            location.reload();
                        }
                    });
                }
            }
        } else {
            alert('You Have to Select Student.');
        }
    });

   
</script>