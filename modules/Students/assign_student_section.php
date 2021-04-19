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


if (isActionAccessible($guid, $connection2, '/modules/Students/assign_student_section.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Students'), 'student_view.php')
        ->add(__('Assign Section'));


        $search = isset($_GET['search']) ? $_GET['search']  : '';
      

        if (empty($_GET['pupilsightYearGroupID'])) {
             $sqlp = 'SELECT pupilsightStudentEnrolment.pupilsightProgramID,pupilsightStudentEnrolment.pupilsightYearGroupID  FROM pupilsightPerson 
    JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) 
    WHERE pupilsightPerson.pupilsightPersonID IN (' . $studentids . ') 
    	';
            $resultp = $connection2->query($sqlp);
            $rowdata = $resultp->fetch();
            $pupilsightProgramID = $rowdata['pupilsightProgramID'];
            $pupilsightYearGroupID = $rowdata['pupilsightYearGroupID'];
        } else {
            $pupilsightProgramID = isset($_GET['pupilsightProgramID'])?$_GET['pupilsightProgramID']:"";
            $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])?$_GET['pupilsightYearGroupID']:"";
           
        }

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
    echo __('Assign Students to Section');
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


$sqls = 'SELECT a.*, c.name FROM pupilsightProgramClassSectionMapping AS a ';       
//$sqls .= "left join assignstaff_toclasssection as b on a.pupilsightMappingID= b.pupilsightMappingID ";     
$sqls .= "LEFT JOIN pupilsightRollGroup AS c ON a.pupilsightRollGroupID = c.pupilsightRollGroupID where a.pupilsightSchoolYearID = ".$pupilsightSchoolYearID." AND a.pupilsightProgramID='".$pupilsightProgramID."'AND  a.pupilsightYearGroupID = '".$pupilsightYearGroupID."' ";

 $sqls .=" GROUP BY a.pupilsightRollGroupID";
//echo $sqls;
$results = $connection2->query($sqls);
$sectionsdata = $results->fetchAll();

$sections_arr = array();
$sections2 = array();
$sections1 = array('' => 'Select Section');
foreach ($sectionsdata as $ct) {
    $sections2[$ct['pupilsightRollGroupID']] = $ct['name'];
}
$sections_arr = $sections1 + $sections2;


//echo "<a  id='unassignStudentroute' data-type='student' class='btn btn-primary'>UnAssign Route</a>&nbsp;&nbsp;";  
    
    $form = Form::create('program', '');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
    $form->addHiddenValue('stu_id', $studentids);
    $row = $form->addRow();
    /*$col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->placeholder();
   */
    $col = $row->addColumn()->setClass(' newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelectYearGroup('pupilsightYearGroupID')->required()->setClass('dsble_attr new_width')->selected($pupilsightYearGroupID); 
    $col = $row->addColumn()->setClass('hiddencol ');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addTextField('');    
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->required()->setId('pupilsightRollGroupID_sel')->setClass(' new_width')->fromArray($sections_arr)->placeholder();
   // $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required()->setClass(' new_width');
    $col = $row->addColumn()->setClass('btnDisplay');
    
    $col->addSubmit(__('Assign Section'))->setClass(' submt new_margin assign_section ');   
    
    $col->addSubmit(__('Remove Section'))->setClass(' submt new_margin remove_section ');
    $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
    $col->addTextField('');    

    echo $form->getOutput();
    
   // echo '<script>$("#oneway_bl,#oneway_bl1,#oneway_bl2,#twoway_bl1,#twoway_bl2,#twoway_bl3").hide();</script>';
   $criteria = $studentGateway->newQueryCriteria()
 ->fromPOST();
   
   $students = $studentGateway->queryStudentsBySchoolYearandID($criteria, $pupilsightSchoolYearID,$studentids);
   // DATA TABLE
   /*
   $table = DataTable::createPaginated('students', $criteria);

   $table->modifyRows($studentGateway->getSharedUserRowHighlighter());

  
   $table->addCheckboxColumn('student_id',__(''))
   ->setClass('chkbox')
   ->notSortable();
   $table->addColumn('studentName', __('Student'));
   $table->addColumn('pupilsightPersonID', __('Student Id'));    
   
   $table->addColumn('classname', __('Class'));
   $table->addColumn('rollGroup', __('Section'));

//   $table->addActionColumn()
//   ->addParam('student_id')
//   ->addParam('search', $criteria->getSearchText(true))
//   ->format(function ($person, $actions) use ($guid) {

//     $sectn = $person['rollGroup'];
//     if($sectn != ""){
//       $actions->addAction('Remove', __('Remove'))
//               ->setURL('/modules/Students/removesection.php');
//     }
//   });

   echo $table->render($students);
   */
}
//print_r($students);
?>

<table class="table">
    <thead>
        <tr>
            <th><input type="checkbox" class="chkAll" ></th>
            <th>Student Id</th>
            <th>Student Name</th>
            <th>Class</th>
            <th>Section</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($students)){
            foreach($students as $st){     
        ?>
        <tr>
            <td><input type="checkbox" class="chkChild" name="student_id[]" value="<?php echo $st['pupilsightPersonID'];?>"></td>
            <td><?php echo $st['pupilsightPersonID'];?></td>
            <td><?php echo $st['studentName'];?></td>
            <td><?php echo $st['classname'];?></td>
            <td><?php echo $st['rollGroup'];?></td>
        </tr>
        <?php } } ?>
    </tbody>
</table>
<style>
.btnDisplay > div {
    display: inline-flex;
}

.remove_section {
    margin-left: 10px;
}

.new_width 
{
    width: 400px;
    float: left !important;
}
.new_margin  

{
    margin-top: 28px;
}
#TB_ajaxContent,#TB_title {
        display:none;/* to avoid multiple occurence of popup in same window   */
    }
</style>
 
<script>
    $(document).ready(function() {
/* to avoid multiple occurence of popup in same window   */
        $("div #TB_title:eq(0)").css( 'display', 'block');
        $("div #TB_ajaxContent:eq(0)").css( 'display', 'block');
  /* to avoid multiple occurence of popup in same window   */
       
        var id = $("#pupilsightYearGroupID").val();     
        var type = 'getSection';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type },
            async: true,
            success: function(response) {
                $("#pupilsightRollGroupID").html();
                $("#pupilsightRollGroupID").html(response);
            }
        });
     
});
 </script>   