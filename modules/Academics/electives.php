<?php
/*
Pupilsight, Flexible & Open School System
 */
use Pupilsight\Domain\Curriculum\CurriculamGateway;

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/electives.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    $CurriculamGateway = $container->get(CurriculamGateway::class);
    $criteria = $CurriculamGateway->newQueryCriteria()
                    ->pageSize('100000');
    
    //Proceed!
    // $page->breadcrumbs->add(__('Student Elective Subject'));
    echo '<h2>Student Elective Subject</h2>';

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

    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $roleID = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
    
    if($roleID == '004'){
        $sqlf = 'SELECT pupilsightFamilyID FROM pupilsightFamilyAdult WHERE pupilsightPersonID= ' . $cuid . ' ';
        $resultf = $connection2->query($sqlf);
        $fdata = $resultf->fetch();
        $pupilsightFamilyID = $fdata['pupilsightFamilyID'];

        if (!empty($_GET['cid'])) {
            $chkchilds = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID = ' . $_GET['cid'] . ' ';
            $resultachk = $connection2->query($chkchilds);
            $chkstuData = $resultachk->fetch();

            if (!empty($chkstuData)) {
                $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
                $resulta = $connection2->query($childs);
                $stuData = $resulta->fetchAll();

                $students = $chkstuData;
                $stuId = $_GET['cid'];
            } else {
                echo '<h1>No Child</h1>';
            }
        } else {
            $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
            $resulta = $connection2->query($childs);
            $stuData = $resulta->fetchAll();
            $students = $stuData[0];
            $stuId = $students['pupilsightPersonID'];
        }

        //$students = $resulta->fetchAll();
        // echo '<pre>';
        // print_r($students);
        // echo '</pre>';
        // die();

        // $parents = 'SELECT email, phone1 FROM pupilsightPerson WHERE pupilsightPersonID = ' . $cuid . ' ';
        // $resultp = $connection2->query($parents);
        // $parData = $resultp->fetch();

        // QUERY
        // $criteria = $CurriculamGateway->newQueryCriteria()
        //     ->sortBy(['id'])
        //     ->fromPOST();

        if (!empty($_GET['success']) && $_GET['success'] == '1') {
            echo '<h3 style="color:light-green;color: green;border: 1px solid grey;text-align: center;padding: 5px 5px;">Payment Succesfully Done!</h3>';
        }

        $tab = '';
        if (!empty($stuData) && count($stuData) > 1) {
            $tab = '<div style="display:inline-flex;width:25%"><span style="width:25%">Child : </span><select id="childSel" class="form-control" style="width:100%">';
            foreach ($stuData as $stu) {
                $selected = '';
                if (!empty($_GET['cid'])) {
                    if ($_GET['cid'] == $stu['pupilsightPersonID']) {
                        $selected = 'selected';
                    }
                }
                $tab .=  '<option value=' . $stu['pupilsightPersonID'] . '  ' . $selected . '>' . $stu['officialName'] . '</option>';
            }
            $tab .=  '</select></div>';
        }
        echo $tab;
        // die();

        if (!empty($_GET['cid'])) {
            $chkchilds = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1, c.* FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID = ' . $_GET['cid'] . ' AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ';
            $resultachk = $connection2->query($chkchilds);
            $chkstuData = $resultachk->fetch();

            if (!empty($chkstuData)) {
                $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1, c.* FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ';
                $resulta = $connection2->query($childs);
                $stuData = $resulta->fetchAll();

                $students = $chkstuData;
                $stuId = $_GET['cid'];
            } else {
                echo '<h1>No Child</h1>';
            }
        } else {
            $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1, c.* FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ';
            $resulta = $connection2->query($childs);
            $stuData = $resulta->fetchAll();
            $students = $stuData[0];
            $stuId = $students['pupilsightPersonID'];
        }

    } else {
        $stuId = $_SESSION[$guid]['pupilsightPersonID'];
        $chkchilds = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1, c.* FROM pupilsightPerson AS b LEFT JOIN pupilsightStudentEnrolment AS c ON b.pupilsightPersonID = c.pupilsightPersonID WHERE b.pupilsightPersonID = ' . $stuId . ' AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ';
        $resultachk = $connection2->query($chkchilds);
        $students = $resultachk->fetch();
    }
    
    // echo '<pre>';
    // print_r($students);
    // die();
    $pupilsightProgramID = $students['pupilsightProgramID'];
    $pupilsightYearGroupID = $students['pupilsightYearGroupID'];
    $pupilsightRollGroupID = $students['pupilsightRollGroupID'];
    $pupilsightPersonID = $students['pupilsightPersonID'];
   
   
    $electiveSubjects =  $CurriculamGateway->getStudentElectiveSubjectClassSectionWise($criteria,$pupilsightSchoolYearID, $students['pupilsightProgramID'], $students['pupilsightYearGroupID'], $students['pupilsightRollGroupID']);

    $students = $CurriculamGateway->getstudent_subject_assigned_data($criteria, $pupilsightSchoolYearID, $students['pupilsightProgramID'], $students['pupilsightYearGroupID'], $students['pupilsightRollGroupID'], $students['pupilsightPersonID']);
    $elective_sub = array();

    // echo '<pre>';
    // print_r($students);

?>
<input type="hidden" id="progId" value="<?php echo $pupilsightProgramID?>">
<input type="hidden" id="classId" value="<?php echo $pupilsightYearGroupID?>">
<input type="hidden" id="secId" value="<?php echo $pupilsightRollGroupID?>">
<?php if($electiveSubjects){ ?>
    <table id="exportElective" class="table table-striped" style="overflow: auto;height:auto;white-space: nowrap;">
        <thead style="font-size:14px;">
            <tr>
            <?php /* ?>
                <th rowspan="2"><input type="checkbox" name="checkall" id="checkall" value="on" class="floatNone checkall"></th>
             
                <th class="removeRow" rowspan="2"><input type='checkbox' class="chkAll"></th>
                <th rowspan="2">Student Name</th>
                <th rowspan="2">Admission No</th>
                <th rowspan="2" style="display:none">Program</th>
                <th rowspan="2" style="display:none">Class</th>
                <th rowspan="2" style="display:none">Section</th>
                <?php if($include_core == '1') { ?>
                <th colspan="<?php echo $kount;?>" style="text-align:center; border:2px solid #dee2e6">Core Subject</th>
                <?php } ?>
            <?php */?>   
                <th rowspan="2">Student Name</th>
                <?php if(!empty($electiveSubjects)){
                foreach($electiveSubjects as $ele){
                ?>
                <th colspan="<?php echo count($ele['elective']);?>" style="text-align:center; border: 2px solid #dee2e6"><?php echo $ele['name']; ?></th>
                <?php } } ?>    
                
            </tr>
            <tr>
            <?php /* if($include_core == '1') { 
                foreach($coreSubjects as $core){
                    if(!empty($core['subject_type']) && $core['subject_type'] == 'Core'){
                ?>
                    <th><?php echo $core['subject_display_name']; ?></th>
            <?php } } } */ ?>    
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
               
                <td class="removeRow"><input type='checkbox' name="student_id[]" class="chkChild" value="<?php echo $stu['pupilsightPersonID']?>"></td>
                <td><?php echo $stu['student_name']; ?></td>
                <td><?php echo $stu['admission_no']?></td>
                <td style="display:none"><?php echo $stu['progname']?></td>
                <td style="display:none"><?php echo $stu['clsname']?></td>
                <td style="display:none"><?php echo $stu['secname']?></td>
                <?php if($include_core == '1') { 
                    foreach($coreSubjects as $core){
                        if(!empty($core['subject_type']) && $core['subject_type'] == 'Core'){
                            $sqlchk = 'SELECT id FROM remove_core_subjects_from_student WHERE pupilsightPersonID = '.$stu['pupilsightPersonID'].'  AND pupilsightDepartmentID= '.$core['pupilsightDepartmentID'].' AND  pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID = '.$pupilsightYearGroupID.'  ';
                            $resultchk = $connection2->query($sqlchk);
                            $assignCheck = $resultchk->fetch();
                            if(!empty($assignCheck['id'])){
                                $chkcls = 'greyicon';
                                $txt = '✖';
                            } else {
                                $chkcls = 'greenicon';
                                $txt = '✔';
                            }
                    ?>
                        <td style="text-align:center;"><i data-sid="<?php echo $core['pupilsightDepartmentID'];?>" data-stid="<?php echo $stu['pupilsightPersonID'];?>" class="mdi mdi-checkbox-marked-circle mdi-24px <?php echo $chkcls;?> tick_core_icon"  style="cursor:pointer;"></i><p style="display:none"><?php echo $txt ?></p></td>
                <?php } } }?>  
                <?php */?>  
                <td><?php echo $stu['student_name']; ?></td>
                <?php if(!empty($electiveSubjects)){
                    foreach($electiveSubjects as $elect){
                    foreach($elect['elective'] as $el){
                        $sqlchk = 'SELECT id FROM assign_elective_subjects_tostudents WHERE pupilsightPersonID = '.$stu['pupilsightPersonID'].'  AND pupilsightDepartmentID= '.$el['pupilsightDepartmentID'].' AND  pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID = '.$pupilsightYearGroupID.' AND ac_elective_group_id = '.$elect['id'].' ';
                        $resultchk = $connection2->query($sqlchk);
                        $assignCheck = $resultchk->fetch();
                        if(!empty($assignCheck['id'])){
                            $chkcls = 'greenicon';
                            $txt = '✔';
                        } else {
                            $chkcls = 'greyicon';
                            $txt = '✖';
                        }
                    ?>
                    <td style="text-align:center;"><i  class=" mdi mdi-checkbox-marked-circle mdi-24px <?php echo $chkcls;?> " ></i><p style="display:none"><?php echo $txt ?></p></td>
                <?php } } } ?>    
            </tr>
        <?php } } ?>
        </tbody>
    </table>
<?php 
} }
?>

<style>
    .cls {
        width: 310px;
        margin: 0 0 0 -125px;
    }
</style>

