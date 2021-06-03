<?php


use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Admission\AdmissionGateway;


// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/index.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

    echo '<h2>';
    echo __('Student View Button Permission');
    echo '</h2>';

    $sql = 'SELECT * FROM pupilsightModuleButton WHERE pupilsightModuleID = 5 ';
    $result = $connection2->query($sql);
    $buttonList = $result->fetchAll();

    $sqlp = 'SELECT a.pupilsightStaffID,a.staff_status AS stat,b.*, b.pupilsightPersonID AS stu_id , a.type, b.firstName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID';
    $resultp = $connection2->query($sqlp);
    $getstaff = $resultp->fetchAll();
    $success = '';

    if($_POST){
       
        $staffPost = $_POST['staff'];
        if(!empty($staffPost)){
            $pupilsightModuleID = '5';
            $datadel = array('pupilsightModuleID' => $pupilsightModuleID);
            $sqldel = 'DELETE FROM pupilsightModuleButtonPermission WHERE pupilsightModuleID=:pupilsightModuleID';
            $resultdel = $connection2->prepare($sqldel);
            $resultdel->execute($datadel);

            foreach($staffPost as $k => $sp){
                $pupilsightModuleButtonID = $k;
                
                $sqln = 'SELECT name FROM pupilsightModuleButton WHERE pupilsightModuleButtonID = '.$pupilsightModuleButtonID.' ';
                $resultn = $connection2->query($sqln);
                $buttonNameData = $resultn->fetch();
                $btnName = $buttonNameData['name'];
                
                foreach($sp as $st){
                    $data = array('pupilsightModuleID' => $pupilsightModuleID, 'pupilsightModuleButtonID' => $pupilsightModuleButtonID, 'pupilsightPersonID' => $st, 'name' => $btnName);
                    $sql = "INSERT INTO pupilsightModuleButtonPermission SET pupilsightModuleID=:pupilsightModuleID, pupilsightModuleButtonID=:pupilsightModuleButtonID, pupilsightPersonID=:pupilsightPersonID, name=:name";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                }
            }
            $success = '1';
        }
    }
   
?>

<?php if(!empty($success)){ ?>
    <div class="alert alert-success">Your request was completed successfully.</div>
<?php } ?>
<form action="" method="post">
    <button type="submit" id="saveButtonPermission" class="btn btn-primary" style="float: right;margin-bottom: 10px;">Save</button>
    <table class="table">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Button Name</th>
                <th>Staff</th>
            </tr>
        </thead>

        <tbody>
            <?php if(!empty($buttonList)){ 
                $i = 1;
                foreach($buttonList as $bl) { ?>
                <tr>
                    <th><?php echo $i?></th>
                    <th>
                        <?php echo $bl['name']?>
                    </th>
                    <th>
                        <select name="staff[<?php echo $bl['pupilsightModuleButtonID']?>][]" class="form-control staffList" multiple>
                                    <option value="">Select Staff</option>
                            <?php if(!empty($getstaff)) { 
                                $selected = '';
                                foreach($getstaff as $st){ 
                                    $sqlchk = 'SELECT pupilsightPersonID FROM pupilsightModuleButtonPermission WHERE pupilsightModuleID = 5 AND pupilsightPersonID = '.$st['stu_id'].' AND pupilsightModuleButtonID = '.$bl['pupilsightModuleButtonID'].' ';
                                    $resultchk = $connection2->query($sqlchk);
                                    $permissionChk = $resultchk->fetch();
                                    if(!empty($permissionChk)){
                                        $selected = 'selected';
                                    } else {
                                        $selected = '';
                                    }
                            ?>  
                                    <option value="<?php echo $st['stu_id']?>" <?php echo $selected;?> ><?php echo $st['officialName']?></option>
                            <?php } } ?>
                        </select>
                    </th>
                </tr>
            <?php $i++; } } ?>
        </tbody>

    </table>
</form>
<?php 
}
?>

<script>
    $(document).ready(function() {
        $('.staffList').selectize({
            plugins: ['remove_button'],
        });
    });
</script>