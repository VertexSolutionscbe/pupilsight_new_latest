<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$studentids = $session->get('student_ids');
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;

$REDIRECTURL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_detail_view.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Students'), 'student_detail_view.php')
        ->add(__('Field to Show'));


    $search = isset($_GET['search']) ? $_GET['search']  : '';
      

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Field to Show');
    echo '</h2>';
   
    $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];

    $sql = 'SELECT field_name, field_title FROM custom_field WHERE FIND_IN_SET("student",modules) ';
    $result = $connection2->query($sql);
    $customFields = $result->fetchAll();

    //print_r($customFields);

    $sqla = 'SELECT GROUP_CONCAT(field_name) AS fname FROM student_field_show WHERE pupilsightPersonID = '.$pupilsightPersonID.' ';
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
            $sql = 'DELETE FROM student_field_show WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
            foreach($field_name as $fn){
                echo $fname = $fn;
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'field_name' => $fname);
                $sqlf = "INSERT INTO student_field_show SET pupilsightPersonID=:pupilsightPersonID, field_name=:field_name"; 
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
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="student" <?php if (in_array("student", $field)) { ?> checked <?php } ?>></td>
                <td>Student Name</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="pupilsightPersonID" <?php if (in_array("pupilsightPersonID", $field)) { ?> checked <?php } ?>></td>
                <td>Student ID</td>
            </tr>
            <?php /*
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="admission_no" <?php if (in_array("admission_no", $field)) { ?> checked <?php } ?>></td>
                <td>Admission No</td>
            </tr>
            */ ?>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="academic_year" <?php if (in_array("academic_year", $field)) { ?> checked <?php } ?>></td>
                <td>Academic Year</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="program" <?php if (in_array("program", $field)) { ?> checked <?php } ?>></td>
                <td>Program</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="yearGroup" <?php if (in_array("yearGroup", $field)) { ?> checked <?php } ?>></td>
                <td>Class</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="rollGroup" <?php if (in_array("rollGroup", $field)) { ?> checked <?php } ?>></td>
                <td>Section</td>
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
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="username" <?php if (in_array("username", $field)) { ?> checked <?php } ?>></td>
                <td>Username</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="address1" <?php if (in_array("address1", $field)) { ?> checked <?php } ?>></td>
                <td>Address</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="address1District" <?php if (in_array("address1District", $field)) { ?> checked <?php } ?>></td>
                <td>District</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="address1Country" <?php if (in_array("address1Country", $field)) { ?> checked <?php } ?>></td>
                <td>Country</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="phone1" <?php if (in_array("phone1", $field)) { ?> checked <?php } ?>></td>
                <td>Phone</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="email" <?php if (in_array("email", $field)) { ?> checked <?php } ?>></td>
                <td>Email</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="languageFirst" <?php if (in_array("languageFirst", $field)) { ?> checked <?php } ?>></td>
                <td>First Language</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="languageSecond" <?php if (in_array("languageSecond", $field)) { ?> checked <?php } ?>></td>
                <td>Second Language</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="languageThird" <?php if (in_array("languageThird", $field)) { ?> checked <?php } ?>></td>
                <td>Third Language</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="religion" <?php if (in_array("religion", $field)) { ?> checked <?php } ?>></td>
                <td>Religion</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="fatherName" <?php if (in_array("fatherName", $field)) { ?> checked <?php } ?>></td>
                <td>Father Name</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="fatherEmail" <?php if (in_array("fatherEmail", $field)) { ?> checked <?php } ?>></td>
                <td>Father Email</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="fatherPhone" <?php if (in_array("fatherPhone", $field)) { ?> checked <?php } ?>></td>
                <td>Father Phone</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="motherName" <?php if (in_array("motherName", $field)) { ?> checked <?php } ?>></td>
                <td>Mother Name</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="motherEmail" <?php if (in_array("motherEmail", $field)) { ?> checked <?php } ?>></td>
                <td>Mother Email</td>
            </tr>
            <tr>
                <td><input type="checkbox" class="chkChild" name="field_name[]" value="motherPhone" <?php if (in_array("motherPhone", $field)) { ?> checked <?php } ?>></td>
                <td>Mother Phone</td>
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