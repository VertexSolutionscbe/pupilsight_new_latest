<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\IndividualNeeds;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class INGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightIN';

    private static $searchableColumns = ['preferredName', 'surname', 'username'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryINBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->distinct()
            ->from($this->getTableName())
            ->cols([
                'pupilsightINID', 'pupilsightPerson.pupilsightPersonID', 'preferredName', 'surname', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup', 'dateStart', 'dateEnd', 'status'
            ])
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightIN.pupilsightPersonID')
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->innerJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->innerJoin('pupilsightINPersonDescriptor', 'pupilsightINPersonDescriptor.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $criteria->addFilterRules([
            'descriptor' => function ($query, $pupilsightINDescriptorID) {
                return $query
                    ->where('pupilsightINPersonDescriptor.pupilsightINDescriptorID = :pupilsightINDescriptorID')
                    ->bindValue('pupilsightINDescriptorID', $pupilsightINDescriptorID);
            },

            'alert' => function ($query, $pupilsightAlertLevelID) {
                return $query
                    ->where('pupilsightINPersonDescriptor.pupilsightAlertLevelID = :pupilsightAlertLevelID')
                    ->bindValue('pupilsightAlertLevelID', $pupilsightAlertLevelID);
            },

            'rollGroup' => function ($query, $pupilsightRollGroupID) {
                return $query
                    ->where('pupilsightStudentEnrolment.pupilsightRollGroupID = :pupilsightRollGroupID')
                    ->bindValue('pupilsightRollGroupID', $pupilsightRollGroupID);
            },

            'yearGroup' => function ($query, $pupilsightYearGroupID) {
                return $query
                    ->where('pupilsightStudentEnrolment.pupilsightYearGroupID = :pupilsightYearGroupID')
                    ->bindValue('pupilsightYearGroupID', $pupilsightYearGroupID);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryIndividualNeedsDescriptors(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightINDescriptor')
            ->cols([
                'pupilsightINDescriptorID', 'name', 'nameShort', 'description', 'sequenceNumber'
            ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryINCountsBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightYearGroupID = '')
    {
        $query = $this
            ->newQuery()
            ->distinct()
            ->from('pupilsightStudentEnrolment')
            ->cols(['pupilsightYearGroup.name as labelName',
                    'pupilsightYearGroup.pupilsightYearGroupID as labelID',
                    'COUNT(DISTINCT pupilsightStudentEnrolment.pupilsightPersonID) as studentCount',
                    'COUNT(DISTINCT pupilsightINPersonDescriptor.pupilsightPersonID) as inCount',
            ])
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->innerJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightINPersonDescriptor', 'pupilsightINPersonDescriptor.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where("pupilsightPerson.status='Full'")
            ->where('(pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<=:today)')
            ->where('(pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd>=:today)')
            ->bindValue('today', date('Y-m-d'))
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        if (!empty($pupilsightYearGroupID)) {
            // Grouped by Roll Groups within a Year Group
            $query->cols([
                'pupilsightRollGroup.name as labelName',
                'pupilsightRollGroup.pupilsightRollGroupID as labelID',
                'COUNT(DISTINCT pupilsightStudentEnrolment.pupilsightPersonID) as studentCount',
                'COUNT(DISTINCT pupilsightINPersonDescriptor.pupilsightPersonID) as inCount',
            ])
            ->where('pupilsightStudentEnrolment.pupilsightYearGroupID = :pupilsightYearGroupID')
            ->bindValue('pupilsightYearGroupID', $pupilsightYearGroupID)
            ->groupBy(['pupilsightRollGroup.pupilsightRollGroupID']);
        } else {
            // Grouped by Year Group
            $query->cols([
                'pupilsightYearGroup.name as labelName',
                'pupilsightYearGroup.pupilsightYearGroupID as labelID',
                'COUNT(DISTINCT pupilsightStudentEnrolment.pupilsightPersonID) as studentCount',
                'COUNT(DISTINCT pupilsightINPersonDescriptor.pupilsightPersonID) as inCount',
            ])
            ->groupBy(['pupilsightYearGroup.pupilsightYearGroupID']);
        }

        return $this->runQuery($query, $criteria);
    }
}
