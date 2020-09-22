<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Staff;

use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;
use Pupilsight\Domain\Traits\TableAware;

/**
 * Staff Absence Type Gateway
 *
 * @version v18
 * @since   v18
 */
class StaffAbsenceTypeGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightStaffAbsenceType';
    private static $primaryKey = 'pupilsightStaffAbsenceTypeID';

    private static $searchableColumns = ['name', 'nameShort'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryAbsenceTypes(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffAbsenceTypeID', 'name', 'nameShort', 'reasons', 'active', 'requiresApproval', 
            ]);

        return $this->runQuery($query, $criteria);
    }

    public function selectAllTypes()
    {
        $sql = "SELECT * FROM pupilsightStaffAbsenceType ORDER BY sequenceNumber, nameShort";

        return $this->db()->select($sql);
    }

    public function selectTypesRequiringApproval()
    {
        $sql = "SELECT * FROM pupilsightStaffAbsenceType WHERE requiresApproval = 'Y' ORDER BY sequenceNumber, nameShort";

        return $this->db()->select($sql);
    }
}
