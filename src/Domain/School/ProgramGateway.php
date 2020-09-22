<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\School;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * YearGroup Gateway
 *
 * @version v16
 * @since   v16
 */
class ProgramGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightProgram';
    private static $searchableColumns = [];

    public function queryYearGroups(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightProgramID', 'name', 'nameShort', 'sequenceNumber'
            ]);

        return $this->runQuery($query, $criteria);
    }

    public function attendanceSettings(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('attn_settings')
            ->cols([
                'pupilsightProgram.name AS program_name', 'attn_settings.*'
            ])
            ->leftJoin('pupilsightProgram', 'attn_settings.pupilsightProgramID=pupilsightProgram.pupilsightProgramID');

        return $this->runQuery($query, $criteria);
    }
}
