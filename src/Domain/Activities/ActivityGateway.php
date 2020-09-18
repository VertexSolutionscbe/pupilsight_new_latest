<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Activities;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Activity Gateway
 *
 * @version v16
 * @since   v16
 */
class ActivityGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightActivity';

    private static $searchableColumns = ['pupilsightActivity.name', 'pupilsightActivity.type'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryActivitiesBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightActivity.pupilsightActivityID', 'pupilsightActivity.name', 'pupilsightActivity.active', 'pupilsightActivity.provider', 'pupilsightActivity.registration', 'pupilsightActivity.type', 'pupilsightSchoolYearTermIDList', 'programStart', 'programEnd', 'payment', 'paymentType', 'paymentFirmness', 'maxParticipants',
                "GROUP_CONCAT(DISTINCT pupilsightYearGroup.nameShort ORDER BY pupilsightYearGroup.sequenceNumber SEPARATOR ', ') as yearGroups",
                "COUNT(DISTINCT pupilsightYearGroup.pupilsightYearGroupID) as yearGroupCount",
                "COUNT(DISTINCT CASE WHEN pupilsightActivityStudent.status = 'Accepted' THEN pupilsightActivityStudent.pupilsightPersonID END) as enrolment",
                "COUNT(DISTINCT CASE WHEN pupilsightActivityStudent.status = 'Waiting List' THEN pupilsightActivityStudent.pupilsightPersonID END) as waiting",
                "COUNT(DISTINCT CASE WHEN pupilsightActivityStudent.status = 'Pending' THEN pupilsightActivityStudent.pupilsightPersonID END) as pending",
            ])
            ->leftJoin('pupilsightYearGroup', 'FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightActivity.pupilsightYearGroupIDList)')
            ->leftJoin('pupilsightActivityStudent', 'pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID')
            ->where('pupilsightActivity.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->groupBy(['pupilsightActivity.pupilsightActivityID']);

        $criteria->addFilterRules([
            'term' => function ($query, $pupilsightSchoolYearTermID) {
                return $query
                    ->where('FIND_IN_SET(:pupilsightSchoolYearTermID, pupilsightActivity.pupilsightSchoolYearTermIDList)')
                    ->bindValue('pupilsightSchoolYearTermID', $pupilsightSchoolYearTermID);
            },
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

    public function selectWeekdayNamesByActivity($pupilsightActivityID)
    {
        $data = array('pupilsightActivityID' => $pupilsightActivityID);
        $sql = "SELECT DISTINCT nameShort 
                FROM pupilsightActivitySlot 
                JOIN pupilsightDaysOfWeek ON (pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID) 
                WHERE pupilsightActivityID=:pupilsightActivityID 
                ORDER BY sequenceNumber";

        return $this->db()->select($sql, $data);
    }
}
