<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$session = $container->get('session');
$formData = $session->get('attendanceByRollGroupFormData');

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byRollGroupListView.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Save Attendance'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    $count = $formData['count'];
   ?>
   <table class='table'>
       <thead>
        <tr>
            <th colspan="4">Absent List</th>
        </tr>
           <tr>
               <th>Name</th>
               <th>Admission no</th>
               <th>Attendance</th>
               <th>
                <center>
                <input type="checkbox" name="checkall" id="checkall" class=" checkall">
                </center>
            </th>
           </tr>
       </thead>
       <body>
        <?php for ($i = 0; $i < $count; ++$i) {
            if($formData[$i.'-type']!="Present - Late" AND $formData[$i.'-type']!="Present"){
         ?>
           <tr>
            <td><?php echo $formData[$i.'-pupilsightPersonName'];?></td>
            <td><?php echo $formData[$i.'-admno'];?></td>
            <td><?php echo $formData[$i.'-type'];?></td>
            <td><center>
                <input type="checkbox" name="sms_users" value="<?php echo $formData[$i.'-pupilsightPersonID'];?>" class="sms_usrs" >
                </center>
            </td>
           </tr>
       <?php } } ?>
        <tr>
        <td colspan="3">
            <a href="javascript:void(0)" id="attendanceSave" class="btn btn-primary">Save Attendance</a>
            <a href="javascript:void(0)" id="widthSmsAttendanceSave"  class="btn btn-primary">Send SMS and Save Attendance</a>
        </td>
        </tr>
       </body>
   </table>
   <?php
  
}
