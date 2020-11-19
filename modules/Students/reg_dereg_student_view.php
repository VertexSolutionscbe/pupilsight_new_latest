<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
 $studentids = $session->get('student_ids');
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Students/reg_dereg_student_view.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Students'), 'student_view.php')
        ->add(__('Register and De-Register Students'));

    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/transport_route_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Register & Deregister Students');
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

 $pupilsightPersonID = $studentids;
$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
$sql = "SELECT * FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID";
$result = $connection2->prepare($sql);
$result->execute($data);

if ($result->rowCount() != 1) {
    echo "<div class='alert alert-danger'>";
    echo __('The selected record does not exist, or you do not have access to it.');
    echo '</div>';
}
else {
    $rowdata = $result->fetch();
    
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/reg_dereg_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
  
    $form->addHiddenValue('stu_id', $studentids);   
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Organisation'));
            $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($rowdata['pupilsightProgramID'])->required()->placeholder();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('studentByClass', __('Class'));
            $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($rowdata['pupilsightYearGroupID'])->placeholder();

              
            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            
            $col->addTextField('');    
           
      
            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('dob', __('Date of Birth'))->addClass('dte');
            $dob = date('d/m/Y', strtotime($rowdata['dob']));          
            $col->addDate('dob')->required()->setValue($dob)->setClass(' small_wdth ');
           
           
            $regdreg =  array();      
            //  $regdreg =  array(''=>'Select',
            //     'reg' =>'Register',
            //     'dereg'=>'De-register');
            $regdreg =  array('dereg'=>'De-register');
              
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('reg_degreg', __('Select'));
            $col->addSelect('reg_degreg')->fromArray($regdreg)->setId('reg_dereg_id');


            $status =  array();      
            $status =  array(''=>'Select Status',
               'Discontinued' =>'Discontinued',
               'Transferred'=>'Transferred'
            );
             
        //    $col = $row->addColumn()->setClass('dereg_col newdes nodisplay');
           $col = $row->addColumn();
           $col->addLabel('dereg_status', __('Status'));
           $col->addSelect('dereg_status')->fromArray($status)->setId('dereg_sts')->placeholder();
                    
            
        $row = $form->addRow()->setID('');
            $row->addFooter();
            $row->addSubmit();
    

    echo $form->getOutput();
   
}
}

echo "<style>
.small_wdth 
{
    width:240px !important;
}
.nodisplay
{
    display:none;
}

</style>";