<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\School\ProgramGateway;

use Pupilsight\Domain\Helper\HelperGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Attendance/staff_attendance_configSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Staff Attendance Configuration'), 'staff_attendance_configSettings.php');

    $editLink = '';
    if (isset($_GET['editID'])) {
      //  $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Attendance/attendanceSettings_manage_add.php&pupilsightDepartmentID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $ProgramGateway = $container->get(ProgramGateway::class);

    // QUERY
    $criteria = $ProgramGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber'])
        ->fromPOST();

    $dataSet = $ProgramGateway->attendanceSettingsForStaff($criteria);
    
    
    // DATA TABLE
    $table = DataTable::createPaginated('programManage', $criteria);

    
    $table->addHeaderAction('add', __('Add'))
    ->setURL('/modules/Attendance/staff_attendance_configSettings_manage_add.php')
    ->displayLabel();
   

    echo $table->render($dataSet);

     ?>
    <table class='table'>
        <thead>
            <tr>
                <th>Organisation</th>
                <!-- <th>Classes</th> -->
                <th>Attendance Type</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataSet as $val) { 
                $edit=$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Attendance/staff_attendance_configSettings_manage_edit.php&id=".$val['id'];
                $del="fullscreen.php?q=/modules/Attendance/attendance_configSettings_manage_delete.php&id=".$val['id']."&width=650&height=135";
                $cls="";
                $clIds=explode(',',$val['pupilsightYearGroupID']);
                foreach ($clIds as $clid) {
                $sqlp = 'SELECT name FROM pupilsightYearGroup WHERE pupilsightYearGroupID ="'.$clid.'"';
                $resultp = $connection2->query($sqlp);
                $class = $resultp->fetch();
                $cls.=$class['name']." ";
                }
                ?>
            <tr>
                <td><?php echo $val['program_name'];?></td>
                <!-- <td><?php /* echo $cls; */ ?></td> -->
                <td>
                <?php if($val['attn_type']=="1"){
                     echo "Session";
                } else {
                    echo "Subject";
                }
                ?>
                </td>
                <td>
                    <a class="" href="<?php echo $edit;?>"> <i title="Edit" class="mdi mdi-pencil-box-outline mdi-24px px-2"></i></a>
                    <a href="javascript:void(0)" class="del_data" data-id="<?php echo $val['id'];?>" ><i title="Delete" class="mdi mdi-trash-can-outline mdi-24px px-2"></i></a>
                   <!--  <a class="thickbox" href="fullscreen.php?q=%2Fmodules%2FAttendance%2Fattendance_configSettings_manage_delete.php&amp;id=12&amp;width=650&amp;height=135"> <i title="Delete" class="mdi mdi-trash-can-outline mdi-24px px-2"></i></a> -->
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php 
}
?>
<script type="text/javascript">
    $(document).on('click','.del_data',function(){
       var id=$(this).attr('data-id');
       var type ="del_attendance_configSettings_staff";
       var r = confirm("Are you sure want to delete ?");
    if (r == true) {
        $.ajax({
        url: 'attendanceSwitch.php',
        type: 'post',
        data: { type: type,id:id },
        async: true,
        success: function(response) {
            window.location.reload();
        }
        });
    } 
    });
</script>