<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;

$session = $container->get('session');
$tid = $session->get('testid');


if (isActionAccessible($guid, $connection2, '/modules/Academics/copy_test_to_sections.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Copy Test'));

    echo '<h3>';
    echo __('Copy Test');
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

    $form = Form::create('copytestclasssectionwise', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/copt_test_to_sections_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
    $form->addHiddenValue('tid', $tid); 
 
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID); 
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPPCopy')->fromArray($program)->required()->placeholder('Select Program');


    // $col = $row->addColumn()->setClass('newdes');
    // $col->addLabel('pupilsightYearGroupID', __('Class'));
    // $col->addSelect('pupilsightYearGroupID')->selectMultiple()->setId('pupilsightYearGroupIDbyPP')->required();
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
    $col->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPPCopy')->placeholder('Select Class')->selectMultiple()->required();

    $col = $row->addColumn()->setClass('newdes ');
    $col->addLabel('test_master_id', __('Test Master'))->addClass('dte');
    $col->addSelect('test_master_id')->required()->placeholder('Select Test Master')->selectMultiple();
    

    // $col = $row->addColumn()->setClass('newdes');
    // $col->addLabel('test_id', __('Select Test'));
    // $col->addSelect('test_id')->setId('testId')->required();


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
    $(document).ready(function () {
      	$('#pupilsightYearGroupIDbyPPCopy').selectize({
      		plugins: ['remove_button'],
        });
        
        $('#test_master_id').selectize({
      		plugins: ['remove_button'],
      	});

    });

    $(document).on('change', '#pupilsightProgramIDbyPPCopy', function () {
        var id = $(this).val();
        var type = 'getClass';
        $('#pupilsightYearGroupIDbyPPCopy').selectize()[0].selectize.destroy();
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type },
            async: true,
            success: function (response) {
                $("#pupilsightYearGroupIDbyPPCopy").html();
                $("#pupilsightYearGroupIDbyPPCopy").html(response);
                $('#pupilsightYearGroupIDbyPPCopy').selectize({
                    plugins: ['remove_button'],
                });
            }
        });
    });

    $(document).on('change', '#pupilsightYearGroupIDbyPPCopy', function () {
        var id = $(this).val();
        var pid = $('#pupilsightProgramIDbyPPCopy').val();
        var type = 'getTestByClassProgram';
        $('#test_master_id').selectize()[0].selectize.destroy();
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid },
            async: true,
            success: function (response) {
                $("#test_master_id").html();
                $("#test_master_id").html(response);
                $('#test_master_id').selectize({
                    plugins: ['remove_button'],
                });
            }
        });
    });

</script>
