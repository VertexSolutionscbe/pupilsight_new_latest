<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$studentids = $session->get('student_ids');


if (isActionAccessible($guid, $connection2, '/modules/Staff/select_staff_toAssign.php') == false) {
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
    echo __('Choose A Staff Member');
    echo '</h2>';


    $sqlp = 'SELECT a.pupilsightStaffID,a.staff_status AS stat,b.*, b.pupilsightPersonID AS stu_id , a.type, b.officialName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE b.pupilsightRoleIDPrimary NOT IN (003,004) ';
    $resultp = $connection2->query($sqlp);
    $getstaff= $resultp->fetchAll();
    


}
?>

<div style="width:40%;" >
    <input type="text" class="w-full" id="searchTable" placeholder="Search">
</div>

<form method="post" action="index.php?q=/modules/Staff/assign_staff_toClassSectionProcess.php">
    <button class="btn btn-primary" style="float:right;margin-bottom:10px;">Submit</button>
    <input type='hidden' name="stu_id" value="<?php echo $studentids; ?>">
    <table class="table" id="staffTable">
        <thead>
            <tr>
                <th><input type="checkbox" class="chkAll"></th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Department</th>
                <!-- <th>Status</th> -->
                
            </tr>
        </thead>
        <tbody>
            <?php
            if(!empty($getstaff)) { 
                foreach($getstaff as $staff){ 
                ?>
                <tr>
                    <th><input type="checkbox" name="staff[]" class="chkChild" value="<?php echo $staff['stu_id']; ?>"></th>
                    <th><?php echo $staff['name']; ?></th>
                    <th><?php echo $staff['email']; ?></th>
                    <th><?php echo $staff['phone1']; ?></th>
                    <th><?php echo $staff['type']; ?></th>
                    <?php /*
                    <th><?php echo $staff['stat']; ?></th>
                    */ ?>
                </tr>
            <?php   } } else { ?> 
                <tr>
                    <th colspan="7">No History</th>
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
    
    $("#searchTable").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#staffTable tbody tr:not(:first-child):not(:last-child)").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

</script>