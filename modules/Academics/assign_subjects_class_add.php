<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Departments\DepartmentGateway;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;


if (isActionAccessible($guid, $connection2, '/modules/Academics/assign_subjects_class_add.php.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Assign Subjects to Class'), 'assign_subjects_class_add.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Assign Subjects to Class'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/studentEnrolment_manage_edit.php&pupilsightStudentEnrolmentID='.$_GET['editID'].'&search='.$_GET['search'].'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    echo '<h3>';
    echo __('Assign Subjects to Class');
    echo '</h3>';

    //Check if school year specified
    
        if ($search != '') {
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Academics/assign_subjects_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
            echo '</div>';
        } 

     //   echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Academics/assign_subjects_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search' class='btn btn-primary'>Assign Core Subjects to class</a>";  
     //   echo "&nbsp;&nbsp;<a href='index.php?q=/modules/Academics/assign_second_lang_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search' class='btn btn-primary'>Assign IInd Language</a>";
      //  echo "&nbsp;&nbsp;<a href='index.php?q=/modules/Academics/assign_third_lang_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search' class='btn btn-primary'>Assign IIIrd Language</a>";
        
     //  echo " </div><div class='float-none'></div></div>"; 
         
    

        $form = Form::create('program', '');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
        $result = $pdo->executeQuery($data, $sql);

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

    
      

//select subjects from department
        $sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ORDER BY name ASC ';
        $resultd = $connection2->query($sqld);
        $rowdatadept = $resultd->fetchAll();


        $subjects=array();  
        $subject2=array();  
       // $subject1=array(''=>'Select Subjects');
        foreach ($rowdatadept as $dt) {
            $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
        }
        $subjects=  $subject2;  


        $schoolYearName = ($result->rowCount() == 1)? $result->fetchColumn(0) : $_SESSION[$guid]['pupilsightSchoolYearName'];

        $row = $form->addRow();
            $row->addLabel('yearName', __('School Year'));
            $row->addTextField('yearName')->readOnly()->maxLength(20)->setValue($schoolYearName);

      /*  $row = $form->addRow();
            $row->addLabel('pupilsightPersonID', __('Student'));
            $row->addSelectStudent('pupilsightPersonID', $pupilsightSchoolYearID, array('allStudents' => true))->required()->placeholder();
     */
        $row = $form->addRow();
            $row->addLabel('pupilsightProgramID_Mnew', __('Program'));
            $row->addSelect('pupilsightProgramID_Mnew')->setId('getMultiClassByProg')->fromArray($program)->required()->placeholder();

        $row = $form->addRow();
            $row->addLabel('pupilsightClassID', __('Class'))->addClass('dte');
            $row->addSelect('pupilsightClassID')->setId('showMultiClassByProg')->selectMultiple();
            
        $row = $form->addRow();
            $row->addLabel('pupilsightDepartmentID', __('Subjects'))->addClass('dte');
            $row->addSelect('pupilsightDepartmentID')->setId('showMultiSubjectByProgCls')->fromArray($subjects)->selectMultiple();
    
            

           // echo $select_sub;

            //$row->addContent($select_sub)->setClass('subject_popup');
          //  $row->addSelect('pupilsightDepartmentID')->fromArray($subjects)->required()->placeholder()->setId('issubjects'); 
          //  $row->addSubmit(__('OK'))->addClass('submit_align submt btnSelected');
  

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit()->addClass('submit_align submt assign_core_sub');

        echo $form->getOutput();

        $departmentGateway = $container->get(DepartmentGateway::class);

        // QUERY
        $criteria = $departmentGateway->newQueryCriteria()
            ->sortBy('id')
            ->fromPOST();
    
        $departments = $departmentGateway->get_assignedsub_toclass($criteria);

      /*  echo "<pre>";
        print_r($departments );
    */
        // DATA TABLE
        $table = DataTable::createPaginated('assignedsubjectclss', $criteria);
    
        $table->addCheckboxColumn('id',__(''))
            ->setClass('chkbox')
            ->notSortable();
    
        $table->addColumn('program_name', __('Program'));
       // $table->addColumn('type', __('Type'))->translatable();
        $table->addColumn('class', __('Class'));
        $table->addColumn('subject', __('Subject'));
           
            //`id`,`pupilsightProgramID`,`pupilsightYearGroupID`,`pupilsightDepartmentID`
        // ACTIONS
        $table->addActionColumn()
        ->addParam('id')
        ->addParam('pupilsightProgramID')
        ->addParam('pupilsightYearGroupID')
        ->addParam('pupilsightDepartmentID')
        ->format(function ($facilities, $actions) use ($guid) {
            // $actions->addAction('copynew', __('Copy'))
            //         ->setURL('/modules/Transport/transport_route_copy.php');
        
            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Academics/remove_assigned_subject_from_class.php');
            
            
        });
    
        echo $table->render($departments);

   
}
?>


<script type="text/javascript">
$(function () {
    
    $(document).ready(function () {
      	$('#showMultiClassByProg').selectize({
      		plugins: ['remove_button'],
      	});
    });
    
    $(document).ready(function () {
      	$('#showMultiSubjectByProgCls').selectize({
      		plugins: ['remove_button'],
      	});
    });
});

</script>


<style>
 .btn-default{

  
    width: 240px !important;
    height: 32px;
    
}
.multiselect-container {
    width: 100%;
}
span .multiselect-selected-text
{
    margin-left: 150px;
    float:left;
}
 
ul[x-placement="top-start"]  
{
    height: 328px !important;
    overflow-y: auto!important;
  

}
    
</style>

