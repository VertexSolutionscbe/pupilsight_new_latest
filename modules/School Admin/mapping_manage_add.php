<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/mapping_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Mapping'), 'mapping_manage.php')
        ->add(__('Add Mapping'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/mapping_manage_edit.php&pupilsightMappingID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }

    // $sql = 'SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup WHERE pupilsightSchoolYearID=' . $pupilsightSchoolYearID . '  ';
    $sql = 'SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup WHERE pupilsightSchoolYearID = '.$pupilsightSchoolYearID.'  ';
    $result = $connection2->query($sql);
    $classes = $result->fetchAll();

    $classData = array();
    foreach ($classes as $dt) {
        $classData[$dt['pupilsightYearGroupID']] = $dt['name'];
    }

    
    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
    $sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
    $result = $pdo->executeQuery($data, $sql);

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

    $form = Form::create('mapping', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/mapping_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
            $row->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required()->placeholder();

    $row = $form->addRow();
            $row->addLabel('pupilsightProgramID', __('Program'));
            $row->addSelect('pupilsightProgramID')->setId('programIdMapping')->fromArray($program)->required()->placeholder();

    $row = $form->addRow();
        $row->addLabel('pupilsightYearGroupID', __('Class'));
        $row->addSelect('pupilsightYearGroupID')->setId('classIdMapping')->fromArray($classData)->required()->placeholder('Select Class');

    $row = $form->addRow();
        $row->addLabel('pupilsightRollGroupID', __('Section'));
        $row->addSelect('pupilsightRollGroupID')->setId('sectionIdMapping')->selectMultiple()->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
?>

<script>

    $(document).on('change', '#pupilsightSchoolYearID', function() {
        var id = $(this).val();
        var type = 'getClassByAcademicYear';
        if (id != "") {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: id, type: type},
                async: true,
                success: function(response) {
                    $("#classIdMapping").html();
                    $("#classIdMapping").html(response);
                }
            });
        } else {
            alert('Please Select Academic Year');
        }
    });

    $(document).on('change', '#classIdMapping', function() {
        var id = $(this).val();
        var pid = $("#programIdMapping").val();
        var aid = $("#pupilsightSchoolYearID").val();
        var type = 'getSectionByClassProgForMapping';
        if (pid != "") {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: id, type: type, pid: pid, aid: aid },
                async: true,
                success: function(response) {
                    $("#sectionIdMapping").html();
                    $("#sectionIdMapping").html(response);
                }
            });
        } else {
            alert('Please Select Program');
        }
    });
</script>