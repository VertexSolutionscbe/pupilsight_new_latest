<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$studentids = $session->get('student_ids');


if (isActionAccessible($guid, $connection2, '/modules/School Admin/mapping_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('View Staff Profiles'));
    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_structure_assign_student_manage_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Choose Class Teacher');
    echo '</h2>';


    $sqlp = 'SELECT b.pupilsightPersonID, b.officialName, b.email FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE b.officialName != "" AND b.pupilsightRoleIDPrimary NOT IN (003,004) ';
    $resultp = $connection2->query($sqlp);
    $getstaff= $resultp->fetchAll();

    $pupilsightMappingID = $_GET['mid'];
    $pupilsightSchoolYearID = $_GET['aid'];
    $pupilsightProgramID = $_GET['pid'];
    $pupilsightYearGroupID = $_GET['cid'];
    $pupilsightRollGroupID = $_GET['sid'];
    
?>
<div>
    <div style="width:40%; margin-bottom:10px; float:left;" >
        <input type="text" class="w-full" id="searchTable" placeholder="Search">
    </div>
    <div style="margin-bottom:10px;float:right;" >
        <a class="btn btn-primary" id="saveClassTeacher" data-mid="<?php echo $pupilsightMappingID;?>" data-aid="<?php echo $pupilsightSchoolYearID;?>" data-pid="<?php echo $pupilsightProgramID;?>" data-cid="<?php echo $pupilsightYearGroupID;?>" data-sid="<?php echo $pupilsightRollGroupID;?>">Save</a>
    </div>
</div>
<table class="table" id="staffData">
    <thead>
        <tr>
            <th>Select</th>
            <th>Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
    <?php
        if(!empty($getstaff)){
            foreach($getstaff as $sd){
    ?>
        <tr>
            <td><input type="radio" name="pupilsightPersonID" class="staffid" value="<?php echo $sd['pupilsightPersonID']?>"></td>
            <td><?php echo $sd['officialName']?></td>
            <td><?php echo $sd['email']?></td>
        </tr>
    <?php } } ?>
    </tbody>
</table>

<?php }?>

<script>
    $("#searchTable").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#staffData tbody tr:not(:first-child):not(:last-child)").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $(document).on('click', '#saveClassTeacher', function () {
        var mid = $(this).attr('data-mid');
        var aid = $(this).attr('data-aid');
        var pid = $(this).attr('data-pid');
        var cid = $(this).attr('data-cid');
        var sid = $(this).attr('data-sid');
        var stid = $(".staffid:checked").val();
        var type = 'assignClassTeacher';
        if (stid != "") {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: stid, type: type, pid: pid, aid: aid, cid: cid, sid: sid, mid: mid },
                async: true,
                success: function (response) {
                    alert('Class Teacher Assign Successfully!');
                    location.reload();
                }
            });
        } else {
            alert('Please Select Staff');
        }
    });
</script>