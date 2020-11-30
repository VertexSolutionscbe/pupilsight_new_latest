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

            $res = $this->runQuery($query, $criteria, TRUE);
            $data = $res->data;
            if(!empty($data)){
                foreach($data as $k=>$d){
                    $clsid = $d['classIds'];
                    if(!empty($clsid)){
                        $query2 = $this
                            ->newQuery()
                            ->from('pupilsightYearGroup')
                            ->cols([
                                'GROUP_CONCAT(pupilsightYearGroup.name) as clsnames','pupilsightYearGroup.pupilsightYearGroupID as id'
                            ])
                            ->where('pupilsightYearGroup.pupilsightYearGroupID IN ('.$clsid.') ')
                            ->orderby(['pupilsightYearGroup.pupilsightYearGroupID DESC']);
                        // echo $query2;
                        // die();
                            $newdata = $this->runQuery($query2, $criteria);
                            if(!empty($newdata->data[0]['id'])){
                                $data[$k]['classes'] = $newdata->data[0]['clsnames'];
                            } else {
                                $data[$k]['classes'] = '';
                            }
                    } else {
                        $data[$k]['classes'] = '';
                    }
                }
               
            }
            // echo '<pre>';
            // print_r($data);
            // echo '</pre>';
            // die();
            $res->data = $data;
            return $res;
    }

    public function getSeries(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {

        $query = $this
            ->newQuery()
            ->from('fn_fee_series')
            ->cols([
                'fn_fee_series.*', 'pupilsightSchoolYear.name AS acedemic_year', 'COUNT(a.id) as invkount', 'COUNT(b.id) as reckount','pupilsightProgram.name as progname'
            ])
            ->leftJoin('pupilsightSchoolYear', 'fn_fee_series.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('fn_fee_invoice AS a', 'fn_fee_series.id=a.inv_fn_fee_series_id')
            ->leftJoin('fn_fee_invoice AS b', 'fn_fee_series.id=b.rec_fn_fee_series_id')
            ->leftJoin('pupilsightProgram', 'fn_fee_series.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->where('fn_fee_series.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
            ->where('fn_fee_series.type != "Finance" ')
            ->where('fn_fee_series.type != "Application" ')
            ->groupBy(['fn_fee_series.id']);
        //return $this->runQuery($query, $criteria,TRUE );

        //return $this->runQuery($query, $criteria, TRUE);

            $res = $this->runQuery($query, $criteria, TRUE);
            $data = $res->data;
            if(!empty($data)){
                foreach($data as $k=>$d){
                    $clsid = $d['classIds'];
                    if(!empty($clsid)){
                        $query2 = $this
                            ->newQuery()
                            ->from('pupilsightYearGroup')
                            ->cols([
                                'GROUP_CONCAT(pupilsightYearGroup.name) as clsnames','pupilsightYearGroup.pupilsightYearGroupID as id'
                            ])
                            ->where('pupilsightYearGroup.pupilsightYearGroupID IN ('.$clsid.') ')
                            ->orderby(['pupilsightYearGroup.pupilsightYearGroupID DESC']);
                        // echo $query2;
                        // die();
                            $newdata = $this->runQuery($query2, $criteria);
                            if(!empty($newdata->data[0]['id'])){
                                $data[$k]['classes'] = $newdata->data[0]['clsnames'];
                            } else {
                                $data[$k]['classes'] = '';
                            }
                    } else {
                        $data[$k]['classes'] = '';
                    }
                }
               
            }
            // echo '<pre>';
            // print_r($data);
            // echo '</pre>';
            // die();
            $res->data = $data;
            return $res;
    }

 
  }