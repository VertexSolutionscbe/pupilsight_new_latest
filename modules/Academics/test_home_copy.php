<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$session = $container->get('session');
$subjectToClassId = $session->get('subjectToClassId');


if (isActionAccessible($guid, $connection2, '/modules/Academics/test_home_copy.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $test_id = $_GET['tid'];

    $page->breadcrumbs->add(__('Manage School Years'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear WHERE status="Upcoming"';
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



    $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/test_home_copyProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
    $form->addHiddenValue('test_id', $test_id); 
 
    $row = $form->addRow();        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
        $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID);   

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightProgramID', __('Program'));
        $col->addSelect('pupilsightProgramID')->fromArray($program)->required()->placeholder();
        
      
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
        $col->addSelect('pupilsightYearGroupID')->placeholder('Select Class')->selectMultiple()->required();
        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __(''))->addClass('dte');
        $col->addSubmit();

        echo $form->getOutput();
  
}
