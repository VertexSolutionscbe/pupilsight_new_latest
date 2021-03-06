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

    $type = $_GET['type'];
    $transId = $_GET['kid'];
    $campId = $_GET['cid'];

    $sqlcam = 'SELECT a.pupilsightProgramID,a.classes, b.name FROM campaign AS a LEFT JOIN pupilsightProgram AS b ON a.pupilsightProgramID = b.pupilsightProgramID WHERE a.id = '.$campId.' ';
    $resultcam = $connection2->query($sqlcam);
    $camData = $resultcam->fetch();

    $pupilsightProgramID = $camData['pupilsightProgramID'];
    $pupilsightProgramName = $camData['name'];
    $classes = $camData['classes'];


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

    // $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    // $resultp = $connection2->query($sqlp);
    // $rowdataprog = $resultp->fetchAll();

    // $program=array();  
    // $program2=array();  
    // $program1=array(''=>'Select Program');
    // $k=1;
    // foreach ($rowdataprog as $key => $dt) {
    //     $program2[$dt['pupilsightProgramID']] = $dt['name'];
    //     if($k == 1){
    //         $pupilsightProgramID=$dt['pupilsightProgramID'];
    //     }
    //     $k++;
    // }
    // $program= $program1 + $program2; 

    // if($_POST){
    //     $pupilsightProgramID =  $_POST['pupilsightProgramID'];
    // } 
    
    $sqlc = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND b.pupilsightYearGroupID IN ('.$classes.') GROUP BY a.pupilsightYearGroupID';
    $resultc = $connection2->query($sqlc);
    $clsdata = $resultc->fetchAll();

    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
         $rowdata = $resultval->fetchAll();
         $academic=array();
         $ayear = '';
        if(!empty($rowdata)){
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }
    
    


    $AdmissionGateway = $container->get(AdmissionGateway::class);
    $criteria = $AdmissionGateway->newQueryCriteria()
        ->pageSize(100)
        ->sortBy(['id'])
        ->fromPOST();
        //print_r($criteria);
      
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
    $feeGroups = $AdmissionGateway->getFeeStructure($criteria, $pupilsightSchoolYearID, $type, $feestgId, $pupilsightProgramID);
    echo "<h4>List Fee group</h4>";
    $table = DataTable::createPaginated('FeeStructureManage', $criteria);
// echo '<pre>';
//     print_r($feeGroups);
//     echo '</pre>';
//     die();
    
   
    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
   
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program : '.$pupilsightProgramName));
    //$col->addLabel('', __($pupilsightProgramName));
    
    //$col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');

    // $col = $row->addColumn()->setClass('newdes');
    // $col->addLabel('', __(''));
    // $col->addContent('<a class="btn btn-primary left-align"  data-kid="'.$transId.'" data-type="'.$type.'" id="getClassByProgFeeSetting">Search</a>');

   
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''))->setClass('right');
    $col->addContent('<a class="btn btn-primary" style="float:right;" id="saveAdmissionFess">Save</a>')->setClass('right');
    
    echo $searchform->getOutput();

}
?>
<input type="hidden" id="kid" value="<?php echo $_GET['kid'];?>">
<form id="admissionForm" >
<input type="hidden" name="form_id" value="<?php echo $_GET['fid'];?>">
<input type="hidden" name="pupilsightProgramID" value="<?php echo $pupilsightProgramID;?>">
<input type="hidden" name="pupilsightSchoolYearID" value="<?php echo $pupilsightSchoolYearID;?>">
    <table class="table">
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
</form>
<style>
    .mb-1 label {
        height: auto !important;
    }
</style>

<script>
    $(document).on('click', '#getClassByProgFeeSetting', function() {
        var pupilsightProgramID = $("#pupilsightProgramID").val();
        var transid = $(this).attr('data-kid');
        var ftype = $(this).attr('data-type');
        if (pupilsightProgramID != '') {
            //$("#admissionForm").submit();
            $.ajax({
            url : 'fullscreen.php?q=/modules/Campaign/fee_setting_ajax.php',
            type: 'post',
            data: { pupilsightProgramID: pupilsightProgramID, transid:transid, ftype:ftype },
            async: true,
            success: function(response)
            {
                $("#admissionForm").html();
                $("#admissionForm").html(response);
            }
        });	
        } else {
            alert('Yo Have to Select Program!');
        }
        
    });
</script>
<?php
