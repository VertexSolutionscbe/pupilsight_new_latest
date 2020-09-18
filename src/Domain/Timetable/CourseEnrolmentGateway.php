<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Timetable;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class CourseEnrolmentGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightCourseClassPerson';

    private static $searchableColumns = ['pupilsightCourse.name', 'pupilsightCourse.nameShort'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryCourseEnrolmentByClass(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightCourseClassID, $left = false, $includeExpected = false)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightCourseClass.pupilsightCourseClassID', 'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.title', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.status', 'pupilsightPerson.dateStart', 'pupilsightPerson.dateEnd', 'pupilsightPerson.email', 'pupilsightPerson.privacy', 'pupilsightPerson.image_240', 'pupilsightPerson.dob', 'pupilsightCourseClassPerson.reportable', 'pupilsightCourseClassPerson.role', "(CASE WHEN pupilsightCourseClassPerson.role NOT LIKE 'Student%' THEN 0 ELSE 1 END) as roleSortOrder", "'Student' as roleCategory", 'pupilsightCourse.pupilsightYearGroupIDList as yearGroup'
            ])
            ->innerJoin('pupilsightCourseClass', 'pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID')
            ->innerJoin('pupilsightCourse', 'pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID')
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID')
            ->where('pupilsightCourse.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where('pupilsightCourseClassPerson.pupilsightCourseClassID = :pupilsightCourseClassID')
            ->bindValue('pupilsightCourseClassID', $pupilsightCourseClassID);

        if ($left) {
            $query->where("pupilsightCourseClassPerson.role LIKE '%Left'");
        } else {
            $query->where("pupilsightCourseClassPerson.role NOT LIKE '%Left'");
        }

        if ($includeExpected) {
            $query->where("(pupilsightPerson.status = 'Full' OR pupilsightPerson.status = 'Expected')")
                  ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
                  ->bindValue('today', date('Y-m-d'));
        } else {
            $query->where("pupilsightPerson.status = 'Full'")
                  ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
                  ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
                  ->bindValue('today', date('Y-m-d'));
        }

        $criteria->addFilterRules([
            'nonStudents' => function ($query, $role) {
                return $query->where("pupilsightCourseClassPerson.role NOT LIKE 'Student%'");
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryCourseEnrolmentByPerson(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightPersonID, $left = false)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightCourseClass.pupilsightCourseClassID', 'pupilsightCourse.name AS courseName', 'pupilsightCourse.nameShort AS course', 'pupilsightCourseClass.nameShort AS class', 'pupilsightCourseClassPerson.reportable', 'pupilsightCourseClassPerson.role', "(CASE WHEN pupilsightCourseClassPerson.role NOT LIKE 'Student%' THEN 0 ELSE 1 END) as roleSortOrder"
            ])
            ->innerJoin('pupilsightCourseClass', 'pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID')
            ->innerJoin('pupilsightCourse', 'pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID')
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID')
            ->where('pupilsightCourse.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where('pupilsightCourseClassPerson.pupilsightPersonID = :pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID);

        if ($left) {
            $query->where("pupilsightCourseClassPerson.role LIKE '%Left'");
        } else {
            $query->where("pupilsightCourseClassPerson.role NOT LIKE '%Left'");
        }

        return $this->runQuery($query, $criteria);
    }

    public function selectEnrolableClassesByYearGroup($pupilsightSchoolYearID, $pupilsightYearGroupID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
        $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.name as courseName, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, 
                    teacher.surname, teacher.preferredName,
                    (SELECT count(*) FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
                    WHERE pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND (status='Full' OR status='Expected') AND role='Student') 
                    AS studentCount
                FROM pupilsightCourse
                JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) 
                LEFT JOIN 
                    (SELECT pupilsightCourseClassID, title, surname, preferredName FROM pupilsightCourseClassPerson 
                    JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
                    WHERE pupilsightPerson.status='Full' AND pupilsightCourseClassPerson.role = 'Teacher') 
                    AS teacher ON (teacher.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID 
                AND FIND_IN_SET(:pupilsightYearGroupID, pupilsightCourse.pupilsightYearGroupIDList) 
                GROUP BY pupilsightCourseClass.pupilsightCourseClassID
                ORDER BY course, class";

        return $this->db()->select($sql, $data);
    }

    public function selectEnrolableStudentsByYearGroup($pupilsightSchoolYearID, $pupilsightYearGroupID)
    {
        $pupilsightYearGroupIDList = is_array($pupilsightYearGroupID)? implode(',', $pupilsightYearGroupID) : $pupilsightYearGroupID;
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList);
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, username, pupilsightRollGroup.name AS rollGroupName
                FROM pupilsightPerson
                JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID 
                AND pupilsightPerson.status='Full' 
                AND FIND_IN_SET(pupilsightStudentEnrolment.pupilsightYearGroupID, :pupilsightYearGroupIDList)
                ORDER BY rollGroupName, surname, preferredName";

        return $this->db()->select($sql, $data);
    }

    public function selectCourseEnrolmentByRollGroup($pupilsightRollGroupID)
    {
        $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID);
        $sql = "SELECT DISTINCT pupilsightPerson.pupilsightPersonID, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRollGroup.name as rollGroup, 
                    (SELECT COUNT(*) FROM pupilsightCourseClassPerson 
                    JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                    JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) 
                    WHERE pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID 
                    AND pupilsightCourse.pupilsightSchoolYearID=pupilsightRollGroup.pupilsightSchoolYearID 
                    AND pupilsightCourseClassPerson.role = 'Student') AS classCount
                FROM pupilsightPerson 
                JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) 
                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) 
                WHERE pupilsightRollGroup.pupilsightRollGroupID=:pupilsightRollGroupID 
                AND pupilsightPerson.status='Full' 
                ORDER BY pupilsightPerson.surname, pupilsightPerson.preferredName";

        return $this->db()->select($sql, $data);
    }
}
