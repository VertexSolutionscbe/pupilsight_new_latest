<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Students;

use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;
use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\Traits\SharedUserLogic;

/**
 * @version v17
 * @since   v17
 */
class StudentReportGateway extends QueryableGateway
{
    use TableAware;
    use SharedUserLogic;

    private static $tableName = 'pupilsightStudentEnrolment';
    private static $searchableColumns = [];

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryStudentTransport(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.transport', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.address1', 'pupilsightPerson.address1District', 'pupilsightPerson.address1Country', 'pupilsightRollGroup.nameShort as rollGroup',
            ])
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->innerJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where("pupilsightPerson.status = 'Full'")
            ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
            ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
            ->bindValue('today', date('Y-m-d'));


        return $this->runQuery($query, $criteria);
    }

    public function queryStudentCountByRollGroup(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightRollGroup')
            ->cols([
                'pupilsightRollGroup.name as rollGroup',
                'pupilsightYearGroup.sequenceNumber',
                'FORMAT(AVG((TO_DAYS(NOW())-TO_DAYS(pupilsightPerson.dob)))/365.242199, 1) as meanAge',
                "count(DISTINCT pupilsightPerson.pupilsightPersonID) AS total",
                "count(CASE WHEN pupilsightPerson.gender='M' THEN pupilsightPerson.pupilsightPersonID END) as totalMale",
                "count(CASE WHEN pupilsightPerson.gender='F' THEN pupilsightPerson.pupilsightPersonID END) as totalFemale",
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightYearGroup.pupilsightYearGroupID=pupilsightStudentEnrolment.pupilsightYearGroupID')
            ->where('pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->groupBy(['pupilsightRollGroup.pupilsightRollGroupID']);

        if (!$criteria->hasFilter('from')) {
            $query->where("pupilsightPerson.status='Full'");
        }

        $criteria->addFilterRules([
            'from' => function ($query, $date) {
                return $query
                    ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<=:date)')
                    ->bindValue('date', $date);
            },
            'to' => function ($query, $date) {
                return $query
                    ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd>=:date)')
                    ->bindValue('date', $date);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryStudentPrivacyChoices(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.privacy', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.image_240', 'pupilsightRollGroup.nameShort as rollGroup',
            ])
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->innerJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where("(pupilsightPerson.privacy <> '' AND pupilsightPerson.privacy IS NOT NULL)")
            ->where("pupilsightPerson.status = 'Full'")
            ->where("pupilsightPerson.status = 'Full'")
            ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart <= :today)')
            ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd >= :today)')
            ->bindValue('today', date('Y-m-d'));

        return $this->runQuery($query, $criteria);
    }

    public function queryStudentStatusBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID, $status = 'Full', $dateFrom = null, $dateTo = null, $ignoreEnrolment = false)
    {
        $query = $this
            ->newQuery()
            ->distinct()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightStudentEnrolmentID', 'pupilsightPerson.title', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'pupilsightPerson.username', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'pupilsightStudentEnrolment.rollOrder', 'pupilsightPerson.dateStart', 'pupilsightPerson.dateEnd', 'pupilsightPerson.status', 'pupilsightPerson.lastSchool', 'pupilsightPerson.departureReason', 'pupilsightPerson.nextSchool', "'Student' as roleCategory"
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->leftJoin('pupilsightSchoolYear AS currentSchoolYear', 'currentSchoolYear.pupilsightSchoolYearID = pupilsightStudentEnrolment.pupilsightSchoolYearID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        if ($ignoreEnrolment) {
            $query->innerJoin('pupilsightRole', 'FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)')
                  ->where("pupilsightRole.category='Student'");
        } else {
            $query->where("pupilsightStudentEnrolment.pupilsightStudentEnrolmentID IS NOT NULL")
                  ->where('pupilsightPerson.status = :status')
                  ->bindValue('status', $status);
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->where($status == 'Full'
                ? 'pupilsightPerson.dateStart BETWEEN :dateFrom AND :dateTo'
                : 'pupilsightPerson.dateEnd BETWEEN :dateFrom AND :dateTo')
            ->bindValue('dateFrom', $dateFrom)
            ->bindValue('dateTo', $dateTo);
        }

        if ($status == 'Full' && empty($dateFrom)) {
            // This ensures the new student list for the current year excludes any students who were enrolled in the previous year
            $query->cols(['(
                SELECT COUNT(*) FROM pupilsightStudentEnrolment AS pastEnrolment WHERE pastEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pastEnrolment.pupilsightSchoolYearID=(
                    SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE sequenceNumber=(
                        SELECT MAX(sequenceNumber) FROM pupilsightSchoolYear WHERE sequenceNumber < currentSchoolYear.sequenceNumber
                        )
                    )
                ) AS pastEnrolmentCount'])
                ->having('pastEnrolmentCount = 0');
        }

        return $this->runQuery($query, $criteria);
    }
}
