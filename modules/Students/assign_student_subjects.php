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


if (isActionAccessible($guid, $connection2, '/modules/Students/assign_student_subjects.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Students'), 'student_view.php')
        ->add(__('Assign Subjects'));


        $search = isset($_GET['search']) ? $_GET['search']  : '';
        $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID']) ? $_GET['pupilsightYearGroupID']  : '';

        $studentGateway = $container->get(StudentGateway::class);
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
        $result = $studentGateway->selectActiveStudentByPerson($pupilsightSchoolYearID, $pupilsightPersonID);
/*echo "<pre>";
        print_r( $result); */
    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/transport_route_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Core Subjects Assigned to Students From Classes');
    echo '</h2>';
   
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();
    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }




$data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
$sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
$result = $pdo->executeQuery($data, $sql);

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

//echo "<a  id='unassignStudentroute' data-type='student' class='btn btn-primary'>UnAssign Route</a>&nbsp;&nbsp;";  
    
    $form = Form::create('program', '');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
  
    $form->addHiddenValue('stu_id', $studentids);
   
            
            $row = $form->addRow();
            

          /*  $col = $row->addColumn()->setClass(' newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'));
            $col->addSelectYearGroup('pupilsightYearGroupID')->required()->setClass(' new_width'); 
       
     
             $col = $row->addColumn()->setClass('hiddencol ');
            $col->addLabel('pupilsightRollGroupID', __('Section'));
            $col->addTextField('');    

    
            $row = $form->addRow();


            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightRollGroupID', __('Section'));
            $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required()->setClass(' new_width');
           
           */
              
        //    $col = $row->addColumn()->setClass('');
           
         //   
         //   $col->addSubmit(__('Assign Section'))->setClass(' submt new_margin assign_section ');
           
          //  
          //  $col->addSubmit(__('Remove Section'))->setClass(' submt new_margin remove_section ');

         //   $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
         //   
         //   $col->addTextField('');    
          
 
    /*    $row = $form->addRow()->setID('route_stops');
            $row->addFooter();
            $row->addSubmit();*/
    

    echo $form->getOutput();
    
   // echo '<script>$("#oneway_bl,#oneway_bl1,#oneway_bl2,#twoway_bl1,#twoway_bl2,#twoway_bl3").hide();</script>';
   $criteria = $studentGateway->newQueryCriteria()
 ->fromPOST();
   
   $students = $studentGateway->queryStudentsBySchoolYearandID_with_assigned_subjects($criteria, $pupilsightSchoolYearID,$studentids);

    

   // DATA TABLE
   $table = DataTable::createPaginated('students', $criteria);

   $table->modifyRows($studentGateway->getSharedUserRowHighlighter());

  /* if ($canViewFullProfile) {
       $table->addMetaData('filterOptions', [
           'all:on'        => __('All Students')
       ]);*/

       if ($criteria->hasFilter('all')) {
           $table->addMetaData('filterOptions', [
               'status:full'     => __('Status').': '.__('Full'),
               'status:expected' => __('Status').': '.__('Expected'),
               'date:starting'   => __('Before Start Date'),
               'date:ended'      => __('After End Date'),
           ]);
       }
  // }
   
//assign subjects
   // COLUMNS
   $table->addCheckboxColumn('student_id',__(''))
   ->setClass('chkbox')
   ->notSortable();
   $table->addColumn('student', __('Student'))
   
       ->sortable(['surname', 'preferredName'])
       ->format(function ($person) {
           return Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
       });
   $table->addColumn('pupilsightPersonID', __('Student Id'));    
   // $table->addColumn('pupilsightStudentEnrolmentID', __('Enrolment Id')); 
 //  $table->addColumn('academic_year', __('Academic Year'));
   $table->addColumn('program', __('Program'));   
   $table->addColumn('classname', __('Class'));
   $table->addColumn('coresubject', __('Core Subjects'));
  // print "<i class='mdi mdi-check mdi-24px' ></i> " ;
   
  
   

   
 //  $table->addColumn('rollGroup', __('Section'));


  /* 
  $table->addActionColumn()
  ->addParam('student_id')
  ->addParam('search', $criteria->getSearchText(true))
  ->format(function ($person, $actions) use ($guid) {


    $sectn = $person['rollGroup'];
    if($sectn != ""){
      $actions->addAction('Remove', __('Remove'))
              ->setURL('/modules/Students/removesection.php');
    }
  });*/
   


   echo $table->render($students);



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