<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$session = $container->get('session');
$sub_id = $session->get('copyDesc');
$cls= $session->get('copycls');
$prg =  $session->get('copyprg');

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_home_copy.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    // $test_id = $_GET['tid'];

    $page->breadcrumbs->add(__('Manage School Years'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear WHERE status!="Past"';
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



    $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/descriptive_indicator_configCopyProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
    $form->addHiddenValue('sub_id', $sub_id); 
    $form->addHiddenValue('copyCls', $cls); 
    $form->addHiddenValue('copyPrg', $prg); 
 
    $row = $form->addRow();        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
        $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID);   

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightProgramID', __('Program'));
        $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProg')->addClass('txtfield')->fromArray($program)->required();
        
      
        $col = $row->addColumn()->setClass('newdes showClass');
        $col->addLabel('classes', __('Class'))->addClass('dte');
        $col->addSelect('classes')->setId('showMultiClassByProg')->addClass('txtfield')->placeholder('Select Class')->selectMultiple(); 
        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __(''))->addClass('dte');
        $col->addSubmit();

        echo $form->getOutput();
  
}
?>

<script>
   
   $(document).ready(function () {
         $('#showMultiClassByProg').selectize({
             maxItems: 15,
             plugins: ['remove_button'],
         });
   });
   
</script>