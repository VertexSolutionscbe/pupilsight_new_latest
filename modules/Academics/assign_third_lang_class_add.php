<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Departments\DepartmentGateway;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Academics/assign_third_lang_class_add.php.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Assign Third Language  to class'), 'assign_third_lang_class_add.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Assign Third Language  to class'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/studentEnrolment_manage_edit.php&pupilsightStudentEnrolmentID='.$_GET['editID'].'&search='.$_GET['search'].'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    //Check if school year specified
    if ($pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        if ($search != '') {
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Academics/assign_third_lang_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
            echo '</div>';
        }

        echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Academics/assign_subjects_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search' class='btn btn-primary'>Assign Core Subjects to class</a>";  
        echo "&nbsp;&nbsp;<a href='index.php?q=/modules/Academics/assign_second_lang_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search' class='btn btn-primary'>Assign IInd Language</a>";
       echo "&nbsp;&nbsp;<a href='index.php?q=/modules/Academics/assign_third_lang_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search' class='btn btn-primary'>Assign IIIrd Language</a>";
        
       echo " </div><div class='float-none'></div></div>"; 
         
    

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
        $sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ';
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
            $row->addLabel('pupilsightProgramID', __('Program'));
            $row->addSelect('pupilsightProgramID')->fromArray($program)->required()->placeholder();

        $row = $form->addRow();
            $row->addLabel('pupilsightYearGroupID', __('Class'));
            $row->addSelectYearGroup('pupilsightYearGroupID')->required();

         
            $row = $form->addRow();
            $row->addLabel('pupilsightDepartmentID', __('Subjects'));
    
            $select_sub = '<select class="w-full" id="issubjects" name="pupilsightDepartmentID" multiple="multiple">';
          
           
            
            foreach (  $subjects as $key => $sub ) {
                $select_sub .=   '<option value="' . $key . '">' .$sub . "</option>";
            }
          
            $select_sub .='</select>';

           // echo $select_sub;

            $row->addContent($select_sub);
          //  $row->addSelect('pupilsightDepartmentID')->fromArray($subjects)->required()->placeholder()->setId('issubjects'); 
          //  $row->addSubmit(__('OK'))->addClass('submit_align submt btnSelected');
  

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit()->addClass('submit_align submt assign_third_sub');

        echo $form->getOutput();

        $departmentGateway = $container->get(DepartmentGateway::class);

        // QUERY
        $criteria = $departmentGateway->newQueryCriteria()
            ->sortBy('id')
            ->fromPOST();
    
        $departments = $departmentGateway->get_thirdlang_assigned_toclass($criteria);

      /*  echo "<pre>";
        print_r($departments );
    */
        // DATA TABLE
        $table = DataTable::createPaginated('assignedsubjectclss', $criteria);
    
       
    
        $table->addColumn('program_name', __('Program'));
       // $table->addColumn('type', __('Type'))->translatable();
        $table->addColumn('class', __('Class'));
        $table->addColumn('subject', __('Subject'));
           
            
        // ACTIONS
      /*  $table->addActionColumn()
            ->addParam('pupilsightDepartmentID')
            ->format(function ($department, $actions) {
                $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Academics/department_manage_edit.php');
    
                $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Academics/department_manage_delete.php');
            });
    */
        echo $table->render($departments);

    }
}
echo '

<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.0.3/js/bootstrap.min.js"></script>
<link href="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/css/bootstrap-multiselect.css"
    rel="stylesheet" type="text/css" />


<script src="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/js/bootstrap-multiselect.js"
type="text/javascript"></script>';

echo '<script type="text/javascript">
$(function () {
    $("#issubjects").multiselect({
        includeSelectAllOption: true
    });
});
</script>';

