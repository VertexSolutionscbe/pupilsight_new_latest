<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$session = $container->get('session');
$id = $_GET['id'];
// echo $classid= $_GET['class_name'];
// echo $section_id= $_GET['section_name'];

if (isActionAccessible($guid, $connection2, '/modules/Academics/modify_test_class_section_wise.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(('Edit Test'), 'manage_edit_test.php')
    ->add(__('Modify Test'));

    echo '<h3>';
    echo __('Modify Test');
    echo '</h3>';

    echo '<h5>';
    echo __('Test Info');
    echo '</h5>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    


//SELECT * FROM `examinationTest` ,`id`,`pupilsightSchoolYearID`,`name`,`code`
    try {
        $data = array('id' => $id);
        $sql = 'SELECT a.*,b.pupilsightProgramID,b.pupilsightYearGroupID,b.pupilsightRollGroupID FROM examinationTest AS a LEFT JOIN examinationTestAssignClass AS b ON a.id = b.test_id  WHERE a.id=:id';
        $result = $connection2->prepare($sql);
        $result->execute($data);
        
    //     echo '<pre>';
    //     print_r($data);
    //     echo '</pre>';
    // die();
    } catch (PDOException $e) {
        echo "<div class='error'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) {
        echo "<div class='error'>";
        echo __('The specified record cannot be found.');
        echo '</div>';
    } else {
        //Let's go!
        $values = $result->fetch(); 
    
    $sqlclasses = 'SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup WHERE pupilsightYearGroupID = "'.$values['pupilsightYearGroupID'].'" ';
    $result_class = $connection2->query($sqlclasses);
    $row_class = $result_class->fetch();


    $sqlsection = 'SELECT pupilsightRollGroupID, name FROM pupilsightRollGroup WHERE pupilsightRollGroupID = "'.$values['pupilsightRollGroupID'].'" ';
    $result_section = $connection2->query($sqlsection);
    $row_section = $result_section->fetch();
    $section_name_sel= $row_section['name'];
  //  echo "<pre>";print_r($values);
   $report_template =  array(__('Select') => array('Template1' => __('Template1'),
   'Template2' => __('Template2'),
   'Template3' => __('Template3')));

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

    // $sqlrt = 'SELECT * FROM examinationReportTemplateMaster ';
    $sqlrt = 'SELECT * FROM examinationReportSketchTemplateMaster ';
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



    $form = Form::create('testCreate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/modify_test_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
    $form->addHiddenValue('id', $id); 
    $form->addHiddenValue('cid', $row_class['pupilsightYearGroupID']); 
    $form->addHiddenValue('pupilsightSchoolYearID', $values['pupilsightSchoolYearID']); 

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('pupilsightProgramID', __('Program'))->addClass('dte');
     $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($values['pupilsightProgramID'])->readonly();


    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('class', __('Class'))->addClass('dte');
    $col->addTextField('class')->addClass('txtfield')->setValue($row_class['name'])->readonly();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('section', __('Section'))->addClass('dte');
    $col->addTextField('section')->addClass('txtfield')->setValue($row_section['name'])->readonly();

    $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('test_master_id', __('Test'))->addClass('dte');
     $col->addSelect('test_master_id')->setId('testMasterId')->fromArray($testmaster)->selected($values['test_master_id'])->readonly();

    

    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('name', __('Test name'));
    $col->addTextField('name')->maxLength(40)->required()->setValue($values['name']);


    // $col = $row->addColumn()->setClass('newdes');
    // $col->addLabel('selecttest', __('Select Test'));
    // $col->addTextField('selecttest')->addClass('txtfield')->maxLength(40)->required()->setValue($values['name'])->readonly();

    $col = $row->addColumn()->setClass('newdes');
     $col->addLabel('gradeSystemId', __('Grading System'));
     $col->addSelect('gradeSystemId')->fromArray($grade)->required()->selected($values['gradeSystemId']);

    $col = $row->addColumn()->setClass('newdes ');
    $col->addLabel('report_template', __('Select Report Template'));
    $col->addSelect('report_template')->fromArray($rt)->selected($values['report_template_id']);

    $col = $row->addColumn()->setClass('newdes ');
    $col->addLabel('pupilsightSchoolYearTermID', __('Test Type'));
    $col->addSelect('pupilsightSchoolYearTermID')->fromArray($term)->selected($values['pupilsightSchoolYearTermID']);

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addCheckbox('enable_schedule')->description(__('Schedule The Test'))->addClass('dte')->checked($values['enable_schedule']);
    // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');


    $row = $form->addRow()->setClass('show_test_schedule');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('start_date', __('Start Date'))->addClass('dte');
    
    if($values['start_date'] != '1970-01-01'){
        if($values['start_date'] != '0000-00-00'){
            $startdate = date('d/m/Y', strtotime($values['start_date']));
        } else {
            $startdate = '';
        }
    } else {
        $startdate = '';
    }
    $col->addDate('start_date')->setValue($startdate);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('start_time', __('Start Time'));
    $col->addTextField('start_time')->setValue($values['start_time']);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('end_date', __('End Date'))->addClass('dte');
    if($values['end_date'] != '1970-01-01'){
        if($values['end_date'] != '0000-00-00'){
            $enddate = date('d/m/Y', strtotime($values['end_date']));
        } else {
            $enddate = '';
        }
    } else {
        $enddate = '';
    }
    $col->addDate('end_date')->setValue($enddate);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('end_time', __('End Time'));
    $col->addTextField('end_time')->setValue($values['end_time']);


    $row = $form->addRow();
    $col->addLabel('', __(''))->addClass('dte');
 //   $row = $form->addRow()->setID('lastseatdiv');
    $row->addFooter();
    $row->addColumn()->setClass('');
    $row->addContent('<a  id="saveTestCreate" class=" btn btn-primary" style=" font-size: 15px !important;width: 221px">Subject wise Test Details</a>');  


        echo $form->getOutput();
    }
}
?>

<style>

 .mt_align 
 {
    margin-top: 17px;
 }

</style>
<script>


</script>
