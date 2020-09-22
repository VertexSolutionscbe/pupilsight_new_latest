<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\User;

use Pupilsight\Domain\Gateway;

/**
 * @version v16
 * @since   v16
 */
class UsernameFormatGateway extends Gateway
{
    public function selectUsernameFormats()
    {
        $sql = "SELECT pupilsightUsernameFormatID, format, isDefault, GROUP_CONCAT(DISTINCT pupilsightRole.name SEPARATOR '<br>') as roles 
                FROM pupilsightUsernameFormat 
                JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightUsernameFormat.pupilsightRoleIDList)) 
                GROUP BY pupilsightUsernameFormatID ORDER BY isDefault";

        return $this->db()->select($sql);
    }
}
