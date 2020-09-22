<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Activities;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v17
 * @since   v17
 */
class ActivityReportGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightActivity';

    private static $searchableColumns = ['pupilsightActivity.name', 'pupilsightActivity.type'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryActivityEnrollmentSummary(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightActivity.pupilsightActivityID', 'pupilsightActivity.name', 'pupilsightActivity.active', 'pupilsightActivity.provider', 'pupilsightActivity.registration', 'pupilsightActivity.type', 'maxParticipants',
                "COUNT(DISTINCT CASE WHEN pupilsightActivityStudent.status = 'Accepted' THEN pupilsightActivityStudent.pupilsightPersonID END) as enrolment",
                "COUNT(DISTINCT CASE WHEN pupilsightActivityStudent.status <> 'Not Accepted' THEN pupilsightActivityStudent.pupilsightPersonID END) as registered",
            ])
            ->leftJoin('pupilsightActivityStudent', 'pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID')
            ->leftJoin('pupilsightPerson', "pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightPerson.status = 'Full'")
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL OR dateEnd>=:today)')
            ->bindValue('today', date('Y-m-d'))
            ->where('pupilsightActivity.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->groupBy(['pupilsightActivity.pupilsightActivityID']);

        $criteria->addFilterRules([
            'active' => function ($query, $active) {
                return $query
                    ->where('pupilsightActivity.active = :active')
                    ->bindValue('active', $active);
            },
            'registration' => function ($query, $registration) {
                return $query
                    ->where('pupilsightActivity.registration = :registration')
                    ->bindValue('registration', $registration);
            },
            'enrolment' => function ($query, $enrolment) {
                if ($enrolment == 'less') $query->having('enrolment < pupilsightActivity.maxParticipants AND pupilsightActivity.maxParticipants > 0');
                if ($enrolment == 'full') $query->having('enrolment = pupilsightActivity.maxParticipants AND pupilsightActivity.maxParticipants > 0');
                if ($enrolment == 'greater') $query->having('enrolment > pupilsightActivity.maxParticipants AND pupilsightActivity.maxParticipants > 0');
                return $query;
            },
            'status' => function ($query, $status) {
                if ($status == 'waiting') $query->having('waiting > 0');
                if ($status == 'pending') $query->having('pending > 0');
                return $query;
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryParticipantsByActivity(QueryCriteria $criteria, $pupilsightActivityID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightActivityStudent.status', 'pupilsightRollGroup.nameShort AS rollGroup'])
            ->innerJoin('pupilsightActivityStudent', 'pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID')
            ->innerJoin('pupilsightPerson', "pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID")
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID')
            ->where('pupilsightActivity.pupilsightActivityID = :pupilsightActivityID')
            ->bindValue('pupilsightActivityID', $pupilsightActivityID)
            ->where("pupilsightActivityStudent.status <> 'Not Accepted'")
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightActivity.pupilsightSchoolYearID')
            ->where("pupilsightPerson.status = 'Full'")
            ->where('(dateStart IS NULL OR dateStart<=:today)')
            ->where('(dateEnd IS NULL OR dateEnd>=:today)')
            ->bindValue('today', date('Y-m-d'));

        return $this->runQuery($query, $criteria);
    }

    public function queryActivityAttendanceByDate(QueryCriteria $criteria, $pupilsightSchoolYearID, $dateType, $date)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightActivity.pupilsightActivityID', 'pupilsightActivity.name as activity', 'pupilsightActivity.provider', 'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightActivityStudent.status', 'pupilsightRollGroup.nameShort AS rollGroup',
                "(CASE WHEN pupilsightActivityAttendance.pupilsightActivityAttendanceID IS NULL THEN 'Absent' ELSE 'Present' END) AS attendance"
            ])
            ->innerJoin('pupilsightActivitySlot', 'pupilsightActivitySlot.pupilsightActivityID=pupilsightActivity.pupilsightActivityID')
            ->innerJoin('pupilsightDaysOfWeek', 'pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID')
            ->innerJoin('pupilsightActivityStudent', 'pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID')
            ->innerJoin('pupilsightPerson', "pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID")
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID')
            ->leftJoin('pupilsightActivityAttendance', "pupilsightActivityAttendance.pupilsightActivityID=pupilsightActivity.pupilsightActivityID
                AND pupilsightActivityAttendance.date = :date
                AND (pupilsightActivityAttendance.attendance LIKE CONCAT('%', pupilsightPerson.pupilsightPersonID, '%') )")
            ->where('pupilsightActivity.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where("pupilsightActivity.active = 'Y'")
            ->where('pupilsightDaysOfWeek.name=:dayOfWeek')
            ->bindValue('dayOfWeek', date('l', dateConvertToTimestamp($date)))
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightActivity.pupilsightSchoolYearID')
            ->where("pupilsightActivityStudent.status='Accepted'")
            ->where("pupilsightPerson.status = 'Full'")
            ->where('(dateStart IS NULL OR dateStart<=:today)')
            ->where('(dateEnd IS NULL OR dateEnd>=:today)')
            ->bindValue('today', date('Y-m-d'))
            ->bindValue('date', $date)
            ->groupBy(['pupilsightActivity.pupilsightActivityID', 'pupilsightActivityStudent.pupilsightPersonID']);

        if ($dateType == 'Term') {
            $query->innerJoin('pupilsightSchoolYearTerm', "FIND_IN_SET(pupilsightSchoolYearTermID, pupilsightActivity.pupilsightSchoolYearTermIDList)")
                ->where('(:date BETWEEN pupilsightSchoolYearTerm.firstDay AND pupilsightSchoolYearTerm.lastDay)');
        } else {
            $query->where('(:date BETWEEN pupilsightActivity.programStart AND pupilsightActivity.programEnd)');
        }

        return $this->runQuery($query, $criteria);
    }

    public function selectActivitiesByStudent($pupilsightSchoolYearID, $pupilsightPersonID, $status = 'Accepted')
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightActivityStudent.pupilsightPersonID', 'pupilsightActivityStudent.status', 'pupilsightActivity.*'
            ])
            ->innerJoin('pupilsightActivityStudent', 'pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID')
            ->where('pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where('pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->orderBy(['pupilsightActivity.name']);

        if ($status == 'Accepted') {
            $query->where("pupilsightActivityStudent.status='Accepted'");
        } else {
            $query->where("pupilsightActivityStudent.status<>'Not Accepted'");
        }

        return $this->db()->select($query->getStatement(), $query->getBindValues());
    }

    public function selectActivitySpreadByStudent($pupilsightSchoolYearID, $pupilsightPersonID, $dateType, $status = 'Accepted')
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols($dateType == 'Term' 
                ? ["CONCAT(pupilsightSchoolYearTerm.pupilsightSchoolYearTermID, '-', pupilsightActivitySlot.pupilsightDaysOfWeekID) AS groupBy"]
                : ['pupilsightActivitySlot.pupilsightDaysOfWeekID AS groupBy'] )
            ->cols([
                'pupilsightActivityStudent.pupilsightPersonID', 
                'COUNT(DISTINCT pupilsightActivityStudent.pupilsightActivityStudentID) AS count', 
                "COUNT(DISTINCT CASE WHEN pupilsightActivityStudent.status<>'Accepted' THEN pupilsightActivityStudent.pupilsightActivityStudentID END) AS notAccepted", 
                "GROUP_CONCAT(DISTINCT pupilsightActivity.name SEPARATOR ', ') AS activityNames"
            ])
            ->innerJoin('pupilsightActivityStudent', 'pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID')
            ->innerJoin('pupilsightActivitySlot', 'pupilsightActivitySlot.pupilsightActivityID=pupilsightActivity.pupilsightActivityID')
            ->where('pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where('pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID);

        if ($status == 'Accepted') {
            $query->where("pupilsightActivityStudent.status='Accepted'");
        } else {
            $query->where("pupilsightActivityStudent.status<>'Not Accepted'");
        }

        if ($dateType == 'Term') {
            $query->innerJoin('pupilsightSchoolYearTerm', 'FIND_IN_SET(pupilsightSchoolYearTerm.pupilsightSchoolYearTermID, pupilsightActivity.pupilsightSchoolYearTermIDList)')
                ->groupBy(['pupilsightSchoolYearTerm.pupilsightSchoolYearTermID', 'pupilsightActivitySlot.pupilsightDaysOfWeekID']);
        } else {
            $query->groupBy(['pupilsightActivitySlot.pupilsightDaysOfWeekID']);
        }

        return $this->db()->select($query->getStatement(), $query->getBindValues());
    }

    public function selectActivityWeekdays($pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightDaysOfWeek.*
                FROM pupilsightDaysOfWeek 
                JOIN pupilsightActivitySlot ON (pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID) 
                JOIN pupilsightActivity ON (pupilsightActivitySlot.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) 
                WHERE pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND schoolDay='Y' 
                GROUP BY pupilsightDaysOfWeek.pupilsightDaysOfWeekID
                ORDER BY pupilsightDaysOfWeek.sequenceNumber";

        return $this->db()->select($sql, $data);
    }

    public function selectActivityWeekdaysPerTerm($pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightSchoolYearTerm.name, pupilsightDaysOfWeek.*, pupilsightSchoolYearTerm.name as termName, pupilsightSchoolYearTerm.pupilsightSchoolYearTermID as pupilsightSchoolYearTermID
                FROM pupilsightDaysOfWeek 
                JOIN pupilsightActivitySlot ON (pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID) 
                JOIN pupilsightActivity ON (pupilsightActivitySlot.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) 
                JOIN pupilsightSchoolYearTerm ON (pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightActivity.pupilsightSchoolYearID)
                WHERE pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND schoolDay='Y' 
                GROUP BY pupilsightDaysOfWeek.pupilsightDaysOfWeekID, pupilsightSchoolYearTerm.pupilsightSchoolYearTermID
                ORDER BY pupilsightSchoolYearTerm.sequenceNumber, pupilsightDaysOfWeek.sequenceNumber";

        return $this->db()->select($sql, $data);
    }
}
