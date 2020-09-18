<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\School;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * School Year Term Gateway
 *
 * @version v17
 * @since   v17
 */
class SchoolYearTermGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightSchoolYearTerm';

    public function querySchoolYearTerms(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightSchoolYearTerm.pupilsightSchoolYearTermID',
                'pupilsightSchoolYear.pupilsightSchoolYearID',
                'pupilsightSchoolYearTerm.name',
                'pupilsightSchoolYearTerm.nameShort',
                'pupilsightSchoolYearTerm.sequenceNumber',
                'pupilsightSchoolYear.sequenceNumber AS schoolYearSequence',
                'pupilsightSchoolYearTerm.firstDay',
                'pupilsightSchoolYearTerm.lastDay',
                'pupilsightSchoolYear.name AS schoolYearName',
                "(CASE WHEN NOW() BETWEEN pupilsightSchoolYearTerm.firstDay AND pupilsightSchoolYearTerm.lastDay THEN 'Current' ELSE '' END) as status"
            ])
            ->innerJoin('pupilsightSchoolYear', 'pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightSchoolYearTerm.pupilsightSchoolYearID');

        $criteria->addFilterRules([
            'schoolYear' => function ($query, $pupilsightSchoolYearID) {
                return $query
                    ->where('pupilsightSchoolYearTerm.pupilsightSchoolYearID=:pupilsightSchoolYearID')
                    ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
            },
            'firstDay' => function ($query, $firstDay) {
                return $query
                    ->where('pupilsightSchoolYearTerm.firstDay <= :firstDay')
                    ->bindValue('firstDay', $firstDay);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function selectSchoolClosuresByTerm($pupilsightSchoolYearTermID)
    {
        $data = array('pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID);
        $sql = "SELECT date, name 
                FROM pupilsightSchoolYearSpecialDay 
                WHERE pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID 
                AND type='School Closure' 
                ORDER BY date";

        return $this->db()->select($sql, $data);
    }
}
