<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_test.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Test'));
    
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if($_POST){
    
        $pupilsightSchoolYearID =  $_POST['pupilsightSchoolYearID'];
       
    } else {
      
        $pupilsightSchoolYearID =  $_SESSION[$guid]['pupilsightSchoolYearID'];
        
    }
    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();

    
    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('<a style="display:none;" id="updateTest" href="" class="">Update</a><a id="updateTestClick" data-hrf="index.php?q=/modules/Academics/update_manage_test.php" class=" btn btn-primary">Update</a>'); 

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required();
     
    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('<button id=""  class=" btn btn-primary">Go</button>'); 

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('');  
    echo $searchform->getOutput();
    $CurriculamGateway = $container->get(CurriculamGateway::class);
    // QUERY
    $criteria = $CurriculamGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $manage_test = $CurriculamGateway->getAllgeneralTestMaster($criteria, $pupilsightSchoolYearID);

    // DATA TABLE
    $table = DataTable::createPaginated('electiveGroup', $criteria);
     $table->addCheckboxColumn('id',__(''))  
   ->notSortable();
    $table->addColumn('name', __('Name'));
    $table->addColumn('code', __('Test Code'))->translatable();
    $table->addColumn('academic_year', __('Academic Year')); 

    echo $table->render($manage_test);
}