<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Students;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\Traits\SharedUserLogic;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class StudentGateway extends QueryableGateway
{
    use TableAware;
    use SharedUserLogic;

    private static $tableName = 'pupilsightStudentEnrolment';

    private static $searchableColumns = ['pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'pupilsightPerson.username', 'pupilsightPerson.email', 'pupilsightPerson.emailAlternate', 'pupilsightPerson.studentID', 'pupilsightPerson.phone1', 'pupilsightPerson.vehicleRegistration'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryStudentsBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID=NULL, $searchFamilyDetails = false, $pupilsightProgramID=NULL, $pupilsightYearGroupID=NULL, $pupilsightRollGroupID=NULL,$search=NULL)
    {
        //print_r($_SESSION['student_search']);
        if(!empty($_SESSION['student_search'])){
            $pupilsightProgramID = $_SESSION['student_search']['pupilsightProgramID'];
            $pupilsightYearGroupID = $_SESSION['student_search']['pupilsightYearGroupID'];
            $pupilsightRollGroupID = $_SESSION['student_search']['pupilsightRollGroupID'];
        } 
         
       //echo $pupilsightProgramID;
       //die();

        $query = $this
            ->newQuery()
            ->distinct()
            ->from('pupilsightPerson')
            ->cols([
                "CASE WHEN (pupilsightPerson.active='1') THEN 'Active' ELSE 'Inactive'  END as active_status",'pupilsightPerson.pupilsightPersonID as student_id', 'pupilsightStudentEnrolmentID', 'pupilsightYearGroup.name AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'pupilsightStudentEnrolment.rollOrder', "'Student' as roleCategory", 'pupilsightSchoolYear.name as academic_year', 'pupilsightProgram.name as program', 'parent1.officialName as fatherName','parent1.email as fatherEmail','parent1.phone1 as fatherPhone','parent2.officialName as motherName','parent2.email as motherEmail','parent2.phone1 as motherPhone', 'pupilsightPerson.*'
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin('pupilsightFamilyChild as child', "child.pupilsightPersonID=pupilsightPerson.pupilsightPersonID")
            ->leftJoin('pupilsightFamilyAdult as adult1', "(adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.contactPriority=1)")
            ->leftJoin('pupilsightPerson as parent1', "(parent1.pupilsightPersonID=adult1.pupilsightPersonID AND parent1.status='Full')")
            ->leftJoin('pupilsightFamilyAdult as adult2', "(adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.contactPriority=2)")
            ->leftJoin('pupilsightPerson as parent2', "(parent2.pupilsightPersonID=adult2.pupilsightPersonID AND parent2.status='Full')")
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        if ($criteria->hasFilter('all')) {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
                  ->where("pupilsightRole.category='Student'");
        } 
        else if(!empty($search)&&empty($pupilsightProgramID))
        {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
            ->where("pupilsightRole.category='Student'");
        }
        else {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
            ->where("pupilsightRole.category='Student'")
            ->where("pupilsightStudentEnrolment.pupilsightStudentEnrolmentID IS NOT NULL")
                  ->where("pupilsightPerson.status = 'Full'")
                  
                  ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
                  ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
                  ->bindValue('today', date('Y-m-d'));
        }
        $query->where('pupilsightPerson.pupilsightRoleIDPrimary = "003" ');
        if (!empty($pupilsightProgramID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
        }
        
        if (!empty($pupilsightYearGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
        }
        if (!empty($pupilsightRollGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
        }

        if (!empty($search)) {
            $query->where('pupilsightPerson.officialName LIKE "%' . $search . '%" ');
        }

        
        
        // if ($searchFamilyDetails && $criteria->hasSearchText()) {
        //     self::$searchableColumns = array_merge(self::$searchableColumns, ['parent1.email', 'parent1.emailAlternate', 'parent2.email', 'parent2.emailAlternate']);
            
        //     $query
        //         ->leftJoin('pupilsightFamilyChild as child', "child.pupilsightPersonID=pupilsightPerson.pupilsightPersonID")
        //         ->leftJoin('pupilsightFamilyAdult as adult1', "(adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.contactPriority=1)")
        //         ->leftJoin('pupilsightPerson as parent1', "(parent1.pupilsightPersonID=adult1.pupilsightPersonID AND parent1.status='Full')")
        //         ->leftJoin('pupilsightFamilyAdult as adult2', "(adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.contactPriority=2)")
        //         ->leftJoin('pupilsightPerson as parent2', "(parent2.pupilsightPersonID=adult2.pupilsightPersonID AND parent2.status='Full')");
        // }

        $query->where('pupilsightPerson.is_delete = "0" ')
        ->groupBy(['pupilsightPerson.pupilsightPersonID'])
        ->orderBy(['pupilsightPerson.pupilsightPersonID DESC']);
        //echo $query;
        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria, TRUE);
    }


    public function queryStudentsBySchoolYearandID(QueryCriteria $criteria, $pupilsightSchoolYearID, $studentids)
    {

      // echo $studentids;
       
        $query = $this
            ->newQuery()
            ->distinct()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.officialName as studentName','pupilsightPerson.pupilsightPersonID',"CASE WHEN (pupilsightPerson.active='1') THEN 'Active' ELSE 'Inactive'  END as active_status",'pupilsightPerson.pupilsightPersonID as student_id','pupilsightPerson.dob', 'pupilsightStudentEnrolmentID', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname','pupilsightYearGroup.name as classname' ,'pupilsightPerson.image_240', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'pupilsightStudentEnrolment.rollOrder', 'pupilsightPerson.dateStart', 'pupilsightPerson.dateEnd', 'pupilsightPerson.status', "'Student' as roleCategory", 'pupilsightSchoolYear.name as academic_year', 'pupilsightProgram.name as program'
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        if ($criteria->hasFilter('all')) {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
                  ->where("pupilsightRole.category='Student'");
        } else {
             $query->where("pupilsightStudentEnrolment.pupilsightStudentEnrolmentID IS NOT NULL")
                  ->where("pupilsightPerson.status = 'Full'")
                  ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
                  ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
                  ->bindValue('today', date('Y-m-d'))
                  ->where('pupilsightPerson.pupilsightPersonID IN( '.$studentids.')');
        }

      

        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria);
    }



    public function queryStudentsBySchoolYearandID_with_assigned_subjects(QueryCriteria $criteria, $pupilsightSchoolYearID, $studentids)
    {

      // echo $studentids;
       
        $query = $this
            ->newQuery()
            ->distinct()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID',"GROUP_CONCAT(DISTINCT pupilsightDepartment.name SEPARATOR ', ') as coresubject",'pupilsightPerson.pupilsightPersonID as student_id', 'pupilsightStudentEnrolment.pupilsightStudentEnrolmentID', "CASE WHEN (pupilsightPerson.active='1') THEN 'Active' ELSE 'Inactive'  END as active_status",'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname','pupilsightYearGroup.name as classname' , 'pupilsightProgram.name as program'
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
           
            ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            
            ->leftJoin('assign_core_subjects_toclass', 'pupilsightStudentEnrolment.pupilsightProgramID=assign_core_subjects_toclass.pupilsightProgramID')
            ->leftJoin('pupilsightDepartment', 'assign_core_subjects_toclass.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')    
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
       
             $query->where("pupilsightStudentEnrolment.pupilsightStudentEnrolmentID IS NOT NULL")

                 ->where('pupilsightPerson.pupilsightPersonID IN( '.$studentids.')') 
                  ->where('pupilsightStudentEnrolment.pupilsightYearGroupID =assign_core_subjects_toclass.pupilsightYearGroupID ')
                  ->groupBy(['assign_core_subjects_toclass.pupilsightYearGroupID,assign_core_subjects_toclass.pupilsightProgramID,pupilsightPerson.pupilsightPersonID']);
       

      

        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function queryStudentEnrolmentBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightStudentEnrolmentID','pupilsightProgram.name AS program', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.officialName', 'pupilsightPerson.surname', 'pupilsightPerson.image_240', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'pupilsightStudentEnrolment.rollOrder', 'pupilsightPerson.dateStart', 'pupilsightPerson.dateEnd', 'pupilsightPerson.status', "'Student' as roleCategory"
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID');
            //->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            //->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function queryStudentEnrolmentByRollGroup(QueryCriteria $criteria, $pupilsightRollGroupID = null)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightStudentEnrolmentID', 'pupilsightStudentEnrolment.pupilsightSchoolYearID', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'pupilsightPerson.image_240', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'pupilsightStudentEnrolment.rollOrder', 'pupilsightPerson.dateStart', 'pupilsightPerson.dateEnd', 'pupilsightPerson.status', "'Student' as roleCategory", 'gender', 'dob', 'citizenship1', 'citizenship2', 'transport', 'lockerNumber', 'privacy'
            ])
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->innerJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where("pupilsightPerson.status = 'Full'")
            ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
            ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
            ->bindValue('today', date('Y-m-d'));
        
        if (!empty($pupilsightRollGroupID)) {
            $query
                ->where('pupilsightStudentEnrolment.pupilsightRollGroupID = :pupilsightRollGroupID')
                ->bindValue('pupilsightRollGroupID', $pupilsightRollGroupID);
        }
            
        $criteria->addFilterRules($this->getSharedUserFilterRules());

        $criteria->addFilterRules([
            'view' => function ($query, $view) {
                if ($view == 'extended') {
                    $query->cols(['pupilsightHouse.name as house', 'pupilsightPersonMedical.*', 'COUNT(pupilsightPersonMedicalConditionID) as conditionCount'])
                        ->leftJoin('pupilsightHouse', 'pupilsightHouse.pupilsightHouseID=pupilsightPerson.pupilsightHouseID')
                        ->leftJoin('pupilsightPersonMedical', 'pupilsightPersonMedical.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                        ->leftJoin('pupilsightPersonMedicalCondition', 'pupilsightPersonMedicalCondition.pupilsightPersonMedicalID=pupilsightPersonMedical.pupilsightPersonMedicalID')
                        ->groupBy(['pupilsightPerson.pupilsightPersonID']);
                }
                return $query;
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryStudentsAndTeachersBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID) 
    {
        $query = $this
            ->newQuery()
            ->distinct()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightStudentEnrolmentID', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'pupilsightPerson.image_240', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'pupilsightStudentEnrolment.rollOrder', 'pupilsightPerson.dateStart', 'pupilsightPerson.dateEnd', 'pupilsightPerson.status', 'pupilsightRole.category as roleCategory', 'pupilsightStaff.type as staffType'
            ])
            ->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightStaff', "pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID")
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            
            ->groupBy(['pupilsightPerson.pupilsightPersonID']);

        if ($criteria->hasFilter('all')) {
            $query->where("(pupilsightPerson.status = 'Full' OR pupilsightPerson.status = 'Expected')");
        } else {
            $query->where("(pupilsightStudentEnrolment.pupilsightStudentEnrolmentID IS NOT NULL OR (pupilsightStaff.pupilsightStaffID IS NOT NULL AND pupilsightRole.category='Staff') )")
                  ->where("pupilsightPerson.status = 'Full'")
                  ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
                  ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
                  ->bindValue('today', date('Y-m-d'));
        }

        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function selectActiveStudentsByFamilyAdult($pupilsightSchoolYearID, $pupilsightPersonID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $pupilsightPersonID, 'today' => date('Y-m-d'));
        $sql = "SELECT pupilsightPerson.pupilsightPersonID,pupilsightPerson.active, title, surname, preferredName, image_240, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, 'Student' as roleCategory
                FROM pupilsightFamilyAdult
                JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID)
                JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID
                AND pupilsightFamilyAdult.childDataAccess='Y'
                AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                AND pupilsightPerson.status='Full' 
                AND (dateStart IS NULL OR dateStart<=:today) 
                AND (dateEnd IS NULL  OR dateEnd>=:today)
                GROUP BY pupilsightPerson.pupilsightPersonID
                ORDER BY surname, preferredName";

        return $this->db()->select($sql, $data);
    }

    public function selectActiveStudentByPerson($pupilsightSchoolYearID, $pupilsightPersonID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $pupilsightPersonID, 'today' => date('Y-m-d'));
        $sql = "SELECT pupilsightPerson.pupilsightPersonID,pupilsightPerson.active, title, surname, preferredName, image_240, pupilsightYearGroup.pupilsightYearGroupID, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.pupilsightRollGroupID, pupilsightRollGroup.nameShort AS rollGroup, 'Student' as roleCategory
                FROM pupilsightPerson
                JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID 
                AND pupilsightPerson.status='Full'
                AND (dateStart IS NULL OR dateStart<=:today) 
                AND (dateEnd IS NULL  OR dateEnd>=:today)
                AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID";

        return $this->db()->select($sql, $data);
    }

    public function selectAllStudentEnrolmentsByPerson($pupilsightPersonID)
    {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = "SELECT * 
                FROM pupilsightStudentEnrolment 
                JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) 
                WHERE pupilsightPersonID=:pupilsightPersonID 
                AND (pupilsightSchoolYear.status='Current' OR pupilsightSchoolYear.status='Past')
                ORDER BY sequenceNumber DESC";

        return $this->db()->select($sql, $data);
    }

    public function getStudentEnrolmentCount($pupilsightSchoolYearID)
    {
        $data = ['pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'today' => date('Y-m-d')];
        $sql = "SELECT COUNT(pupilsightPerson.pupilsightPersonID) 
                FROM pupilsightPerson
                JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                WHERE pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
                AND status='FULL' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today)";

        return $this->db()->selectOne($sql, $data);
    }

    public function get_assigned_elect_sub_tostudents(QueryCriteria $criteria,$studentids) {
      

 /// `assign_elective_subjects_tostudents`,`pupilsightProgramID`,`pupilsightYearGroupID`,`pupilsightDepartmentID`,`pupilsightPersonID`        
                    $query = $this
                    ->newQuery()
                    ->from('assign_elective_subjects_tostudents')
                    ->cols([
                        'assign_elective_subjects_tostudents.*','pupilsightProgram.name AS program_name','pupilsightPerson.pupilsightPersonID','pupilsightPerson.surname','pupilsightPerson.officialName',"GROUP_CONCAT(DISTINCT pupilsightDepartment.name SEPARATOR ', ') as subject","GROUP_CONCAT(DISTINCT pupilsightYearGroup.name SEPARATOR ', ') as class"
                    ])
                   ->leftJoin('pupilsightDepartment', 'assign_elective_subjects_tostudents.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
                    ->leftJoin('pupilsightProgram', 'assign_elective_subjects_tostudents.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                    ->leftJoin('pupilsightYearGroup', 'assign_elective_subjects_tostudents.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                    ->leftJoin('pupilsightPerson', 'assign_elective_subjects_tostudents.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                    
                    ->where('assign_elective_subjects_tostudents.pupilsightPersonID IN( '.$studentids.')')           
                   // ->bindValue('fn_fee_invoice_id', $fn_fee_invoice_id)
                    ->groupBy(['assign_elective_subjects_tostudents.pupilsightYearGroupID,assign_elective_subjects_tostudents.pupilsightProgramID']);
        
         
                    return $this->runQuery($query, $criteria);
                } 



//get inactive students to get registerd if not registred/assigned  previosly 
                public function queryStudentsBySchoolYear_inactive_students(QueryCriteria $criteria, $pupilsightSchoolYearID)
                {
            
                  // echo $studentids;
                   
                    $query = $this
                        ->newQuery()
                        ->distinct()
                        ->from('pupilsightPerson')
                        ->cols([
                            'pupilsightPerson.pupilsightPersonID',"CASE WHEN (pupilsightPerson.active='1') THEN 'Active' ELSE 'Inactive'  END as active_status",'pupilsightPerson.pupilsightPersonID as student_id','pupilsightPerson.dob', 'pupilsightStudentEnrolmentID', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname','pupilsightYearGroup.name as classname' ,'pupilsightPerson.image_240', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'pupilsightStudentEnrolment.rollOrder', 'pupilsightPerson.dateStart', 'pupilsightPerson.dateEnd', 'pupilsightPerson.status', "'Student' as roleCategory", 'pupilsightSchoolYear.name as academic_year', 'pupilsightProgram.name as program'
                        ])
                        ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
                        ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                        ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                        ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                        ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                        ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
            
                    if ($criteria->hasFilter('all')) {
                        $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
                              ->where("pupilsightRole.category='Student'");
                    } else {
                         $query->where("pupilsightStudentEnrolment.pupilsightStudentEnrolmentID IS NOT NULL")
                              ->where("pupilsightPerson.status = 'Full'")
                              ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
                              ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
                              ->bindValue('today', date('Y-m-d'))
                             ->where('(pupilsightStudentEnrolment.active=0 )');
                    }
            
                  
            
                    $criteria->addFilterRules($this->getSharedUserFilterRules());
            
                    return $this->runQuery($query, $criteria);
                }

                
    public function getStudentData(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID)
    {
        if(!empty($pupilsightProgramID) || !empty($pupilsightYearGroupID) || !empty($pupilsightRollGroupID)){
            //print_r($criteria);
            $clsId = implode(',', $pupilsightYearGroupID);
            $secId = implode(',', $pupilsightRollGroupID);
            $pupilsightRoleIDAll = '003';
            $query = $this
                ->newQuery()
                ->from('pupilsightPerson')
                ->cols([
                    'pupilsightPerson.pupilsightPersonID AS stuid', 'pupilsightPerson.officialName AS student_name','pupilsightProgram.name as progname','pupilsightYearGroup.name as classname','pupilsightRollGroup.name as secname'
                ])
                ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID');
            if (!empty($pupilsightProgramID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
            }
            if (!empty($pupilsightSchoolYearID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ');
            }
            if (!empty($clsId)) {
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID IN (' . $clsId . ') ');
            }
            if (!empty($secId)) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID IN (' .$secId . ') ');
            }

            $query->where('pupilsightPerson.pupilsightRoleIDAll = "' . $pupilsightRoleIDAll . '" ')
                ->groupBy(['pupilsightPerson.pupilsightPersonID'])
                ->orderBy(['pupilsightYearGroup.pupilsightYearGroupID ASC']);
            //   echo $query;    
            //   die();
            $res = $this->runQuery($query, $criteria);
            $data = $res->data;

            $res->data = $data;
            return $res;
        }
    }      


    public function getAllDeletedStudents(QueryCriteria $criteria, $pupilsightSchoolYearID=NULL, $searchFamilyDetails = false, $pupilsightProgramID=NULL, $pupilsightYearGroupID=NULL, $pupilsightRollGroupID=NULL,$search=NULL)
    {
        //print_r($_SESSION['student_search']);
        if(!empty($_SESSION['student_search'])){
            $pupilsightProgramID = $_SESSION['student_search']['pupilsightProgramID'];
            $pupilsightYearGroupID = $_SESSION['student_search']['pupilsightYearGroupID'];
            $pupilsightRollGroupID = $_SESSION['student_search']['pupilsightRollGroupID'];
        } 
         
       //echo $pupilsightProgramID;
       //die();

        $query = $this
            ->newQuery()
            ->distinct()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID',"CASE WHEN (pupilsightPerson.active='1') THEN 'Active' ELSE 'Inactive'  END as active_status",'pupilsightPerson.pupilsightPersonID as student_id','pupilsightPerson.dob', 'pupilsightStudentEnrolmentID', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname','pupilsightPerson.officialName', 'pupilsightPerson.image_240', 'pupilsightYearGroup.name AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'pupilsightStudentEnrolment.rollOrder', 'pupilsightPerson.dateStart', 'pupilsightPerson.dateEnd', 'pupilsightPerson.status', "'Student' as roleCategory", 'pupilsightSchoolYear.name as academic_year', 'pupilsightProgram.name as program','pupilsightPerson.gender','pupilsightPerson.username','pupilsightPerson.address1','pupilsightPerson.address1District','pupilsightPerson.address1Country','pupilsightPerson.phone1','pupilsightPerson.languageFirst','pupilsightPerson.languageSecond','pupilsightPerson.languageThird','pupilsightPerson.religion','pupilsightPerson.admission_no', 'pupilsightPerson.active'
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        if ($criteria->hasFilter('all')) {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
                  ->where("pupilsightRole.category='Student'");
        } 
        else if(!empty($search)&&empty($pupilsightProgramID))
        {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
            ->where("pupilsightRole.category='Student'");
        }
        else {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
            ->where("pupilsightRole.category='Student'")
            ->where("pupilsightStudentEnrolment.pupilsightStudentEnrolmentID IS NOT NULL")
                  ->where("pupilsightPerson.status = 'Full'")
                  
                  ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
                  ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
                  ->bindValue('today', date('Y-m-d'));
        }
        $query->where('pupilsightPerson.pupilsightRoleIDPrimary = "003" ');
        if (!empty($pupilsightProgramID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
        }
        
        if (!empty($pupilsightYearGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
        }
        if (!empty($pupilsightRollGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
        }
        
        if ($searchFamilyDetails && $criteria->hasSearchText()) {
            self::$searchableColumns = array_merge(self::$searchableColumns, ['parent1.email', 'parent1.emailAlternate', 'parent2.email', 'parent2.emailAlternate']);
            
            $query
                ->leftJoin('pupilsightFamilyChild as child', "child.pupilsightPersonID=pupilsightPerson.pupilsightPersonID")
                ->leftJoin('pupilsightFamilyAdult as adult1', "(adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.contactPriority=1)")
                ->leftJoin('pupilsightPerson as parent1', "(parent1.pupilsightPersonID=adult1.pupilsightPersonID AND parent1.status='Full')")
                ->leftJoin('pupilsightFamilyAdult as adult2', "(adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.contactPriority=2)")
                ->leftJoin('pupilsightPerson as parent2', "(parent2.pupilsightPersonID=adult2.pupilsightPersonID AND parent2.status='Full')");
        }

        $query->where('pupilsightPerson.is_delete = "1" ')
        ->orderBy(['pupilsightPerson.pupilsightPersonID DESC']);
        //echo $query;
        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getAllDeRegisterStudents(QueryCriteria $criteria, $pupilsightSchoolYearID=NULL, $searchFamilyDetails = false, $pupilsightProgramID=NULL, $pupilsightYearGroupID=NULL, $pupilsightRollGroupID=NULL,$search=NULL)
    {
        //print_r($_SESSION['student_search']);
        if(!empty($_SESSION['student_search'])){
            $pupilsightProgramID = $_SESSION['student_search']['pupilsightProgramID'];
            $pupilsightYearGroupID = $_SESSION['student_search']['pupilsightYearGroupID'];
            $pupilsightRollGroupID = $_SESSION['student_search']['pupilsightRollGroupID'];
        } 
         
       //echo $pupilsightProgramID;
       //die();

        $query = $this
            ->newQuery()
            ->distinct()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID',"CASE WHEN (pupilsightPerson.active='1') THEN 'Active' ELSE 'Inactive'  END as active_status",'pupilsightPerson.pupilsightPersonID as student_id','pupilsightPerson.dob', 'pupilsightStudentEnrolmentID', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname','pupilsightPerson.officialName', 'pupilsightPerson.image_240', 'pupilsightYearGroup.name AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'pupilsightStudentEnrolment.rollOrder', 'pupilsightPerson.dateStart', 'pupilsightPerson.dateEnd', 'pupilsightPerson.status', "'Student' as roleCategory", 'pupilsightSchoolYear.name as academic_year', 'pupilsightProgram.name as program','pupilsightPerson.gender','pupilsightPerson.username','pupilsightPerson.address1','pupilsightPerson.address1District','pupilsightPerson.address1Country','pupilsightPerson.phone1','pupilsightPerson.languageFirst','pupilsightPerson.languageSecond','pupilsightPerson.languageThird','pupilsightPerson.religion','pupilsightPerson.admission_no', 'pupilsightPerson.active'
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        if ($criteria->hasFilter('all')) {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
                  ->where("pupilsightRole.category='Student'");
        } 
        else if(!empty($search)&&empty($pupilsightProgramID))
        {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
            ->where("pupilsightRole.category='Student'");
        }
        else {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
            ->where("pupilsightRole.category='Student'")
            ->where("pupilsightStudentEnrolment.pupilsightStudentEnrolmentID IS NOT NULL")
                  ->where("pupilsightPerson.status = 'Full'")
                  
                  ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
                  ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
                  ->bindValue('today', date('Y-m-d'));
        }
        $query->where('pupilsightPerson.pupilsightRoleIDPrimary = "003" ');
        if (!empty($pupilsightProgramID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
        }
        
        if (!empty($pupilsightYearGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
        }
        if (!empty($pupilsightRollGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
        }
        
        if ($searchFamilyDetails && $criteria->hasSearchText()) {
            self::$searchableColumns = array_merge(self::$searchableColumns, ['parent1.email', 'parent1.emailAlternate', 'parent2.email', 'parent2.emailAlternate']);
            
            $query
                ->leftJoin('pupilsightFamilyChild as child', "child.pupilsightPersonID=pupilsightPerson.pupilsightPersonID")
                ->leftJoin('pupilsightFamilyAdult as adult1', "(adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.contactPriority=1)")
                ->leftJoin('pupilsightPerson as parent1', "(parent1.pupilsightPersonID=adult1.pupilsightPersonID AND parent1.status='Full')")
                ->leftJoin('pupilsightFamilyAdult as adult2', "(adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.contactPriority=2)")
                ->leftJoin('pupilsightPerson as parent2', "(parent2.pupilsightPersonID=adult2.pupilsightPersonID AND parent2.status='Full')");
        }

        $query->where('pupilsightPerson.active = "0" ')
        ->orderBy(['pupilsightPerson.pupilsightPersonID DESC']);
        //echo $query;
        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getLeaveHistory(QueryCriteria $criteria, $pupilsightPersonID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightLeaveApply')
            ->cols([
                'pupilsightLeaveApply.*','pupilsightPerson.officialName as studentName', 'pupilsightLeaveReason.name as leaveReason'
            ])
            ->leftJoin('pupilsightPerson', 'pupilsightLeaveApply.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightLeaveReason', 'pupilsightLeaveApply.pupilsightLeaveReasonID=pupilsightLeaveReason.id')
            ->where('pupilsightLeaveApply.pupilsightPersonID IN ('.$pupilsightPersonID.') ')
            ->orderBy(['pupilsightLeaveApply.id DESC']);
            // echo $query;
            // die();
        return $this->runQuery($query, $criteria,TRUE );
    } 

    public function getLeaveHistoryByAdmin(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID, $search)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightLeaveApply')
            ->cols([
                'pupilsightLeaveApply.*','pupilsightPerson.officialName as studentName', 'pupilsightLeaveReason.name as leaveReason', 'pupilsightYearGroup.name AS class', 'pupilsightRollGroup.name AS section','pupilsightProgram.name as program'
            ])
            ->leftJoin('pupilsightPerson', 'pupilsightLeaveApply.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightLeaveReason', 'pupilsightLeaveApply.pupilsightLeaveReasonID=pupilsightLeaveReason.id')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID');
            if (!empty($pupilsightSchoolYearID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ');
            }
            if (!empty($pupilsightProgramID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
            }
            
            if (!empty($pupilsightYearGroupID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
            }
            if (!empty($pupilsightRollGroupID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
            }
            if (!empty($search)) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $search . '%" ');
            }
            $query->orderBy(['pupilsightLeaveApply.id DESC']);
            // echo $query;
            // die();
        return $this->runQuery($query, $criteria,TRUE );
    } 



}
