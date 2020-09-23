<?php 
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');
if(isset($_POST['type'])){
    $type=trim($_POST['type']);
    switch ($type) {
           case "download_excel_results":
            $testId=$_POST['testId'];
            $pupilsightProgramID=$_POST['pupilsightProgramID'];
            $pupilsightYearGroupID=$_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID=$_POST['pupilsightRollGroupID'];
            $stdid=implode(',',$_POST['stuid']);
        /*$sqls ="SELECT a.*,c.name as test ,d.skill_id,GROUP_CONCAT(DISTINCT d.skill_id SEPARATOR ', ') as skill_ids,GROUP_CONCAT(DISTINCT d.skill_display_name SEPARATOR ', ') as skillname, GROUP_CONCAT(DISTINCT d.skill_display_name SEPARATOR ', ') as skillname,GROUP_CONCAT(DISTINCT sub.gradeId SEPARATOR ', ') as grade,b.pupilsightDepartmentID,e.pupilsightDepartmentID ,e.name AS subject FROM examinationSubjectToTest AS a  LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID  LEFT JOIN examinationTest AS c ON a.test_id = c.id LEFT JOIN  subjectSkillMapping as d ON a.pupilsightDepartmentID = d.pupilsightDepartmentID  LEFT JOIN pupilsightDepartment AS e ON a.pupilsightDepartmentID = e.pupilsightDepartmentID 
        LEFT JOIN examinationMarksEntrybySubject as sub ON a.test_id=sub.test_id
        WHERE a.test_id = ".$testId." AND 
       b.pupilsightYearGroupID = ".$pupilsightYearGroupID." AND d.pupilsightYearGroupID = ".$pupilsightYearGroupID." GROUP BY a.pupilsightDepartmentID";
         $results = $connection2->query($sqls);
        $rowdatas = $results->fetchAll();

        $sqlo = "SELECT a.* ,c.name as subjects ,c.pupilsightDepartmentID, b.max_marks,d.id as skill_id,d.name as skill,b.name as test FROM examinationMarksEntrybySubject AS a  LEFT JOIN  examinationTest as b ON a.test_id = b.id  LEFT JOIN pupilsightDepartment AS c ON a.pupilsightDepartmentID = c.pupilsightDepartmentID LEFT JOIN ac_manage_skill as d ON a.skill_id = d.id WHERE a.test_id =  '".$testId."' ";
        $resulto = $connection2->query($sqlo);
        $rowdatao = $resulto->fetchAll();
      
       $sqlm = "SELECT a.*,b.*,c.name AS test,c.max_marks as maxMarks,e.name AS section,b.marks_obtained ,f.name as class,i.pupilsightDepartmentID,i.subject_display_name as subname,j.name as skill,j.id as skill_id FROM pupilsightPerson AS a LEFT JOIN examinationMarksEntrybySubject AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN  examinationTest as c ON b.test_id = c.id 
        LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightRollGroup AS e ON d.pupilsightRollGroupID = e.pupilsightRollGroupID LEFT JOIN pupilsightYearGroup AS f ON d.pupilsightYearGroupID = f.pupilsightYearGroupID  LEFT JOIN pupilsightProgram as h ON d.pupilsightProgramID = h.pupilsightProgramID LEFT JOIN subjectToClassCurriculum as i ON b.pupilsightDepartmentID =i.pupilsightDepartmentID LEFT JOIN ac_manage_skill as j ON b.skill_id = j.id WHERE  d.pupilsightProgramID = ".$pupilsightProgramID." AND  d.pupilsightYearGroupID = ".$pupilsightYearGroupID." AND  d.pupilsightRollGroupID = ".$pupilsightRollGroupID." AND c.id =".$testId." AND  a.pupilsightPersonID IN(".$stdid.") GROUP BY a.pupilsightPersonID";
        //echo($sqlm);die();
        $resultm = $connection2->query($sqlm);
        $rowdatam = $resultm->fetchAll();*/
        $sql = 'SELECT b.officialName, b.pupilsightPersonID, d.name as classname, e.name as sectionname FROM `examinationMarksEntrybySubject` AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE  a.pupilsightPersonID IN('.$stdid.') AND a.test_id = '.$testId.' GROUP BY a.pupilsightPersonID';
        $result = $connection2->query($sql);
        $data = $result->fetchAll();
         foreach($data as $k => $dt){
            $sqlm='SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                    LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
                    WHERE a.test_id = '.$testId.' AND a.pupilsightPersonID = '.$dt['pupilsightPersonID'].' GROUP by a.pupilsightDepartmentID,c.skill_id';
                    $resultm = $connection2->query($sqlm);
                    $datam = $resultm->fetchAll();
                    if(!empty($datam)){
                    $data[$k]['marks'] = $datam;
                    }
              }    
            $sql1='SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
            LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
            WHERE a.test_id = '.$testId.' GROUP by a.pupilsightDepartmentID,c.skill_id';
            $resu_h= $connection2->query($sql1);
            $datam_h = $resu_h->fetchAll();
        ?>
            
<table id="excelexport">
  <tr>
    <th>Student Name</th>
    <th>Student ID</th>
    <th>Class</th>
    <th>Section</th>
    <?php 
         foreach ($datam_h as $m) {
            ?>
            <th><?php echo $m['subject_name']."-".$m['skill_display_name']."/".ceil($m['max_marks']);?></th>
            <?php
         }
    ?>
  </tr>
  
  <?php foreach($data as $row) {  
  echo "<tr>
    <td>".$row['officialName']."</td>
    <td>".$row['pupilsightPersonID']."</td>
    <td>".$row['classname']."</td>
    <td>".$row['sectionname']."</td>";
    $marks=$row['marks'];
    foreach ($data as $val) { 
         $marks=$val['marks'];
         foreach ($marks as $m) {
            $sql='SELECT grade_name FROM  examinationGradeSystemConfiguration WHERE id="'.$m['gradeId'].'"';
            $result = $connection2->query($sql);
            $gradeName = $result->fetch();
            $grade_name='';

            if(!empty($gradeName['grade_name'])){
              $grade_name=$gradeName['grade_name'];
            }
            $marks = str_replace(".00","",$m['marks_obtained']);
            if(empty($marks)){
              if(isset($m['marks_abex'])){
                if(empty($m['marks_abex'])){
                  $marks = $m['marks_abex'];
                }
              }
            }
            $mg = $marks."(".$grade_name.")";
            if($row['pupilsightPersonID']==$val['pupilsightPersonID']){

                $marks = str_replace(".00","",$m['marks_obtained']);
                  if($marks==0){
                      if($m['marks_abex']){
                          $marks = $m['marks_abex'];
                      }
                }
                if(!empty($grade_name)){
                    echo "<td>".$marks."(".$grade_name.")</td>";
                } else {
                    echo "<td>".$marks."</td>";
                }
            }
            ?>
            
            <?php
        
        }
    } 
    ?>
</tr>
<?php } ?>
</table>

<?php
     break;
     case "systemByidloadGrades":
      $syid=$_POST['syid'];
      $sql_grade = 'SELECT id,grade_name  FROM `examinationGradeSystemConfiguration` WHERE `gradeSystemId` ="'.$syid.'" ';
      $r_grade = $connection2->query($sql_grade);
      $grade_data = $r_grade->fetchAll();
      ?>
      <option value="">Select Grade </option>
      <?php foreach ($grade_data as $val) { ?>
      <option value="<?php echo $val['id'];?>"><?php echo $val['grade_name'];?></option>
      <?php } 
     break;
    default:
      echo "Invalid request";
    }
} else {
  echo "Request type is missing";
}
?>


