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
class ExternalAssessmentGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightExternalAssessment';

    private static $searchableColumns = ['name'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryExternalAssessments(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightExternalAssessmentID', 'name', 'description', 'active', 'allowFileUpload'
            ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryExternalAssessmentFields(QueryCriteria $criteria, $pupilsightExternalAssessmentID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightExternalAssessmentField')
            ->cols([
                'pupilsightExternalAssessmentFieldID', 'pupilsightExternalAssessmentID', 'name', 'category', 'pupilsightExternalAssessmentField.order'
            ])
            ->where('pupilsightExternalAssessmentField.pupilsightExternalAssessmentID = :pupilsightExternalAssessmentID')
            ->bindValue('pupilsightExternalAssessmentID', $pupilsightExternalAssessmentID);

        return $this->runQuery($query, $criteria);
    }
}
