<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Helper;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;



/**
 * School Year Gateway
 *
 * @version v17
 * @since   v17
 */
class HelperGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = '';

    public function getStudentList($con, $data){
        $sq = "select pupilsightPersonID, officialName, username, email, phone1, admission_no from pupilsightPerson ";
        $sq .=" where pupilsightRoleIDPrimary='003' order by officialName asc";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function getArchiveReport($connection2)
    {
        $sq = "select * from report_manager where status='2' and module_id='archive' ";
        $result = $connection2->query($sq);
        return $result->fetchAll();
    }
    
    public function getActiveReport($connection2)
    {
        $sq = "select * from report_manager where status='2' and module_id<>'archive' ";
        $result = $connection2->query($sq);
        return $result->fetchAll();
    }

    public function getBasicActiveReport($connection2)
    {
        $sq = "select id,name,header,total_column,description,module,date1,date2,date3,date4,param1,param2,param3,param4,param5,param6,param7,param8 from report_manager where status='2' and module_id<>'archive'";
        $result = $connection2->query($sq);
        return $result->fetchAll();
    }

    public function getArchiveFeeRecipt($connection2)
    {
        $sq = "select * from archive_fee_receipt_html order by student_name asc";
        $result = $connection2->query($sq);
        return $result->fetchAll();
    }

    public function getClassByProgram($connection2, $pupilsightProgramID, $pupilsightSchoolYearID) {
        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
        $result = $connection2->query($sql);
        $classesdata = $result->fetchAll();

        $classes = array();
        $classes2 = array();
        $classes1 = array('' => 'Select Class');
        foreach ($classesdata as $ct) {
            $classes2[$ct['pupilsightYearGroupID']] = $ct['name'];
        }
        $classes = $classes1 + $classes2;
        return $classes;
    }

    public function getSectionByProgram($connection2, $pupilsightYearGroupID, $pupilsightProgramID, $pupilsightSchoolYearID) {
        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" GROUP BY a.pupilsightRollGroupID';
        $result = $connection2->query($sql);
        $sectionsdata = $result->fetchAll();

        $sections = array();
        $sections2 = array();
        $sections1 = array('' => 'Select Section');
        foreach ($sectionsdata as $ct) {
            $sections2[$ct['pupilsightRollGroupID']] = $ct['name'];
        }
        $sections = $sections1 + $sections2;
        return $sections;
    }

    public function getClassByProgram_staff($connection2, $pupilsightProgramID,$pupilsightPersonID=Null) {
        $sql = 'SELECT a.*, c.name FROM pupilsightProgramClassSectionMapping AS a     ';       
        $sql .= "left join assignstaff_toclasssection as b on a.pupilsightMappingID= b.pupilsightMappingID ";     
        $sql .= "LEFT JOIN pupilsightYearGroup AS c ON a.pupilsightYearGroupID =c.pupilsightYearGroupID where a.pupilsightProgramID ='".$pupilsightProgramID."'  ";
        if (!empty($pupilsightPersonID)) {
            $sql .="and  b.pupilsightPersonID='".$pupilsightPersonID."' ";
            }
            $sql .=" GROUP BY a.pupilsightYearGroupID";
      //  echo  $sql;
        $result = $connection2->query($sql);
        $classesdata = $result->fetchAll();

        $classes = array();
        $classes2 = array();
        $classes1 = array('' => 'Select Class');
        foreach ($classesdata as $ct) {
            $classes2[$ct['pupilsightYearGroupID']] = $ct['name'];
        }
        $classes = $classes1 + $classes2;
        return $classes;
    }

    public function getClassByProgram_Attconfig($connection2, $pupilsightProgramID, $pupilsightSchoolYearID) {
        $sq = 'SELECT pupilsightYearGroupID FROM attn_settings WHERE pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND pupilsightProgramID = "' . $pupilsightProgramID . '" ';
        if ($att_type != "") {
            $sq .= ' AND a.attn_type="' . $att_type . '"';
        }
        $results = $connection2->query($sq);
        $classData = $results->fetch();
        $clsIDS = $classData['pupilsightYearGroupID'];

        if(!empty($clsIDS)){
            $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightYearGroupID IN (' . $clsIDS . ')  GROUP BY a.pupilsightYearGroupID';
            $result = $connection2->query($sql);
            $classesdata = $result->fetchAll();
            // echo '<pre>';
            // print_r($classes);
            // echo '</pre>';
            $classes = array();
            $classes2 = array();
            $classes1 = array('' => 'Select Class');
            if (!empty($classesdata)) {
                foreach ($classesdata as $k => $cl) {
                    $classes2[$cl['pupilsightYearGroupID']]=  $cl['name'];
                }
            }
            $classes = $classes1 + $classes2;
            return $classes;
        }

    //   $sql= 'SELECT a.*,GROUP_CONCAT(b.pupilsightYearGroupID SEPARATOR ",") as clid,GROUP_CONCAT(b.name SEPARATOR ", ") as name  FROM attn_settings AS a LEFT JOIN pupilsightYearGroup as b ON (FIND_IN_SET(b.pupilsightYearGroupID, a.pupilsightYearGroupID)) WHERE  a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND  b.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" GROUP BY a.pupilsightYearGroupID   ORDER BY b.pupilsightYearGroupID';       
    //   //  echo  $sql;
    //     $result = $connection2->query($sql);
    //     $classesdata = $result->fetchAll();

    //     $classes = array();
    //     $classes2 = array();
    //     $classes1 = array('' => 'Select Class');
    //      if (!empty($classesdata)) {
    //         foreach ($classesdata as  $cl) {
               
    //             $class = explode(',' , $cl['name']);
    //             $cid = explode(',' , $cl['clid']);            
    //             $count=count($class);
    //             for($i=0;$i<$count;$i++){                     
    //             $classes2[$cid[$i]]=  $class[$i];
    //         }
                             
    //         }
    //     }
       /* if (!empty($classes)) {
            foreach ($classes as  $cl) {
               
                $class = explode(',' , $cl['name']);
                $cid = explode(',' , $cl['clid']);            
                $count=count($classesdata);
                for($i=0;$i<$count;$i++){ 
                $classes2[$cid[$i] ]=  $class[$i];                   
                //$data .= '<option value="'. $cid[$i] .'">' . $class[$i] . '</option>';
            }
                             
            }
        }*/
        /*foreach ($classesdata as $ct) {
            $classes2[$ct['pupilsightYearGroupID']] = $ct['name'];
        }*/
        // $classes = $classes1 + $classes2;
        // return $classes;
    }
    public function getSectionByProgram_staff($connection2, $pupilsightYearGroupID, $pupilsightProgramID,$pupilsightPersonID=Null) {
       
        $sql = 'SELECT a.*, c.name FROM pupilsightProgramClassSectionMapping AS a ';       
        $sql .= "left join assignstaff_toclasssection as b on a.pupilsightMappingID= b.pupilsightMappingID ";     
        $sql .= "LEFT JOIN pupilsightRollGroup AS c ON a.pupilsightRollGroupID = c.pupilsightRollGroupID where   a.pupilsightYearGroupID = '".$pupilsightYearGroupID."' ";
        if (!empty($pupilsightPersonID)) {
            $sql .="and  b.pupilsightPersonID='".$pupilsightPersonID."' ";
            }
            $sql .=" GROUP BY a.pupilsightRollGroupID";
       
        $result = $connection2->query($sql);
        $sectionsdata = $result->fetchAll();

        $sections = array();
        $sections2 = array();
        $sections1 = array('' => 'Select Section');
        foreach ($sectionsdata as $ct) {
            $sections2[$ct['pupilsightRollGroupID']] = $ct['name'];
        }
        $sections = $sections1 + $sections2;
        return $sections;
    }

    public function getSectionByProgram_attConfig($connection2, $pupilsightYearGroupID, $pupilsightProgramID, $pupilsightSchoolYearID) {
       
        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" GROUP BY a.pupilsightRollGroupID ';       

       
        $result = $connection2->query($sql);
        $sectionsdata = $result->fetchAll();

        $sections = array();
        $sections2 = array();
        $sections1 = array('' => 'Select Section');
        foreach ($sectionsdata as $ct) {
            $sections2[$ct['pupilsightRollGroupID']] = $ct['name'];
        }
        $sections = $sections1 + $sections2;
        return $sections;
    }

    public function getSectionByStudents_attConfig($connection2, $pupilsightSchoolYearID, $pupilsightProgramID,$pupilsightYearGroupID,$pupilsightRollGroupID) {
       
        $sql = 'SELECT a.*, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';       

       
        $result = $connection2->query($sql);
        $sectionsdata = $result->fetchAll();

        $sections = array();
        $sections2 = array();
        $sections1 = array('' => 'Select Student');
        foreach ($sectionsdata as $ct) {
            $sections2[$ct['pupilsightPersonID']] = $ct['officialName'];
        }
        $sections = $sections1 + $sections2;
        return $sections;
    }
 // Get Parents names
    public function getParentNameByPupilsightPersonID($connection2,$pupilsightPersonID,$relationshipType){

            $f_sql = 'SELECT  b.officialName  FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE pupilsightPersonID2 ="'.$pupilsightPersonID.'" AND relationship="'.$relationshipType.'"';
            $f_sql = $connection2->query($f_sql);
            $f_sql = $f_sql->fetch();
            $name="";
            if(!empty($f_sql['officialName'])){
            $name=$f_sql['officialName'];
            }
            return $name;
         }
          
         // check PupilsightAttendanceBlocked
    public function checkPupilsightAttendanceBlocked($connection2, $pupilsightProgramID,$pupilsightYearGroupID,$pupilsightRollGroupID,$currentDate){
        $sql='SELECT * FROM `pupilsightAttendanceBlocked` WHERE pupilsightProgramID="'.$pupilsightProgramID.'" AND pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" AND pupilsightYearGroupID="'.$pupilsightYearGroupID.'"';
        $result=$connection2->query($sql);
        $data=$result->fetch();
        $array=array();
        if(!empty($data['pupilsightAttendanceBlockID'])){
        if($data['start_date']<=$currentDate AND $data['end_date']>=$currentDate){
            $array['status']="Yes";
            $array['msg']="This  Attendance blocked by admin because of ".$data['name']." (between ".date('d/m/Y',strtotime($data['start_date']))." To ".date('d/m/Y',strtotime($data['end_date']))."), Please Contact Admin.";
        }
        } 
        return $array;
    }

    public function getClassTeacher($connection2, $pupilsightSchoolYearID, $pupilsightPersonID) {
        $res = array();
        if($pupilsightSchoolYearID && $pupilsightPersonID){
            $sql = "select pupilsightYearGroupID,pupilsightProgramID,pupilsightRollGroupID  from pupilsightStudentEnrolment ";
            $sql .=" where pupilsightSchoolYearID='".$pupilsightSchoolYearID."' and pupilsightPersonID='".$pupilsightPersonID."' ";       

            $query = $connection2->query($sql);
            $result = $query->fetch();
            $pupilsightYearGroupID = $result["pupilsightYearGroupID"];
            $pupilsightProgramID = $result["pupilsightProgramID"];
            $pupilsightRollGroupID = $result["pupilsightRollGroupID"];
            
            $sq1 = "SELECT pupilsightPersonID FROM assign_class_teacher_section 
            where pupilsightSchoolYearID='".$pupilsightSchoolYearID."' 
            and pupilsightYearGroupID='".$pupilsightYearGroupID."' 
            and pupilsightProgramID='".$pupilsightProgramID."' 
            and pupilsightRollGroupID='".$pupilsightRollGroupID."' ";

            $query1 = $connection2->query($sq1);
            $result1 = $query1->fetch();
            
            $sq = "select stc.subject_display_name, subject_type, ps.pupilsightPersonID from subjectToClassCurriculum as stc
            left join assignstaff_tosubject as asub on asub.subjectToClassCurriculumID = stc.id
            left join pupilsightStaff as ps on ps.pupilsightStaffID = asub.pupilsightStaffID
            where stc.pupilsightSchoolYearID='".$pupilsightSchoolYearID."' 
            and stc.pupilsightYearGroupID='".$pupilsightYearGroupID."' 
            and stc.pupilsightProgramID='".$pupilsightProgramID."' and ps.pupilsightPersonID is not null ";
            
            $query3 = $connection2->query($sq);
            $res["sublist"] = $query3->fetchAll();
            $res["pupilsightPersonID"] = $result1["pupilsightPersonID"];
        }
        return $res;
    }

    public function getGroupList($connection2, $pupilsightSchoolYearID){
            $sq = "select group_concat(pg.pupilsightPersonID) as uid, g.name, g.pupilsightGroupID as groupid FROM pupilsightGroup as g 
            left join pupilsightGroupPerson as pg on pg.pupilsightGroupID = g.pupilsightGroupID
            where g.is_chat=1 and g.pupilsightSchoolYearID='".$pupilsightSchoolYearID."' and pg.pupilsightPersonID is not null group by g.pupilsightGroupID; ";
            $query = $connection2->query($sq);
            return $query->fetchAll();
    }
         //get sort val name in attendance_take_byRollGroupListView using
    public function getSortValByName($type){
        switch ($type) {
            case "Student ID":
                return "pupilsightPersonID";
                break;
                case "Admission No":
                return "admission_no";
                break;
                case "gender":
                return "gender";
                break;
                case "gender":
                return "gender";
                break;
                case " Date OF Birth":
                return "dob";
                break;
                case "Class":
                return "classname";
                break;
                default:
                return '';
        }

    }


    public function getClassByProgramInSection_Attconfig($connection2, $pupilsightProgramID) {
        $sql= 'SELECT a.*,GROUP_CONCAT(b.pupilsightYearGroupID SEPARATOR ",") as clid,GROUP_CONCAT(b.name SEPARATOR ", ") as name  FROM attn_settings AS a LEFT JOIN pupilsightYearGroup as b ON (FIND_IN_SET(b.pupilsightYearGroupID, a.pupilsightYearGroupID)) WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.attn_type="1" GROUP BY a.pupilsightYearGroupID   ORDER BY b.pupilsightYearGroupID';       
        //  echo  $sql;
            $result = $connection2->query($sql);
            $classesdata = $result->fetchAll();
    
            $classes = array();
            $classes2 = array();
            $classes1 = array('' => 'Select Class');
            if (!empty($classesdata)) {
                foreach ($classesdata as  $cl) {
                
                    $class = explode(',' , $cl['name']);
                    $cid = explode(',' , $cl['clid']);            
                    $count=count($class);
                    for($i=0;$i<$count;$i++){                     
                    $classes2[$cid[$i]]=  $class[$i];
                }
                                
                }
            }
            $classes = $classes1 + $classes2;
            return $classes;
    }

    public function getClassByProgramInperiodWise_Attconfig($connection2, $pupilsightProgramID) {
        $sql= 'SELECT a.*,GROUP_CONCAT(b.pupilsightYearGroupID SEPARATOR ",") as clid,GROUP_CONCAT(b.name SEPARATOR ", ") as name  FROM attn_settings AS a LEFT JOIN pupilsightYearGroup as b ON (FIND_IN_SET(b.pupilsightYearGroupID, a.pupilsightYearGroupID)) WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.attn_type="2" GROUP BY a.pupilsightYearGroupID   ORDER BY b.pupilsightYearGroupID';       
        //  echo  $sql;
            $result = $connection2->query($sql);
            $classesdata = $result->fetchAll();

            $classes = array();
            $classes2 = array();
            $classes1 = array('' => 'Select Class');
            if (!empty($classesdata)) {
                foreach ($classesdata as  $cl) {
                
                    $class = explode(',' , $cl['name']);
                    $cid = explode(',' , $cl['clid']);            
                    $count=count($class);
                    for($i=0;$i<$count;$i++){                     
                    $classes2[$cid[$i]]=  $class[$i];
                }
                                
                }
            }
            $classes = $classes1 + $classes2;
            return $classes;
    }

    public function getSubjectByClassWise($connection2,$pupilsightSchoolYearID,$pupilsightProgramID,$pupilsightYearGroupID) {
            $sqlt = 'SELECT `pupilsightDepartment`.`pupilsightDepartmentID`, `pupilsightDepartment`.`name`, `pupilsightDepartment`.`type`, `pupilsightDepartment`.`nameShort` FROM `assign_core_subjects_toclass` LEFT JOIN `pupilsightProgramClassSectionMapping` ON `assign_core_subjects_toclass`.`pupilsightYearGroupID`=`pupilsightProgramClassSectionMapping`.`pupilsightYearGroupID` LEFT JOIN `pupilsightDepartment` ON `assign_core_subjects_toclass`.`pupilsightDepartmentID`=`pupilsightDepartment`.`pupilsightDepartmentID` WHERE `assign_core_subjects_toclass`.`pupilsightYearGroupID` ="'.$pupilsightYearGroupID.'" AND `assign_core_subjects_toclass`.`pupilsightProgramID` = "'.$pupilsightProgramID.'" AND `pupilsightProgramClassSectionMapping`.`pupilsightSchoolYearID` = "'.$pupilsightSchoolYearID.'" GROUP BY `assign_core_subjects_toclass`.`pupilsightDepartmentID` ORDER BY `assign_core_subjects_toclass`.`pos` ASC';
        $resultt = $connection2->query($sqlt);  
        $subjects_data = $resultt->fetchAll();

        $subjects = array();
        $subjects2 = array();
        $subjects1 = array('' => 'Select Subject');
        foreach ($subjects_data as $ct) {
            $subjects2[$ct['pupilsightDepartmentID']] = $ct['name'];
        }
        $subjects = $subjects1 + $subjects2;
        return $subjects;
    }


    public function getMultipleSectionByProgram($connection2, $pupilsightYearGroupID, $pupilsightProgramID) {
        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID IN (' . implode(',',$pupilsightYearGroupID) .') GROUP BY a.pupilsightRollGroupID';
        $result = $connection2->query($sql);
        $sectionsdata = $result->fetchAll();

        $sections = array();
        $sections2 = array();
        //$sections1 = array('' => 'Select Section');
        foreach ($sectionsdata as $ct) {
            $sections2[$ct['pupilsightRollGroupID']] = $ct['name'];
        }
        $sections = $sections2;
        return $sections;
    }    

    public function getSubjectByProgramClass($connection2, $pupilsightYearGroupID, $pupilsightProgramID, $pupilsightSchoolYearID, $pupilsightPersonID) {
        $sqlck = 'SELECT pupilsightRoleIDPrimary FROM pupilsightPerson WHERE pupilsightPersonID = '.$pupilsightPersonID.' ';
        $resultck = $connection2->query($sqlck);
        $ckdata = $resultck->fetch();
        $roleid= $ckdata['pupilsightRoleIDPrimary'];
        
        if($roleid=='002')//for teacher login
        {
            $sq = "select DISTINCT subjectToClassCurriculum.pupilsightDepartmentID, subjectToClassCurriculum.subject_display_name from subjectToClassCurriculum  LEFT JOIN assignstaff_tosubject ON subjectToClassCurriculum.pupilsightDepartmentID = assignstaff_tosubject.pupilsightDepartmentID  LEFT JOIN pupilsightStaff ON assignstaff_tosubject.pupilsightStaffID = pupilsightStaff.pupilsightStaffID  where subjectToClassCurriculum.pupilsightSchoolYearID = '".$pupilsightSchoolYearID."' AND subjectToClassCurriculum.pupilsightProgramID = '".$pupilsightProgramID."' AND subjectToClassCurriculum.pupilsightYearGroupID ='".$pupilsightYearGroupID."' AND pupilsightStaff.pupilsightPersonID='".$pupilsightPersonID."' order by subjectToClassCurriculum.subject_display_name asc";
        }
        else
        {
            $sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightSchoolYearID = '".$pupilsightSchoolYearID."' AND pupilsightProgramID = '".$pupilsightProgramID."' AND pupilsightYearGroupID ='".$pupilsightYearGroupID."' order by subject_display_name asc";
        }
        //echo $sq;
    
        $result = $connection2->query($sq);
        $rowdata = $result->fetchAll();

        $subjects = array();
        $subjects2 = array();
        $subjects1 = array('' => 'Select Subject');
        foreach ($rowdata as $ct) {
            $subjects2[$ct['pupilsightDepartmentID']] = $ct['subject_display_name'];
        }
        $subjects = $subjects1 + $subjects2;
        return $subjects;

    }

    public function getStudentAndClassViaClassTeacher($connection2, $pupilsightSchoolYearID, $uid) {
        try{
            $sql = 'SELECT a.*, b.name, c.name as section FROM assign_class_teacher_section AS a 
            LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID 
            LEFT JOIN pupilsightRollGroup AS c ON a.pupilsightRollGroupID = c.pupilsightRollGroupID 
            WHERE a.pupilsightPersonID = "'.$uid.'" AND a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" 
            GROUP BY a.pupilsightYearGroupID';
            
            $query = $connection2->query($sql);
            
            $result = $query->fetchAll();
            $len = count($result);
            $i = 0;
            $res = array();
            while($i<$len){
                $sq = "select b.officialName, b.pupilsightPersonID from pupilsightStudentEnrolment as a
                left join pupilsightPerson as b on a.pupilsightPersonID=b.pupilsightPersonID
                where a.pupilsightSchoolYearID='".$pupilsightSchoolYearID."'
                and a.pupilsightProgramID='".$result[$i]["pupilsightProgramID"]."'
                and a.pupilsightYearGroupID='".$result[$i]["pupilsightYearGroupID"]."' 
                and a.pupilsightRollGroupID='".$result[$i]["pupilsightRollGroupID"]."'  ";
                
                $query1 = $connection2->query($sq);
                $st = $query1->fetchAll();
                
                $res[$i]["st"]=$st;
                $res[$i]["class"]=$result[$i]["name"]."-".$result[$i]["section"];
                $res[$i]["classid"]=$result[$i]["pupilsightSchoolYearID"]."-".$result[$i]["pupilsightProgramID"]."-".$result[$i]["pupilsightYearGroupID"]."-".$result[$i]["pupilsightRollGroupID"];
                $res[$i]["result"]=$result[$i];
                $i++;
            }
            return $res;
        }catch(Exception $ex){
            echo $ex->getMessage();
        }
        return "";
    }

    


    public function getProgram($connection2) {
        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        return $resultp->fetchAll();
    }      

    public function getClassByProgramForTeacher($connection2, $pupilsightProgramID, $uid) {
        $sql = 'SELECT a.*, b.name FROM assign_class_teacher_section AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightPersonID = "'.$uid.'" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
        $result = $connection2->query($sql);
        $classesdata = $result->fetchAll();

        $classes = array();
        $classes2 = array();
        $classes1 = array('' => 'Select Class');
        foreach ($classesdata as $ct) {
            $classes2[$ct['pupilsightYearGroupID']] = $ct['name'];
        }
        $classes = $classes1 + $classes2;
        return $classes;
    }

    public function getSectionByProgramForTeacher($connection2, $pupilsightYearGroupID, $pupilsightProgramID, $uid) {
        $sql = 'SELECT a.*, b.name FROM assign_class_teacher_section AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID 
        WHERE a.pupilsightPersonID = "'.$uid.'" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" 
        AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" 
        GROUP BY a.pupilsightRollGroupID';
        
        $result = $connection2->query($sql);
        $sectionsdata = $result->fetchAll();

        $sections = array();
        $sections2 = array();
        $sections1 = array('' => 'Select Section');
        foreach ($sectionsdata as $ct) {
            $sections2[$ct['pupilsightRollGroupID']] = $ct['name'];
        }
        $sections = $sections1 + $sections2;
        return $sections;
    }

    public function getStudentByAll($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID, $pupilsightRollGroupID){
        
        $sql = 'SELECT a.*, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';
        $result = $connection2->query($sql);
        $studentData = $result->fetchAll();

        $students = array();
        $students2 = array();
        $students1 = array('' => 'Select Student');
        foreach ($studentData as $ct) {
            $students2[$ct['pupilsightPersonID']] = $ct['officialName'];
        }
        $students = $students1 + $students2;
        return $students;

    }

    public function getInvoice($connection2, $pupilsightYearGroupID, $pupilsightProgramID, $pupilsightSchoolYearID) {
        $sql = 'SELECT a.*, b.title, b.fn_fee_structure_id FROM fn_fee_invoice_class_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND b.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" GROUP BY b.title';
        $result = $connection2->query($sql);
        $sectionsdata = $result->fetchAll();

        $sections = array();
        $sections2 = array();
        $sections1 = array('' => 'Select Invoice');
        foreach ($sectionsdata as $ct) {
            $sections2[$ct['title']] = $ct['title'];
        }
        $invoices = $sections1 + $sections2;
        return $invoices;
    }

    public function getClassByProgramAcademic($connection2, $pupilsightProgramID, $pupilsightSchoolYearID) {
        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '"  GROUP BY a.pupilsightYearGroupID';
        $result = $connection2->query($sql);
        $classesdata = $result->fetchAll();

        $classes = array();
        $classes2 = array();
        $classes1 = array('' => 'Select Class');
        foreach ($classesdata as $ct) {
            $classes2[$ct['pupilsightYearGroupID']] = $ct['name'];
        }
        $classes = $classes1 + $classes2;
        return $classes;
    }

    public function getClassByProgramAcademicForTeacher($connection2, $pupilsightProgramID, $uid, $pupilsightSchoolYearID) {
        $sql = 'SELECT a.*, b.name FROM assign_class_teacher_section AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightPersonID = "'.$uid.'" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '"  GROUP BY a.pupilsightYearGroupID';
        $result = $connection2->query($sql);
        $classesdata = $result->fetchAll();

        $classes = array();
        $classes2 = array();
        $classes1 = array('' => 'Select Class');
        foreach ($classesdata as $ct) {
            $classes2[$ct['pupilsightYearGroupID']] = $ct['name'];
        }
        $classes = $classes1 + $classes2;
        return $classes;
    }

}