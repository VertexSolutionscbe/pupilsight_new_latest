<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Admission\AdmissionGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/fee_setting.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__(' Fee Setting'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    
    if($_POST){
        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        $type = $_POST['ftype'];
        $transId = $_POST['transid'];  
    } 

    if($type == 2){
        $sqls = 'SELECT fn_fee_admission_setting_ids FROM workflow_transition WHERE id = '.$transId.' ';
        $results = $connection2->query($sqls);
        $feedatasttg = $results->fetch();
        if(!empty($feedatasttg)){
            $feestgId = $feedatasttg['fn_fee_admission_setting_ids']; 
        } else {
            $feestgId = '';
        }
    }  
    
    $sqlc = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
    $resultc = $connection2->query($sqlc);
    $clsdata = $resultc->fetchAll();

    
    $AdmissionGateway = $container->get(AdmissionGateway::class);
    $criteria = $AdmissionGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $feeGroups = $AdmissionGateway->getFeeStructure($criteria, $pupilsightSchoolYearID, $type, $feestgId, $pupilsightProgramID);
   
}
?>

<input type="hidden" name="pupilsightProgramID" value="<?php echo $pupilsightProgramID;?>">
<input type="hidden" name="pupilsightSchoolYearID" value="<?php echo $pupilsightSchoolYearID;?>">
    <table style="width:100%">
        <thead>
            <tr>
                <th>SI No</th>
                <th>Group Name</th>
                <th>Amount</th>
                <th>Select</th>
                <th>Class</th>
            </tr>    
        </thead>
        <tbody>
            <?php if(!empty($feeGroups)){
                 $i = 1;
                foreach($feeGroups as $fg){    
                    if(!empty($fg['classes'])){
                        $checked = 'checked';
                        $classes = explode(',',$fg['classes']);
                    } else {
                        $checked = '';
                        $classes = array();
                    }
            ?>
                <tr>
                    <td><?php echo $i;?></td>
                    <td><?php echo $fg['name'];?></td>
                    <td><?php echo $fg['totalamount'];?></td>
                    <input type="hidden" name="amount[<?php echo $fg['id'];?>]" value="<?php echo $fg['totalamount'];?>">
                    <td><input type="checkbox" class="feestrid" name="fee_structure_id[]" value="<?php echo $fg['id'];?>" <?php echo $checked;?>></td>
                    <td>
                        <select multiple name="class[<?php echo $fg['id'];?>][]">
                        <?php if(!empty($clsdata)){
                            foreach($clsdata as $cd){    
                                if(in_array($cd['pupilsightYearGroupID'], $classes)){
                                    $selected = 'selected';
                                } else {
                                    $selected = '';
                                }
                        ?>
                            <option value="<?php echo $cd['pupilsightYearGroupID'];?>" <?php echo $selected;?>><?php echo $cd['name'];?></option>
                        <?php } } ?>
                        </select>
                    </td>
                </tr>    
            <?php $i++; } } ?>
        </tbody>
    </table>

<?php
