<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Timetable;

use Pupilsight\Domain\DBQuery;
use Pupilsight\Domain\Gateway;

/**
 * @version v16
 * @since   v16
 */
class TimetableGateway extends Gateway
{
    public function selectTimetablesBySchoolYear($pupilsightSchoolYearID) 
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightTTID, pupilsightTT.pupilsightSchoolYearID, pupilsightTT.name, pupilsightTT.nameShort, pupilsightTT.active, pupilsightYearGroup.nameShort as yearGroups, GROUP_CONCAT(pupilsightRollGroup.nameShort ORDER BY pupilsightRollGroupID ASC SEPARATOR ', ') as sections
                FROM pupilsightTT 
                LEFT JOIN pupilsightYearGroup ON pupilsightYearGroup.pupilsightYearGroupID = pupilsightTT.pupilsightYearGroupIDList
                LEFT JOIN pupilsightRollGroup ON (FIND_IN_SET(pupilsightRollGroup.pupilsightRollGroupID, pupilsightTT.pupilsightRollGroupIDList))
                WHERE pupilsightTT.pupilsightSchoolYearID=:pupilsightSchoolYearID 
                GROUP BY pupilsightTT.pupilsightTTID
                ORDER BY pupilsightTT.name";

        return $this->db()->select($sql, $data);
    }

    public function getNonTimetabledYearGroups($pupilsightSchoolYearID, $pupilsightTTID = null)
    {
        $db = new DBQuery();
        $classes = $db->select("select * from pupilsightYearGroup");
        foreach($classes as $cls){
            $sqlroll = $db->select('SELECT DISTINCT a.pupilsightRollGroupID AS sections FROM pupilsightRollGroup AS a LEFT JOIN pupilsightProgramClassSectionMapping AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE b.pupilsightYearGroupID = "'.$cls['pupilsightYearGroupID'].'" ');
            if(!empty($sqlroll)){
                foreach($sqlroll as $sec){
                    if(!empty($pupilsightTTID)){
                        $sqlchk = $db->select('SELECT pupilsightTTID FROM pupilsightTT WHERE find_in_set("'.$sec['sections'].'",pupilsightRollGroupIDList) <> 0 AND pupilsightYearGroupIDList = "'.$cls['pupilsightYearGroupID'].'" AND pupilsightTTID != '.$pupilsightTTID.' ');
                    } else {
                        $sqlchk = $db->select('SELECT pupilsightTTID FROM pupilsightTT WHERE find_in_set("'.$sec['sections'].'",pupilsightRollGroupIDList) <> 0 AND pupilsightYearGroupIDList = "'.$cls['pupilsightYearGroupID'].'" ');
                    }
                    
                    if(empty($sqlchk->data[0]['pupilsightTTID'])){
                        $classIds[$cls['pupilsightYearGroupID']] = $cls['name'];
                    } 
                }
            } else {
                $classIds[$cls['pupilsightYearGroupID']] = $cls['name'];
            }
        }
        return $classIds;

        // echo '<pre>';
        // print_r($classIds);
        // echo '</pre>';
        //  die();
       
        // $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightTTID' => $pupilsightTTID);
        // $sql = "SELECT pupilsightYearGroup.pupilsightYearGroupID, pupilsightYearGroup.name
        //         FROM pupilsightYearGroup
        //         LEFT JOIN pupilsightTT ON (FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightTT.pupilsightYearGroupIDList) AND pupilsightTT.pupilsightSchoolYearID=:pupilsightSchoolYearID AND (pupilsightTT.active='Y' OR pupilsightTT.pupilsightTTID=:pupilsightTTID))
        //         WHERE pupilsightTT.pupilsightTTID IS NULL OR pupilsightTT.pupilsightTTID=:pupilsightTTID
        //         ORDER BY pupilsightYearGroup.sequenceNumber";

        // return $this->db()->select($sql, $data)->fetchKeyPair();
    }

    public function getTTByID($pupilsightTTID)
    {
        $data = array('pupilsightTTID' => $pupilsightTTID);
        $sql = "SELECT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShort, pupilsightTT.nameShortDisplay, pupilsightTT.active, pupilsightTT.pupilsightYearGroupIDList,pupilsightTT.pupilsightProgramID, pupilsightTT.pupilsightRollGroupIDList, pupilsightSchoolYear.name as schoolYear
                FROM pupilsightTT 
                JOIN pupilsightSchoolYear ON (pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightTT.pupilsightSchoolYearID)
                WHERE pupilsightTT.pupilsightTTID=:pupilsightTTID";

        return $this->db()->selectOne($sql, $data);
    }
}
