<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\User;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class DistrictGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightDistrict';

    private static $searchableColumns = ['name'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryDistricts(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightDistrictID', 'name'
            ]);

        return $this->runQuery($query, $criteria);
    }
}
