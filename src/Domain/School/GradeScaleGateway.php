<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\School;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v17
 * @since   v17
 */
class GradeScaleGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightScale';

    private static $searchableColumns = ['name'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryGradeScales(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightScaleID', 'name', 'nameShort', 'pupilsightScale.usage', 'pupilsightScale.active', 'pupilsightScale.numeric'
            ]);

        return $this->runQuery($query, $criteria);
    }

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryGradeScaleGrades(QueryCriteria $criteria, $pupilsightScaleID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightScaleGrade')
            ->cols([
                'pupilsightScaleGradeID', 'pupilsightScaleID', 'value', 'descriptor', 'sequenceNumber', 'isDefault'
            ])
            ->where('pupilsightScaleGrade.pupilsightScaleID = :pupilsightScaleID')
            ->bindValue('pupilsightScaleID', $pupilsightScaleID);

        return $this->runQuery($query, $criteria);
    }
}
