<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway ;

if (isActionAccessible($guid, $connection2, '/modules/Student/assign_subject_student.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

    $CurriculamGateway = $container->get(CurriculamGateway::class);
    $criteria = $CurriculamGateway->newQueryCriteria()
                    ->pageSize('100000');
    

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    //Proceed!
    $pupilsightYearGroupID = $_GET['cid'];
    $pupilsightProgramID = $_GET['pid'];
    $pupilsightRollGroupID = $_GET['sid'];
    $studentId = $_GET['stid'];
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    $electiveSubjects =  $CurriculamGateway->getStudentElectiveSubjectClassSectionWise($criteria,$pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID);

    // echo '<pre>';
    // print_r($electiveSubjects);
    // echo '</pre>';
    // die();

    
    $page->breadcrumbs->add(__('Add Subject'));

    echo '<h2>';
    echo __('Add Subject');
    echo '</h2>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

?>
<input type="hidden" id="progId" value="<?php echo $pupilsightProgramID?>">
<input type="hidden" id="classId" value="<?php echo $pupilsightYearGroupID?>">
<input type="hidden" id="secId" value="<?php echo $pupilsightRollGroupID?>">
<input type="hidden" id="stuid" value="<?php echo $studentId;?>">
<button type="button" id="assignSubjecttoStudent" class="btn btn-primary" style="float:right;margin-bottom:10px;">Assign</button>
<form method="post" action="" >
    <table class="table">
        <thead>
            <tr>    
                <th>Select</th>
                <th>Subject</th>
            </tr>
        </thead>

        <tbody>
        <?php if(!empty($electiveSubjects)){ 
            foreach($electiveSubjects as $ele){    
        ?>
            <tr>    
                <td></td>
                <td><i class="mdi mdi-folder-outline "></i>&nbsp;<?php echo $ele['name'];?></td>
            </tr>
            <?php if(!empty($ele['elective'])) { 
                foreach($ele['elective'] as $el){    
            ?>
            <tr>    
                <td><input type="checkbox" name="subject_id[]" value="<?php echo $el['pupilsightDepartmentID'];?>"></td>
                <td><i class="mdi mdi-file-outline "></i>&nbsp;<?php echo $el['subject_display_name'];?></td>
            </tr>
        <?php } } } } ?>
        </tbody>
    </table>
</form>
<?php
}
?>

<script>
    $(document).on('click', '#assignSubjecttoStudent', function() {
        var favorite = [];
        $.each($("input[name='subject_id[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var subId = favorite.join(",");
        //alert(subid);
        if (subId) {
            var val = subId;
            var type = 'assignBulkSubject';
            var stuid = $("#stuid").val();
            var pid = $("#progId").val();
            var cid = $("#classId").val();
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: {
                        val: val,
                        type: type,
                        stuid: stuid,
                        pid: pid,
                        cid: cid
                    },
                    async: true,
                    success: function(response) {
                        alert('Subject Assign Successfully!');
                        location.reload();
                    }
                });
            }
        } else {
            alert('You Have to Select Subject.');
        }
    });
</script>