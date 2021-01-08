<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_create_with_section.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Edit Test'), 'manage_edit_test.php')
        ->add(__('Add Test'));

   
    // if (isset($_GET['return'])) {
    //     returnProcess($guid, $_GET['return'], $editLink, null);
    // }
    echo '<h2>';
    echo __('Add Test');
    echo '</h2>';

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

    $sqlm = 'SELECT id, name FROM examinationTestMaster ';
    $resultm = $connection2->query($sqlm);
    $rowdatatest = $resultm->fetchAll();

    $testmaster = array();
    $testmaster2 = array();
    $testmaster1 = array('' => 'Select Test');
    foreach ($rowdatatest as $dtm) {
        $testmaster2[$dtm['id']] = $dtm['name'];
    }
    $testmaster = $testmaster1 + $testmaster2;

    $sqlterm = 'SELECT * FROM pupilsightSchoolYearTerm ORDER BY pupilsightSchoolYearTermID ASC';
    $resultterm = $connection2->query($sqlterm);
    $termdata = $resultterm->fetchAll();

    $term = array();
    $term1 = array(''=>'Please Select');
    $term2 = array();
    foreach($termdata as $trm){
        $term2[$trm['pupilsightSchoolYearTermID']] = $trm['name'];
    }
    if(!empty($term2)){
        $term = $term1 + $term2;
    }

    $sqlgrade = 'SELECT * FROM examinationGradeSystem ORDER BY id ASC';
    $resultgrade = $connection2->query($sqlgrade);
    $gradedata = $resultgrade->fetchAll();

    $grade = array();
    $grade1 = array(''=>'Please Select');
    $grade2 = array();
    foreach($gradedata as $grd){
        $grade2[$grd['id']] = $grd['name'];
    }
    if(!empty($grade2)){
        $grade = $grade1 + $grade2;
    }
    
    

    $sqlrt = 'SELECT * FROM examinationReportTemplateMaster ';
    $resultrt = $connection2->query($sqlrt);
    $reportTempData = $resultrt->fetchAll();

    $rt = array();
    $rt1 = array(''=>'Please Select');
    $rt2 = array();
    foreach($reportTempData as $rtd){
        $rt2[$rtd['id']] = $rtd['name'];
    }
    if(!empty($rt2)){
        $rt = $rt1 + $rt2;
    }
 
    
     $form = Form::create('testCreate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/test_create_with_section_addProcess.php');
     $form->setFactory(DatabaseFormFactory::create($pdo));
     $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
     
     $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
     $form->addHiddenValue('subject_type', ''); 
     $form->addHiddenValue('assesment_method', ''); 
     $form->addHiddenValue('assesment_option', ''); 
     $form->addHiddenValue('max_marks', ''); 
     $form->addHiddenValue('min_marks', ''); 
     $form->addHiddenValue('enable_remarks', ''); 


     $row = $form->addRow();
     $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('pupilsightProgramID', __('Program'));
     $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramID_check')->fromArray($program)->required()->placeholder('Select Program');

     $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('pupilsightYearGroupID', __('Class'));
     $col->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupID_check')->required()->placeholder('Select Class');
 
     //$col = $row->addColumn()->setClass('newdes');
    // $col->addLabel('pupilsightRollGroupID', __('Section'));
     //$col->addSelect('pupilsightRollGroupID', $pupilsightSchoolYearID)->required()->placeholder('Select Section');
     $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('pupilsightRollGroupID', __('Section'));
     $col->addContent('<div id="pupilsightRollGroupID_check" class="section_div w-full txtfield"></div> ');
     
     $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('test_master_id', __('Test'));
     $col->addSelect('test_master_id')->setId('testMasterId')->fromArray($testmaster)->required()->placeholder('Select Test');


     $row = $form->addRow();
     $col = $row->addColumn()->setClass('newdes ');
     $col->addLabel('name', __('Test name'));
     $col->addTextField('name')->setId('testName')->maxLength(40)->required();
 
     
     $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('gradeSystemId', __('Grading System'));
     $col->addSelect('gradeSystemId')->fromArray($grade)->required();
 
     $col = $row->addColumn()->setClass('newdes ');
     $col->addLabel('report_template', __('Select Report Template'));
     $col->addSelect('report_template')->fromArray($rt);
 
     $col = $row->addColumn()->setClass('newdes ');
     $col->addLabel('pupilsightSchoolYearTermID', __('Test Type'));
     $col->addSelect('pupilsightSchoolYearTermID')->fromArray($term);
 
     $row = $form->addRow();
     $col = $row->addColumn()->setClass('newdes');
     $col->addCheckbox('enable_schedule')->setValue('1')->description(__('Schedule The Test'))->addClass('dte');
 
     $row = $form->addRow()->setClass('show_test_schedule');
     $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('start_date', __('Start Date'))->addClass('dte');
     $col->addDate('start_date')->maxLength(40);
 
     $col = $row->addColumn()->setClass('newdes ');
     $col->addLabel('start_time', __('Start Time'));
     $col->addTextField('start_time')->maxLength(40);
 
     $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('end_date', __('End Date'))->addClass('dte');
     $col->addDate('end_date')->maxLength(40);
 
     $col = $row->addColumn()->setClass('newdes ');
     $col->addLabel('end_time', __('End Time'));
     $col->addTextField('end_time')->maxLength(40);
 
     $row = $form->addRow();
    
    $row = $form->addRow();
    $row->addFooter();
    $row->addContent('<a  id="saveTestCreate" class=" btn btn-primary" style=" font-size: 14px !important;width: 221px">Subject wise Test Details</a>');  
 
         echo $form->getOutput();

}
?>
<style>

 .mt_align 
 {
    margin-top: 21px;
 }
 .sectionmultiple 
 {
    height: 60px !important;
    min-height: px!important;
 }

 .section_div 

 {
    height: 36px;
    background-color: #f0f1f3;
    border-radius: 4px;
    overflow-y:scroll!important;
 }
 .check_mrgin 
{
    margin-top: 11px!important;
}

</style>




