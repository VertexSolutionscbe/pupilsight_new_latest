<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Staff/feedback_category_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Manage Feedback Category'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    echo '<h3>';
    echo __('Add Feedback Category');
    echo '</h3>';

    $staff_id = $_GET['stid'];

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

    $sqlt = 'SELECT id, name FROM pupilsightFeedbackCategory ';
    $resultt = $connection2->query($sqlt);
    $catData = $resultt->fetchAll();

    $category=array();  
    $category2=array();  
    $category1=array(''=>'Select Category');
    foreach ($catData as $dt) {
        $category2[$dt['id']] = $dt['name'];
    }
    $category= $category1 + $category2;  
    echo '<input type="hidden" id="staffId" value="'.$staff_id.'">';
    $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/feedback_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
    $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
    $form->addHiddenValue('staff_id', $staff_id); 
 
    $row = $form->addRow();        
        //$row = $row->addColumn()->setClass('newdes');
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->addClass('txtfield')->required();

    $row = $form->addRow();
        //$row = $row->addColumn()->setClass('newdes');
        $row->addLabel('category_id', __('Category'));
        $row->addSelect('category_id')->fromArray($category);

    $row = $form->addRow();    
        //$row = $row->addColumn()->setClass('newdes');
        $row->addLabel('pupilsightProgramID', __('Program'));
        $row->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDforStaff')->fromArray($program);
        
    $row = $form->addRow();
        //$row = $row->addColumn()->setClass('newdes');
        $row->addLabel('pupilsightYearGroupID', __('Class'));
        $row->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDforStaff')->placeHolder('Select Class');

    $row = $form->addRow();
        //$row = $row->addColumn()->setClass('newdes');
        $row->addLabel('pupilsightRollGroupID', __('Section'));
        $row->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDforStaff')->placeHolder('Select Staff');

    $row = $form->addRow();
        //$row = $row->addColumn()->setClass('newdes');
        $row->addLabel('pupilsightDepartmentID', __('Subjects'));
        $row->addSelect('pupilsightDepartmentID')->placeHolder('Select Subject');

    $row = $form->addRow();
        //$row = $row->addColumn()->setClass('newdes');
        $row->addLabel('feedback_date', __('Date'));
        $row->addDate('feedback_date');

    $row = $form->addRow();
        //$row = $row->addColumn()->setClass('newdes');
        $row->addLabel('description', __('Description'));
        $row->addTextArea('description');

       
    $row = $form->addRow();
        
        $row->addLabel('', __(''))->addClass('dte');
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();
  
}
?>


<script>

    $(document).on('change', '#pupilsightProgramIDforStaff', function () {
        var id = $(this).val();
        var stid = $("#staffId").val();
        var type = 'getClassForStaff';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, stid: stid },
            async: true,
            success: function (response) {
                $("#pupilsightDepartmentID").html('');
                $("#pupilsightRollGroupIDforStaff").html('');
                $("#pupilsightYearGroupIDforStaff").html();
                $("#pupilsightYearGroupIDforStaff").html(response);
            }
        });
    });

    $(document).on('change', '#pupilsightYearGroupIDforStaff', function () {
        var id = $(this).val();
        var stid = $("#staffId").val();
        var pid = $('#pupilsightProgramID').val();
        var type = 'getSectionForStaff';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid, stid: stid },
            async: true,
            success: function (response) {
                $("#pupilsightRollGroupIDforStaff").html();
                $("#pupilsightRollGroupIDforStaff").html(response);
            }
        });
    });

    $("#pupilsightYearGroupIDforStaff").change(function() {
        loadSubjects();
    });
    

    function loadSubjects() {
        var pupilsightProgramID = $("#pupilsightProgramIDforStaff").val();
        var pupilsightYearGroupID = $("#pupilsightYearGroupIDforStaff").val();
        var stid = $("#staffId").val();
        //var roleid= $("#roleid").val();
        if (pupilsightYearGroupID) {
            var type = "getSubjectForStaff";
            try {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: {
                        val: pupilsightYearGroupID,
                        type: type,
                        pupilsightProgramID:pupilsightProgramID,
                        stid: stid

                    },
                    async: true,
                    success: function(response) {
                        $("#pupilsightDepartmentID").html(response);
                        if (reloadCall) {
                            $("#pupilsightDepartmentID").val(_pupilsightDepartmentID);
                            
                        }
                    }
                });
            } catch (ex) {
                reloadCall = false;
            }
        }
    }
</script>