<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Markbook;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Markbook Column Gateway
 *
 * @version v17
 * @since   v17
 */
class MarkbookColumnGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightMarkbookColumn';
    private static $searchableColumns = ['name', 'description', 'type'];
    
    /**
     * 
     */
    public function queryMarkbookColumnsByClass(QueryCriteria $criteria, $pupilsightCourseClassID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightMarkbookColumn')
            ->cols(['*','pupilsightMarkbookColumn.name as name','pupilsightMarkbookColumn.sequenceNumber as sequenceNumber'])
            ->where('pupilsightMarkbookColumn.pupilsightCourseClassID = :pupilsightCourseClassID')
            ->bindValue('pupilsightCourseClassID', $pupilsightCourseClassID)
            ->groupBy(['pupilsightMarkbookColumn.pupilsightMarkbookColumnID']);

        $criteria->addFilterRules([
            'term' => function ($query, $pupilsightSchoolYearTermID) {
                if ($pupilsightSchoolYearTermID <= 0) return $query;

                return $query
                    ->innerJoin('pupilsightSchoolYearTerm', 'pupilsightSchoolYearTerm.pupilsightSchoolYearTermID=pupilsightMarkbookColumn.pupilsightSchoolYearTermID 
                        OR pupilsightMarkbookColumn.date BETWEEN pupilsightSchoolYearTerm.firstDay AND pupilsightSchoolYearTerm.lastDay')
                    ->where('pupilsightSchoolYearTerm.pupilsightSchoolYearTermID = :pupilsightSchoolYearTermID')
                    ->bindValue('pupilsightSchoolYearTermID', $pupilsightSchoolYearTermID);
            },
            'show' => function ($query, $show) {
                switch ($show) {
                    case 'marked'  : $query->where("complete = 'Y'"); break;
                    case 'unmarked': $query->where("complete = 'N'"); break;
                }
                return $query;
            },
        ]);

        return $this->runQuery($query, $criteria);
    }
}
