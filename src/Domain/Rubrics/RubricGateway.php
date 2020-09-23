<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Rubrics;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v17
 * @since   v17
 */
class RubricGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightRubric';
    private static $searchableColumns = ['pupilsightRubric.name', 'pupilsightRubric.category'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryRubrics(QueryCriteria $criteria, $active = null, $pupilsightYearGroupID = null)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightRubricID', 'pupilsightRubric.scope', 'pupilsightRubric.category', 'pupilsightRubric.name', 'pupilsightRubric.description', 'pupilsightRubric.active', 'pupilsightRubric.pupilsightDepartmentID', 'pupilsightDepartment.name AS learningArea', 
                "GROUP_CONCAT(DISTINCT pupilsightYearGroup.nameShort ORDER BY pupilsightYearGroup.sequenceNumber SEPARATOR ', ') as yearGroups",
                "COUNT(DISTINCT pupilsightYearGroup.pupilsightYearGroupID) as yearGroupCount",
            ])
            ->leftJoin('pupilsightDepartment', "pupilsightRubric.scope = 'Learning Area' AND pupilsightDepartment.pupilsightDepartmentID=pupilsightRubric.pupilsightDepartmentID")
            ->leftJoin('pupilsightYearGroup', 'FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightRubric.pupilsightYearGroupIDList)')
            ->groupBy(['pupilsightRubric.pupilsightRubricID']);
            
        if (!empty($active)) {
            $query->where('pupilsightRubric.active = :active')
                ->bindValue('active', $active);
        }

        if (!empty($pupilsightYearGroupID)) {
            $query->where('FIND_IN_SET(:pupilsightYearGroupID, pupilsightRubric.pupilsightYearGroupIDList)')
                ->bindValue('pupilsightYearGroupID', $pupilsightYearGroupID);
        }

        $criteria->addFilterRules([
            'department' => function ($query, $pupilsightDepartmentID) {
                return $query
                    ->where('pupilsightRubric.pupilsightDepartmentID = :pupilsightDepartmentID')
                    ->bindValue('pupilsightDepartmentID', $pupilsightDepartmentID);
            },
        ]);
        
        return $this->runQuery($query, $criteria);
    }
}
