
<?php
   /*
   Pupilsight, Flexible & Open School System
   */
   use Pupilsight\Forms\Form;
   use Pupilsight\Tables\DataTable;
   use Pupilsight\Services\Format;
   use Pupilsight\Domain\Curriculum\CurriculamGateway;
   use Pupilsight\Forms\DatabaseFormFactory;
   ini_set('max_execution_time', 7200);
   $session = $container->get('session');
   $std_id = $session->get('studentmarks_id');
   $testId = $session->get('test_id');
   if (isActionAccessible($guid, $connection2, '/modules/Academics/entry_marks_byStudent.php') == false) {
       //Acess denied
       echo "<div class='error'>";
       echo __('You do not have access to this action.');
       echo '</div>';
   } else {
       //Proceed!
       $page->breadcrumbs->add(__('Entry Marks By Student'));    
   
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
   
       
       $sqlp = 'SELECT pupilsightPerson.officialName , pupilsightPerson.admission_no,pupilsightYearGroup.name AS class,pupilsightYearGroup.pupilsightYearGroupID ,pupilsightStudentEnrolment.pupilsightProgramID,pupilsightRollGroup.name AS section ,pupilsightRollGroup.pupilsightRollGroupID  FROM pupilsightPerson 
       LEFT JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
       LEFT JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)  
       LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) 
        
       WHERE pupilsightPerson.pupilsightPersonID="'.$std_id.'" AND pupilsightStudentEnrolment.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'"
       	';
       $resultp = $connection2->query($sqlp);
       $rowdataprog = $resultp->fetch();
       //print_r($rowdataprog);
      
       if($_POST){    
           $pupilsightYearGroupID = $rowdataprog['pupilsightYearGroupID'];
           $pupilsightRollGroupID =   $rowdataprog['pupilsightRollGroupID'];
           $pupilsightProgramID = $rowdataprog['pupilsightProgramID'];
   
       } else {    
           
           $pupilsightYearGroupID = $rowdataprog['pupilsightYearGroupID'];  
           $pupilsightRollGroupID =   $rowdataprog['pupilsightRollGroupID'];
           $pupilsightProgramID = $rowdataprog['pupilsightProgramID'];
                
       }
   
   
       $sqlp1 = 'SELECT pupilsightPerson.pupilsightPersonID  FROM pupilsightPerson 
       JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
       JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)  
       JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) 
       
       WHERE pupilsightPerson.pupilsightPersonID < "'.$std_id.'" AND pupilsightPerson.pupilsightRoleIDPrimary = "003" AND pupilsightStudentEnrolment.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND pupilsightStudentEnrolment.pupilsightProgramID="'.$pupilsightProgramID.'"  AND pupilsightStudentEnrolment.pupilsightYearGroupID="'.$pupilsightYearGroupID.'" AND pupilsightStudentEnrolment.pupilsightRollGroupID="'.$pupilsightRollGroupID.'"  ORDER BY pupilsightPerson.pupilsightPersonID ASC
           ';
       $resultp1 = $connection2->query($sqlp1);
       $previous1 = $resultp1->fetch();
       if(!empty($previous1))
       {
           $disble_class_pre= '';
           $previous_std = implode(' ',$previous1);
       }
       else{
           $disble_class_pre= 'dsble_attr';
          
       }
   
       $sqln = 'SELECT pupilsightPerson.pupilsightPersonID  FROM pupilsightPerson 
       JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
       JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)  
       JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) 
       
       WHERE pupilsightPerson.pupilsightPersonID > "'.$std_id.'" AND pupilsightPerson.pupilsightRoleIDPrimary = "003" AND pupilsightStudentEnrolment.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND pupilsightStudentEnrolment.pupilsightProgramID="'.$pupilsightProgramID.'"  AND pupilsightStudentEnrolment.pupilsightYearGroupID="'.$pupilsightYearGroupID.'" AND pupilsightStudentEnrolment.pupilsightRollGroupID="'.$pupilsightRollGroupID.'"  ORDER BY pupilsightPerson.pupilsightPersonID ASC
           ';
       $resultn = $connection2->query($sqln);
       $next = $resultn->fetch();
       if(!empty($next))
       {
           $disble_class_next= '';
         //  $next_std = implode(' ',$next);
       }
       else{
           $disble_class_next= 'dsble_attr';
          
       }
       ?>
       <div class='mb-2'>
   <a style='display:none' id='showMarkHistory' href='fullscreen.php?q=/modules/Academics/show_mark_history.php&width=800'  class='thickbox '></a>
   <span class="badge bg-cyan font16px">Admission No <span class="badge-addon "><?php echo $rowdataprog['admission_no']; ?></span></span>
   <span class="ml-2 badge bg-cyan font16px">Name <span class="badge-addon "><?php echo $rowdataprog['officialName']; ?></span></span>
   <span class="ml-2 badge bg-cyan font16px">Class <span class="badge-addon "><?php echo $rowdataprog['class']; ?></span></span>
   <span class="ml-2 badge bg-cyan font16px">Section <span class="badge-addon "><?php echo $rowdataprog['section']; ?></span></span>
</div>
<?php
       $searchform = Form::create('searchForm','');
       $searchform->setFactory(DatabaseFormFactory::create($pdo));
       $searchform->addHiddenValue('studentId', '0');
       $row = $searchform->addRow();
       
       $testIds = implode(',', $testId);
       $col = $row->addColumn()->setClass('');   
       $col->addContent('<div class="float-left"><a id="'.$std_id.'"  class="'.$disble_class_pre.'  previous_std_data btn btn-primary " data-tid="'.$testIds.'">Previous</a> <a id="'.$std_id.'"  class="'.$disble_class_next.' next_std_data btn btn-primary ">Next</a></div><div class="float-right"><a style="margin: 0 0 0 520px;" id="saveMarksByStudent" class="  btn btn-primary">Save</a> <a  id="" href="index.php?q=/modules/Academics/marks_by_student.php&pid='.$pupilsightProgramID.'&cid='.$pupilsightYearGroupID.'&sid='.$pupilsightRollGroupID.'&tid='.$testIds.'" class=" btn btn-primary">Close</a></div><div class="float-none;"></div></div>'); 
       
       echo $searchform->getOutput();
       ?>

<?php $CurriculamGateway = $container->get(CurriculamGateway::class);
   // QUERY
   $criteria = $CurriculamGateway->newQueryCriteria()
       //->sortBy(['id'])
       ->fromPOST();   
   //  echo '<pre>';
   // print_r($entrymarks);die();
   echo '<div style="overflow:hidden; overflow-x:auto;"><form method="POST" id="marksbyStudent" action="'.$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/entry_marks_byStudentProcess.php">
   <input type="hidden" name="address" value="'.$_SESSION[$guid]['address'].'">
   <input type="hidden" name="pupilsightSchoolYearID" value="'.$pupilsightSchoolYearID.'">      
   <input type="hidden" name="pupilsightYearGroupID" value="'.$pupilsightYearGroupID.'">
   
   <input type="hidden" name="pupilsightRollGroupID" value="'.$rowdataprog['pupilsightRollGroupID'] .'">       
   <input type="hidden" name="studentID" value="'.$std_id.'"> 
   <div style="display:inline-flex;width:100%"> 
   '; 
   foreach ($testId as $tst ) {
   $entrymarks = $CurriculamGateway->getsubjectmarksStdWise($criteria ,$pupilsightProgramID, $pupilsightYearGroupID , $pupilsightSchoolYearID,$tst);      
      // echo '<pre>';
      // print_r($entrymarks);
      // echo '</pre>';
      // die();
   
   ?>

<!-- <button type='submit' id='save_marks_by_subject' data-type='test'
   class='btn btn-primary '>Save</button> -->
<table class='table text-nowrap' cellspacing='0' style='width:100%;margin-top: 20px;'>
<thead class="show_div_marks" data-id="<?php echo $tst;?>">
   <tr class='head'>
      <th rowspan="2" style="width:80px" class="bdr_right"> Subject & Skill </th>
      <?php 
         //echo count($subject_wise_tests);
         foreach($entrymarks as $s_test)
         { 
             ?>
      <th colspan="7"   style="text-align:center; border:1px solid #dee2e6"><?php echo $s_test['name']?></th>
      <?php 
         break;
         } ?>
   </tr>
   <tr>
      <?php 
         foreach($entrymarks as $s_test)
                 { 
                     ?>
      <th>Max Marks</th>
      <th>Marks history</th>
      <th>Mark Obtain</th>
      <th></th>
      <th>Grade</th>
      <th>Grade Status</th>
      <th class="bdr_right">Remark</th>
      <?php  break; } ?>
   </tr>
</thead>
<tbody class="t_doby_<?php echo $tst;?>">
   <?php   $i=1;
      foreach($entrymarks as $s_test)
          { 

            // echo '<pre>';
            // print_r($s_test);
            // echo '</pre>';
            // die();
               $electshow = '1';

               if($s_test['subject_type'] == 'Elective'){
                  $departmentId = $s_test['pupilsightDepartmentID'];
                  $studentId = $std_id;
                  $sqlelc = 'SELECT id FROM assign_elective_subjects_tostudents WHERE pupilsightPersonID = '.$studentId.' AND pupilsightDepartmentID = '.$departmentId.' ';
                  $resultele = $connection2->query($sqlelc);
                  $electData = $resultele->fetch();
                  if(!empty($electData['id'])){
                     $electshow = '2';
                  } else {
                     $electshow = '3';
                  }
               }
            //     $skill_arr  =array();
            //   $skills= explode(',', $s_test['skillname']);
            //  // $removed = array_shift($skills);
            //    $skill_ids= explode(',', $s_test['skill_ids']);
            //    echo ' <input type="hidden" name="skill_id[]"  value="'.$s_test['skill_id'].'">';  
            //    $skill_arr = array_combine($skill_ids, $skills);
              if($electshow == '1' || $electshow == '2') {
          ?>
   <?php if(!empty($s_test['skill_id'])){?>
   <tr>
      <td><strong><h3> <?php echo $s_test['subject'];?></h3></strong></td>
      <?php echo ' <input type="hidden" name="pupilsightDepartmentID['.$s_test['pupilsightDepartmentID'].']" value="'.$s_test['pupilsightDepartmentID'].'">' ;
         echo ' <input type="hidden" name="test_id['.$s_test['test_id'].']" value="'.$s_test['test_id'].'">' ;
               
         $sqlsc = 'SELECT skill_configure FROM examinationSubjectToTest WHERE test_id =' . $s_test['test_id'] . ' AND pupilsightDepartmentID = '.$s_test['pupilsightDepartmentID'].' AND skill_configure != "None" AND is_tested = "0" ';
         $resultsc = $connection2->query($sqlsc);
         $scData = $resultsc->fetch();
         if(!empty($scData)){ 
            $sConf = $scData['skill_configure'];
            if($sConf == 'Sum'){
               $data1 = array('test_id' => $s_test['test_id'], 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $s_test['pupilsightDepartmentID'],'pupilsightPersonID' => $std_id);                    
               $sql1 = 'SELECT SUM(marks_obtained) AS tot_mrks FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID ';
               $result = $connection2->prepare($sql1);
               $result->execute($data1);
               $sConfMarks =  $result->fetch();
               if(!empty($sConfMarks)){
                  $totdata = str_replace(".00","",$sConfMarks['tot_mrks']);
               } else {
                  $totdata = '';
               }
               
            } else if($sConf == 'Average'){
               $data1 = array('test_id' => $s_test['test_id'], 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $s_test['pupilsightDepartmentID'],'pupilsightPersonID' => $std_id);                    
               $sql1 = 'SELECT AVG(marks_obtained) AS tot_mrks FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID ';
               $result = $connection2->prepare($sql1);
               $result->execute($data1);
               $sConfMarks =  $result->fetch();
               if(!empty($sConfMarks)){
                  $totdata = str_replace(".00","",round($sConfMarks['tot_mrks'], 2));
               } else {
                  $totdata = '';
               }
            } else {
               $totdata = '';
            }
         } else {
            $totdata = '';
         }
       
             ?>
             <td colspan='2'></td>
      <td colspan='2'>
        <span class="display_total<?php echo $s_test['test_id']."".ltrim($s_test['pupilsightDepartmentID'], "0");?>"><?php echo $totdata;?></span>
        <input type="hidden" name="main_sub['<?php echo $s_test['test_id'];?>']['<?php echo $s_test['pupilsightDepartmentID'];?>']" class="main_sub<?php echo $s_test['test_id']."d".ltrim($s_test['pupilsightDepartmentID'], "0");?>">
      </td>
      <td></td>
      <td></td>
      <td>
      
      </td>
      
   </tr>
   <?php } ?>
   <?php       
      foreach($s_test['skills'] as $key=>$sl)
          { 
            $data1 = array('test_id' => $s_test['test_id'], 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $sl['pupilsightDepartmentID'],'pupilsightPersonID' => $std_id,'skill_id' => $sl['skill_id']);                    
            $sql1 = 'SELECT * FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID AND  skill_id=:skill_id ';
            $result = $connection2->prepare($sql1);
            $result->execute($data1);
            $prevdata =  $result->fetch();
        
      ?>
   <tr>
   <?php if(!empty($s_test['skill_id'])){ ?>
      <th scope="row"><span class='leftpadding'>
         <?php echo $sl['skill_display_name'];?>
         </span>
      </th>
   <?php } else { ?>
      <th scope="row">
      <strong>
      <h3 style="color: #354052 !important;"><?php echo $s_test['subject'];?></h3>
      </strong>
      </th>
   <?php } ?>
      <?php 
        //  foreach($entrymarks as $s_test)
        //      {
                 //echo $s_test['pupilsightDepartmentID'];
                 if(empty($sl['skill_display_name'])){
                    $total_class="st".$s_test['test_id']."d".$sl['pupilsightDepartmentID'];
                 } else {
                  $total_class="st".$s_test['test_id']."d".$sl['pupilsightDepartmentID'];
                 }
                 
                 if($s_test['lock_marks_entry'] == '1'){
                     $disabled = 'disabled';
                 } else {
                     $disabled = '';
                 }
             ?>
      <td class="px-2 border-b-0 sm:border-b border-t-0 newdes">
         <div class="input-group stylish-input-group">
            <div class="flex-1 relative">
               <?php echo ceil($sl['max_marks']);?>
            </div>
         </div>
      </td>
      <td>
      <center><a href="fullscreen.php?q=/modules/Academics/show_history.php&pid=<?php echo $pupilsightProgramID;?>&cid=<?php echo $pupilsightYearGroupID;?>&sid=<?php echo $rowdataprog['pupilsightRollGroupID'];?>&did=<?php echo $sl['pupilsightDepartmentID'];?>&skid=<?php echo $sl['skill_id'];?>&tid=<?php echo $s_test['test_id'];?>&stid=<?php echo $std_id;?>" class="thickbox" title="Max history" data-stid="<?php echo $std_id;?>" data-tid="<?php echo $s_test['test_id'];?>" data-skil="<?php echo $key;?>"><i class="mdi mdi-history mdi-24px" aria-hidden="true"></i></a></center>
      </td>
      <td class="px-2 border-b-0 sm:border-b border-t-0 newdes">
         <div class="input-group stylish-input-group">
            <div class="flex-1 relative">
               <?php
                  $en_dis_clss=($s_test['assesment_method']=='Marks')? '' : 'disable_input';
                  $en_dis_grd_clss=($s_test['assesment_method']=='Grade')? '' : 'disable_input';  

                  if(!empty($prevdata['marks_abex'])){
                     $marksobt = '';
                  } else {
                     $marksobt = str_replace(".00","",$prevdata['marks_obtained']);
                        //$marksobt = rtrim($marksobt,'0');
                  }

                  //$marksobt = str_replace(".00","",$prevdata['marks_obtained']);
                  //$marksobt = rtrim($marksobt,'0');

                                     echo '<input type="text" data-mode="'.$sl['skill_configure'].'" data-mark="'.$sl['max_marks'].'" data-d="'.$sl['pupilsightDepartmentID'].'" data-gsid="'.$s_test['gradeSystemId'].'" data-cnt="'.$i.'" data-tid="'.$s_test['test_id'].'" name="mark_obtained['.$s_test['test_id'].']['.$sl['pupilsightDepartmentID'].']['.$sl['skill_id'].']" data-fid="'.$i.'" id="focustab-'.$s_test['test_id'].'-'.$i.'" class="tabfocus numMarksfield chkData enable_input mark_obtn textfield_wdth abexClsDis'.$s_test['test_id'].$sl['pupilsightDepartmentID'].$sl['skill_id'].'  '.$en_dis_clss.' '.$total_class.' " value="'.$marksobt.'"  '.$disabled.'>';?>
            </div>
         </div>
      </td>
      <?php    
        $seab = array("AB", "EX");
        $slen = count($seab);
        $s = 0;

        echo '<td><select class="chkData mr-2 abex" data-id="'.$s_test['test_id'].$sl['pupilsightDepartmentID'].$sl['skill_id'].'" name="mark_abex[' . $s_test['test_id'] . '][' . $sl['pupilsightDepartmentID'] . '][' . $sl['skill_id'] . ']" >';
        echo '<option value="">-</option>';
        while ($s < $slen) {
            if($prevdata['marks_abex'] == $seab[$s]){
               $selected = 'selected';
            } else {
                  $selected = '';
            }
            echo '<option value="' . $seab[$s] . '" '.$selected.'>' . $seab[$s] . '</option>';
            $s++;
        }
        echo '</select></td>';
      
        echo '<td class="'.$en_dis_grd_clss.'">'; 
         // $grade_arr=array();
         // $test_grades = explode(',', $s_test['grade_names']);
         // $grade_ids = explode(',', $s_test['grade_ids']);
         // $grade_arr = array_combine($grade_ids, $test_grades);
         //      echo "<pre>";print_r($grade_ids);
         $sqlGr = 'SELECT * FROM examinationGradeSystemConfiguration WHERE gradeSystemId = '.$s_test['gradeSystemId'].' ORDER BY rank ASC';
         $resultGr = $connection2->query($sqlGr);
         $gradeData = $resultGr->fetchAll();
         foreach ($gradeData as $gdata) {

            $grdid = $gdata['id'];
            $tgrade = $gdata['grade_name'];
            if($prevdata['gradeId'] == $grdid){
               $selected = 'checked';
            } else {
               $selected = '';
            }
         echo ' <input type="radio" class="chkData abexClsDis'.$s_test['test_id'].$sl['pupilsightDepartmentID'].$sl['skill_id'].'" id="grade_val'.$s_test['test_id'].'row'.$i.'grade'.trim($grdid).'"   name="grade_val['.$s_test['test_id'].']['.$sl['pupilsightDepartmentID'].']['.$sl['skill_id'].']" value="'.$grdid.'" '.$disabled.'  '.$selected.'>'.$tgrade.'';
         
         }
         echo '</td>';

         if(!empty($marksobt) && !empty($s_test['gradeSystemId']) && $s_test['max_marks'] != '0.00'){
            $obtMark = $s_test['max_marks'];
            $mrks = ($marksobt / $obtMark) * 100; 
            $sql = 'SELECT grade_name,id, subject_status FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="' . $s_test['gradeSystemId'] . '" AND  (' . $mrks . ' BETWEEN `lower_limit` AND `upper_limit`)';
            $result = $connection2->query($sql);
            $grade = $result->fetch();

            $gstatus = $grade['subject_status'];
            
         } else {
            $gstatus = '';
         }

         echo '<td id="grade_status'.$s_test['test_id'].'row'.$i.'">'.$gstatus.'</td>';
         $i++; ?> 
      <td>
         <?php
            $data1 = array('test_id' => $s_test['test_id'], 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $s_test['pupilsightDepartmentID'],'pupilsightPersonID' => $std_id, 'skill_id' => $sl['skill_id']);                    
            $sql1 = 'SELECT remarks FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID AND skill_id=:skill_id';
            $result = $connection2->prepare($sql1);
            $result->execute($data1);
            $remdata =  $result->fetch();
         ?>
            <div class="input-group stylish-input-group">
               <div class="flex-1 relative">
               <?php if($s_test['enable_remarks'] == '1'){ ?>
                  <textarea type='text' name='remark_own[<?php echo $s_test['test_id'];?>][<?php echo $s_test['pupilsightDepartmentID'];?>][<?php echo $sl['skill_id'];?>]' class="remark_textarea w-full "><?php echo $remdata['remarks'];?></textarea>
                  <br><span></span>
               <?php } ?>
               </div>
            </div>
         <?php  ?>
      </td>
     
   </tr>
   <?php } } } ?>
</tbody>
<?php  
   echo '</table>';
   }
   echo  '</div></form></div>';
   echo '<input type="hidden" id="chkMarksSaveData" value="0">';
    ?>
<style>
   .enable_input {
   background-color: #fff !important;
   border: 1px solid gray !important;
   }
   .disable_input {
   background-color: #d2d2d2 !important;
   pointer-events: none;
   opacity: 0.5;
   }
   table th,
   table tr {
   border: 1px solid #bab8b8;
   text-align: center !important;
   }
   .dsble_attr
   {
   pointer-events: none;
   color: #e8e8e8!important;
   }
   .rwidth
   {
   width: 125px;
   }

   .txtColor {
        background-color: lightblue !important;
    }
</style>
<script>
   $(document).on('keyup','.remark_textarea',function(){
      var txt=$(this).val();
      var count=txt.length;
      var dis=txt.replace(/\"/g, "");
      $(this).nextAll('span:first').html('Character length (<i class="fa fa-eye" aria-hidden="true"></i> ): '+count);
      $(this).nextAll('span:first').attr("title",dis);
   });
   
       $(document).on('change', '.mark_obtn', function() {
           var obtain_mark = Number($(this).attr('data-mark'));
           var enterd_mrk = Number($(this).val()); 
           var gsid = $(this).attr('data-gsid'); 
           var d=$(this).attr('data-d');          
           if(obtain_mark < enterd_mrk){
               alert('You cannot enter marks greater than max marks defined');
               $(this).val(""); 
               return;
            }  
          var grad_val = percentage(obtain_mark, enterd_mrk);
            //t250d41
          //var grad_val =  enterd_mrk;
          var tid = $(this).attr('data-tid');
          var data_cnt = $(this).attr('data-cnt');
          //var check_mode = $(".t"+tid+"d"+d).attr('data-mode');
          var sum=0;
            /*if(check_mode=="Sum"){
            $(".st"+tid+"d"+d).each(function() {
                if ($(this).val() != '') {
                 sum += parseInt($(this).val());
                } 
            });
            } else if(check_mode=="Average") {
              var count=0;
              $(".st"+tid+"d"+d).each(function() {
              if ($(this).val() != '') {
                count++;
              sum += parseInt($(this).val());
              } 
              });
              sum=parseFloat(sum)/count;
            }*/
          
           var type = 'getGradeConfigData1';
           if (grad_val != '') {
               $.ajax({
                   url: 'ajax_data.php',
                   type: 'post',
                   data: {
                       val: grad_val,
                       gsid:gsid,
                       tid:tid,
                       d:d,
                       type: type
                   },
                   dataType: "json",
                   async: true,
                   success: function(response) {
                          /*if(check_mode=="Sum" || check_mode=="Average"){
                                 $('.display_total'+tid+''+d).html(sum); 
                                 //main_sub250d0041
                                 $('.main_sub'+tid+'d'+d).val(sum); 
                                 //$(".t"+tid+"d"+d).trigger('change');
                          }*/
                       if (enterd_mrk != '' && response != '') {
                           var gid = response.id;
                           var gstatus = response.status;
                           $('#grade_val'+tid+'row'+data_cnt+'grade'+gid).prop("checked",true);
                           $('#grade_status'+tid+'row'+data_cnt).html(gstatus);

                           var check_mode=response.skill_configure;
                           if(check_mode=="Sum"){
                              $(".st"+tid+"d"+d).each(function() {
                                 //alert($(this).val());
                                 if ($(this).val() != '') {
                                    sum += parseFloat($(this).val());
                                 } 
                              });
                           } else if(check_mode=="Average") {
                              var count=0;
                              $(".st"+tid+"d"+d).each(function() {
                                 if ($(this).val() != '') {
                                    count++;
                                    sum += parseFloat($(this).val());
                                 } 
                              });
                              sum=parseFloat(sum)/count;
                           } else {
                              $(".st"+tid+"d"+d).each(function() {
                                 if ($(this).val() != '') {
                                    sum += parseFloat($(this).val());
                                 } 
                              });
                           }
                           $('.display_total'+tid+''+d).html(sum);
                           $('.main_sub'+tid+'d'+d).val(sum); 
                       } else {
                           var rdname = 'grade_val' + '[' + tid + ']' + '[' + data_cnt + ']';
                           //  alert(rdname);               
                           $('input[type="radio"][name=' + rdname + ']').prop("checked",
                               false);
                       }
                 //alert("dsfsa");
   
                   }
               });
           }
   
   
           //  alert(grad_val);
   
       });
   
       function percentage(obtain_mark, enterd_mrk) {
           //return (enterd_mrk / obtain_mark) * 99.99;
             return (enterd_mrk/obtain_mark)*100;
       }
       $(document).on('click','.nav-link',function(){
           var val_count=0;
               $(".mark_obtn").each(function() {
               if ($(this).val() != '') {
                  val_count++;
               } 
               });
               $(".remark_textarea").each(function() {
               if ($(this).val() != '') {
                  val_count++;
               } 
               });
           if(val_count!=0){
           var r = confirm("Do you want to save the changes ?");
           if (r == true) {
            $("#saveMarksByStudent").click();
            return false;
           } 
           }
       });
       $(document).on('click','.getMaxHistroy',function(){
           var stid = $(this).attr('data-stid');
           var tid = $(this).attr('data-tid');
           var skil_id = $(this).attr('data-skil');
           var type ="setMarkHistoryVal";
           $.ajax({
           url: 'ajaxSwitch.php',
           type: 'post',
           data: { stid:stid,tid:tid,skil_id:skil_id,type:type},
           async: true,
           success: function(response) {
             $("#showMarkHistory").click();
           }
           });
   
       });
       

      $(document).on('change', '.abex', function(e) {
         var val = $(this).val();
         var id = $(this).attr('data-id');
         if(val == 'AB' || val == 'EX'){
               $(".abexClsDis"+id).prop('readonly', true);
               $(".abexClsDis"+id).addClass('disabled');
         } else {
               $(".abexClsDis"+id).prop('readonly', false);
               $(".abexClsDis"+id).removeClass('disabled');
         }
      });

      $(document).on('change', '.chkData', function(e) {
         $("#chkMarksSaveData").val(1);
      }); 


      $(document).on('keydown', '.tabfocus', function(e) {
            $(".numMarksfield").removeClass('txtColor');
            var id = $(this).attr('data-fid');
            var tid = $(this).attr('data-tid');
            var newid = parseInt(id) + 1;
            var keycode = (window.event) ? event.keyCode : e.keyCode;
            if (keycode == 9){
                  window.setTimeout(function() {
                     $("#focustab-"+tid+'-'+newid).focus().addClass('txtColor');
                  }, 10);
            }
      }); 
</script>
<?php

}