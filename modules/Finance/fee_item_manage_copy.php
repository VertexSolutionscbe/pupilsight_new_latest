<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;

$session = $container->get('session');
$tid = $session->get('fee_items');


if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_item_manage_copy.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Copy Fee Items'));

    echo '<h3>';
    echo __('Copy Fee Items');
    echo '</h3>';

  
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
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

    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program = $program1 + $program2;



    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $form = Form::create('copytestclasssectionwise', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_item_manage_copyProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
    $form->addHiddenValue('tid', $tid); 
 
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID); 
    
        $row = $form->addRow();
    $col->addLabel('', __(''))->addClass('dte');
 //   $row = $form->addRow()->setID('lastseatdiv');
    $row->addFooter();
    $row->addColumn()->setClass('');
    $row->addSubmit(__('Copy'));


        echo $form->getOutput();

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
