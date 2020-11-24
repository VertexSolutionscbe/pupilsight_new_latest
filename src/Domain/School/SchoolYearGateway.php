<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\School;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * School Year Gateway
 *
 * @version v17
 * @since   v17
 */
class SchoolYearGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightSchoolYear';

    public function querySchoolYears(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightSchoolYearID', 'name', 'sequenceNumber', 'status', 'firstDay', 'lastDay'
            ]);

        return $this->runQuery($query, $criteria);
    }
    
    public function getSchoolYearByID($pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID";

        return $this->db()->selectOne($sql, $data);
    }

    public function getNextSchoolYearByID($pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT * FROM pupilsightSchoolYear WHERE sequenceNumber=(SELECT MIN(sequenceNumber) FROM pupilsightSchoolYear WHERE sequenceNumber > (SELECT sequenceNumber FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID))";

        return $this->db()->selectOne($sql, $data);
    }

    public function getPreviousSchoolYearByID($pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT * FROM pupilsightSchoolYear WHERE sequenceNumber=(SELECT MAX(sequenceNumber) FROM pupilsightSchoolYear WHERE sequenceNumber < (SELECT sequenceNumber FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID))";

        return $this->db()->selectOne($sql, $data);
    }

    public function getSkill(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('ac_manage_skill')
            ->cols([
                'id', 'name', 'code', 'description'
            ])
            ->groupby(['ac_manage_skill.id'])
            ->orderby(['id DESC']);

        return $this->runQuery($query, $criteria,TRUE );
    }

    public function queryFinancialYears(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightSchoolFinanceYear')
            ->cols([
                'pupilsightSchoolFinanceYearID', 'name', 'sequenceNumber', 'status', 'firstDay', 'lastDay'
            ]);

        return $this->runQuery($query, $criteria);
    }

    public function getAllEmailTemplate(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightTemplate')
            ->cols([
                'pupilsightTemplate.*','pupilsightPerson.officialName as createdBy'
            ])
            ->leftJoin('pupilsightPerson', 'pupilsightTemplate.created_by=pupilsightPerson.pupilsightPersonID')
            ->where('pupilsightTemplate.type = "Email"');

        return $this->runQuery($query, $criteria,TRUE );
    }

    public function getAllSmsTemplate(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightTemplate')
            ->cols([
                'pupilsightTemplate.*','pupilsightPerson.officialName as createdBy'
            ])
            ->leftJoin('pupilsightPerson', 'pupilsightTemplate.created_by=pupilsightPerson.pupilsightPersonID')
            ->where('pupilsightTemplate.type = "Sms"');

        return $this->runQuery($query, $criteria,TRUE );
    } 

     public function getAllSmsTemplateForAttendance(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightTemplateForAttendance')
            ->cols([
                'pupilsightTemplateForAttendance.*','pupilsightPerson.officialName as createdBy'
            ])
            ->leftJoin('pupilsightPerson', 'pupilsightTemplateForAttendance.created_by=pupilsightPerson.pupilsightPersonID')
            ->where('pupilsightTemplateForAttendance.type = "Sms"');

        return $this->runQuery($query, $criteria,TRUE );
    } 

    public function getLeaveReason(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightLeaveReason')
            ->cols([
                'pupilsightLeaveReason.*'
            ])
            ->orderby(['pupilsightLeaveReason.id DESC']);

        return $this->runQuery($query, $criteria,TRUE );
    }

    public function getTCTemplate(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightDocTemplate')
            ->cols([
                'pupilsightDocTemplate.*','pupilsightProgram.name as progname'
            ])
            ->leftJoin('pupilsightProgram', 'pupilsightDocTemplate.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->where('pupilsightDocTemplate.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'"')
            ->orderby(['pupilsightDocTemplate.id DESC']);

        return $this->runQuery($query, $criteria,TRUE );
    }

 
  }