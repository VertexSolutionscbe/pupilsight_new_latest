<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$session = $container->get('session');

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_home_copy.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';




} else {
    //Proceed!
    //send sms email
   $test_id = $_GET['tid'];
   $test_name = $_GET['name'];

    $page->breadcrumbs->add(__('Manage School Years'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear WHERE status="Upcoming"';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();
?>
<form method="post" id="send_email_sms_form">
    <input type="hidden" name="type" value="send_sms_or_email">
    <input type="hidden" name="testID" value="<?php echo $test_id;?>">
    <input type="hidden" name="testName" value="<?php echo $test_name;?>">
<div class="row">
<div class="col-sm-12">
    <br/>
<table style="width: 100%;">
    <tr>
        <th colspan="4">
            Send SMS/Email
        </th>
    </tr>
  <tr>
    <th>
        <label><input type="checkbox" name="sms"  class="check_status" data-type="sms_table"> SMS</label>
    </th>
    <th>
        <label><input type="checkbox" name="email" class="check_status" data-type="email_table"> Email</label>
    </th>
    <th>
        <select name="marks_type">
            <option value="mark">Mark</option>
            <option value="grade">Grade</option>
        </select>
    </th>
    <th>
        <a id="send_sms_or_email" class=" btn btn-primary">Send</a>
    </th>
  </tr>
</table>
<br/>
</div>
</div>
<div class="row">
    <div class="col-sm-6">
        <table style="width: 100%;">
            <thead>
            <tr>
                <th><input type="checkbox" name="checkall" class="checkall"></th>
                <th>Send To SMS</th>
            </tr>
            </thead>
            <tbody class="sms_table" style="display: none">
            <tr>
                <td><input type="checkbox" name="sms_usr[]" value="Student"></td>
                <td>Student Mobile</td>
            </tr>
             <tr>
                <td><input type="checkbox" name="sms_usr[]" value="Father"></td>
                <td>Father's Mobile</td>
            </tr>
            <tr>
                <td><input type="checkbox" name="sms_usr[]" value="Mother"></td>
                <td>Mother Mobile</td>
            </tr>
             <tr>
                <td><input type="checkbox" name="sms_usr[]" value="Other"></td>
                <td>Guardian Mobile</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-sm-6">
        <table style="width: 100%;">
            <thead>
            <tr>
                <th><input type="checkbox" name="checkall" class="checkall"></th>
                <th>Send To Email</th>
            </tr>
            </thead>
            <tbody class="email_table" style="display: none">
             <tr>
                <td><input type="checkbox" name="email_usr[]" value="Father"></td>
                <td>Father's Email</td>
            </tr>
            <tr>
                <td><input type="checkbox" name="email_usr[]" value="Mother"></td>
                <td>Mother Email</td>
            </tr>
             <tr>
                <td><input type="checkbox" name="email_usr[]" value="Other"></td>
                <td>Guardian Email</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
</form>
<?php
  
}
