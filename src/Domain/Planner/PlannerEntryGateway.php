<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Planner;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Planner Entry Gateway
 *
 * @version v17
 * @since   v17
 */
class PlannerEntryGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightPlannerEntry';
    private static $searchableColumns = [];
    
    public function getPlannerEntryByID($pupilsightPlannerEntryID)
    {
        $data = ['pupilsightPlannerEntryID' => $pupilsightPlannerEntryID];
        $sql = "SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID";

        return $this->db()->selectOne($sql, $data);
    }
}
