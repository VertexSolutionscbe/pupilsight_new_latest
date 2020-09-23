<?php
/*
Pupilsight, Flexible & Open School System
*/

$session = $container->get('session');
$id = $session->get('staffs_id');

use Pupilsight\Domain\Staff\StaffGateway;
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Staff/select_staff_sub.php') == false) {
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
    echo __('Choose A Staff to subject');
    echo '</h2>';
   
$StaffGateway = $container->get(StaffGateway::class);
$criteria = $StaffGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();
        
$getselstaff = $StaffGateway->getselectedStaff($criteria);
    //print_r($getselstaff);die();
    $sqlp = 'SELECT a.pupilsightStaffID,a.staff_status AS stat,b.*, b.pupilsightPersonID AS stu_id , a.type, b.officialName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID';
    $resultp = $connection2->query($sqlp);
    $getstaff= $resultp->fetchAll();
    
  
    $sqld = 'SELECT name, pupilsightDepartmentID AS sub_id FROM pupilsightDepartment';
    $resultd = $connection2->query($sqld);
    $getsub= $resultd->fetchAll();

    echo "<a style='display:none' id='clickstaffunassign' href='fullscreen.php?q=/modules/Staff/remove_assigned_staffSub.php&width=800'  class='thickbox '>Unassign staff</a>"; 
   
    $getselectedstaff = [];
    ?>
    <form action="<?=$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assign_staff_toSubjectProcess.php';?>" method="post" autocomplete="on" enctype="multipart/form-data" class="smallIntBorder fullWidth standardForm" id="program">
        <input type="hidden" name="address" value="/modules/Staff/select_staff_sub.php">
        <div class='row'>
            <div class='col-12 text-right'>
                <button id="simplesubmitInvoice" style="height: 34px; float: right;"class=" btn btn-primary">Assign</button>
            </div>
        </div>
        <div class='row'>

            <div class='col-6'>
                <h3>Staff List</h3>
                <div>
                <?php
                    foreach($getstaff as $staff){
                        $staffName = $staff['name'];
                        if($staffName){
                            $staffid = $staff['pupilsightStaffID'];
                            echo "\n<div class='m-2' style='line-height:20px;'>";
                            echo "<input type=\"checkbox\" name=\"selected_sstaff[]\" id=\"".$staffid."\" value='".$staffid."'>";
                            echo "<label class='ml-2' for=\"".$staffid."\">".ucwords($staffName)."</label></div>";
                        }
                    }
                ?>
                </div>
            </div>
            <div class='col-6'>
                <h3>Subject List</h3>
                <div>
                <?php
                    foreach($getsub as $sub){
                        $subname = $sub['name'];
                        if($subname){
                            $subid = $sub['sub_id'];
                            echo "\n<div class='m-2' style='line-height:20px;'>";
                            echo "\n<input type=\"checkbox\" name=\"selected_sub[]\" id=\"".$subid."\" value='".$subid."'>";
                            echo "<label class='ml-2' for=\"".$subid."\">".ucwords($subname)."</label></div>";
                        }
                    }
                ?>
                </div>
            </div>
        </div>
        
    </form>
    <?php
}
