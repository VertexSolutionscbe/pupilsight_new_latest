<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\School;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * YearGroup Gateway
 *
 * @version v16
 * @since   v16
 */
class YearGroupGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightYearGroup';
    private static $searchableColumns = [];

    public function queryYearGroups(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightYearGroupID', 'pupilsightYearGroup.name', 'nameShort', 'sequenceNumber', 'pupilsightPersonIDHOY', 'preferredName', 'surname'
            ])
            ->leftJoin('pupilsightPerson', 'pupilsightYearGroup.pupilsightPersonIDHOY=pupilsightPerson.pupilsightPersonID')
            ->where('pupilsightYearGroup.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
        return $this->runQuery($query, $criteria);
    }
}
