<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$studentids = $session->get('student_ids');
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;
     

if (isActionAccessible($guid, $connection2, '/modules/Students/register_student_bulk.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Students'), 'student_view.php')
        ->add(__('Register Students'));


        $search = isset($_GET['search']) ? $_GET['search']  : '';
        $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID']) ? $_GET['pupilsightYearGroupID']  : '';

        $studentGateway = $container->get(StudentGateway::class);
        $criteria = $studentGateway->newQueryCriteria()
        ->fromPOST();


        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
      //  $result = $studentGateway->selectActiveStudentByPerson($pupilsightSchoolYearID, $pupilsightPersonID);
        
        

      /*  echo "<pre>";
        print_r( $result_inactive_students); */
    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/transport_route_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Register Students');
    echo '</h2>';
   
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();
    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }



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

$result_inactive_students = $studentGateway->queryStudentsBySchoolYear_inactive_students($criteria,$pupilsightSchoolYearID);
//echo count($result_inactive_students);

if (count($result_inactive_students) < 1) {
    echo "<div class='alert alert-danger'>";
    echo __('There is no Student to Register,all students are Registered. ');
    echo '</div>';
}
else {
  //  $rowdata = $result->fetch();
    
    $form = Form::create('program','');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
  
  //  $form->addHiddenValue('stu_id', $studentids);   
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Organisation'));
            $col->addSelect('pupilsightProgramID')->fromArray($program)->placeholder();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('studentByClass', __('Class'));
            $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->placeholder();


            $col = $row->addColumn()->setClass('newdes');
            $col->addSubmit(__('Register'))->setClass(' submt new_margin bulk_reg_students ');
/*
            $regdreg =  array();      
            $regdreg =  array(''=>'Select',
               'reg' =>'Register',
               'dereg'=>'De-register');
             
           $col = $row->addColumn()->setClass('newdes');
           $col->addLabel('reg_degreg', __('Select'));
           $col->addSelect('reg_degreg')->fromArray($regdreg)->setId('reg_dereg_id')->selected('reg')->placeholder();


           $status =  array();      
           $status =  array(''=>'Select Status',
              'Discontinued' =>'Discontinued',
              'Transferred'=>'Transferred'
           );
            
          $col = $row->addColumn()->setClass('dereg_col newdes nodisplay');
          $col->addLabel('dereg_status', __('Status'));
          $col->addSelect('dereg_status')->fromArray($status)->setId('dereg_sts')->placeholder();
           */   
            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            
            $col->addTextField('');    
           
      
      //      $row = $form->addRow();

           /* $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('dob', __('Date of Birth'))->addClass('dte');
            $dob = date('d/m/Y', strtotime($rowdata['dob']));          
            $col->addDate('dob')->required()->setValue($dob)->setClass(' small_wdth ');
           */
           
           
                    
            
        $row = $form->addRow()->setID('');
            $row->addFooter();
          
    

    echo $form->getOutput();

        }
    
  
  // $students = $studentGateway->queryStudentsBySchoolYearandID($criteria, $pupilsightSchoolYearID,$studentids);

 

   // DATA TABLE
   $table = DataTable::createPaginated('inactive_students', $criteria);


   

   // COLUMNS
   $table->addCheckboxColumn('student_id',__(''))
   ->setClass('chkbox')
   ->notSortable();
   $table->addColumn('student', __('Student'))
   
       ->sortable(['surname', 'preferredName'])
       ->format(function ($person) {
           return Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
       });
  // $table->addColumn('pupilsightPersonID', __('Student Id'));    
   // $table->addColumn('pupilsightStudentEnrolmentID', __('Enrolment Id')); 
 //  $table->addColumn('academic_year', __('Academic Year'));
   $table->addColumn('program', __('Program'));   
   $table->addColumn('classname', __('Class'));
   $table->addColumn('rollGroup', __('Section'));
   $table->addColumn('active_status', __('Status'));

   
 /* $table->addActionColumn()
  ->addParam('student_id')
  ->addParam('search', $criteria->getSearchText(true))
  ->format(function ($person, $actions) use ($guid) {


    $sectn = $person['rollGroup'];
    if($sectn != ""){
      $actions->addAction('Remove', __('Remove'))
              ->setURL('/modules/Students/removesection.php');
    }
  });*/
   


   echo $table->render($result_inactive_students);



}

echo "<style>
.new_width 
{
    width: 400px;
    float: left !important;
}
.new_margin  

{
    margin-top: 28px;
}
</style>";