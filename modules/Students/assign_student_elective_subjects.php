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
use Pupilsight\Domain\Departments\DepartmentGateway;



if (isActionAccessible($guid, $connection2, '/modules/Students/assign_student_elective_subjects.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Students'), 'student_view.php')
        ->add(__('Assign Elective Subject  to Students'));


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
    echo __('Assign Elective Subjects To Student');
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

$data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
$sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
$result = $pdo->executeQuery($data, $sql);


$data_sel = array('pupilsightPersonID'=>$studentids,'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
$sql_sel = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID and pupilsightSchoolYearID=:pupilsightSchoolYearID';
$result_person =  $pdo->executeQuery($data_sel, $sql_sel);
$row_person = $result_person->fetch();
/*
echo "<pre>";
print_r($row_person);

*/
//select subjects from department
$sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ';
$resultd = $connection2->query($sqld);
$rowdatadept = $resultd->fetchAll();


$subjects=array();  
$subject2=array();  
// $subject1=array(''=>'Select Subjects');
foreach ($rowdatadept as $dt) {
    $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
}
$subjects=  $subject2;  


$schoolYearName = ($result->rowCount() == 1)? $result->fetchColumn(0) : $_SESSION[$guid]['pupilsightSchoolYearName'];

$row = $form->addRow();
    $row->addLabel('yearName', __('School Year'));
    $row->addTextField('yearName')->readOnly()->maxLength(20)->setValue($schoolYearName);

/*  $row = $form->addRow();
    $row->addLabel('pupilsightPersonID', __('Student'));
    $row->addSelectStudent('pupilsightPersonID', $pupilsightSchoolYearID, array('allStudents' => true))->required()->placeholder();
*/

/*
$row = $form->addRow();
    $row->addLabel('pupilsightProgramID', __('Program'));
    $row->addSelect('pupilsightProgramID')->fromArray($program)->required()->placeholder();

$row = $form->addRow();
    $row->addLabel('pupilsightYearGroupID', __('Class'));
    $row->addSelectYearGroup('pupilsightYearGroupID')->required();

 */
    $row = $form->addRow();
  /*  $row->addLabel('pupilsightDepartmentID', __('Subjects'));

    $select_sub = '<select class="w-full" id="issubjects" name="pupilsightDepartmentID" multiple="multiple">';
  
   
    
    foreach (  $subjects as $key => $sub ) {
        $select_sub .=   '<option value="' . $key . '">' .$sub . "</option>";
    }
  
    $select_sub .='</select>';

   // echo $select_sub;

    $row->addContent($select_sub);*/
  //  $row->addSelect('pupilsightDepartmentID')->fromArray($subjects)->required()->placeholder()->setId('issubjects'); 
  //  $row->addSubmit(__('OK'))->addClass('submit_align submt btnSelected');

  $sql = "SELECT pupilsightDepartmentID as value, name FROM pupilsightDepartment WHERE type='Learning Area' ORDER BY name";
  $results = $pdo->executeQuery(array(), $sql);

  $row = $form->addRow();
  $row->addLabel('pupilsightDepartmentIDs', __('Select Elective Subjects'));
     
      if ($results->rowCount() == 0) {
          $row->addContent(__('No Subjects available.'))->wrap('<i>', '</i>');
      } else {
         // $row->addCheckbox('pupilsightDepartmentIDs')->fromResults($results)->addCheckAllNone();
          $row->addCheckbox('pupilsightDepartmentIDs')->fromResults($results);
      }

      $row = $form->addRow();
      $col = $row->addColumn()->setClass(' nobrdbtm  ');
      $col->addTextField('stud_id')->addClass('txtfield hidediv stud_id')->setValue($studentids);
      $col->addTextField('pupilsightProgramID')->addClass('txtfield hidediv ')->setValue($row_person['pupilsightProgramID']);
      $col->addTextField('pupilsightYearGroupID')->addClass('txtfield hidediv ')->setValue($row_person['pupilsightYearGroupID']);
      
$row = $form->addRow();
    $row->addFooter();
    $row->addSubmit()->addClass('submit_align submt assign_elective_sub');

echo $form->getOutput();


//$studentGateway = $container->get(StudentGateway::class);

// QUERY
$criteria = $studentGateway->newQueryCriteria()
    ->sortBy('id')
    ->fromPOST();

$student_elec_subj = $studentGateway->get_assigned_elect_sub_tostudents($criteria,$studentids);

/*  echo "<pre>";
print_r($departments );
*/
// DATA TABLE
$table = DataTable::createPaginated('assignedelectivesubjectstudent', $criteria);

$table->addColumn('student', __('Student'))
   
->sortable(['surname', 'preferredName'])
->format(function ($person) {
    return Format::name($person['officialName'], 'Student', true, true) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
});
$table->addColumn('pupilsightPersonID', __('Student Id'));   
$table->addColumn('program_name', __('Program'));
// $table->addColumn('type', __('Type'))->translatable();
$table->addColumn('class', __('Class'));
$table->addColumn('subject', __('Elective Subjects'));


$table->addActionColumn()
        ->addParam('id')
        ->addParam('pupilsightPersonID')
        ->addParam('pupilsightProgramID')
        ->addParam('pupilsightYearGroupID')
        ->addParam('pupilsightDepartmentID')
        ->format(function ($facilities, $actions) use ($guid) {
            // $actions->addAction('copynew', __('Copy'))
            //         ->setURL('/modules/Transport/transport_route_copy.php');
        
            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Students/remove_assigned_elect_subject_from_student.php');
            
            
        });
   
    
// ACTIONS
/*  $table->addActionColumn()
    ->addParam('pupilsightDepartmentID')
    ->format(function ($department, $actions) {
        $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/School Admin/department_manage_edit.php');

        $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/School Admin/department_manage_delete.php');
    });
*/
echo $table->render($student_elec_subj);


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
