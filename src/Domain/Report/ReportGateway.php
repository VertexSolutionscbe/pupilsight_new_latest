<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Report;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;



/**
 * School Year Gateway
 *
 * @version v17
 * @since   v17
 */
class ReportGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'report_manager';

    public function getStudentList($con, $data){
        $sq = "select pupilsightPersonID, officialName, username, email, phone1, admission_no from pupilsightPerson ";
        $sq .=" where pupilsightRoleIDPrimary='003' order by officialName asc";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function getParentList($con, $data){
        $sq = "select pupilsightPersonID, officialName, username, email, phone1, admission_no from pupilsightPerson ";
        $sq .=" where pupilsightRoleIDPrimary='004' order by officialName asc";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function getParentList($con, $data){
        $sq = "select pupilsightPersonID, officialName, username, email, phone1, admission_no from pupilsightPerson ";
        $sq .=" where pupilsightRoleIDPrimary='004' order by officialName asc";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

}