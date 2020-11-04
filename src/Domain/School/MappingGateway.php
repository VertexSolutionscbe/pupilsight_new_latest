<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\School;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\Traits\SharedUserLogic;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * YearGroup Gateway
 *
 * @version v16
 * @since   v16
 */
class MappingGateway extends QueryableGateway
{
    use TableAware;
    use SharedUserLogic;

    private static $tableName = 'pupilsightProgramClassSectionMapping';
    private static $searchableColumns = ['pupilsightProgramID'];

    public function queryMappingGroups(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID)
    {
        $query = $this
        ->newQuery()
        ->distinct()
        ->from('pupilsightProgramClassSectionMapping')
        ->cols(['pupilsightProgramClassSectionMapping.pupilsightMappingID','pupilsightSchoolYear.name AS academicyear','pupilsightProgram.name AS program', 'pupilsightYearGroup.name AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup'])
        ->leftJoin('pupilsightSchoolYear', 'pupilsightProgramClassSectionMapping.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
        ->leftJoin('pupilsightProgram', 'pupilsightProgramClassSectionMapping.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
        ->leftJoin('pupilsightYearGroup', 'pupilsightProgramClassSectionMapping.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
        ->leftJoin('pupilsightRollGroup', 'pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID');
        if(!empty($pupilsightSchoolYearID))
        {
            $query->where('pupilsightProgramClassSectionMapping.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" ');
        }
        if(!empty($pupilsightProgramID)){
            $query->where('pupilsightProgramClassSectionMapping.pupilsightProgramID = "'.$pupilsightProgramID.'" ');
        }
        if(!empty($pupilsightYearGroupID)){
            $query->where('pupilsightProgramClassSectionMapping.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" ');
        }
        if(!empty($pupilsightRollGroupID)){
            $query->where('pupilsightProgramClassSectionMapping.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" ');
        }

        // $criteria->addFilterRules([
        //     'type' => function ($query, $type) {
        //             return $query
        //                 ->where('pupilsightProgramClassSectionMapping.pupilsightProgramID = :type')
        //                 ->bindValue('type', ucfirst($type));
        //     },
        // ]);
        $query->orderBy(['pupilsightProgramClassSectionMapping.pupilsightProgramID ASC, pupilsightProgramClassSectionMapping.pupilsightYearGroupID ASC']);


        return $this->runQuery($query, $criteria);
    }
}
