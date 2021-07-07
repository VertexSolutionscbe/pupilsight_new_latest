<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Messenger;

class ChatGateway
{
    public function getRoleTabs($con, $role)
    {
        $sq = "select id, name, roleids from chat_tab where FIND_IN_SET($role, roleids) order by name asc";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function getAdminRoleTabs($con)
    {
        $sq = "select id, name, roleids from chat_tab order by name asc";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function getRoleMaster($con)
    {
        $sq = "select pupilsightRoleID as id, name, category from pupilsightRole order by name asc ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }
}
