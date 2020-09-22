<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Staff;

use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;
use Pupilsight\Domain\Traits\TableAware;

/**
 * Staff Coverage Date Gateway
 *
 * @version v18
 * @since   v18
 */
class StaffCoverageDateGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightStaffCoverageDate';
    private static $primaryKey = 'pupilsightStaffCoverageDateID';

    private static $searchableColumns = [''];

    public function selectDatesByCoverage($pupilsightStaffCoverageID)
    {
        $pupilsightStaffCoverageIDList = is_array($pupilsightStaffCoverageID)? $pupilsightStaffCoverageID : [$pupilsightStaffCoverageID];
        $data = ['pupilsightStaffCoverageIDList' => implode(',', $pupilsightStaffCoverageIDList) ];
        $sql = "SELECT pupilsightStaffCoverageDate.pupilsightStaffCoverageID as groupBy, pupilsightStaffCoverageDate.*, pupilsightStaffCoverage.status as coverage, coverage.title as titleCoverage, coverage.preferredName as preferredNameCoverage, coverage.surname as surnameCoverage, coverage.pupilsightPersonID as pupilsightPersonIDCoverage
                FROM pupilsightStaffCoverageDate
                LEFT JOIN pupilsightStaffCoverage ON (pupilsightStaffCoverage.pupilsightStaffCoverageID=pupilsightStaffCoverageDate.pupilsightStaffCoverageID)
                LEFT JOIN pupilsightPerson AS coverage ON (pupilsightStaffCoverage.pupilsightPersonIDCoverage=coverage.pupilsightPersonID)
                WHERE FIND_IN_SET(pupilsightStaffCoverageDate.pupilsightStaffCoverageID, :pupilsightStaffCoverageIDList)
                ORDER BY pupilsightStaffCoverageDate.date";

        return $this->db()->select($sql, $data);
    }
}
