<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Students/assign_subjects_class_add.php.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Student Enrolment'), 'studentEnrolment_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Add Student Enrolment'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/studentEnrolment_manage_edit.php&pupilsightStudentEnrolmentID='.$_GET['editID'].'&search='.$_GET['search'].'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    //Check if school year specified
    if ($pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        if ($search != '') {
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/assign_subjects_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
            echo '</div>';
        }

        $form = Form::create('assignsubjectclassAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/studentEnrolment_manage_addProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");
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
        $subject1=array(''=>'Select Subjects');
        foreach ($rowdatadept as $dt) {
            $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
        }
        $subjects= $subject1 + $subject2;  


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
            $row->addSelect('pupilsightDepartmentID')->fromArray($subjects)->required()->placeholder(); 

   /*     $row = $form->addRow();
            $row->addLabel('pupilsightRollGroupID', __('Section'));
            $row->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required();

    */  

        // Check to see if any class mappings exists -- otherwise this feature is inactive, hide it
  /*      $sql = "SELECT COUNT(*) FROM pupilsightCourseClassMap";
        $resultClassMap = $pdo->executeQuery(array(), $sql);
        $classMapCount = ($resultClassMap->rowCount() > 0)? $resultClassMap->fetchColumn(0) : 0;

        if ($classMapCount > 0) {
            $autoEnrolDefault = getSettingByScope($connection2, 'Timetable Admin', 'autoEnrolCourses');
            $row = $form->addRow();
                $row->addLabel('autoEnrolStudent', __('Auto-Enrol Courses?'))
                    ->description(__('Should this student be automatically enroled in courses for their Roll Group?'));
                $row->addYesNo('autoEnrolStudent')->selected($autoEnrolDefault);
        }

        */

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();
    }
}
