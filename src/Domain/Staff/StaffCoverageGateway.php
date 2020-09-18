<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Staff;

use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;
use Pupilsight\Domain\Traits\TableAware;

/**
 * Staff Coverage Gateway
 *
 * @version v18
 * @since   v18
 */
class StaffCoverageGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightStaffCoverage';
    private static $primaryKey = 'pupilsightStaffCoverageID';

    private static $searchableColumns = ['absence.preferredName', 'absence.surname', 'coverage.preferredName', 'coverage.surname', 'status.preferredName', 'status.surname', 'pupilsightStaffCoverage.status'];

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryCoverageBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffCoverage.pupilsightStaffCoverageID', 'pupilsightStaffCoverage.status',  'pupilsightStaffAbsenceType.name as type', 'pupilsightStaffAbsence.reason', 'date', 'COUNT(*) as days', 'MIN(date) as dateStart', 'MAX(date) as dateEnd', 'allDay', 'timeStart', 'timeEnd', 'timestampStatus', 'timestampCoverage', 'pupilsightStaffCoverage.pupilsightStaffAbsenceID',
                'pupilsightStaffCoverage.pupilsightPersonID', 'absence.title AS titleAbsence', 'absence.preferredName AS preferredNameAbsence', 'absence.surname AS surnameAbsence', 
                'pupilsightStaffCoverage.pupilsightPersonIDCoverage', 'coverage.title as titleCoverage', 'coverage.preferredName as preferredNameCoverage', 'coverage.surname as surnameCoverage',
                'pupilsightStaffCoverage.pupilsightPersonIDStatus', 'status.title as titleStatus', 'status.preferredName as preferredNameStatus', 'status.surname as surnameStatus',
                'pupilsightStaffCoverage.notesStatus', 'absenceStaff.jobTitle as jobTitleAbsence'
            ])
            ->innerJoin('pupilsightStaffCoverageDate', 'pupilsightStaffCoverageDate.pupilsightStaffCoverageID=pupilsightStaffCoverage.pupilsightStaffCoverageID')
            ->innerJoin('pupilsightSchoolYear', 'pupilsightStaffCoverageDate.date BETWEEN firstDay AND lastDay')
            ->leftJoin('pupilsightStaffAbsence', 'pupilsightStaffCoverage.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID')
            ->leftJoin('pupilsightStaffAbsenceType', 'pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID')
            ->leftJoin('pupilsightPerson AS coverage', 'pupilsightStaffCoverage.pupilsightPersonIDCoverage=coverage.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS status', 'pupilsightStaffCoverage.pupilsightPersonIDStatus=status.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS absence', 'pupilsightStaffCoverage.pupilsightPersonID=absence.pupilsightPersonID')
            ->leftJoin('pupilsightStaff AS absenceStaff', 'absence.pupilsightPersonID=absenceStaff.pupilsightPersonID')
            ->where('pupilsightSchoolYear.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->groupBy(['pupilsightStaffCoverage.pupilsightStaffCoverageID']);

        $criteria->addFilterRules($this->getSharedFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function queryCoverageByPersonCovering(QueryCriteria $criteria, $pupilsightPersonID, $grouped = true)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffCoverage.pupilsightStaffCoverageID', 'pupilsightStaffCoverage.status',  'pupilsightStaffAbsenceType.name as type', 'pupilsightStaffAbsence.reason', 'date', 'COUNT(*) as days', 'MIN(date) as dateStart', 'MAX(date) as dateEnd', 'allDay', 'timeStart', 'timeEnd', 'timestampStatus', 'timestampCoverage', 'pupilsightStaffCoverage.pupilsightPersonIDCoverage', 
                'pupilsightStaffCoverage.pupilsightPersonID', 'absence.title AS titleAbsence', 'absence.preferredName AS preferredNameAbsence', 'absence.surname AS surnameAbsence',
                'pupilsightStaffCoverage.pupilsightPersonIDStatus', 'status.title as titleStatus', 'status.preferredName as preferredNameStatus', 'status.surname as surnameStatus',
                'pupilsightStaffCoverage.notesStatus', 'absenceStaff.jobTitle as jobTitleAbsence'
            ])
            ->leftJoin('pupilsightStaffCoverageDate', 'pupilsightStaffCoverageDate.pupilsightStaffCoverageID=pupilsightStaffCoverage.pupilsightStaffCoverageID')
            ->leftJoin('pupilsightStaffAbsence', 'pupilsightStaffCoverage.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID')
            ->leftJoin('pupilsightStaffAbsenceType', 'pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID')
            ->leftJoin('pupilsightPerson AS status', 'pupilsightStaffCoverage.pupilsightPersonIDStatus=status.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS absence', 'pupilsightStaffCoverage.pupilsightPersonID=absence.pupilsightPersonID')
            ->leftJoin('pupilsightStaff AS absenceStaff', 'absence.pupilsightPersonID=absenceStaff.pupilsightPersonID')
            ->where('pupilsightStaffCoverage.pupilsightPersonIDCoverage = :pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->groupBy($grouped ? ['pupilsightStaffCoverage.pupilsightStaffCoverageID'] : ['pupilsightStaffCoverageDate.pupilsightStaffCoverageDateID'])
            ->orderBy(["pupilsightStaffCoverage.status = 'Requested' DESC"]);

        $criteria->addFilterRules($this->getSharedFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function queryCoverageByPersonAbsent(QueryCriteria $criteria, $pupilsightPersonID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffCoverage.pupilsightStaffCoverageID', 'pupilsightStaffCoverage.status',  'pupilsightStaffAbsenceType.name as type', 'pupilsightStaffAbsence.reason', 'date', 'COUNT(*) as days', 'MIN(date) as dateStart', 'MAX(date) as dateEnd', 'allDay', 'timeStart', 'timeEnd', 'timestampStatus', 'timestampCoverage', 'pupilsightStaffCoverage.pupilsightPersonIDCoverage', 'pupilsightStaffCoverage.pupilsightPersonID', 
                'coverage.title as titleCoverage', 'coverage.preferredName as preferredNameCoverage', 'coverage.surname as surnameCoverage', 'pupilsightStaffCoverage.notesCoverage'
            ])
            ->leftJoin('pupilsightStaffCoverageDate', 'pupilsightStaffCoverageDate.pupilsightStaffCoverageID=pupilsightStaffCoverage.pupilsightStaffCoverageID')
            ->leftJoin('pupilsightStaffAbsence', 'pupilsightStaffCoverage.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID')
            ->leftJoin('pupilsightStaffAbsenceType', 'pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID')
            ->leftJoin('pupilsightPerson AS coverage', 'pupilsightStaffCoverage.pupilsightPersonIDCoverage=coverage.pupilsightPersonID')
            ->where('pupilsightStaffCoverage.pupilsightPersonID = :pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->groupBy(['pupilsightStaffCoverage.pupilsightStaffCoverageID'])
            ->orderBy(["pupilsightStaffCoverage.status = 'Requested' DESC"]);

        $criteria->addFilterRules($this->getSharedFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function queryCoverageWithNoPersonAssigned(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffCoverage.pupilsightStaffCoverageID', 'pupilsightStaffCoverage.status',  'pupilsightStaffAbsenceType.name as type', 'pupilsightStaffAbsence.reason', 'date', 'COUNT(*) as days', 'MIN(date) as dateStart', 'MAX(date) as dateEnd', 'allDay', 'timeStart', 'timeEnd', 'timestampStatus', 'timestampCoverage', 'pupilsightStaffCoverage.pupilsightPersonIDCoverage', 'pupilsightStaffCoverage.pupilsightPersonID', 
                'absence.title AS titleAbsence', 'absence.preferredName AS preferredNameAbsence', 'absence.surname AS surnameAbsence', 'absenceStaff.jobTitle as jobTitleAbsence'
            ])
            ->innerJoin('pupilsightStaffAbsence', 'pupilsightStaffCoverage.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID')
            ->innerJoin('pupilsightStaffAbsenceType', 'pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID')
            ->leftJoin('pupilsightStaffCoverageDate', 'pupilsightStaffCoverageDate.pupilsightStaffCoverageID=pupilsightStaffCoverage.pupilsightStaffCoverageID')
            ->leftJoin('pupilsightPerson AS absence', 'pupilsightStaffCoverage.pupilsightPersonID=absence.pupilsightPersonID')
            ->leftJoin('pupilsightStaff AS absenceStaff', 'absence.pupilsightPersonID=absenceStaff.pupilsightPersonID')
            ->where('pupilsightStaffCoverage.pupilsightPersonIDCoverage IS NULL')
            ->groupBy(['pupilsightStaffCoverage.pupilsightStaffCoverageID']);

        $criteria->addFilterRules($this->getSharedFilterRules());

        return $this->runQuery($query, $criteria);
    }

    public function getCoverageDetailsByID($pupilsightStaffCoverageID)
    {
        $data = ['pupilsightStaffCoverageID' => $pupilsightStaffCoverageID];
        $sql = "SELECT pupilsightStaffCoverage.pupilsightStaffCoverageID, pupilsightStaffCoverage.status, pupilsightStaffAbsence.pupilsightStaffAbsenceID, pupilsightStaffAbsenceType.name as type, pupilsightStaffAbsence.reason, substituteTypes,
                MIN(date) as date, COUNT(*) as days, MIN(date) as dateStart, MAX(date) as dateEnd, MAX(allDay) as allDay, MIN(timeStart) as timeStart, MAX(timeEnd) as timeEnd, timestampStatus, timestampCoverage, pupilsightStaffCoverage.requestType,
                pupilsightStaffCoverage.notesCoverage, pupilsightStaffCoverage.notesStatus, 0 as urgent, pupilsightStaffAbsence.notificationSent, pupilsightStaffAbsence.pupilsightGroupID, pupilsightStaffCoverage.notificationList as notificationListCoverage, pupilsightStaffAbsence.notificationList as notificationListAbsence, 
                pupilsightStaffCoverage.pupilsightPersonID, absence.title AS titleAbsence, absence.preferredName AS preferredNameAbsence, absence.surname AS surnameAbsence, 
                pupilsightStaffCoverage.pupilsightPersonIDStatus, status.title AS titleStatus, status.preferredName AS preferredNameStatus, status.surname AS surnameStatus, 
                pupilsightStaffCoverage.pupilsightPersonIDCoverage, coverage.title as titleCoverage, coverage.preferredName as preferredNameCoverage, coverage.surname as surnameCoverage
            FROM pupilsightStaffCoverage
            LEFT JOIN pupilsightStaffCoverageDate ON (pupilsightStaffCoverageDate.pupilsightStaffCoverageID=pupilsightStaffCoverage.pupilsightStaffCoverageID)
            LEFT JOIN pupilsightStaffAbsence ON (pupilsightStaffAbsence.pupilsightStaffAbsenceID=pupilsightStaffCoverage.pupilsightStaffAbsenceID)
            LEFT JOIN pupilsightStaffAbsenceType ON (pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID)
            LEFT JOIN pupilsightPerson AS coverage ON (pupilsightStaffCoverage.pupilsightPersonIDCoverage=coverage.pupilsightPersonID)
            LEFT JOIN pupilsightPerson AS status ON (pupilsightStaffCoverage.pupilsightPersonIDStatus=status.pupilsightPersonID)
            LEFT JOIN pupilsightPerson AS absence ON (pupilsightStaffCoverage.pupilsightPersonID=absence.pupilsightPersonID)
            WHERE pupilsightStaffCoverage.pupilsightStaffCoverageID=:pupilsightStaffCoverageID
            GROUP BY pupilsightStaffCoverage.pupilsightStaffCoverageID
            ";

        return $this->db()->selectOne($sql, $data);
    }

    public function selectTimetableRowsByCoverageDate($pupilsightStaffCoverageID, $date)
    {
        $data = ['pupilsightStaffCoverageID' => $pupilsightStaffCoverageID, 'date' => $date];
        $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightTTColumnRow.name as period, pupilsightTTColumnRow.timeStart, pupilsightTTColumnRow.timeEnd, pupilsightCourse.name as courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourseClass.nameShort as className, pupilsightCourseClass.attendance, pupilsightSpace.name as spaceName
                FROM pupilsightStaffCoverage
                JOIN pupilsightStaffCoverageDate ON (pupilsightStaffCoverage.pupilsightStaffCoverageID=pupilsightStaffCoverageDate.pupilsightStaffCoverageID)
                JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStaffCoverage.pupilsightPersonID)
                JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID AND pupilsightTTDayDate.date=pupilsightStaffCoverageDate.date)
                JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnRowID=pupilsightTTDayRowClass.pupilsightTTColumnRowID)
                LEFT JOIN pupilsightSpace ON (pupilsightSpace.pupilsightSpaceID=pupilsightTTDayRowClass.pupilsightSpaceID)
                WHERE pupilsightStaffCoverage.pupilsightStaffCoverageID=:pupilsightStaffCoverageID 
                AND (pupilsightCourseClassPerson.role = 'Teacher' OR pupilsightCourseClassPerson.role = 'Assistant')
                AND pupilsightStaffCoverageDate.date=:date
                AND pupilsightCourse.pupilsightSchoolYearID=pupilsightStaffCoverage.pupilsightSchoolYearID
                AND (pupilsightStaffCoverageDate.allDay='Y' 
                    OR (pupilsightStaffCoverageDate.allDay='N' AND pupilsightTTColumnRow.timeStart <= pupilsightStaffCoverageDate.timeEnd AND pupilsightTTColumnRow.timeEnd >= pupilsightStaffCoverageDate.timeStart)
                )
                GROUP BY pupilsightTTColumnRow.pupilsightTTColumnRowID, pupilsightCourse.pupilsightCourseID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightSpace.pupilsightSpaceID
                ORDER BY pupilsightTTColumnRow.timeStart
        ";

        return $this->db()->select($sql, $data);
    }

    public function selectCoverageByAbsenceID($pupilsightStaffAbsenceID)
    {
        $data = ['pupilsightStaffAbsenceID' => $pupilsightStaffAbsenceID];
        $sql = "SELECT pupilsightStaffCoverageID
                FROM pupilsightStaffCoverage
                WHERE pupilsightStaffCoverage.pupilsightStaffAbsenceID = :pupilsightStaffAbsenceID
                ORDER BY pupilsightStaffCoverage.timestampStatus ASC";

        return $this->db()->select($sql, $data);
    }

    protected function getSharedFilterRules()
    {
        return [
            'requested' => function ($query, $requested) {
                return $requested == 'Y'
                    ? $query->where("pupilsightStaffCoverage.status = 'Requested'")
                    : $query->where("pupilsightStaffCoverage.status <> 'Requested'");
            },
            'status' => function ($query, $status) {
                return $query->where('pupilsightStaffCoverage.status = :status')
                             ->bindValue('status', $status);
            },
            'dateStart' => function ($query, $dateStart) {
                return $query->where("pupilsightStaffCoverageDate.date >= :dateStart")
                             ->bindValue('dateStart', $dateStart);
            },
            'dateEnd' => function ($query, $dateEnd) {
                return $query->where("pupilsightStaffCoverageDate.date <= :dateEnd")
                             ->bindValue('dateEnd', $dateEnd);
            },
            'date' => function ($query, $date) {
                switch (ucfirst($date)) {
                    case 'Upcoming': return $query->where("date >= CURRENT_DATE()")->where("pupilsightStaffCoverage.status <> 'Declined' AND pupilsightStaffCoverage.status <> 'Cancelled'");
                    case 'Today'   : return $query->where("pupilsightStaffCoverageDate.date = CURRENT_DATE()");
                    case 'Past'    : return $query->where("pupilsightStaffCoverageDate.date < CURRENT_DATE()");
                }
            },
        ];
    }
}
