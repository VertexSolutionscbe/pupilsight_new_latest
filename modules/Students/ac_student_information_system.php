<?php
/*
Pupilsight, Flexible & Open School System
 */
use Pupilsight\Domain\Curriculum\CurriculamGateway;

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Students/ac_student_information_system.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

    $CurriculamGateway = $container->get(CurriculamGateway::class);
    $criteria = $CurriculamGateway->newQueryCriteria()
                    ->pageSize('100000');
    
    //Proceed!
    $page->breadcrumbs->add(__('Student Information System'));

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

    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program = $program1 + $program2;

    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $academic = array();
    $ayear = '';
    if (!empty($rowdata)) {
        $ayear = $rowdata[0]['name'];
        foreach ($rowdata as $dt) {
            $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
        }
    }
    $include_core=0;
    if(isset($_POST['include_coresubjects'])) {
        $include_core = $_POST['include_coresubjects'];
        $coreSubjects =  $CurriculamGateway->getStudentCoreSubjectClassWise($criteria,$pupilsightSchoolYearID, $_POST['pupilsightProgramID'], $_POST['pupilsightYearGroupID']);
        $kount = '0';
        if(!empty($coreSubjects)){
            foreach($coreSubjects as $cs){
                if(!empty($cs['subject_type']) && $cs['subject_type'] == 'Core'){
                    $kount++;
                }
            }
        }
       
    }
    
    //die();
    if ($_POST) {
        $electiveSubjects =  $CurriculamGateway->getStudentElectiveSubjectClassSectionWise($criteria,$pupilsightSchoolYearID, $_POST['pupilsightProgramID'], $_POST['pupilsightYearGroupID'], $_POST['pupilsightRollGroupID']);

        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        //    $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
        $searchbyPost = '';
      
        //  $search =  $_POST['search'];
        $stuId = $_POST['studentId'];
    } else {
        $pupilsightProgramID =  '';
        //   $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
        $pupilsightYearGroupID = '';
        $pupilsightRollGroupID = '';
        $searchbyPost = '';
        $search = '';
        $stuId = '0';
       
    }
   
    //  echo '<button id="save_student_to_subject" href=""  class=" btn btn-primary" style="margin:5px">Save</button>';
   
    //  echo "<a style='display:none' id='clickstudent_to_subject' href='fullscreen.php?q=/modules/Students/select_student_to_addsubjects.php&width=800'  class='thickbox '>Change Route</a>";   
    //  echo "<a  id='add_student_to_subject' data-type='student' class='btn btn-primary'>Add Subject</a>&nbsp;&nbsp;";  
    
    // echo '<button id=""  class=" btn btn-primary" style="margin:5px">Unassign route</button>';
    // echo '<button id=""  class=" btn btn-primary" style="margin:5px">Change route</button>';

    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
   
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelectYearGroup('pupilsightYearGroupID')->required()->selected($pupilsightYearGroupID);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required()->selected($pupilsightRollGroupID);

    $col = $row->addColumn()->setClass('newdes width_smll');
    $col->addLabel('include_coresubjects', __('Include Core Subjects'));
    $col->addCheckbox('include_coresubjects')->setClass('core_style include_core')->setValue(1)->checked($include_core);

    $col = $row->addColumn()->setClass('newdes');
    
    $col->addLabel('', __(''));
    $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');
    echo $searchform->getOutput();

    $students = $CurriculamGateway->getstudent_subject_assigned_data($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID);
    $elective_sub = array();

    // $sql_elective = 'SELECT a.*,b.pupilsightDepartmentID, b.name AS subject FROM ac_elective_group AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN pupilsightProgramClassSectionMapping AS c ON a.pupilsightMappingID = c.pupilsightMappingID WHERE c.pupilsightYearGroupID="'.$pupilsightYearGroupID.'" AND c.pupilsightRollGroupID="'.$pupilsightRollGroupID.'"';
    // $result_elective = $connection2->query($sql_elective);
    // $getelectivegrp= $result_elective->fetchAll();
   /* echo "<pre>";
    print_r($getelectivegrp);
*/
// echo '<pre>';
//     print_r($students);
//     echo '</pre>';
//     die();
?>
<input type="hidden" id="progId" value="<?php echo $pupilsightProgramID?>">
<input type="hidden" id="classId" value="<?php echo $pupilsightYearGroupID?>">
<?php if($_POST){ ?>
    <table class="table table-striped" style="display: block;overflow: auto;height:auto;white-space: nowrap;">
        <thead style="font-size:14px;">
            <tr>
            <?php /* ?>
                <th rowspan="2"><input type="checkbox" name="checkall" id="checkall" value="on" class="floatNone checkall"></th>
            <?php */?>    
                <th rowspan="2">Student Name</th>
                <th rowspan="2">Student Id</th>
                <?php if($include_core == '1') { ?>
                <th colspan="<?php echo $kount;?>" style="text-align:center; border:2px solid #dee2e6">Core Subject</th>
                <?php } ?>
                <?php if(!empty($electiveSubjects)){
                foreach($electiveSubjects as $ele){
                ?>
                <th colspan="<?php echo count($ele['elective']);?>" style="text-align:center; border: 2px solid #dee2e6"><?php echo $ele['name']; ?></th>
                <?php } } ?>    
                
            </tr>
            <tr>
            <?php if($include_core == '1') { 
                foreach($coreSubjects as $core){
                    if(!empty($core['subject_type']) && $core['subject_type'] == 'Core'){
                ?>
                    <th><?php echo $core['subject_display_name']; ?></th>
            <?php } } }?>    
            <?php if(!empty($electiveSubjects)){
                foreach($electiveSubjects as $elect){
                   foreach($elect['elective'] as $el){
                ?>
                <th style="text-align:center; border:2px solid #dee2e6"><?php echo $el['subject_display_name']; ?></th>
            <?php } } } ?>    
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($students)){
                foreach($students as $stu){
            ?>
            <tr>
            <?php /* ?>
                <td><input type="checkbox" name="student_id[]" id="student_id[' .<?php echo $stu['pupilsightPersonID'] ?>. ']" value="'.<?php echo $stu['pupilsightPersonID'] ?> .'" ></td>
            <?php */?>    
                <td><?php echo $stu['student_name']; ?></td>
                <td><?php echo $stu['pupilsightPersonID']?></td>
                <?php if($include_core == '1') { 
                    foreach($coreSubjects as $core){
                        if(!empty($core['subject_type']) && $core['subject_type'] == 'Core'){
                    ?>
                        <td style="text-align:center;"><i class="mdi mdi-checkbox-marked-circle mdi-24px greenicon"  style="cursor:pointer;"></i></td>
                <?php } } }?>   
                <?php if(!empty($electiveSubjects)){
                    foreach($electiveSubjects as $elect){
                    foreach($elect['elective'] as $el){
                        $sqlchk = 'SELECT id FROM assign_elective_subjects_tostudents WHERE pupilsightPersonID = '.$stu['pupilsightPersonID'].'  AND pupilsightDepartmentID= '.$el['pupilsightDepartmentID'].' AND  pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID = '.$pupilsightYearGroupID.' AND ac_elective_group_id = '.$elect['id'].' ';
                        $resultchk = $connection2->query($sqlchk);
                        $assignCheck = $resultchk->fetch();
                        if(!empty($assignCheck['id'])){
                            $chkcls = 'greenicon';
                        } else {
                            $chkcls = 'greyicon';
                        }
                    ?>
                    <td style="text-align:center;"><i data-maxsel="<?php echo $elect['max_selection'];?>" data-eid="<?php echo $elect['id'];?>" data-sid="<?php echo $el['pupilsightDepartmentID'];?>" data-stid="<?php echo $stu['pupilsightPersonID'];?>" class="chkcls<?php echo $elect['id'].'-'.$stu['pupilsightPersonID'];?>  mdi mdi-checkbox-marked-circle mdi-24px <?php echo $chkcls;?> tick_icon" style="cursor:pointer;"></i></td>
                <?php } } } ?>    
            </tr>
        <?php } } ?>
        </tbody>
    </table>
<?php 
} }
?>
