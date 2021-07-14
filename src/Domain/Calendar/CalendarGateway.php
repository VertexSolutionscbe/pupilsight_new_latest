<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Calendar;

use Exception;
use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;



/**
 * Archive Gateway
 *
 * @version v17
 * @since   v17
 */
class CalendarGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'calendar_event_type';

    public function listEventType($con)
    {
        $sq = "SELECT * FROM calendar_event_type  order by id desc ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function listEvent($con)
    {
        $sq = "SELECT e.*, et.title as event_type_title FROM calendar_event as e, calendar_event_type as et ";
        $sq .= "where e.event_type_id = et.id ";
        $sq .= "order by e.id desc ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function getMyEvent($con, $roleid, $uid, $schoolYearID, $parentID = NULL)
    {
        $sq = $this->_getRoleEventQuery($con, $roleid, $uid, $schoolYearID, $parentID);
        if ($sq) {
            $result = $con->query($sq);
            return $result->fetchAll();
        }
        return "";
    }

    //get role based events query
    public function _getRoleEventQuery($con, $roleid, $uid, $schoolYearID, $parentID = NULL)
    {
        $sq = "";
        if ($roleid == 1) {
            //admin
            $sq = $this->_getAdminEventQuery();
        } else if ($roleid == 2) {
            //class teacher
            $sq = $this->_getTeacherEventQuery($con, $uid, $schoolYearID);
        } else if ($roleid == 3) {
            //student
            $sq = $this->_getStudentEventQuery($con, $uid, $schoolYearID);
        } else if ($roleid == 4) {
            //parents
            $sq = $this->_getStudentEventQuery($con, $uid, $schoolYearID, $parentID);
        } else {
            //FOR STAFF
            $sq = $this->_getStaffEventQuery($uid);
        }
        return $sq;
    }

    public function _getAdminEventQuery()
    {
        $sq = "SELECT e.*, et.title as event_type_title, et.color FROM calendar_event as e, calendar_event_type as et ";
        $sq .= "where e.event_type_id = et.id and e.is_publish=2 ";
        $sq .= "order by e.id desc ";
        return $sq;
    }

    public function _getStaffEventQuery($uid)
    {
        $sq = 'SELECT e.*, et.title as event_type_title, et.color FROM calendar_event as e ';
        $sq .= 'left join calendar_event_type as et on e.event_type_id = et.id ';
        $sq .= 'left join calendar_event_share as es on e.event_type_id = es.calendar_event_id ';
        $sq .= ' where  ';
        $sq .= " es.uid='" . $uid . "' or (e.tagid in('all','all_staff') ";
        return $sq;
    }

    public function _getTeacherEventQuery($con, $uid, $schoolYearID)
    {
        $sq1 = "select pupilsightSchoolYearID, pupilsightProgramID, pupilsightYearGroupID, pupilsightRollGroupID from assign_class_teacher_section ";
        $sq1 .= "where pupilsightPersonID='" . $uid . "' ";
        $sq1 .= "and pupilsightSchoolYearID='" . $schoolYearID . "' ";
        $query2 = $con->query($sq1);
        $res2 = $query2->fetch();

        $schoolYearID = (int)$res2["pupilsightSchoolYearID"];
        $pupilsightProgramID = (int)$res2["pupilsightProgramID"];
        $yearGroupID = (int)$res2["pupilsightYearGroupID"];
        $rollGroupID = (int)$res2["pupilsightRollGroupID"];

        $schoolYear = $schoolYearID . "-" . $pupilsightProgramID . "-" . $yearGroupID . "-" . $rollGroupID;
        $sectionid =  $pupilsightProgramID . "-" . $yearGroupID . "-" . $rollGroupID;
        $classid = $pupilsightProgramID . "-" . $yearGroupID;
        $programID = $pupilsightProgramID;


        $sq = 'SELECT e.*, et.title as event_type_title, et.color FROM calendar_event as e ';
        $sq .= 'left join calendar_event_type as et on e.event_type_id = et.id ';
        $sq .= 'left join calendar_event_share as es on e.event_type_id = es.calendar_event_id ';
        $sq .= ' where  ';
        $sq .= " es.uid='" . $uid . "' or (e.tagid in('all','all_students','all_staff','" . $schoolYear . "','" . $sectionid . "','" . $classid . "','" . $programID . "')) ";
        return $sq;
    }


    public function _getStudentEventQuery($con, $uid, $schoolYearID, $parentID = NULL)
    {
        $sq1 = "select pupilsightSchoolYearID, pupilsightProgramID, pupilsightYearGroupID, pupilsightRollGroupID from pupilsightStudentEnrolment ";
        $sq1 .= "where pupilsightPersonID='" . $uid . "' ";
        $sq1 .= "and pupilsightSchoolYearID='" . $schoolYearID . "' ";
        $query2 = $con->query($sq1);
        $res2 = $query2->fetch();

        $schoolYearID = (int)$res2["pupilsightSchoolYearID"];
        $pupilsightProgramID = (int)$res2["pupilsightProgramID"];
        $yearGroupID = (int)$res2["pupilsightYearGroupID"];
        $rollGroupID = (int)$res2["pupilsightRollGroupID"];

        $schoolYear = $schoolYearID . "-" . $pupilsightProgramID . "-" . $yearGroupID . "-" . $rollGroupID;
        $sectionid =  $pupilsightProgramID . "-" . $yearGroupID . "-" . $rollGroupID;
        $classid = $pupilsightProgramID . "-" . $yearGroupID;
        $programID = $pupilsightProgramID;

        $sq = 'SELECT e.*, et.title as event_type_title, et.color FROM calendar_event as e ';
        $sq .= 'left join calendar_event_type as et on e.event_type_id = et.id ';
        $sq .= 'left join calendar_event_share as es on e.event_type_id = es.calendar_event_id ';
        $sq .= ' where  ';
        if (empty($parentID)) {
            $sq .= " es.uid='" . $uid . "' or (e.tagid in('all_students','all','" . $schoolYear . "','" . $sectionid . "','" . $classid . "','" . $programID . "')) ";
        } else {
            //for parent request
            $sq .= " es.uid='" . $uid . "' or es.uid='" . $parentID . "' or (e.tagid in('all','all_students','all_parents','" . $schoolYear . "','" . $sectionid . "','" . $classid . "','" . $programID . "')) ";
        }
        //echo $sq;
        return $sq;
    }

    public function getChildListForParent($connection2, $uid)
    {
        try {
            $sqlf = 'SELECT pupilsightFamilyID FROM pupilsightFamilyAdult WHERE pupilsightPersonID= ' . $uid . ' ';
            $resultf = $connection2->query($sqlf);
            $fdata = $resultf->fetch();
            $pupilsightFamilyID = $fdata['pupilsightFamilyID'];

            $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a 
        LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID 
        WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
            $res = $connection2->query($childs);
            return $res->fetchAll();
            //$students = $stuData[0];
            //$stuId = $students['pupilsightPersonID'];
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
        return "";
    }
}
