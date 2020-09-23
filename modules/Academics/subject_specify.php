<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway ;

if (isActionAccessible($guid, $connection2, '/modules/Academics/subject_specify.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    //Proceed!
    $pupilsightYearGroupID = $_GET['cid'];
    $pupilsightProgramID = $_GET['pid'];

    $eid = '';
    if(!empty($_GET['eid'])){
        $eid = $_GET['eid'];
    } 
    $page->breadcrumbs->add(__('Manage School Years'));

    echo '<h2>';
    echo __('Add Subject');
    echo '</h2>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $CurriculamGateway   = $container->get(CurriculamGateway  ::class);

    // QUERY
    $criteria = $CurriculamGateway  ->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    if(!empty($eid)){
        $subject = $CurriculamGateway->getSub($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $eid);
    } else {
        $session = $container->get('session');
        $sectionids = $session->get('section_ids');
        if(!empty($sectionids)){
            $chksec = 'SELECT GROUP_CONCAT(DISTINCT b.pupilsightRollGroupID) as secId, GROUP_CONCAT(DISTINCT ac_elective_group_id) as aceId FROM ac_elective_group AS a LEFT JOIN ac_elective_group_section AS b ON a.id = b.ac_elective_group_id WHERE a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$pupilsightProgramID.' AND a.pupilsightYearGroupID = '.$pupilsightYearGroupID.'  AND b.pupilsightRollGroupID IN ('.$sectionids.') ';
            $resultchksec = $connection2->query($chksec);
            $secchk = $resultchksec->fetch();  
            if(!empty($secchk['secId'])){
                
                $chkSubject = 'SELECT GROUP_CONCAT(DISTINCT pupilsightDepartmentID) as subId FROM  ac_elective_group_subjects WHERE ac_elective_group_id IN ('.$secchk['aceId'].')  ';
                $resultchk = $connection2->query($chkSubject);
                $subchk = $resultchk->fetch();  
                $subId = $subchk['subId'];

                $subject = $CurriculamGateway->getSubNew($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $eid, $subId);
            } else {
               
                $subject = $CurriculamGateway->getSub($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $eid);
            }

        } else {
            $chkSubject = 'SELECT GROUP_CONCAT(DISTINCT b.pupilsightDepartmentID) as subId FROM ac_elective_group AS a LEFT JOIN ac_elective_group_subjects AS b ON a.id = b.ac_elective_group_id WHERE a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$pupilsightProgramID.' AND a.pupilsightYearGroupID = '.$pupilsightYearGroupID.' ';
            $resultchk = $connection2->query($chkSubject);
            $subchk = $resultchk->fetch();  
            $subId = $subchk['subId'];
            $subject = $CurriculamGateway->getSubNew($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $eid, $subId);
        }
        
    }
    
    
    

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearManage', $criteria);

    
 
    $table->addCheckboxColumn('id',__(''))
    ->setClass('chkbox')
    ->notSortable()
    ->format(function ($subject) {
        if($subject['checked'] == '1'){
            return "<input type='checkbox' name='id[]' value='".$subject['id']."' checked>";
        } else {    
            return "<input type='checkbox' name='id[]' value='".$subject['id']."' >";
        }
    });
    $table->addColumn('name', __('Subject Name'));
  
    echo $table->render($subject);

    echo "<div style='height:50px;'><div class='float-right mb-2'><a id='electiveSub' class=' btn btn-primary'>Save</a><div class='float-none'></div></div></div>";  
    
}
