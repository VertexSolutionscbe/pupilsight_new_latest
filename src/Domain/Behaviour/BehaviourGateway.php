<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Behaviour;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Behaviour Gateway
 *
 * @version v17
 * @since   v17
 */
class BehaviourGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightBehaviour';

    private static $searchableColumns = [];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryBehaviourBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightPersonIDCreator = null)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightBehaviour.pupilsightBehaviourID',
                'pupilsightBehaviour.type',
                'pupilsightBehaviour.descriptor',
                'pupilsightBehaviour.level',
                'pupilsightBehaviour.date',
                'pupilsightBehaviour.timestamp',
                'pupilsightBehaviour.comment',
                'pupilsightBehaviour.followup',
                'student.pupilsightPersonID',
                'student.surname',
                'student.preferredName',
                'pupilsightRollGroup.nameShort AS rollGroup',
                'creator.title AS titleCreator',
                'creator.surname AS surnameCreator',
                'creator.preferredName AS preferredNameCreator',
            ])
            ->innerJoin('pupilsightPerson AS student', 'pupilsightBehaviour.pupilsightPersonID=student.pupilsightPersonID')
            ->innerJoin('pupilsightStudentEnrolment', 'student.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightPerson AS creator', 'pupilsightBehaviour.pupilsightPersonIDCreator=creator.pupilsightPersonID')
            ->where('pupilsightBehaviour.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightBehaviour.pupilsightSchoolYearID');

        if (!empty($pupilsightPersonIDCreator)) {
            $query->where('pupilsightBehaviour.pupilsightPersonIDCreator = :pupilsightPersonIDCreator')
                ->bindValue('pupilsightPersonIDCreator', $pupilsightPersonIDCreator);
        }

        $criteria->addFilterRules([
            'student' => function ($query, $pupilsightPersonID) {
                return $query
                    ->where('pupilsightBehaviour.pupilsightPersonID = :pupilsightPersonID')
                    ->bindValue('pupilsightPersonID', $pupilsightPersonID);
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
            'type' => function ($query, $type) {
                return $query
                    ->where('pupilsightBehaviour.type = :type')
                    ->bindValue('type', $type);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryBehaviourPatternsBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID',
                'pupilsightStudentEnrolmentID',
                'pupilsightPerson.surname',
                'pupilsightPerson.preferredName',
                'pupilsightYearGroup.nameShort AS yearGroup',
                'pupilsightRollGroup.nameShort AS rollGroup',
                'pupilsightPerson.dateStart',
                'pupilsightPerson.dateEnd',
                "COUNT(DISTINCT pupilsightBehaviourID) AS count",
            ])
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID')
            ->innerJoin('pupilsightYearGroup', 'pupilsightYearGroup.pupilsightYearGroupID=pupilsightStudentEnrolment.pupilsightYearGroupID')
            ->leftJoin('pupilsightBehaviour', "pupilsightBehaviour.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightBehaviour.type='Negative' 
                AND pupilsightBehaviour.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID")
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where("pupilsightPerson.status = 'Full'")
            ->groupBy(['pupilsightPerson.pupilsightPersonID']);

        $criteria->addFilterRules([
            'descriptor' => function ($query, $descriptor) {
                return $query
                    ->where('(pupilsightBehaviourID IS NULL OR pupilsightBehaviour.descriptor = :descriptor)')
                    ->bindValue('descriptor', $descriptor);
            },
            'level' => function ($query, $level) {
                return $query
                    ->where('(pupilsightBehaviourID IS NULL OR pupilsightBehaviour.level = :level)')
                    ->bindValue('level', $level);
            },
            'fromDate' => function ($query, $fromDate) {
                return $query
                    ->where('(pupilsightBehaviourID IS NULL OR pupilsightBehaviour.date >= :fromDate)')
                    ->bindValue('fromDate', $fromDate);
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
            'minimumCount' => function ($query, $minimumCount) {
                return $query
                    ->having('count >= :minimumCount')
                    ->bindValue('minimumCount', $minimumCount);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryBehaviourLettersBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightBehaviourLetter')
            ->cols([
                'pupilsightBehaviourLetter.*',
                'pupilsightPerson.pupilsightPersonID',
                'pupilsightPerson.surname',
                'pupilsightPerson.preferredName',
                'pupilsightRollGroup.nameShort AS rollGroup',
            ])
            ->innerJoin('pupilsightPerson', 'pupilsightBehaviourLetter.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID 
                AND pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightBehaviourLetter.pupilsightSchoolYearID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID')
            ->where('pupilsightBehaviourLetter.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where("pupilsightPerson.status = 'Full'");

        $criteria->addFilterRules([
            'student' => function ($query, $pupilsightPersonID) {
                return $query
                    ->where('pupilsightBehaviourLetter.pupilsightPersonID = :pupilsightPersonID')
                    ->bindValue('pupilsightPersonID', $pupilsightPersonID);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function queryBehaviourRecordsByPerson(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightPersonID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightBehaviour.*',
                'creator.title AS titleCreator',
                'creator.surname AS surnameCreator',
                'creator.preferredName AS preferredNameCreator',
            ])
            ->leftJoin('pupilsightPerson AS creator', 'pupilsightBehaviour.pupilsightPersonIDCreator=creator.pupilsightPersonID')
            ->where('pupilsightBehaviour.pupilsightPersonID = :pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->where('pupilsightBehaviour.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

            return $this->runQuery($query, $criteria);
    }
}
