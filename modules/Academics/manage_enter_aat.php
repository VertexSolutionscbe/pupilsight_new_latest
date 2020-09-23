<?php
/*
Pupilsight, Flexible & Open School System
 */
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_enter_aat.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Enter A.A.T'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo '<h3>';
    echo __('Enter A.A.T(Assesment By All Teachers)');
    echo '</h3>';
    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

   

    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $academic = array();
    $ayear = '';
    if (!empty($rowdata)) {
        $ayear = $rowdata[0]['name'];
        foreach ($rowdata as $dt) {
            $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
        }
    }
    if ($_POST) {
         $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        // $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
        $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
        $skill_id = $_POST['skill_id'];
        $test_id  =  $_POST['test_id'];
        $test_type  =  $_POST['test_type'];
        $searchbyPost = '';     
        //  $search =  $_POST['search'];
        $stuId = $_POST['studentId'];
    } else {
        $pupilsightProgramID =  '';
        //   $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
        $pupilsightYearGroupID = '';
        $pupilsightRollGroupID = '';
        $pupilsightDepartmentID='';
        $skill_id = '';
        $test_id  = '';
        $test_type  = '';
        $searchbyPost = '';
        $search = '';
        $stuId = '0';      
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


    $sql_rmk = 'SELECT id, description FROM acRemarks ';
    $result_rmk = $connection2->query($sql_rmk);
    $rowdata_rmk = $result_rmk->fetchAll();
    $remarks=array();  
    $remark2=array();  
    // $subject1=array(''=>'Select Subjects');
    foreach ($rowdata_rmk as $dr) {
        $subject2[$dr['id']] = $dr['description'];
    }
    $remarks=  $remark2;  
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
    $test_types = array(__('Select') => array ('Term1' => __('Term 1'), 'Term2' => __('Term 2')));
    $sql_tst = 'SELECT id,name FROM examinationTest';
    $result_test = $connection2->query($sql_tst);
    $tests = $result_test->fetchAll();
    $testarr=array ('' => __('Select'));  
    $test2=array();  

    foreach ($tests as $ts) {
        $test2[$ts['id']] = $ts['name'];
    }
    $testarr+=  $test2; 

    $sqlsk = 'SELECT id, name FROM ac_manage_skill ';
    $resultsk = $connection2->query($sqlsk);
    $skilldata = $resultsk->fetchAll();
    $skills=array ('' => __('Select'));  
    $skills2=array();  

    foreach ($skilldata as $sk) {
        $skills2[$sk['id']] = $sk['name'];
    }
    $skills+=  $skills2;        
    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->setClass('noIntBorder fullWidth');
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();  

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->required()->placeholder('Select Class');
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID', $pupilsightSchoolYearID)->selected($pupilsightRollGroupID)->required()->placeholder('Select Section');
    
    $col = $row->addColumn()->setClass('newdes ');
    $col->addLabel('test_type', __('Test Type'));
    $col->addSelect('test_type')->fromArray($test_types)->required();
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('test_id', __(' Test Name'));
    $col->addSelect('test_id')->fromArray($testarr)->required()->selected($test_id);
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightDepartmentID', __('Subjects'));
    $col->addSelect('pupilsightDepartmentID')->fromArray($subjects)->required()->selected($pupilsightDepartmentID)->placeholder();
   
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('skill_id', 'Skill');
    $col->addSelect('skill_id')->fromArray($skills)->placeholder(' Skill')->selected($skill_id); 
   
    $col = $row->addColumn()->setClass('newdes');   
    $col->addContent('<div style="width:150px;margin-top: 30px;"><button type="submit"  class=" btn btn-primary">Go</button>&nbsp;&nbsp;
   </div>');      
    echo $searchform->getOutput();
    echo  "<div style='height:20px'></div>";

    $CurriculamGateway = $container->get(CurriculamGateway::class);
    $criteria = $CurriculamGateway->newQueryCriteria()      
        ->fromPOST();
       if(isset($_POST['pupilsightYearGroupID']) && $_POST['pupilsightRollGroupID'] )
       {
      //  $skill_names=$CurriculamGateway->getStudentSubjectskillsClassWise_ATT($criteria, $pupilsightSchoolYearID,$pupilsightYearGroupID,$pupilsightDepartmentID,$skill_id,$test_id,$test_type);
          $students = $CurriculamGateway->getstudent_subject_assigned_data_for_AAT($criteria, $pupilsightSchoolYearID,$pupilsightYearGroupID, $pupilsightRollGroupID,$pupilsightDepartmentID,$skill_id,$test_id,$test_type);
           
        }
       else
       {
         //  $skill_names =array();
           $students= array();        
       }
        /* echo "<pre>";
        print_r($students);*/
   if (count($students) < 1  ) {
       echo "<div class='error'>";
       echo __('There are no records to display.');
       echo '</div>';
   } else {
    echo '<form method="POST" id="subject_to_class_form" action="'.$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/enter_aat_addProcess.php">
    <input type="hidden" name="address" value="'.$_SESSION[$guid]['address'].'">
        <input type="hidden" name="pupilsightSchoolYearID" value="'.$pupilsightSchoolYearID.'">      
        <input type="hidden" name="pupilsightYearGroupID" value="'.$pupilsightYearGroupID.'">
        <input type="hidden" name="pupilsightDepartmentID" value="'.$pupilsightDepartmentID.'">
        <input type="hidden" name="pupilsightRollGroupID" value="'.$pupilsightRollGroupID.'">       
     
        <input type="hidden" name="test_id" value="'.$test_id.'">
        ';
        
?>
  <button type='submit'  id='save_marks_by_aat' data-type='test' class='btn btn-primary'>Save</button>     
        <div style="overflow-x:auto;">
        <table  class ='table text-nowrap' cellspacing='0' style='width: 100%;margin-top: 20px;'>
        <thead>
        <tr class='head'>
           
            <th> Locked </th>
            <th> Name  </th>
            <th> Roll No </th>
            <?php 
             foreach ($students as $sk) { 
                $sklarr_ids=explode(',',$sk['columname']);
                if(!empty($sk['skill_names']))
                {
                $sklarr_names=explode(',',$sk['skill_names']);
                $entry_basedon="skill";
                }
                else
                {
                    $sklarr_names[]=$sk['name'];
                    $entry_basedon="subject";
                }           
             }
                 ?>
                        
            <?php         
                if(count($sklarr_ids)!=count($sklarr_names))
                {
                      $arr_skname= array_slice($sklarr_names, 0, -1);
                }
                else
                {  $arr_skname = $sklarr_names;
                }
                        $sk_arr = array();
                        $sk_arr = array_combine($sklarr_ids, $arr_skname);     
                 foreach ($sk_arr as $skll) {        
                    echo '<th> '.$skll.'</th> ';
                
                }
            
            ?>  
        </tr>    
        </thead>        
        <tbody>
            <?php      
                $count = 0;
                $rowNum = 'odd';
                foreach ($students as $row) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                    $rowNum = 'odd';
                }
                ++$count;
                echo "<tr class=$rowNum>";         
                echo '<td>';
                echo ' <input type="hidden" name="student_id['.$row['pupilsightPersonID'].']" value="'.$row['pupilsightPersonID'].'">';
                if($row['status']==1) 
                {
                    echo '<i class="fas fa-1x fa-lock px-4   "></i>';
                    $en_dis_clss= 'disable_input';
                }    
                else
                {
                    echo '<i class="fas fa-2x fa-times px-4 x_icon "></i>';
                    $en_dis_clss= 'enable_input';
                   
                }    
                echo '</td>';     
                echo '<td>'; echo $row['student_name']; echo '</td>';
                echo '<td >';  echo $row['stuid']; echo '</td>';
                echo ' <input type="hidden" name="entry_based_on" value="'.$entry_basedon.'">';
                foreach ($sk_arr as $sk_id=>$sk) {                       
                    if($entry_basedon=='skill')//based on skill id checking
                    {
                        $skill_id= $sk_id;
                    }
                    else //based on subject id checking
                    {
                        $skill_id= 0;
                    }
                    $data1 = array('test_id' => $test_id, 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $pupilsightDepartmentID,'pupilsightPersonID' => $row['pupilsightPersonID'],'skill_id' => $skill_id,'entrytype' =>$row['entrytype'] );                    
                    $sql1 = 'SELECT * FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID AND  skill_id=:skill_id AND entrytype=:entrytype';
                    $result = $connection2->prepare($sql1);
                    $result->execute($data1);
                    $prevdata =  $result->fetch();
                    if($prevdata['status']==1)
                    {
                        $locked = 'locked';
                        
                    }
                    else
                    {
                        $locked = '';
                    }
                    
                    echo ' <input type="hidden" name="prev_mark['.trim($sk_id).']['. $row['pupilsightPersonID'].']" value="'.$prevdata['marks_obtained'].'">';
                    echo ' <input type="hidden" name="entrytype['.trim($sk_id).']['. $row['pupilsightPersonID'].']" value="'.$prevdata['entrytype'].'">';
                    echo ' <input type="hidden" name="lock_status['.trim($sk_id).']['. $row['pupilsightPersonID'].']" value="'.$prevdata['status'].'">';
                     echo ' <input type="hidden" name="skill_id['.trim($sk_id).']" value="'.$sk_id.'">';
                    echo '<td> <input type="text" name="marks_obtain['.trim($sk_id).']['.$row['pupilsightPersonID'].']" class="'.$en_dis_clss.'"></td>';       
                }              
               
                echo '</tr>';
            }
        echo "</tbody>";
        echo '</table></div> </form>';                                         

}}
?>
<style>
   .x_icon,.small_icon
 {
    font-size: 18px !important;
 } 
 .enable_input 
    {
        background-color: #fff !important;
        border: 1px solid gray!important;
    }
    .disable_input 
    {
        background-color: #d2d2d2  !important;
        pointer-events:none;
        opacity:0.5;
    }
</style>
