<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
 $studentids = $session->get('student_ids');
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Students/transfer_student_view.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Students'), 'student_view.php')
        ->add(__('Transfer Students'));

    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/transport_route_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Transfer Students');
    echo '</h2>';
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();
    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }

   
    $type = array();
    $type =  array(''=>'Select  Type',
        'pickup' =>'Pick Up',
        'drop'=>'Drop',
        'both'=>'Both'
);

// $transport_for = array();
// $type =  array(''=>'Select  Transport For',
//     'oneway' =>'Oneway',
//     'twoway'=>'Two way',
    
// );

$sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
$resultp = $connection2->query($sqlp);
$rowdataprog = $resultp->fetchAll();

$program=array();  
$program2=array();  
$program1=array(''=>'Select Program');
foreach ($rowdataprog as $dt) {
    $program2[$dt['pupilsightProgramID']] = $dt['name'];
}
$program= $program1 + $program2;  

$sqlc = 'SELECT a.pupilsightYearGroupID, a.name, b.id, GROUP_CONCAT(fn_fee_structure_id) AS fsid FROM pupilsightYearGroup AS a LEFT JOIN fn_fees_class_assign AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID GROUP BY a.pupilsightYearGroupID ORDER BY a.pupilsightYearGroupID ASC ';
$resultc = $connection2->query($sqlc);
$rowdatacls = $resultc->fetchAll();
$firstClassId = $rowdatacls[0]['pupilsightYearGroupID'];


$classes=array(); 
$classes1=array(''=> 'Select Class'); 
$classes2=array();  
foreach ($rowdatacls as $dt) {
    $classes2[$dt['pupilsightYearGroupID']] = $dt['name'];
}
$classes = $classes1 + $classes2;
    
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/transfer_stud_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
  
    $form->addHiddenValue('stu_id', $studentids);

  
     
  $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'));
            $col->addSelect('pupilsightProgramID')->fromArray($program)->required()->placeholder();


            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->placeholder();
            
    
            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            
            $col->addTextField('');    
           
    
           
 $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('studentByClass', __('Class'));
            $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($firstClassId)->placeholder();
            $col = $row->addColumn()->setClass('newdes');           
            $col->addLabel('remarks', __('Remarks'));
            $col->addTextArea('remarks')->setRows(2);    

            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            
            $col->addTextField('');    
              
            
        $row = $form->addRow()->setID('route_stops');
            $row->addFooter();
            $row->addSubmit();
    

    echo $form->getOutput();
   
}