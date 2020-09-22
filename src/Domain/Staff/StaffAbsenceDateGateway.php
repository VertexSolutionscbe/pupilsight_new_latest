<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Staff;

use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;
use Pupilsight\Domain\Traits\TableAware;

/**
 * Staff Absence Date Gateway
 *
 * @version v18
 * @since   v18
 */
class StaffAbsenceDateGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightStaffAbsenceDate';
    private static $primaryKey = 'pupilsightStaffAbsenceDateID';

    private static $searchableColumns = [];

    public function selectDatesByAbsence($pupilsightStaffAbsenceID)
    {
        $pupilsightStaffAbsenceIDList = is_array($pupilsightStaffAbsenceID)? $pupilsightStaffAbsenceID : [$pupilsightStaffAbsenceID];
        $data = ['pupilsightStaffAbsenceIDList' => implode(',', $pupilsightStaffAbsenceIDList) ];
        $sql = "SELECT pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID as groupBy, pupilsightStaffAbsenceDate.*, pupilsightStaffCoverage.status as coverage, coverage.title as titleCoverage, coverage.preferredName as preferredNameCoverage, coverage.surname as surnameCoverage, coverage.pupilsightPersonID as pupilsightPersonIDCoverage, pupilsightStaffCoverage.pupilsightStaffCoverageID
                FROM pupilsightStaffAbsenceDate
                LEFT JOIN pupilsightStaffCoverageDate ON (pupilsightStaffCoverageDate.pupilsightStaffAbsenceDateID=pupilsightStaffAbsenceDate.pupilsightStaffAbsenceDateID)
                LEFT JOIN pupilsightStaffCoverage ON (pupilsightStaffCoverage.pupilsightStaffCoverageID=pupilsightStaffCoverageDate.pupilsightStaffCoverageID)
                LEFT JOIN pupilsightPerson AS coverage ON (pupilsightStaffCoverage.pupilsightPersonIDCoverage=coverage.pupilsightPersonID)
                WHERE FIND_IN_SET(pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID, :pupilsightStaffAbsenceIDList)
                ORDER BY pupilsightStaffAbsenceDate.date";

        return $this->db()->select($sql, $data);
    }

    public function selectApprovedAbsenceDatesByPerson($pupilsightPersonID)
    {
        $data = ['pupilsightPersonID' => $pupilsightPersonID];
        $sql = "SELECT pupilsightStaffAbsenceDate.date as groupBy, pupilsightStaffAbsence.*, pupilsightStaffAbsenceDate.*, pupilsightStaffAbsenceType.name as type, pupilsightStaffAbsenceType.sequenceNumber
                FROM pupilsightStaffAbsence 
                JOIN pupilsightStaffAbsenceDate ON (pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID) 
                JOIN pupilsightStaffAbsenceType ON (pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID)
                WHERE pupilsightStaffAbsence.pupilsightPersonID=:pupilsightPersonID
                AND pupilsightStaffAbsence.status='Approved'
                ORDER BY pupilsightStaffAbsenceDate.date";

        return $this->db()->select($sql, $data);
    }

    public function getByAbsenceAndDate($pupilsightStaffAbsenceID, $date)
    {
        $data = ['pupilsightStaffAbsenceID' => $pupilsightStaffAbsenceID, 'date' => $date ];
        $sql = "SELECT pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID as groupBy, pupilsightStaffAbsenceDate.*, pupilsightStaffCoverage.status as coverage, coverage.title as titleCoverage, coverage.preferredName as preferredNameCoverage, coverage.surname as surnameCoverage, coverage.pupilsightPersonID as pupilsightPersonIDCoverage, pupilsightStaffCoverage.pupilsightStaffCoverageID
                FROM pupilsightStaffAbsenceDate
                LEFT JOIN pupilsightStaffCoverageDate ON (pupilsightStaffCoverageDate.pupilsightStaffAbsenceDateID=pupilsightStaffAbsenceDate.pupilsightStaffAbsenceDateID)
                LEFT JOIN pupilsightStaffCoverage ON (pupilsightStaffCoverage.pupilsightStaffCoverageID=pupilsightStaffCoverageDate.pupilsightStaffCoverageID)
                LEFT JOIN pupilsightPerson AS coverage ON (pupilsightStaffCoverage.pupilsightPersonIDCoverage=coverage.pupilsightPersonID)
                WHERE pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID=:pupilsightStaffAbsenceID
                AND pupilsightStaffAbsenceDate.date=:date";

        return $this->db()->selectOne($sql, $data);
    }
}
