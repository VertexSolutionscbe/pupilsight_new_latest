<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute_plugin.php') == false)
{ 
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    
    echo '<h2>';
    echo __('Subjects');
    echo '</h2>';

    $sketchId = $_GET['skid'];

    $sqlchk = "SELECT  pupilsightSchoolYearID, pupilsightProgramID, class_ids FROM examinationReportTemplateSketch   WHERE id = ".$sketchId." ";
    $resultchk = $connection2->query($sqlchk);
    $chkdata = $resultchk->fetch();

    $sqld = 'SELECT a.name, a.pupilsightDepartmentID AS sub_id, b.subject_display_name FROM pupilsightDepartment AS a LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE b.pupilsightSchoolYearID = '.$chkdata['pupilsightSchoolYearID'].' AND b.pupilsightProgramID = '.$chkdata['pupilsightProgramID'].' AND b.pupilsightYearGroupID IN ('.$chkdata['class_ids'].') GROUP BY a.pupilsightDepartmentID ';
    $resultd = $connection2->query($sqld);
    $subjectData= $resultd->fetchAll();
    


}
?>

<div style="width:40%;" >
    <input type="text" class="w-full" id="searchTable2" placeholder="Search">
</div>

<form>
    <a class="btn btn-primary" style="float:right;margin-bottom:10px;" id="addSubjectToSketch">Submit</a>
    <table class="table" id="subjectTable">
        <thead>
            <tr>
                <th><input type="checkbox" class="chkAll"></th>
                <th>Subject Name</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
            if(!empty($subjectData)) { 
                foreach($subjectData as $sub){ 
                ?>
                <tr>
                    <th><input type="checkbox" name="subid[]" class="chkChild" value="<?php echo $sub['sub_id']; ?>"></th>
                    <th><?php echo $sub['subject_display_name']; ?></th>
                </tr>
            <?php   } } else { ?> 
                <tr>
                    <th colspan="2">No Data</th>
                </tr>
            <?php } ?>
        </tbody>

    </table>
</form>


<style>
    #program table.smallIntBorder td {
        min-width: 142px;
        width: auto !important;
    }
    .min_width_check
    {
        min-width: 40px!important;

    }
    .margin_check
    {
        margin-top:2px;
    }
    .mrlft 
    {
        margin-left: 18px;
    }

</style>

<script>
    $(document).on('click', '#addSubjectToSketch', function() {
        var favorite = [];
        $.each($("input[name='subid[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var subid = favorite.join(",");
        $("#selectedSubject").val(subid);
        $("#TB_closeWindowButton").click();
    });
    
    $("#searchTable2").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#subjectTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

</script>