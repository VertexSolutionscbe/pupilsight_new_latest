<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\System;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * String Repalcement Gateway
 *
 * @version v16
 * @since   v16
 */
class StringGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightString';

    private static $searchableColumns = ['original', 'replacement'];
    
    /**
     * Queries the list of strings for the Manage String Replacements page.
     *
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryStrings(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStringID', 'original', 'replacement', 'mode', 'caseSensitive', 'priority'
            ]);

        return $this->runQuery($query, $criteria);
    }
}
