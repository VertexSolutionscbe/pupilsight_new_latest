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

    $form = Form::create('searchForm', '');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('studentId', '0');
    $row = $form->addRow();
   
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program : '.$pupilsightProgramName));
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''))->setClass('right');
    $col->addContent('<a class="btn btn-primary" style="float:right;" id="saveAdmissionFess">Save</a>')->setClass('right');
    
    echo $searchform->getOutput();

}
?>