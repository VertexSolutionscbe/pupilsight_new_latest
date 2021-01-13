<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Staff;

use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;
use Pupilsight\Domain\Traits\TableAware;

/**
 * Staff Absence Gateway
 *
 * @version v18
 * @since   v18
 */
class StaffAbsenceGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightStaffAbsence';
    private static $primaryKey = 'pupilsightStaffAbsenceID';

    private static $searchableColumns = ['pupilsightStaffAbsence.reason', 'pupilsightStaffAbsence.comment', 'pupilsightStaffAbsence.status', 'pupilsightStaffAbsenceType.name', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname'];

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryAbsencesBySchoolYear($criteria, $pupilsightSchoolYearID, $grouped = true)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffAbsence.*', 'pupilsightStaffAbsenceDate.*', 'pupilsightStaffAbsenceType.name as type', 'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'creator.preferredName AS preferredNameCreator', 'creator.surname AS surnameCreator', 'MIN(pupilsightStaffCoverage.status) as coverage',
            ])
            ->innerJoin('pupilsightStaffAbsenceType', 'pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID')
            ->innerJoin('pupilsightStaffAbsenceDate', 'pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID')
            ->innerJoin('pupilsightSchoolYear', 'pupilsightStaffAbsenceDate.date BETWEEN firstDay AND lastDay')
            ->leftJoin('pupilsightStaffCoverageDate', 'pupilsightStaffCoverageDate.pupilsightStaffAbsenceDateID=pupilsightStaffAbsenceDate.pupilsightStaffAbsenceDateID')
            ->leftJoin('pupilsightStaffCoverage', 'pupilsightStaffCoverage.pupilsightStaffCoverageID=pupilsightStaffCoverageDate.pupilsightStaffCoverageID')
            ->leftJoin('pupilsightPerson', 'pupilsightStaffAbsence.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS creator', 'pupilsightStaffAbsence.pupilsightPersonIDCreator=creator.pupilsightPersonID')
            ->where('pupilsightSchoolYear.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        if ($grouped) {
            $query->cols(['COUNT(*) as days', 'MIN(pupilsightStaffAbsenceDate.date) as dateStart', 'MAX(pupilsightStaffAbsenceDate.date) as dateEnd', 'SUM(pupilsightStaffAbsenceDate.value) as value'])
                ->groupBy(['pupilsightStaffAbsence.pupilsightStaffAbsenceID']);
        } else {
            $query->cols(['1 as days', 'pupilsightStaffAbsenceDate.date as dateStart', 'pupilsightStaffAbsenceDate.date as dateEnd', 'pupilsightStaffAbsenceDate.value as value'])
                ->groupBy(['pupilsightStaffAbsenceDate.pupilsightStaffAbsenceDateID']);
        }

        $criteria->addFilterRules($this->getSharedFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function queryAbsencesByPerson(QueryCriteria $criteria, $pupilsightPersonID, $grouped = true)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffAbsence.pupilsightStaffAbsenceID', 'pupilsightStaffAbsence.pupilsightPersonID', 'pupilsightStaffAbsenceType.name as type', 'pupilsightStaffAbsence.reason', 'comment', 'pupilsightStaffAbsenceDate.date', 'pupilsightStaffAbsenceDate.allDay', 'pupilsightStaffAbsenceDate.timeStart', 'pupilsightStaffAbsenceDate.timeEnd', 'timestampCreator', 'pupilsightStaffCoverage.status as coverage', 'pupilsightStaffAbsence.status',
                'creator.title as titleCreator', 'creator.preferredName AS preferredNameCreator', 'creator.surname AS surnameCreator', 'pupilsightStaffAbsence.pupilsightPersonIDCreator',
                'coverage.title as titleCoverage', 'coverage.preferredName as preferredNameCoverage', 'coverage.surname as surnameCoverage', 'pupilsightStaffCoverage.pupilsightPersonIDCoverage',
            ])
            ->innerJoin('pupilsightStaffAbsenceType', 'pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID')
            ->innerJoin('pupilsightStaffAbsenceDate', 'pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID')
            ->leftJoin('pupilsightStaffCoverageDate', 'pupilsightStaffCoverageDate.pupilsightStaffAbsenceDateID=pupilsightStaffAbsenceDate.pupilsightStaffAbsenceDateID')
            ->leftJoin('pupilsightStaffCoverage', 'pupilsightStaffCoverage.pupilsightStaffCoverageID=pupilsightStaffCoverageDate.pupilsightStaffCoverageID')
            ->leftJoin('pupilsightPerson AS creator', 'pupilsightStaffAbsence.pupilsightPersonIDCreator=creator.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS coverage', 'pupilsightStaffCoverage.pupilsightPersonIDCoverage=coverage.pupilsightPersonID')
            ->where('pupilsightStaffAbsence.pupilsightPersonID = :pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID);

        if ($grouped) {
            $query->cols(['COUNT(*) as days', 'MIN(pupilsightStaffAbsenceDate.date) as dateStart', 'MAX(pupilsightStaffAbsenceDate.date) as dateEnd', 'SUM(pupilsightStaffAbsenceDate.value) as value'])
                ->groupBy(['pupilsightStaffAbsence.pupilsightStaffAbsenceID']);
        } else {
            $query->cols(['1 as days', 'pupilsightStaffAbsenceDate.date as dateStart', 'pupilsightStaffAbsenceDate.date as dateEnd', 'pupilsightStaffAbsenceDate.value as value'])
                ->groupBy(['pupilsightStaffAbsenceDate.pupilsightStaffAbsenceDateID']);
        }

        $criteria->addFilterRules($this->getSharedFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function queryAbsencesByApprover(QueryCriteria $criteria, $pupilsightPersonIDApproval)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffAbsence.pupilsightStaffAbsenceID', 'pupilsightStaffAbsenceType.name as type', 'pupilsightStaffAbsence.reason', 'comment', 'pupilsightStaffAbsenceDate.date', 'COUNT(*) as days', 'MIN(pupilsightStaffAbsenceDate.date) as dateStart', 'MAX(pupilsightStaffAbsenceDate.date) as dateEnd', 'pupilsightStaffAbsenceDate.allDay', 'pupilsightStaffAbsenceDate.timeStart', 'pupilsightStaffAbsenceDate.timeEnd', 'SUM(pupilsightStaffAbsenceDate.value) as value', 'timestampCreator', 'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'pupilsightStaffAbsence.pupilsightPersonIDCreator', 'creator.preferredName AS preferredNameCreator', 'creator.surname AS surnameCreator', 'pupilsightStaffCoverage.status as coverage', 'pupilsightStaffAbsence.status',
            ])
            ->innerJoin('pupilsightStaffAbsenceType', 'pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID')
            ->innerJoin('pupilsightStaffAbsenceDate', 'pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID')
            ->leftJoin('pupilsightStaffCoverageDate', 'pupilsightStaffCoverageDate.pupilsightStaffAbsenceDateID=pupilsightStaffAbsenceDate.pupilsightStaffAbsenceDateID')
            ->leftJoin('pupilsightStaffCoverage', 'pupilsightStaffCoverage.pupilsightStaffCoverageID=pupilsightStaffCoverageDate.pupilsightStaffCoverageID')
            ->leftJoin('pupilsightPerson', 'pupilsightStaffAbsence.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS creator', 'pupilsightStaffAbsence.pupilsightPersonIDCreator=creator.pupilsightPersonID')
            ->where('pupilsightStaffAbsence.pupilsightPersonIDApproval = :pupilsightPersonIDApproval')
            ->bindValue('pupilsightPersonIDApproval', $pupilsightPersonIDApproval)
            ->groupBy(['pupilsightStaffAbsence.pupilsightStaffAbsenceID']);

        $criteria->addFilterRules($this->getSharedFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function queryApprovedAbsencesByDateRange(QueryCriteria $criteria, $dateStart, $dateEnd = null, $grouped = true)
    {
        if (empty($dateEnd)) $dateEnd = $dateStart;

        $query = $this
            ->newQuery()
            ->from('pupilsightStaffAbsenceDate')
            ->cols([
                'pupilsightStaffAbsence.pupilsightStaffAbsenceID', 'pupilsightStaffAbsence.pupilsightPersonID', 'pupilsightStaffAbsenceType.name as type', 'pupilsightStaffAbsence.reason', 'comment', 'pupilsightStaffAbsenceDate.date',  'pupilsightStaffAbsenceDate.allDay', 'pupilsightStaffAbsenceDate.timeStart', 'pupilsightStaffAbsenceDate.timeEnd', 'pupilsightStaffAbsenceDate.value', 'timestampCreator',  'MIN(pupilsightStaffCoverage.status) as coverage',
                'pupilsightStaffAbsence.status',
                'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname',
                'creator.title AS titleCreator', 'creator.preferredName AS preferredNameCreator', 'creator.surname AS surnameCreator', 'pupilsightStaffAbsence.pupilsightPersonIDCreator',
                'coverage.title as titleCoverage', 'coverage.preferredName as preferredNameCoverage', 'coverage.surname as surnameCoverage', 'pupilsightStaffCoverage.pupilsightPersonIDCoverage',
            ])
            ->innerJoin('pupilsightStaffAbsence', 'pupilsightStaffAbsence.pupilsightStaffAbsenceID=pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID')
            ->innerJoin('pupilsightStaffAbsenceType', 'pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID')
            ->leftJoin('pupilsightStaffAbsenceDate AS dates', 'dates.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID')
            ->leftJoin('pupilsightStaffCoverageDate', 'pupilsightStaffCoverageDate.pupilsightStaffAbsenceDateID=pupilsightStaffAbsenceDate.pupilsightStaffAbsenceDateID')
            ->leftJoin('pupilsightStaffCoverage', 'pupilsightStaffCoverage.pupilsightStaffCoverageID=pupilsightStaffCoverageDate.pupilsightStaffCoverageID')
            ->leftJoin('pupilsightPerson', 'pupilsightStaffAbsence.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS creator', 'pupilsightStaffAbsence.pupilsightPersonIDCreator=creator.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS coverage', 'pupilsightStaffCoverage.pupilsightPersonIDCoverage=coverage.pupilsightPersonID')
            ->where('pupilsightStaffAbsenceDate.date BETWEEN :dateStart AND :dateEnd')
            ->where("pupilsightStaffAbsence.status = 'Approved'")
            ->bindValue('dateStart', $dateStart)
            ->bindValue('dateEnd', $dateEnd);

        if ($grouped) {
            $query->cols(['COUNT(DISTINCT dates.pupilsightStaffAbsenceDateID) as days', 'MIN(dates.date) as dateStart', 'MAX(dates.date) as dateEnd', 'SUM(value) as value'])
                ->groupBy(['pupilsightStaffAbsence.pupilsightStaffAbsenceID']);
        } else {
            $query->cols(['1 as days', 'pupilsightStaffAbsenceDate.date as dateStart', 'pupilsightStaffAbsenceDate.date as dateEnd', 'pupilsightStaffAbsenceDate.value as value'])
                ->groupBy(['pupilsightStaffAbsenceDate.pupilsightStaffAbsenceDateID']);
        }

        $criteria->addFilterRules($this->getSharedFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function getAbsenceDetailsByID($pupilsightStaffAbsenceID)
    {
        $data = ['pupilsightStaffAbsenceID' => $pupilsightStaffAbsenceID];
        $sql = "SELECT pupilsightStaffAbsence.pupilsightStaffAbsenceID, pupilsightStaffAbsence.pupilsightStaffAbsenceID, pupilsightStaffAbsenceType.name as type, pupilsightStaffAbsenceType.sequenceNumber, pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID, pupilsightStaffAbsence.reason, pupilsightStaffAbsence.comment, pupilsightStaffAbsence.commentConfidential, 
                MIN(pupilsightStaffAbsenceDate.date) as date, COUNT(DISTINCT pupilsightStaffAbsenceDateID) as days, MIN(pupilsightStaffAbsenceDate.date) as dateStart, MAX(pupilsightStaffAbsenceDate.date) as dateEnd, MAX(pupilsightStaffAbsenceDate.allDay) as allDay, MIN(pupilsightStaffAbsenceDate.timeStart) as timeStart, MAX(pupilsightStaffAbsenceDate.timeEnd) as timeEnd, 0 as urgent, SUM(pupilsightStaffAbsenceDate.value) as value,
                pupilsightStaffAbsence.status, pupilsightStaffAbsence.timestampApproval, pupilsightStaffAbsence.notesApproval,
                pupilsightPersonIDCreator, timestampCreator, timestampStatus, timestampCoverage, pupilsightStaffAbsence.notificationList, pupilsightStaffAbsence.notificationSent, pupilsightStaffAbsence.pupilsightGroupID, pupilsightStaffAbsence.googleCalendarEventID,
                pupilsightStaffCoverage.status as coverage, pupilsightStaffCoverage.notesCoverage, pupilsightStaffCoverage.notesStatus, 
                pupilsightStaffAbsence.pupilsightPersonID, absence.title AS titleAbsence, absence.preferredName AS preferredNameAbsence, absence.surname AS surnameAbsence, 
                pupilsightStaffAbsence.pupilsightPersonIDApproval, approval.title as titleApproval, approval.preferredName as preferredNameApproval, approval.surname as surnameApproval,
                pupilsightStaffCoverage.pupilsightPersonIDCoverage, coverage.title as titleCoverage, coverage.preferredName as preferredNameCoverage, coverage.surname as surnameCoverage
            FROM pupilsightStaffAbsence 
            JOIN pupilsightStaffAbsenceType ON (pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID)
            LEFT JOIN pupilsightStaffAbsenceDate ON (pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID)
            LEFT JOIN pupilsightStaffCoverage ON (pupilsightStaffCoverage.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID)
            LEFT JOIN pupilsightPerson AS absence ON (pupilsightStaffAbsence.pupilsightPersonID=absence.pupilsightPersonID)
            LEFT JOIN pupilsightPerson AS coverage ON (pupilsightStaffCoverage.pupilsightPersonIDCoverage=coverage.pupilsightPersonID)
            LEFT JOIN pupilsightPerson AS approval ON (pupilsightStaffAbsence.pupilsightPersonIDApproval=approval.pupilsightPersonID)
            WHERE pupilsightStaffAbsence.pupilsightStaffAbsenceID=:pupilsightStaffAbsenceID
            GROUP BY pupilsightStaffAbsence.pupilsightStaffAbsenceID
            ";

        return $this->db()->selectOne($sql, $data);
    }

    public function getMostRecentAbsenceByPerson($pupilsightPersonID)
    {
        $data = ['pupilsightPersonID' => $pupilsightPersonID];
        $sql = "SELECT * 
                FROM pupilsightStaffAbsence 
                WHERE pupilsightStaffAbsence.pupilsightPersonID=:pupilsightPersonID
                ORDER BY timestampCreator DESC
                LIMIT 1";

        return $this->db()->selectOne($sql, $data);
    }

    public function getMostRecentApproverByPerson($pupilsightPersonID)
    {
        $data = ['pupilsightPersonID' => $pupilsightPersonID];
        $sql = "SELECT pupilsightPersonIDApproval 
                FROM pupilsightStaffAbsence 
                WHERE pupilsightStaffAbsence.pupilsightPersonID=:pupilsightPersonID
                AND pupilsightPersonIDApproval IS NOT NULL
                ORDER BY timestampCreator DESC
                LIMIT 1";

        return $this->db()->selectOne($sql, $data);
    }

    protected function getSharedFilterRules()
    {
        return [
            'type' => function ($query, $type) {
                return $query
                    ->where('pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID = :type')
                    ->bindValue('type', $type);
            },
            'status' => function ($query, $status) {
                return $query->where('pupilsightStaffAbsence.status = :status')
                    ->bindValue('status', ucwords($status));
            },
            'coverage' => function ($query, $coverage) {
                return $query->where('pupilsightStaffCoverage.status = :coverage')
                    ->bindValue('coverage', $coverage);
            },
            'dateStart' => function ($query, $dateStart) {
                return $query->where("pupilsightStaffAbsenceDate.date >= :dateStart")
                    ->bindValue('dateStart', $dateStart);
            },
            'dateEnd' => function ($query, $dateEnd) {
                return $query->where("pupilsightStaffAbsenceDate.date <= :dateEnd")
                    ->bindValue('dateEnd', $dateEnd);
            },
            'date' => function ($query, $date) {
                switch (ucfirst($date)) {
                    case 'Upcoming':
                        return $query->where("pupilsightStaffAbsenceDate.date >= CURRENT_DATE()");
                    case 'Today':
                        return $query->where("pupilsightStaffAbsenceDate.date = CURRENT_DATE()");
                    case 'Past':
                        return $query->where("pupilsightStaffAbsenceDate.date < CURRENT_DATE()");
                }
            },
        ];
    }
}
