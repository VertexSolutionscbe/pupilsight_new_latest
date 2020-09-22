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
class FacilityGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightSpace';

    private static $searchableColumns = ['name', 'type'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryFacilities(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightSpaceID', 'name', 'type', 'capacity', 'computer', 'computerStudent', 'projector', 'tv', 'dvd', 'hifi', 'speakers', 'iwb', 'phoneInternal', 'phoneExternal'
            ]);

        return $this->runQuery($query, $criteria);
    }
}