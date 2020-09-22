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
class FacilityChangeGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightTTSpaceChange';

    private static $searchableColumns = ['spaceOld.name', 'spaceNew.name'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryFacilityChanges(QueryCriteria $criteria, $pupilsightPersonID = null)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightTTSpaceChangeID', 'date', 'pupilsightCourseClass.pupilsightCourseClassID', 'pupilsightCourse.nameShort as courseName', 'pupilsightCourseClass.nameShort as className', 'spaceOld.name as spaceOld', 'spaceNew.name as spaceNew', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname'
            ])
            ->innerJoin('pupilsightTTDayRowClass', 'pupilsightTTSpaceChange.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID')
            ->innerJoin('pupilsightCourseClass', 'pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID')
            ->innerJoin('pupilsightCourse', 'pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID')
            ->leftJoin('pupilsightSpace AS spaceOld', 'pupilsightTTDayRowClass.pupilsightSpaceID=spaceOld.pupilsightSpaceID')
            ->leftJoin('pupilsightSpace AS spaceNew', 'pupilsightTTSpaceChange.pupilsightSpaceID=spaceNew.pupilsightSpaceID')
            ->leftJoin('pupilsightPerson', 'pupilsightTTSpaceChange.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where('date >= :today')
            ->bindValue('today', date('Y-m-d'));

        if (!empty($pupilsightPersonID)) {
            $query->leftJoin('pupilsightCourseClassPerson', 
                             'pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID')
                  ->where('pupilsightCourseClassPerson.pupilsightPersonID = :pupilsightPersonID')
                  ->bindValue('pupilsightPersonID', $pupilsightPersonID);
        }

        return $this->runQuery($query, $criteria);
    }

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryFacilityChangesByDepartment(QueryCriteria $criteria, $pupilsightPersonID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightTTSpaceChangeID', 'date', 'pupilsightCourseClass.pupilsightCourseClassID', 'pupilsightCourse.nameShort as courseName', 'pupilsightCourseClass.nameShort as className', 'spaceOld.name as spaceOld', 'spaceNew.name as spaceNew', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname'
            ])
            ->innerJoin('pupilsightTTDayRowClass', 'pupilsightTTSpaceChange.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID')
            ->innerJoin('pupilsightCourseClass', 'pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID')
            ->innerJoin('pupilsightCourse', 'pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID')
            ->innerJoin('pupilsightCourseClassPerson', 'pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID')
            ->leftJoin('pupilsightSpace AS spaceOld', 'pupilsightTTDayRowClass.pupilsightSpaceID=spaceOld.pupilsightSpaceID')
            ->leftJoin('pupilsightSpace AS spaceNew', 'pupilsightTTSpaceChange.pupilsightSpaceID=spaceNew.pupilsightSpaceID')
            ->leftJoin('pupilsightPerson', 'pupilsightTTSpaceChange.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where('pupilsightCourseClassPerson.pupilsightPersonID = :pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->where('date >= :today')
            ->bindValue('today', date('Y-m-d'));

        $query->union()
            ->from($this->getTableName())
            ->cols([
                'pupilsightTTSpaceChangeID', 'date', 'pupilsightCourseClass.pupilsightCourseClassID', 'pupilsightCourse.nameShort as courseName', 'pupilsightCourseClass.nameShort as className', 'spaceOld.name as spaceOld', 'spaceNew.name as spaceNew', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname'
            ])
            ->innerJoin('pupilsightTTDayRowClass', 'pupilsightTTSpaceChange.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID')
            ->innerJoin('pupilsightCourseClass', 'pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID')
            ->innerJoin('pupilsightCourse', 'pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID')
            ->innerJoin('pupilsightDepartment', 'pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
            ->innerJoin('pupilsightDepartmentStaff', 'pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
            ->leftJoin('pupilsightSpace AS spaceOld', 'pupilsightTTDayRowClass.pupilsightSpaceID=spaceOld.pupilsightSpaceID')
            ->leftJoin('pupilsightSpace AS spaceNew', 'pupilsightTTSpaceChange.pupilsightSpaceID=spaceNew.pupilsightSpaceID')
            ->leftJoin('pupilsightPerson', 'pupilsightTTSpaceChange.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where("pupilsightDepartmentStaff.role = 'Coordinator'")
            ->where("pupilsightDepartmentStaff.pupilsightPersonID = :pupilsightPersonID")
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->where('date >= :today')
            ->bindValue('today', date('Y-m-d'));

        return $this->runQuery($query, $criteria);
    }
}
