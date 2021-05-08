<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_elective_group.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Elective Group'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
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

    if($_POST){
        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];

        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" GROUP BY a.pupilsightYearGroupID';
        $result = $connection2->query($sql);
        $classesdata = $result->fetchAll();

        $classes = array();
        $classes2 = array();
        $classes1 = array('' => 'Select Class');
        foreach ($classesdata as $ct) {
            $classes2[$ct['pupilsightYearGroupID']] = $ct['name'];
        }
        $classes = $classes1 + $classes2;
        
    } else {
        $classes = array('' => 'Select Class');
        if(!empty($_GET['sid'])){
            $pupilsightSchoolYearID = $_GET['sid'];
        } else {
            $pupilsightSchoolYearID = '';
        }
        if(!empty($_GET['pid'])){
            $pupilsightProgramID = $_GET['pid'];
            $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" GROUP BY a.pupilsightYearGroupID';
            $result = $connection2->query($sql);
            $classesdata = $result->fetchAll();

            $classes = array();
            $classes2 = array();
            $classes1 = array('' => 'Select Class');
            foreach ($classesdata as $ct) {
                $classes2[$ct['pupilsightYearGroupID']] = $ct['name'];
            }
            $classes = $classes1 + $classes2;
        } else {
            $pupilsightProgramID =  '';
        }
        if(!empty($_GET['cid'])){
            $pupilsightYearGroupID = $_GET['cid'];
        } else {
            $pupilsightYearGroupID =  '';
        }
        
        
        
    }
    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
  
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');
  
    $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightYearGroupID', __('Class'));
        $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightYearGroupID)->required();

$col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('<button id=""  class=" btn btn-primary">GO</button> &nbsp&nbsp;&nbsp;<a style="display:none;" id="clickElectiveGroup" href="fullscreen.php?q=/modules/Academics/copy_elective_group.php"  class="thickbox btn btn-primary">Copy</a>
    <a id="copyElectiveGroup" class="btn btn-primary">Copy</a>');  
   


    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('');  
    echo $searchform->getOutput();

   
    $CurriculamGateway = $container->get(CurriculamGateway::class);

    // QUERY
    $criteria = $CurriculamGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $electiveGrp = $CurriculamGateway->getElectiveGrp($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID);

    // DATA TABLE
    $table = DataTable::createPaginated('electiveGroup', $criteria);

    if(!empty($pupilsightYearGroupID)){
        echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Academics/create_elective_group.php&sid=".$pupilsightSchoolYearID."&pid=".$pupilsightProgramID."&cid=".$pupilsightYearGroupID."' class=' btn btn-primary'>Add</a><div class='float-none'></div></div></div>";  
    } else {
        echo "<div style='height:50px;'><div class='float-right mb-2'><a style='color: #dae0e5!important;' class=' btn btn-primary' disabled>Add</a><div class='float-none'></div></div></div>";  
    }
   
    
    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Academics/schoolYear_manage_add.php')
    //     ->displayLabel();
    $table->addCheckBoxColumn('id', __(''));
    $table->addColumn('name', __('Elective Group Name'));
    $table->addColumn('order_no', __('Order Number'));
   
    $table->addColumn('min_selection', __('Min Selection'));
    $table->addColumn('max_selection', __('Max Selection'));
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($skills, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Academics/ac_manage_electiveGrp_edit.php');
                    
                    

            // if ($schoolYear['status'] != 'Current') {
                $actions->addAction('delete', __('Delete'))
                       ->setURL('/modules/Academics/ac_manage_electiveGrp_delete.php');
            // }
        });

    echo $table->render($electiveGrp);
}
