<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\School;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class HouseGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightHouse';

    private static $searchableColumns = ['name', 'nameShort'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryHouses(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightHouseID', 'name', 'nameShort', 'logo'
            ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryStudentHouseCountByYearGroup(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightYearGroup.pupilsightYearGroupID',
                'pupilsightYearGroup.name as yearGroupName',
                'pupilsightHouse.name AS house',
                'pupilsightHouse.pupilsightHouseID',
                "count(pupilsightStudentEnrolment.pupilsightPersonID) AS total",
                "count(CASE WHEN pupilsightPerson.gender='M' THEN pupilsightStudentEnrolment.pupilsightPersonID END) as totalMale",
                "count(CASE WHEN pupilsightPerson.gender='F' THEN pupilsightStudentEnrolment.pupilsightPersonID END) as totalFemale",
            ])
            ->leftJoin('pupilsightPerson', "pupilsightPerson.pupilsightHouseID=pupilsightHouse.pupilsightHouseID
                        AND pupilsightPerson.status='Full'
                        AND (pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<=:today)
                        AND (pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd>=:today)")
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID
                        AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightYearGroup.pupilsightYearGroupID=pupilsightStudentEnrolment.pupilsightYearGroupID')
            ->groupBy(['pupilsightYearGroup.pupilsightYearGroupID', 'pupilsightHouse.pupilsightHouseID'])
            ->having('total > 0')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->bindValue('today', date('Y-m-d'));

        return $this->runQuery($query, $criteria);
    }
}
