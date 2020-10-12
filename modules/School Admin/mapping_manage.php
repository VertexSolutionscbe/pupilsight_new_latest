<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\MappingGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/mapping_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    //Proceed!
    $page->breadcrumbs->add(__('Manage Mapping'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = isset($_GET['search'])? $_GET['search'] : '';

    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }

    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();
    $prog = array();
    $prog1 = array();
    foreach($rowdataprog as $prg){
        $id = 'type:'.$prg['pupilsightProgramID'];
        $prog[$id] = $prg['name'];
    }

    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program = $program1 + $program2;

    if ($_POST) 
    {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
        
        $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
        $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);
    }
    else 
    {
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $pupilsightProgramID =  '';
        $pupilsightYearGroupID = '';
        $pupilsightRollGroupID = '';
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    }

    $MappingGateway = $container->get(MappingGateway::class);
    $criteria = $MappingGateway->newQueryCriteria()
                                ->searchBy($MappingGateway->getSearchableColumns(), $search)
                                ->sortBy(['pupilsightMappingID'])
                                ->fromPOST();

    $yearGroups = $MappingGateway->queryMappingGroups($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID);

    echo '<h2>';
    echo __('Filter');
    echo '</h2>';

    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required();
   
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->required()->fromArray($classes)->selected($pupilsightYearGroupID);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->selected($pupilsightRollGroupID);

    $col = $row->addColumn()->setClass('newdes');
    

    $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>  &nbsp;&nbsp; <a href=""  >Clr Filter</a>');

    
    echo $searchform->getOutput();

    // DATA TABLE
    $table = DataTable::createPaginated('programManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/School Admin/mapping_manage_add.php')
        ->displayLabel();
   
    // $table->addMetaData('filterOptions', [
    //     'program'     => __('Program').': '.__('Full'),
    //     'status:left'     => __('Status').': '.__('Left'),
    //     'status:expected' => __('Status').': '.__('Expected'),
    //     'date:starting'   => __('Before Start Date'),
    //     'date:ended'      => __('After End Date'),
    // ]);
    
    //$table->addMetaData('filterOptions', $prog);
    $table->addColumn('academicyear', __('Academic Year'));
    $table->addColumn('program', __('Program'));
    $table->addColumn('yearGroup', __('Class'));
    $table->addColumn('rollGroup', __('Section'));
   
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightMappingID')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/mapping_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/mapping_manage_delete.php');
        });

    echo $table->render($yearGroups);



    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
