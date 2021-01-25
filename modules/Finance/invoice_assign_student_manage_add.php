<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_assign_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Invoice Assign'), 'invoice_assign_manage.php')
        ->add(__('Add Invoice Assign'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Finance/invoice_assign_manage_edit.php&id=' . $_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Generate Invoice by Students');
    echo '</h2>';
    //if(isset($_REQUEST['sid'])?$id=$_REQUEST['sid']:$id="" );
    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
    $sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
    $result = $pdo->executeQuery($data, $sql);

    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();
    if ($_POST) {
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    } else {
        $pupilsightProgramID = "";
        $pupilsightSchoolYearID = "";
    }

    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program = $program1 + $program2;

    $sqlc = 'SELECT a.pupilsightYearGroupID, a.name, b.id, GROUP_CONCAT(fn_fee_structure_id) AS fsid FROM pupilsightYearGroup AS a LEFT JOIN fn_fees_class_assign AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID GROUP BY a.pupilsightYearGroupID ORDER BY a.pupilsightYearGroupID ASC ';
    $resultc = $connection2->query($sqlc);
    $rowdatacls = $resultc->fetchAll();
    $firstClassId = $rowdatacls[0]['pupilsightYearGroupID'];


    // echo '<pre>';
    // print_r($rowdatacls);
    // echo '</pre>';
    $classes = array();
    $classes1 = array('' => 'Select Class');
    $classes2 = array();
    foreach ($rowdatacls as $dt) {
        $classes2[$dt['pupilsightYearGroupID']] = $dt['name'];
    }
    $classes = $classes1 + $classes2;

    $sqls = 'SELECT a.officialName AS student_name, a.pupilsightPersonID, GROUP_CONCAT(fn_fee_structure_id) AS fsid FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN fn_fees_student_assign AS c ON a.pupilsightPersonID = c.pupilsightPersonID WHERE a.pupilsightRoleIDAll = "003" AND b.pupilsightYearGroupID = ' . $firstClassId . ' GROUP BY a.pupilsightPersonID';
    $results = $connection2->query($sqls);
    $students = $results->fetchAll();
    // echo '<pre>';
    //     print_r($students);
    //     echo '</pre>';
    //die();


    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/invoice_assign_student_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    //$form->addHiddenValue('fn_fee_invoice_id', $id);

    //selected($firstClassId)->
    $row = $form->addRow();
    $row->addLabel('pupilsightProgramID', __('Program'));
    $row->addSelect('pupilsightProgramID')->setClass('program_cls')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();

    $row = $form->addRow();
    $row->addLabel('pupilsightYearGroupID1', __('Filter By Class'));
    $row->addSelectYearGroup('pupilsightYearGroupID1')->setclass('invoice_studentByClass w-full')->fromArray($classes)->placeholder();
    // $row = $form->addRow();
    // $row->addLabel('studentByClass', __('Filter By Class'));
    // $row->addSelectYearGroup('studentByClass')->fromArray($classes)->placeholder();

    $row = $form->addRow();

    $row->addTextField('studentByStudent')->placeholder('Search By Student Name');

    $row = $form->addRow()->setId('filterStudentByClass')->setClass('filterStudentBystudent');
    $row->addLabel('pupilsightYearGroupID', __(''));

    //$col = $row->addColumn()->setClass('newdes');
    //$col->addLabel('name', __('Student'));
    /*
    foreach ($students as $k => $cl) {
        if (empty($cl['fsid'])) {
            $content = '<span style="color:red;">(Not Assign)</span>';
            $col->addCheckbox('pupilsightPersonID[student][' . $cl['pupilsightPersonID'] . ']')->setDisabled('1')->description(__($content . ' ' . $cl['student_name']));
        } else {
            $content = '';
            $col->addCheckbox('pupilsightPersonID[student][' . $cl['pupilsightPersonID'] . ']')->description(__($content . ' ' . $cl['student_name']));
        }
    }*/

    $col = $row->addColumn()->setClass('newdes');

    //$col->addLabel('invoice_title', __('Fee Structure'));
    // foreach($students as $k => $cl){
    //     if(!empty($cl['fsid'])){

    //         $resultf = $connection2->query($sqlf);
    //         $rowdatafees = $resultf->fetchAll();
    //         $feesStructure = array();
    //         foreach ($rowdatafees as $dt) {
    //             $feesStructure[$dt['id']] = $dt['name'];
    //         }
    //         $col->addSelect('pupilsightPersonID[structure]['.$cl['pupilsightPersonID'].']')->fromArray($feesStructure)->required()->addClass('selbox'); 
    //     } else {
    //         $feesStructure = array();
    //         $col->addLabel('pupilsightPersonID[structure]['.$cl['pupilsightPersonID'].']', __(''))->setClass('hidelevel');
    //     }


    // }

    //     $row->addLabel('pupilsightYearGroupID', __('Class'));

    // $row->addCheckbox('pupilsightYearGroupID[]')->fromArray($classes)->required()->addCheckAllNone();

    // $row = $form->addRow();
    //     $row->addLabel('pupilsightRollGroupID', __('Section'));
    //     $row->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required();


    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();
} ?>
<script>
    $(document).on('keyup', '#studentByStudent', function() {
        var value = $(this).val().toLowerCase();
        $(".inline label.leading-normal").filter(function() {
            $(this).parent().toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    $(document).on('change', ".program_cls", function() {
        var id = $(this).val();
        var type = 'getClass';
        if (id != "") {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: {
                    val: id,
                    type: type
                },
                async: true,
                success: function(response) {
                    $("#pupilsightYearGroupID1").html();
                    $("#pupilsightYearGroupID1").html(response);
                    $("#pupilsightYearGroupID1").trigger('change');
                }
            });
        }
    });
</script>