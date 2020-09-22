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
class CourseGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightCourse';

    private static $searchableColumns = ['pupilsightCourse.name', 'pupilsightCourse.nameShort'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryCoursesBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightCourse.pupilsightCourseID', 'pupilsightCourse.name', 'pupilsightCourse.nameShort', 'pupilsightDepartment.name as department', 'COUNT(DISTINCT pupilsightCourseClassID) as classCount'
            ])
            ->leftJoin('pupilsightDepartment', 'pupilsightDepartment.pupilsightDepartmentID=pupilsightCourse.pupilsightDepartmentID')
            ->leftJoin('pupilsightCourseClass', 'pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID')
            ->where('pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->groupBy(['pupilsightCourse.pupilsightCourseID']);

        $criteria->addFilterRules([
            'yearGroup' => function ($query, $pupilsightYearGroupID) {
                return $query
                    ->where('FIND_IN_SET(:pupilsightYearGroupID, pupilsightCourse.pupilsightYearGroupIDList)')
                    ->bindValue('pupilsightYearGroupID', $pupilsightYearGroupID);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function selectClassesBySchoolYear($pupilsightSchoolYearID)
    {
        $data= array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.name as courseName, pupilsightCourse.nameShort as course, pupilsightCourseClass.nameShort as class 
                FROM pupilsightCourse 
                JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) 
                WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID 
                ORDER BY pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";

        return $this->db()->select($sql, $data);
    }

    public function selectClassesByCourseID($pupilsightCourseID)
    {
        $data = array('pupilsightCourseID' => $pupilsightCourseID, 'today' => date('Y-m-d'));
        $sql = "SELECT pupilsightCourseClass.*, COUNT(CASE WHEN pupilsightPerson.status='Full' AND (pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<:today) THEN pupilsightPerson.status END) as participantsActive, COUNT(CASE WHEN pupilsightPerson.status='Expected' OR pupilsightPerson.dateStart>=:today THEN pupilsightPerson.status END) as participantsExpected, COUNT(DISTINCT pupilsightPerson.pupilsightPersonID) as participantsTotal 
            FROM pupilsightCourseClass 
            LEFT JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND NOT pupilsightCourseClassPerson.role LIKE '% - Left') 
            LEFT JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND (pupilsightPerson.status='Full' OR pupilsightPerson.status='Expected')) 
            WHERE pupilsightCourseClass.pupilsightCourseID=:pupilsightCourseID
            GROUP BY pupilsightCourseClass.pupilsightCourseClassID
            ORDER BY pupilsightCourseClass.nameShort";

        return $this->db()->select($sql, $data);
    }

    public function getCourseClassByID($pupilsightCourseClassID)
    {
        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
        $sql = "SELECT pupilsightCourseClassID, pupilsightCourseClass.name, pupilsightCourseClass.nameShort, pupilsightCourse.pupilsightCourseID, pupilsightCourse.name AS courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourse.description AS courseDescription, pupilsightCourse.pupilsightSchoolYearID, pupilsightSchoolYear.name as yearName, pupilsightYearGroupIDList 
                FROM pupilsightCourseClass
                JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                WHERE pupilsightCourseClassID=:pupilsightCourseClassID";

        return $this->db()->selectOne($sql, $data);
    }
}
