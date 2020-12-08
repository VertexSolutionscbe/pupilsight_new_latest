<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Finance\FeesGateway;
use Pupilsight\Domain\Helper\HelperGateway;


if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_student_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Structure Student Assign'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    //$id = $_GET['id'];
   
    

    

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();




    
    echo "<a style='display:none' id='clickStudentPage' href='fullscreen.php?q=/modules/Finance/fee_structure_assign_student_manage_add.php&width=800'  class='thickbox '>Assign Fee Structure</a>";  
    echo "<a style='display:none' id='deleteStudentFeeStructure' href='fullscreen.php?q=/modules/Finance/fee_structure_assign_student_manage_delete.php'  class='thickbox '>Delete Fee Structure</a>";  
    echo "<a style='display:none' id='massDeleteStudentFeeStructure' href='fullscreen.php?q=/modules/Finance/fee_structure_assign_student_manage_massDelete.php'  class='thickbox '>Delete Fee Structure</a>";  
    


    echo "<div style='height:50px;'><div class='float-right mb-2'><a id='assignStudentPage' class=' btn btn-primary'>Assign Fee Structure</a>";  

    echo "&nbsp;&nbsp;<a id='deleteStudentPage' class=' btn btn-primary'>Delete Fee Structure</a>";

    echo "&nbsp;&nbsp;<i style='cursor:pointer' id='exportFeeAssignStr' title='Export Excel' class='mdi mdi-file-excel mdi-24px download_icon'></i> ";

    echo "</div><div class='float-none'></div></div>";

    // echo "&nbsp;&nbsp;<a  id='massDeleteStudentPage' class=' btn btn-primary'>Mass Delete</a></div><div class='float-none'></div></div>";  

    // $search = '';
    // if($_POST && !empty($_POST['search'])){
    //     $search =  $_POST['search'];
    // } else {
    //     $search = '';
    // }

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

    $program=array();  
    $program2=array();  
    $program1=array(''=>'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program= $program1 + $program2;  

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

    $sqlcol = 'SHOW COLUMNS FROM pupilsightPerson';   
    $resultcol = $connection2->query($sqlcol);
    $coldata = $resultcol->fetchAll();

    $searchby=array();  
    $searchby2=array();  
    $searchby1=array(''=>'Select Field');
    foreach ($coldata as $ct) {
        $searchby2[$ct['Field']] = $ct['Field'];
    }
    $searchby= $searchby2 + $searchby1;  
    $searchby= array_reverse($searchby);

    // $searchby = array(''=>'Search By', 'stu_name'=>'Student Name', 'stu_id'=>'Student Id', 'adm_id'=>'Admission Id', 'father_name'=>'Father Name', 'father_email'=>'Father Email', 'mother_name'=>'Mother Name', 'mother_email'=>'Mother Email');

    // echo '<pre>';
    // print_r($coldata);    
    // echo '</pre>';
    // die();
    $search =  '';
    $searchbyPost =  '';
    $advsearch = '';
    if($_POST){
        $input = $_POST;

        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        if(!empty($_POST['simplesearch'])){
            $search =  $_POST['simplesearch'];
            $searchbyPost =  $_POST['simplesearch'];
        } 
        if(!empty($_POST['search'])){
            $search =  $_POST['search'];
            $searchbyPost =  $_POST['search'];
            $advsearch =  $_POST['search'];
        } 
        $stuId = $_POST['studentId'];
        $searchtype = $_POST['searchtype'];
        $searchfield = $_POST['searchfield'];
        $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
        $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);
    } else {
        $input = '';
        $pupilsightProgramID =  '';
        $pupilsightSchoolYearIDpost =  $pupilsightSchoolYearID;
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $searchbyPost =  '';
        $search = '';
        $searchfield = '';
        $stuId = '0';
        $searchtype = '1';
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        unset($_SESSION['fee_str_search']);
    }

    if(!empty($pupilsightProgramID)){
        $_SESSION['fee_str_search'] = $input;
    } 


    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');

    if($_POST){
        if(!empty($_POST['simplesearch'])){
            $row = $searchform->addRow()->setId('normalSearchRow');
        } else {
            $row = $searchform->addRow()->setId('normalSearchRow')->setClass('hiddencol');
        }
    } else {
        $row = $searchform->addRow()->setId('normalSearchRow');
    }
    
    
    $col = $row->addColumn()->setClass('newdes');    
        $col->addLabel('', __(''));
        $col->addTextField('simplesearch')->placeholder('Search by Student Name, ID')->addClass('txtfield')->setValue($searchbyPost);

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('<input type="hidden" name="searchtype" value="1"><a id="simplesubmitInvoice" class=" btn btn-primary">Search</a>&nbsp;&nbsp;<a id="advanceSearch" class="btn btn-primary">Advance Search</a>');  
    //$col->addContent('<a id="advanceSearch" class="transactionButton btn btn-primary" style="position:absolute; right:0;">Advance Search</a>');
    
    if($_POST){
        if(empty($_POST['simplesearch'])){
            $row = $searchform->addRow()->setId('advanceSearchRow');
        } else {
            $row = $searchform->addRow()->setId('advanceSearchRow')->setClass('hiddencol');
        }
    } else {
        $row = $searchform->addRow()->setId('advanceSearchRow')->setClass('hiddencol');
    }
    
    
    

    $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
        $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost);  
        
    $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('pupilsightProgramID', __('Program'));
        $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->placeholder();
        
    $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('pupilsightYearGroupID', __('Class'));
        $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightYearGroupID);

    $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('pupilsightRollGroupID', __('Section'));
        $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->selected($pupilsightRollGroupID);

    $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('searchfield', __('Search Field'));
        $col->addSelect('searchfield')->fromArray($searchby)->selected($searchfield);    

    $col = $row->addColumn()->setClass('newdes advsrch');    
        $col->addLabel('search', __('Search'));
        $col->addTextField('search')->addClass('txtfield')->setValue($advsearch);

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('<input type="hidden" name="searchtype" value="2"><button id="" class=" btn btn-primary">Search</button><button id="submitInvoice" style="display:none;" class=" btn btn-primary">Submit</button><a id="normalSearch" title="Normal Search" class=" btn btn-primary" style="position:absolute; right:0; margin-right: -5px;width: 96px;">Nr Search</a>');   
    
    // $col->addContent('<button id="submitInvoice" style="display:none;" class="transactionButton btn btn-primary">Submit</button>'); 
    
    // $col->addContent('<a id="normalSearch" class="transactionButton btn btn-primary" style="position:absolute; right:0; margin-top: -39px;width: 100px; font-size: 8px; line-height: 28px;">Normal Search</a>');

    echo $searchform->getOutput();
    
    
    $criteria = $FeesGateway->newQueryCriteria()
        ->pageSize(5000)
        ->sortBy(['id'])
        ->fromPOST();
   
    $yearGroups = $FeesGateway->getFeesStructureAssignStudent($criteria,$input);
    $table = DataTable::createPaginated('FeeStructureStudentAssignManage', $criteria);

    
    $table->addCheckboxColumn('stuid',__(''))
    ->setClass('chkbox')
        ->context('Select');
    $table->addColumn('serial_number', __('Sl No'));
    $table->addColumn('admission_no', __('Admission No'));    
    $table->addColumn('student_name', __('Student Name'));
    $table->addColumn('progname', __('Program'));
    $table->addColumn('classname', __('Class'));
    $table->addColumn('structure_name', __('Fee Structure'));
    //$table->addColumn('section', __('Section'));
   
        
    // ACTIONS
    // $table->addActionColumn()
    //     ->addParam('id')
    //     ->format(function ($facilities, $actions) use ($guid) {
    //         $actions->addAction('editnew', __('Edit'))
    //                 ->setURL('/modules/Finance/fee_structure_assign_student_manage_edit.php');

    //         $actions->addAction('delete', __('Delete'))
    //                 ->setURL('/modules/Finance/fee_structure_assign_student_manage_delete.php');
    //     });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}

?>

<script>
    $(document).on('click', '#exportFeeAssignStr', function () {
       
        $("#expore_tbl tr").each(function () {
            $(this).find("th:first").remove();
            $(this).find("td:first").remove();
        });

       $("#expore_tbl").table2excel({
           name: "Worksheet Name",
           filename: "Student_Fee_Structure.xls",
           fileext: ".xls",
           // exclude: ".checkall",
           // exclude: ".rm_cell",
           // exclude_inputs: true,
           // columns: [0, 1, 2, 3, 4, 5]

       });
       location.reload();

   });
</script>