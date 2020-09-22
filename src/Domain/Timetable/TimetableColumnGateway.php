<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Timetable;

use Pupilsight\Domain\Gateway;

/**
 * @version v16
 * @since   v16
 */
class TimetableColumnGateway extends Gateway
{
    public function selectTTColumns()
    {
        $sql = "SELECT pupilsightTTColumn.pupilsightTTColumnID, pupilsightTTColumn.name, pupilsightTTColumn.nameShort, COUNT(pupilsightTTColumnRowID) as rowCount
                FROM pupilsightTTColumn 
                LEFT JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID)
                GROUP BY pupilsightTTColumn.pupilsightTTColumnID
                ORDER BY pupilsightTTColumn.name";

        return $this->db()->select($sql);
    }

    public function getTTColumnByID($pupilsightTTColumnID)
    {
        $data = array('pupilsightTTColumnID' => $pupilsightTTColumnID);
        $sql = "SELECT pupilsightTTColumnID, name, nameShort FROM pupilsightTTColumn WHERE pupilsightTTColumnID=:pupilsightTTColumnID";

        return $this->db()->selectOne($sql, $data);
    }

    public function selectTTColumnRowsByID($pupilsightTTColumnID)
    {
        $data = array('pupilsightTTColumnID' => $pupilsightTTColumnID);
        $sql = "SELECT pupilsightTTColumnRowID, name, nameShort, timeStart, timeEnd, type
                FROM pupilsightTTColumnRow 
                WHERE pupilsightTTColumnID=:pupilsightTTColumnID 
                ORDER BY timeStart, name";

        return $this->db()->select($sql, $data);
    }
}
