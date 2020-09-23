<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Staff;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Substitute Gateway
 *
 * @version v18
 * @since   v18
 */
class SubstituteGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightSubstitute';
    private static $primaryKey = 'pupilsightSubstituteID';

    private static $searchableColumns = ['preferredName', 'surname', 'username'];
    
    /**
     * Queries the list of users for the Manage Substitutes page.
     *
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryAllSubstitutes(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightSubstitute.pupilsightSubstituteID', 'pupilsightSubstitute.type', 'pupilsightSubstitute.details', 'pupilsightSubstitute.priority', 'pupilsightSubstitute.active',
                'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.title', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.status', 'pupilsightPerson.image_240', 'pupilsightStaff.pupilsightStaffID'
                
            ])
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightSubstitute.pupilsightPersonID')
            ->leftJoin('pupilsightStaff', 'pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID');

        $criteria->addFilterRules([
            'active' => function ($query, $active) {
                return $query
                    ->where('pupilsightSubstitute.active = :active')
                    ->bindValue('active', $active);
            },
            'status' => function ($query, $status) {
                return $query
                    ->where('pupilsightPerson.status = :status')
                    ->bindValue('status', ucfirst($status));
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryUnavailableDatesBySub(QueryCriteria $criteria, $pupilsightPersonIDCoverage)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightStaffCoverageDate')
            ->cols([
                'date as groupBy', 'pupilsightStaffCoverageDate.*', 'pupilsightStaffCoverageDate.pupilsightPersonIDUnavailable as pupilsightPersonID'
            ])
            ->where('pupilsightStaffCoverageDate.pupilsightPersonIDUnavailable = :pupilsightPersonIDCoverage')
            ->bindValue('pupilsightPersonIDCoverage', $pupilsightPersonIDCoverage);

        return $this->runQuery($query, $criteria);
    }

    public function queryAvailableSubsByDate($criteria, $date, $timeStart = null, $timeEnd = null)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID as groupBy', 'pupilsightPerson.pupilsightPersonID', 'pupilsightSubstitute.details', 'pupilsightSubstitute.type', 'pupilsightSubstitute.priority', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'pupilsightPerson.status', 'pupilsightPerson.image_240', 'pupilsightPerson.email', 'pupilsightPerson.phone1', 'pupilsightPerson.phone1Type', 'pupilsightPerson.phone1CountryCode', 'pupilsightStaff.pupilsightStaffID',
                '(absence.ID IS NULL AND coverage.ID IS NULL AND timetable.ID IS NULL AND unavailable.pupilsightStaffCoverageDateID IS NULL) as available',
                'absence.status as absence', 'coverage.status as coverage', 'timetable.status as timetable', 'unavailable.reason as unavailable',
            ])
            ->leftJoin('pupilsightSubstitute', 'pupilsightSubstitute.pupilsightPersonID=pupilsightPerson.pupilsightPersonID');
                
        if ($criteria->hasFilter('allStaff')) {
            $query->innerJoin('pupilsightStaff', 'pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                  ->innerJoin('pupilsightRole', 'pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary');
        } else {
            $query->leftJoin('pupilsightStaff', 'pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID');
        }

        if (!empty($timeStart) && !empty($timeEnd)) {
            $query->bindValue('timeStart', $timeStart)
                  ->bindValue('timeEnd', $timeEnd);

            // Not available?
            $query->leftJoin('pupilsightStaffCoverageDate as unavailable', "unavailable.pupilsightPersonIDUnavailable=pupilsightPerson.pupilsightPersonID AND unavailable.date = :date 
                    AND (unavailable.allDay='Y' OR (unavailable.allDay='N' AND unavailable.timeStart < :timeEnd AND unavailable.timeEnd > :timeStart))");

            // Already covering?
            $query->joinSubSelect(
                'LEFT',
                "SELECT pupilsightStaffCoverageDateID as ID, (CASE WHEN absence.pupilsightPersonID IS NOT NULL THEN CONCAT(absence.preferredName, ' ', absence.surname) ELSE CONCAT(status.preferredName, ' ', status.surname) END) as status, pupilsightStaffCoverage.pupilsightPersonIDCoverage, pupilsightStaffCoverageDate.date, allDay, timeStart, timeEnd
                    FROM pupilsightStaffCoverage 
                    JOIN pupilsightStaffCoverageDate ON (pupilsightStaffCoverageDate.pupilsightStaffCoverageID=pupilsightStaffCoverage.pupilsightStaffCoverageID)
                    LEFT JOIN pupilsightStaffAbsence ON (pupilsightStaffAbsence.pupilsightStaffAbsenceID=pupilsightStaffCoverage.pupilsightStaffAbsenceID)
                    LEFT JOIN pupilsightPerson as absence ON (absence.pupilsightPersonID=pupilsightStaffAbsence.pupilsightPersonID)
                    LEFT JOIN pupilsightPerson as status ON (status.pupilsightPersonID=pupilsightStaffCoverage.pupilsightPersonID)
                    WHERE pupilsightStaffCoverage.status = 'Accepted'",
                'coverage',
                "coverage.pupilsightPersonIDCoverage=pupilsightPerson.pupilsightPersonID AND coverage.date = :date 
                    AND (coverage.allDay='Y' OR (coverage.allDay='N' AND coverage.timeStart < :timeEnd AND coverage.timeEnd > :timeStart))"
            );

            // Already absent?
            $query->joinSubSelect(
                'LEFT',
                "SELECT pupilsightStaffAbsenceDateID as ID, pupilsightStaffAbsenceType.name as status, pupilsightStaffAbsence.pupilsightPersonID, pupilsightStaffAbsenceDate.date, allDay, timeStart, timeEnd
                    FROM pupilsightStaffAbsence 
                    JOIN pupilsightStaffAbsenceDate ON (pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID)
                    JOIN pupilsightStaffAbsenceType ON (pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID) 
                    WHERE pupilsightStaffAbsence.status <> 'Declined'",
                'absence',
                "absence.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND absence.date = :date 
                    AND (absence.allDay='Y' OR (absence.allDay='N' AND absence.timeStart < :timeEnd AND absence.timeEnd > :timeStart))"
            );

            // Already teaching?
            $query->joinSubSelect(
                'LEFT',
                "SELECT pupilsightTTColumnRow.pupilsightTTColumnRowID as ID, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as status, pupilsightCourseClassPerson.pupilsightPersonID, pupilsightTTDayDate.date, timeStart, timeEnd
                    FROM pupilsightCourseClassPerson 
                    JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                    JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID)
                    JOIN pupilsightTTDay ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID)
                    JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnRowID=pupilsightTTDayRowClass.pupilsightTTColumnRowID 
                        AND pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID)
                    JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightTTDayRowClass.pupilsightCourseClassID)
                    JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                    WHERE pupilsightCourseClassPerson.role = 'Teacher'",
                'timetable',
                "timetable.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND timetable.date = :date 
                    AND timetable.timeStart < :timeEnd AND timetable.timeEnd > :timeStart"
            );
        } else {
            // Not available?
            $query->leftJoin('pupilsightStaffCoverageDate as unavailable', 'unavailable.date = :date 
                AND unavailable.pupilsightPersonIDUnavailable=pupilsightPerson.pupilsightPersonID');

            // Already covering?
            $query->joinSubSelect(
                'LEFT',
                "SELECT pupilsightStaffCoverageDateID as ID, (CASE WHEN absence.pupilsightPersonID IS NOT NULL THEN CONCAT(absence.preferredName, ' ', absence.surname) ELSE CONCAT(status.preferredName, ' ', status.surname) END) as status, pupilsightStaffCoverage.pupilsightPersonIDCoverage, pupilsightStaffCoverageDate.date
                    FROM pupilsightStaffCoverage 
                    JOIN pupilsightStaffCoverageDate ON (pupilsightStaffCoverageDate.pupilsightStaffCoverageID=pupilsightStaffCoverage.pupilsightStaffCoverageID)
                    LEFT JOIN pupilsightStaffAbsence ON (pupilsightStaffAbsence.pupilsightStaffAbsenceID=pupilsightStaffCoverage.pupilsightStaffAbsenceID)
                    LEFT JOIN pupilsightPerson as absence ON (absence.pupilsightPersonID=pupilsightStaffAbsence.pupilsightPersonID)
                    LEFT JOIN pupilsightPerson as status ON (status.pupilsightPersonID=pupilsightStaffCoverage.pupilsightPersonID)
                    WHERE pupilsightStaffCoverage.status = 'Accepted'
                    ",
                'coverage',
                'coverage.pupilsightPersonIDCoverage=pupilsightPerson.pupilsightPersonID AND coverage.date = :date'
            );

            // Already absent?
            $query->joinSubSelect(
                'LEFT',
                "SELECT pupilsightStaffAbsenceDateID as ID, pupilsightStaffAbsenceType.name as status, pupilsightStaffAbsence.pupilsightPersonID, pupilsightStaffAbsenceDate.date
                    FROM pupilsightStaffAbsence 
                    JOIN pupilsightStaffAbsenceDate ON (pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID)
                    JOIN pupilsightStaffAbsenceType ON (pupilsightStaffAbsenceType.pupilsightStaffAbsenceTypeID=pupilsightStaffAbsence.pupilsightStaffAbsenceTypeID) 
                    WHERE pupilsightStaffAbsence.status <> 'Declined'
                    ",
                'absence',
                'absence.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND absence.date = :date'
            );

            // Already teaching?
            $query->joinSubSelect(
                'LEFT',
                "SELECT pupilsightTTDayDate.pupilsightTTDayDateID as ID, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as status, pupilsightCourseClassPerson.pupilsightPersonID, pupilsightTTDayDate.date
                    FROM pupilsightCourseClassPerson 
                    JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                    JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID)
                    JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightTTDayRowClass.pupilsightCourseClassID)
                    JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                    WHERE (pupilsightCourseClassPerson.role = 'Teacher' OR pupilsightCourseClassPerson.role = 'Assistant')",
                'timetable',
                'timetable.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND timetable.date = :date'
            );
        }

        $query->where("pupilsightPerson.status='Full'")
              ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<=:date)')
              ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd>=:date)')
              ->bindValue('date', $date);

        if ($criteria->hasFilter('allStaff')) {
            $query->where("pupilsightRole.category='Staff' AND (pupilsightStaff.type LIKE '%Teaching%' OR pupilsightStaff.type LIKE '%Teacher%')");
            $query->where("(SELECT COUNT(*) FROM pupilsightCourseClassPerson 
                INNER JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                INNER JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                INNER JOIN pupilsightSchoolYear ON (pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightCourse.pupilsightSchoolYearID)
                WHERE pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightSchoolYear.status='Current') > 0");  
        } else {
            $query->where("pupilsightSubstitute.active='Y'");
        }

        if (!$criteria->hasFilter('showUnavailable')) {
            $query->where('absence.ID IS NULL')
                  ->where('coverage.ID IS NULL')
                  ->where('timetable.ID IS NULL')
                  ->where('unavailable.pupilsightStaffCoverageDateID IS NULL');
        } else {
            $query->groupBy(['pupilsightPerson.pupilsightPersonID']);
            $query->orderBy(['available DESC']);
        }

        $criteria->addFilterRules([
            'substituteTypes' => function ($query, $substituteTypes) {
                if (!empty($substituteTypes)) {
                    $query->where('FIND_IN_SET(pupilsightSubstitute.type, :substituteTypes)')
                          ->bindValue('substituteTypes', $substituteTypes);
                }

                return $query;
            },
        ]);
        
        return $this->runQuery($query, $criteria);
    }

    public function selectUnavailableDatesBySub($pupilsightPersonID, $pupilsightStaffCoverageIDExclude = '')
    {
        $data = ['pupilsightPersonID' => $pupilsightPersonID, 'pupilsightStaffCoverageIDExclude' => $pupilsightStaffCoverageIDExclude];
        $sql = "(
                SELECT date as groupBy, 'Not Available' as status, allDay, timeStart, timeEnd
                FROM pupilsightStaffCoverageDate 
                WHERE pupilsightStaffCoverageDate.pupilsightPersonIDUnavailable=:pupilsightPersonID 
                ORDER BY DATE
            ) UNION ALL (
                SELECT date as groupBy, 'Covering' as status, allDay, timeStart, timeEnd
                FROM pupilsightStaffCoverage
                JOIN pupilsightStaffCoverageDate ON (pupilsightStaffCoverageDate.pupilsightStaffCoverageID=pupilsightStaffCoverage.pupilsightStaffCoverageID)
                WHERE pupilsightStaffCoverage.pupilsightPersonIDCoverage=:pupilsightPersonID 
                AND (pupilsightStaffCoverage.status='Accepted')
                AND pupilsightStaffCoverage.pupilsightStaffCoverageID <> :pupilsightStaffCoverageIDExclude
            ) UNION ALL (
                SELECT date as groupBy, 'Absent' as status, allDay, timeStart, timeEnd
                FROM pupilsightStaffAbsence
                JOIN pupilsightStaffAbsenceDate ON (pupilsightStaffAbsenceDate.pupilsightStaffAbsenceID=pupilsightStaffAbsence.pupilsightStaffAbsenceID)
                WHERE pupilsightStaffAbsence.pupilsightPersonID=:pupilsightPersonID 
                AND pupilsightStaffAbsence.status <> 'Declined'
            ) UNION ALL (
                SELECT date as groupBy, 'Teaching' as status, 'N', timeStart, timeEnd
                FROM pupilsightCourseClassPerson 
                JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID)
                JOIN pupilsightTTDay ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID)
                JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnRowID=pupilsightTTDayRowClass.pupilsightTTColumnRowID 
                    AND pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID)
                WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassPerson.role = 'Teacher'
            )";

        return $this->db()->select($sql, $data);
    }

    public function getSubstituteByPerson($pupilsightPersonID)
    {
        $data = ['pupilsightPersonID' => $pupilsightPersonID];
        $sql = "SELECT * FROM pupilsightSubstitute WHERE pupilsightPersonID=:pupilsightPersonID";

        return $this->db()->selectOne($sql, $data);
    }
}
