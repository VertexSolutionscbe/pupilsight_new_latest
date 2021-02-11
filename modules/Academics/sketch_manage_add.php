<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Manage Sketch'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    echo '<h3>';
    echo __('Add Sketch');
    echo '</h3>';

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

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

    $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/sketch_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
 
    $row = $form->addRow();        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('sketch_name', __('Name'));
        $col->addTextField('sketch_name')->addClass('txtfield')->required();
    
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('sketch_code', __('Sketch Code'));
        $col->addTextField('sketch_code')->addClass('txtfield')->required();

    $row = $form->addRow();         

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightProgramID', __('Program'));
        $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDSketch')->fromArray($program)->required()->placeholder('Select Program');

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('class_ids', __('Class'));
        $col->addSelect('class_ids')->setId('pupilsightYearGroupID')->selectMultiple()->required()->placeholder('Select Class');
        
        $col->addContent('<input type="hidden" name="pupilsightSchoolYearID" value='.$pupilsightSchoolYearID.'>');
   
    $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();
  
}
?>

<style>
    .text-xxs {
        display:none;
    }
    #pupilsightYearGroupID {
        margin: 23px 41px 0 0px;
    }
    
</style>

<script>
    $(document).ready(function () {
      	$('#pupilsightYearGroupID').selectize({
      		plugins: ['remove_button'],
      	});
    });

    $(document).on('change', '#pupilsightProgramIDSketch', function () {
        var id = $(this).val();
        var type = 'getClass';
        $('#pupilsightYearGroupID').selectize()[0].selectize.destroy();
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type },
            async: true,
            success: function (response) {
                $("#pupilsightYearGroupID").html();
                $("#pupilsightYearGroupID").html(response);
                $("#pupilsightYearGroupID").parent().children('.LV_validation_message').remove();
                $('#pupilsightYearGroupID').selectize({
                    plugins: ['remove_button'],
                });
            }
        });
    });
</script>