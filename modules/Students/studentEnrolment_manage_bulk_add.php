<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$studentids = $session->get('student_ids');

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Student Enrolment'), 'studentEnrolment_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Add Student Enrolment'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/studentEnrolment_manage_edit.php&pupilsightStudentEnrolmentID='.$_GET['editID'].'&search='.$_GET['search'].'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    //Check if school year specified
    if ($pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        if ($search != '') {
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/studentEnrolment_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
            echo '</div>';
        }

        $form = Form::create('studentEnrolmentAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/studentEnrolment_manage_bulk_addProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('stu_id', $studentids);
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

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

        $schoolYearName = ($result->rowCount() == 1)? $result->fetchColumn(0) : $_SESSION[$guid]['pupilsightSchoolYearName'];

        $row = $form->addRow();
            $row->addLabel('yearName', __('School Year'));
            $row->addTextField('yearName')->readOnly()->maxLength(20)->setValue($schoolYearName);

        
        $row = $form->addRow();
            $row->addLabel('pupilsightProgramID', __('Program'));
            $row->addSelect('pupilsightProgramID')->fromArray($program)->required()->placeholder();

        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID', __('Class'));
            $row->addSelectYearGroup('pupilsightYearGroupID')->required();

        $row = $form->addRow();
            $row->addLabel('pupilsightRollGroupID', __('Section'));
            $row->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID);

        
        
        $row = $form->addRow();
            $row->addFooter();
            $row->addContent('<a class="btn btn-primary" id="addStuEnroll">Submit</a>');

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

    $(document).on('click', '#addStuEnroll', function () {
        var pid = $("#pupilsightProgramID").val();
        var cid = $("#pupilsightYearGroupID").val();
        var formData = new FormData(document.getElementById("studentEnrolmentAdd"));
        if(pid != '' && cid != ''){
            $.ajax({
                url: "modules/Students/studentEnrolment_manage_bulk_addProcess.php",
                type: "POST",
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                async: false,
                success: function (response) {
                    alert('Student Enrolled Successfully!');
                    $("#studentViewSearch").submit();
                }
            });
        } else {
            alert('Please Select Mandatory Field!');
        }
        
    });
 </script>   