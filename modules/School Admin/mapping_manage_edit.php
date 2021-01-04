<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/mapping_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Mapping'), 'mapping_manage.php')
        ->add(__('Edit Mapping'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightMappingID = $_GET['pupilsightMappingID'];
    if ($pupilsightMappingID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightMappingID' => $pupilsightMappingID);
            $sql = 'SELECT * FROM pupilsightProgramClassSectionMapping WHERE pupilsightMappingID=:pupilsightMappingID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();
            // echo '<pre>';
            // print_r($values);
            // echo '</pre>';

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
            $sql = 'SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup ';
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

            $form = Form::create('mapping', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/mapping_manage_editProcess.php?pupilsightMappingID='.$pupilsightMappingID);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
            $row->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($values['pupilsightSchoolYearID'])->required()->placeholder();

            $row = $form->addRow();
            $row->addLabel('pupilsightProgramID', __('Program'));
            $row->addSelect('pupilsightProgramID')->setId('programId')->fromArray($program)->selected($values['pupilsightProgramID'])->required()->placeholder();

            $row = $form->addRow();
                $row->addLabel('pupilsightYearGroupID', __('Class'));
                $row->addSelect('pupilsightYearGroupID')->setId('classId')->fromArray($classData)->selected($values['pupilsightYearGroupID'])->required()->placeholder('Select Class');

            $row = $form->addRow();
                $row->addLabel('pupilsightRollGroupID', __('Section'));
                $row->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->setId('sectionId')->selected($values['pupilsightRollGroupID'])->required();
           
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
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
                    $("#classId").html();
                    $("#classId").html(response);
                }
            });
        } else {
            alert('Please Select Academic Year');
        }
    });

</script>