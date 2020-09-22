<?php
/*
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/TransportFee/generation_by_class.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Invoice Generarion By Class'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
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
    if($_POST){
        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
    } else {
        $pupilsightProgramID =  '';
        $pupilsightSchoolYearIDpost = '';
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    
    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $academic=array();
    $ayear = '';
        if(!empty($rowdata)){
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }

    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');

        $row = $searchform->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'));
            $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelectSchoolYear('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost)->required();    

            $col = $row->addColumn()->setClass('newdes');   
            $col->addLabel('', __(''));

            $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">submit</button>');  
        echo $searchform->getOutput();
        echo "<div style='height:50px;'><div class='float-left mb-2'>";
        echo "<a  id='' class='btn btn-primary'>Generate Invoice</a>&nbsp;&nbsp;</div><div class='float-none'></div></div>";  
       
        $TransportGateway = $container->get(TransportGateway::class);
        $criteria = $TransportGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

        $students = $TransportGateway->getclasses($criteria, $pupilsightProgramID, $pupilsightSchoolYearIDpost);
            // print_r($students);die();
            $table = DataTable::createPaginated('FeeStructureManage', $criteria)->setId('table_width');
            $table->addCheckboxColumn('stuid',__(''))
            ->setClass('chkbox')
            ->notSortable();
            $table->addColumn('class', __('Class'));
        echo $table->render($students);
}