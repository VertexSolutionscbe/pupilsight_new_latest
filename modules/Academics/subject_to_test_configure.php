<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/subject_to_test.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Manage Skills'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $aid = $_GET['aid'];
    $pid = $_GET['pid'];
    $cid = $_GET['cid'];
    $did = $_GET['did'];

    $kid = $_GET['kid'];
    $type = $_GET['type'];

    $sqlskl = 'SELECT * FROM subjectSkillMapping WHERE pupilsightSchoolYearID = '.$aid.' AND pupilsightProgramID = '.$pid.' AND pupilsightYearGroupID = '.$cid.'  AND pupilsightDepartmentID = '.$did.' ';
    $resultskl = $connection2->query($sqlskl);
    $getSkills = $resultskl->fetchAll();


    echo '<h3>';
    echo __('Configure');
    echo '</h3>';

    $types = array('None' => 'None', 'Sum' => 'Sum', 'Average' => 'Average', 'Percentage' => 'Percentage');

    foreach ($types as $tp) {
        if($tp == $type){
            $checked = 'checked';
        } else {
            $checked = '';
        }
        echo ' <input type="radio" name="configure" value="'.$tp.'" class="configureVal" '.$checked.' > <span class="showRadio" >'.$tp.'</span> ';
    }

        echo '<a style="cursor:pointer;float: right;" id="configureSet" data-id="'.$kid.'" class="btn btn-primary">OK</a>';

    if(!empty($getSkills)){
        echo '<div id="showPerc" style="display:none;"><form id="skillConfigureForm"><table style="margin: 20px;" class="table"> <thead><th>Skills</th><th>Weightage</th></thead><tbody>';

        foreach($getSkills as $skl){
            echo '<tr><td>'.$skl['skill_display_name'].'</td><td><input type="textbox" style="border:1px solid grey;" name="weightage['.$skl['skill_id'].']"></td></tr>';
        }
        echo '</tbody></table>';

        echo '<span style="font-size: 16px;margin: 20px;">Select Formula</span><select name="weightage_formula" style="float:none;"><option>Select Formula</option><option value="Sum">Sum</option><option value="Average">Average</option></select></form></div>';
    }
}


?>

<style>
    .showRadio {
        font-size: 14px;
    }
</style>

<script>
    $(document).on('change', '.configureVal', function() {
        var val = $(".configureVal:checked").val();
        if (val == 'Percentage') {
            $("#showPerc").show();
        } else {
            $("#showPerc").hide();
        }
    });
</script>