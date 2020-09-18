<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Timetable;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v17
 * @since   v17
 */
class CourseSyncGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightCourseClassMap';
    private static $searchableColumns = [];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryCourseClassMaps(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightCourseClassMap.pupilsightCourseClassID',
                'pupilsightCourseClassMap.pupilsightRollGroupID',
                'pupilsightCourseClassMap.pupilsightYearGroupID',
                'pupilsightYearGroup.pupilsightYearGroupID',
                'pupilsightRollGroup.name as rollGroupName',
                'pupilsightYearGroup.name as yearGroupName',
                'COUNT(DISTINCT pupilsightCourseClassMap.pupilsightCourseClassID) as classCount',
                "GROUP_CONCAT(DISTINCT pupilsightRollGroup.nameShort ORDER BY pupilsightRollGroup.nameShort SEPARATOR ', ') as rollGroupList",
                "GROUP_CONCAT(DISTINCT pupilsightRollGroup.pupilsightRollGroupID ORDER BY pupilsightRollGroup.pupilsightRollGroupID SEPARATOR ',') as pupilsightRollGroupIDList",
            ])
            ->innerJoin('pupilsightRollGroup', 'pupilsightCourseClassMap.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->innerJoin('pupilsightYearGroup', 'pupilsightYearGroup.pupilsightYearGroupID=pupilsightCourseClassMap.pupilsightYearGroupID')
            ->innerJoin('pupilsightCourseClass', 'pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassMap.pupilsightCourseClassID')
            ->innerJoin('pupilsightCourse', 'pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID')
            ->where('FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightCourse.pupilsightYearGroupIDList)')
            ->where('pupilsightCourse.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->groupBy(['pupilsightYearGroup.pupilsightYearGroupID']);

        return $this->runQuery($query, $criteria);
    }
}
