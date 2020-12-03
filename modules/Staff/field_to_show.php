<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffGateway;

$REDIRECTURL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/staff_view.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Students'), 'student_view.php')
        ->add(__('Field to Show'));


    $search = isset($_GET['search']) ? $_GET['search']  : '';
      

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Field to Show');
    echo '</h2>';
   
    $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];

    $sql = 'SELECT field_name, field_title FROM custom_field WHERE FIND_IN_SET("staff",modules) ';
    $result = $connection2->query($sql);
    $customFields = $result->fetchAll();

    //print_r($customFields);

    $sqla = 'SELECT GROUP_CONCAT(field_name) AS fname FROM staff_field_show WHERE pupilsightPersonID = '.$pupilsightPersonID.' ';
    $resulta = $connection2->query($sqla);
    $fieldSave = $resulta->fetch();

    $field = array();
    if(!empty($fieldSave)){
        $field = explode(',', $fieldSave['fname']);
    }
    

    if($_POST){
        $field_name = $_POST['field_name'];
        if(!empty($field_name)){
            $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'DELETE FROM staff_field_show WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
            foreach($field_name as $fn){
                echo $fname = $fn;
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'field_name' => $fname);
                $sqlf = "INSERT INTO staff_field_show SET pupilsightPersonID=:pupilsightPersonID, field_name=:field_name"; 
                $resultf = $connection2->prepare($sqlf);
                $resultf->execute($data);
            }
            $REDIRECTURL .= "&return=success0";
            header("Location: {$REDIRECTURL}");
        }
    }

?>
<form method="post" action="">
    <button type="submit" class="btn btn-primary" style="float: right;margin-bottom: 10px;">Save</button>
    <input type="hidden" name="pupilsightPersonID" value="<?php echo $pupilsightPersonID;?>">
    <table class="table">
        <thead>
            <tr>
                <th><input type="checkbox" class="chkAll" ></th>
                <th>Column Name</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="staff_name" <?php if (in_array("staff_name", $field)) { ?> checked <?php } ?>></td>
                <td>Staff Name</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="email" <?php if (in_array("email", $field)) { ?> checked <?php } ?>></td>
                <td>Email</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="phone1" <?php if (in_array("phone1", $field)) { ?> checked <?php } ?>></td>
                <td>Phone</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="type" <?php if (in_array("type", $field)) { ?> checked <?php } ?>></td>
                <td>Type</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="jobTitle" <?php if (in_array("jobTitle", $field)) { ?> checked <?php } ?>></td>
                <td>Job Title</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="username" <?php if (in_array("username", $field)) { ?> checked <?php } ?>></td>
                <td>Username</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="dob" <?php if (in_array("dob", $field)) { ?> checked <?php } ?>></td>
                <td>Date of Birth</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="gender" <?php if (in_array("gender", $field)) { ?> checked <?php } ?>></td>
                <td>Gender</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="stat" <?php if (in_array("stat", $field)) { ?> checked <?php } ?>></td>
                <td>Status</td>
            </tr>
            
            <?php 
            if(!empty($customFields)){
                foreach($customFields as $cf){
            ?>
                    <tr>
                        <td><input type="checkbox" class="chkChild" name="field_name[]" value="<?php echo $cf['field_name']?>" <?php if (in_array($cf['field_name'], $field)) { ?> checked <?php } ?>></td>
                        <td><?php echo $cf['field_title']?></td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</form>
<?php 
}
?>