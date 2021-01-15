<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$studentids = $_GET['sid'];

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    $page->breadcrumbs
        ->add(__('Student Enrolment'), 'studentEnrolment_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Add Student Enrolment'));

   
    //Check if school year specified
    if ($pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        
        $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
        $resulta = $connection2->query($sqla);
        $academic = $resulta->fetchAll();

        $academicData = array();
        foreach ($academic as $dt) {
            $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
        }

        $form = Form::create('studentEnrolmentAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/detain_student_addProcess.php");
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('stu_id', $studentids);
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        

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

        

        
        $row = $form->addRow();
            $row->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID);

        $row = $form->addRow();
            $row->addLabel('pupilsightProgramID', __('Program'));
            $row->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDSchool')->fromArray($program)->required()->placeholder();

        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID', __('Class'));
            $row->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDSchool')->required();

        $row = $form->addRow();
            $row->addLabel('pupilsightRollGroupID', __('Section'));
            $row->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDSchool');

   
        
        $row = $form->addRow();
            $row->addFooter();
            $row->addContent('<a class="btn btn-primary" id="detainStudentinSameClass">Submit</a>');

        echo $form->getOutput();
    }
}
?>

<style>
    /* to avoid multiple occurence of popup in same window   */
    #TB_ajaxContent,#TB_title {
        display:none;
    }
   </style> 
<script>
$(document).ready(function() {
    $("div #TB_title:eq(0)").css( 'display', 'block');
    $("div #TB_ajaxContent:eq(0)").css( 'display', 'block');
});
/* to avoid multiple occurence of popup in same window   */

    $(document).on('click', '#detainStudentinSameClass', function () {
        var pid = $("#pupilsightProgramID").val();
        var cid = $("#pupilsightYearGroupID").val();
        var formData = new FormData(document.getElementById("studentEnrolmentAdd"));
        if(pid != '' && cid != ''){
            if (confirm("Do you want to assign same class/section for this student for next year?")) {
                $.ajax({
                    url: "modules/Students/detain_student_addProcess.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    async: false,
                    success: function (response) {
                        alert('Student Detain Successfully!');
                        $("#studentViewSearch").submit();
                    }
                });
            }
        } else {
            alert('Please Select Mandatory Field!');
        }
        
    });
 </script>   