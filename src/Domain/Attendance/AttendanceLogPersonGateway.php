<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Attendance;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;
use Pupilsight\Domain\DBQuery;

/**
 * @version v18
 * @since   v18
 */
class AttendanceLogPersonGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightAttendanceLogPerson';
    private static $primaryKey = 'pupilsightAttendanceLogPersonID';

    private static $searchableColumns = [''];

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryByPersonAndDate(QueryCriteria $criteria, $pupilsightPersonID, $date)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightAttendanceLogPersonID', 'pupilsightAttendanceLogPerson.direction', 'pupilsightAttendanceLogPerson.type', 'pupilsightAttendanceLogPerson.reason', 'pupilsightAttendanceLogPerson.context', 'pupilsightAttendanceLogPerson.comment', 'pupilsightAttendanceLogPerson.timestampTaken', 'pupilsightAttendanceLogPerson.pupilsightCourseClassID', 'takenBy.title', 'takenBy.preferredName', 'takenBy.surname', 'pupilsightCourseClass.nameShort as className', 'pupilsightCourse.nameShort as courseName', 'pupilsightAttendanceCode.scope'
            ])
            ->innerJoin('pupilsightPerson as takenBy', 'pupilsightAttendanceLogPerson.pupilsightPersonIDTaker=takenBy.pupilsightPersonID')
            ->leftJoin('pupilsightAttendanceCode', 'pupilsightAttendanceCode.pupilsightAttendanceCodeID=pupilsightAttendanceLogPerson.pupilsightAttendanceCodeID')
            ->leftJoin('pupilsightCourseClass', 'pupilsightAttendanceLogPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID')
            ->leftJoin('pupilsightCourse', 'pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID')
            ->where('pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->where('pupilsightAttendanceLogPerson.date=:date')
            ->bindValue('date', $date);

        $criteria->addFilterRules([
            'notClass' => function ($query, $context) {
                return $query->where('NOT pupilsightAttendanceLogPerson.context="Class"');
            },
        ]);
        return $this->runQuery($query, $criteria);
    }
    public function queryByPersonAndDateNew(QueryCriteria $criteria, $pupilsightPersonID, $date)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightAttendanceLogPersonID', 'pupilsightAttendanceLogPerson.direction', 'pupilsightAttendanceLogPerson.type', 'attn_session_settings.session_name as session_name', 'pupilsightAttendanceLogPerson.reason', 'pupilsightAttendanceLogPerson.context', 'pupilsightAttendanceLogPerson.comment', 'pupilsightAttendanceLogPerson.timestampTaken', 'pupilsightAttendanceLogPerson.pupilsightCourseClassID', 'takenBy.title', 'takenBy.preferredName', 'takenBy.surname', 'pupilsightCourseClass.nameShort as className', 'pupilsightCourse.nameShort as courseName', 'pupilsightAttendanceCode.scope'
            ])
            ->innerJoin('pupilsightPerson as takenBy', 'pupilsightAttendanceLogPerson.pupilsightPersonIDTaker=takenBy.pupilsightPersonID')
            ->leftJoin('pupilsightAttendanceCode', 'pupilsightAttendanceCode.pupilsightAttendanceCodeID=pupilsightAttendanceLogPerson.pupilsightAttendanceCodeID')
            ->leftJoin('attn_session_settings', 'pupilsightAttendanceLogPerson.session_no=attn_session_settings.session_no')
            ->leftJoin('pupilsightCourseClass', 'pupilsightAttendanceLogPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID')
            ->leftJoin('pupilsightCourse', 'pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID')
            ->where('pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->where('pupilsightAttendanceLogPerson.date=:date')
            ->bindValue('date', $date);

        $criteria->addFilterRules([
            'notClass' => function ($query, $context) {
                return $query->where('NOT pupilsightAttendanceLogPerson.context="Class"');
            },
        ]);
        $query->groupBy(['attn_session_settings.session_no']);
        return $this->runQuery($query, $criteria);
    }


    public function attendanceNotTaken()
    {
        $sq = "select count(direction) as direction from pupilsightAttendanceLogPerson where pupilsightPersonID = '" . $pupilsightPersonID . "' and direction='" . $direction . "'";
        $db = new DBQuery();
        $rs = $db->selectRaw($sq);
        return $rs[0]["direction"];
    }


    public function getUserLog($dt)
    {

        $sq = "select p.admission_no, l.pupilsightPersonID, p.officialName  from pupilsightAttendanceLogPerson as l, pupilsightPerson as p where p.pupilsightPersonID = l.pupilsightPersonID group by l.pupilsightPersonID ";
        //print_r($sq);
        $flag = FALSE;
        if ($dt) {
            $flag = TRUE;
            $start_date = $dt["start_date"]; // => 01/08/2018 
            $end_date = $dt["end_date"]; // => 13/04/2020 
            $pupilsightYearGroupID = $dt["pupilsightYearGroupID"]; // => 001  // class
            $pupilsightRollGroupID = $dt["pupilsightRollGroupID"]; // => 00143 //section
            $pupilsightDepartmentID = $dt["pupilsightDepartmentID"]; // => 0005  //subject
            $minpercentage = $dt["minpercentage"]; // => 1 
            $maxpercentage = $dt["maxpercentage"]; // => 10

            $sd = explode('/', $start_date);
            $sd = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));

            $ed = explode('/', $end_date);
            $ed = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));


            $sq = "select p.admission_no,l.pupilsightPersonID, p.officialName  from pupilsightAttendanceLogPerson as l, ";
            $sq .= "pupilsightPerson as p, pupilsightStudentEnrolment as s ";
            $sq .= "where p.pupilsightPersonID = l.pupilsightPersonID ";
            $sq .= "and s.pupilsightPersonID = l.pupilsightPersonID ";
            $sq .= "and l.date >= '" . $sd . "' and l.date <= '" . $ed . "' ";
            $sq .= "and pupilsightYearGroupID = '" . $pupilsightYearGroupID . "' ";
            $sq .= "and pupilsightRollGroupID = '" . $pupilsightRollGroupID . "' ";
            /*if(!empty(!$pupilsightDepartmentID)){
                $sq .= "and pupilsightRollGroupID = '".$pupilsightRollGroupID."' ";
            }*/
            $sq .= "group by l.pupilsightPersonID ";
            //echo $sq;
        }
        // echo $sq;
        $db = new DBQuery();
        $rs = $db->selectRaw($sq, TRUE);
        if (empty($rs)) {
            $dsempty = array();
            return $db->convertDataset($dsempty);
        }

        $len = count($rs);
        $i = 0;
        while ($i < $len) {
            $present = $this->getLogCount("In", $rs[$i]["pupilsightPersonID"]);
            $absent = $this->getLogCount("Out", $rs[$i]["pupilsightPersonID"]);
            $total = $present + $absent;

            $rs[$i]["student_id"] = $rs[$i]["pupilsightPersonID"];
            $rs[$i]["total"] = $total;
            $rs[$i]["present"] = $present;

            if ($total == $present) {
                $perPresent = 100;
            } else {
                $perPresent = number_format(($present / $total) * 100, 2);
            }
            if ($flag) {

                if (($perPresent >= $minpercentage) && ($perPresent <= $maxpercentage)) {
                    $rs[$i]["percentage"] = $perPresent;
                } else {
                    $rs[$i]["percentage"] = "";
                }
            } else {
                $rs[$i]["percentage"] = $perPresent;
            }
            $i++;
        }
        return $db->convertDataset($rs);
    }

    public function getLogCount($direction, $pupilsightPersonID)
    {
        $sq = "select count(direction) as direction from pupilsightAttendanceLogPerson where pupilsightPersonID = '" . $pupilsightPersonID . "' and direction='" . $direction . "'";
        $db = new DBQuery();
        $rs = $db->selectRaw($sq);
        return $rs[0]["direction"];
    }

    public function queryClassAttendanceByPersonAndDate(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightPersonID, $date)
    {
        $subSelect = $this
            ->newSelect()
            ->from('pupilsightTTDayRowClass')
            ->cols(['pupilsightTTColumnRow.name as period', 'pupilsightTTColumnRow.timeStart', 'pupilsightTTColumnRow.timeEnd', 'pupilsightTTDayDate.date', 'pupilsightTTDayRowClass.pupilsightCourseClassID', 'pupilsightTTDayRowClass.pupilsightTTDayRowClassID'])
            ->innerJoin('pupilsightTTColumnRow', 'pupilsightTTColumnRow.pupilsightTTColumnRowID=pupilsightTTDayRowClass.pupilsightTTColumnRowID')
            ->innerJoin('pupilsightTTDay', 'pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID AND pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID')
            ->innerJoin('pupilsightTTDayDate', 'pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID')
            ->where('pupilsightTTDayDate.date=:date')
            ->bindValue('date', $date);

        $query = $this
            ->newQuery()
            ->from('pupilsightCourseClassPerson')
            ->cols([
                'pupilsightAttendanceLogPersonID', 'pupilsightAttendanceLogPerson.direction', 'pupilsightAttendanceLogPerson.type', 'pupilsightAttendanceLogPerson.reason',  "'Class' as context", 'pupilsightAttendanceLogPerson.comment', 'pupilsightAttendanceLogPerson.timestampTaken', 'takenBy.title', 'takenBy.preferredName', 'takenBy.surname',
                'pupilsightCourseClass.pupilsightCourseClassID', 'pupilsightCourseClass.nameShort as className', 'pupilsightCourse.nameShort as courseName',
                'timetable.period', '(CASE WHEN timetable.timeStart IS NOT NULL THEN timetable.timeStart ELSE pupilsightAttendanceLogPerson.timestampTaken END) as timeStart', '(CASE WHEN timetable.timeEnd IS NOT NULL THEN timetable.timeEnd ELSE pupilsightAttendanceLogPerson.timestampTaken END) as timeEnd', 'pupilsightAttendanceCode.scope'
            ])
            ->innerJoin('pupilsightCourseClass', 'pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID')
            ->innerJoin('pupilsightCourse', 'pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID')
            ->leftJoin('pupilsightAttendanceLogPerson', "pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID
                AND pupilsightAttendanceLogPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID
                AND pupilsightAttendanceLogPerson.date=:date
                AND pupilsightAttendanceLogPerson.context = 'Class'")
            ->leftJoin('pupilsightAttendanceCode', 'pupilsightAttendanceCode.pupilsightAttendanceCodeID=pupilsightAttendanceLogPerson.pupilsightAttendanceCodeID')
            ->leftJoin('pupilsightPerson as takenBy', 'pupilsightAttendanceLogPerson.pupilsightPersonIDTaker=takenBy.pupilsightPersonID')
            ->joinSubSelect('LEFT', $subSelect, 'timetable', '(timetable.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND timetable.date=:date)')
            ->where("pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID")
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->where("pupilsightCourseClassPerson.role = 'Student'")
            ->where("pupilsightCourseClass.attendance='Y'")
            ->where('pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where('NOT (pupilsightAttendanceLogPerson.pupilsightAttendanceLogPersonID IS NULL AND timetable.pupilsightCourseClassID IS NULL)')
            ->bindValue('date', $date)
            ->groupBy(['pupilsightAttendanceLogPerson.pupilsightAttendanceLogPersonID', 'timetable.pupilsightTTDayRowClassID']);

        return $this->runQuery($query, $criteria);
    }

    public function selectAllAttendanceLogsByPerson($pupilsightSchoolYearID, $pupilsightPersonID, $countClassAsSchool)
    {
        $query = $this
            ->newSelect()
            ->from('pupilsightSchoolYear')
            ->cols([
                'pupilsightAttendanceLogPerson.date as groupBy', 'pupilsightAttendanceLogPerson.date', 'pupilsightAttendanceLogPerson.type', 'pupilsightAttendanceLogPerson.reason', 'pupilsightAttendanceLogPerson.timestampTaken', 'pupilsightAttendanceCode.nameShort as code', 'pupilsightAttendanceCode.direction', 'pupilsightAttendanceCode.scope', 'pupilsightAttendanceLogPerson.context', "(CASE WHEN pupilsightCourse.pupilsightCourseID IS NOT NULL THEN CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) END) as contextName",
            ])
            ->innerJoin('pupilsightAttendanceLogPerson', 'pupilsightAttendanceLogPerson.date >= firstDay AND pupilsightAttendanceLogPerson.date <= lastDay')
            ->innerJoin('pupilsightAttendanceCode', 'pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name')
            ->leftJoin('pupilsightCourseClass', "pupilsightCourseClass.pupilsightCourseClassID=pupilsightAttendanceLogPerson.pupilsightCourseClassID AND pupilsightAttendanceLogPerson.context='Class'")
            ->leftJoin('pupilsightCourse', 'pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID')
            ->where('pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where('pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID')
            ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->orderBy(['timestampTaken ASC']);

        if ($countClassAsSchool == 'N') {
            $query->where("NOT pupilsightAttendanceLogPerson.context='Class'");
        }

        return $this->runSelect($query);
    }

    public function queryAttendanceCountsByType($criteria, $pupilsightSchoolYearID, $rollGroups, $dateStart, $dateEnd, $countClassAsSchool)
    {
        $subSelect = $this
            ->newSelect()
            ->from('pupilsightAttendanceLogPerson')
            ->cols(['pupilsightPersonID', 'date', 'MAX(timestampTaken) as maxTimestamp', 'context'])
            ->where("date>=:dateStart AND date<=:dateEnd")
            ->groupBy(['pupilsightPersonID', 'date']);

        if ($countClassAsSchool == 'N') {
            $subSelect->where("context <> 'Class'");
        }

        $query = $this
            ->newQuery()
            ->from('pupilsightAttendanceLogPerson')
            ->cols([
                'pupilsightAttendanceCode.name', 'pupilsightAttendanceLogPerson.reason', 'count(DISTINCT pupilsightAttendanceLogPerson.pupilsightPersonID) as count', 'pupilsightAttendanceLogPerson.date'
            ])
            ->innerJoin('pupilsightAttendanceCode', 'pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name')
            ->joinSubSelect(
                'INNER',
                $subSelect,
                'log',
                'pupilsightAttendanceLogPerson.pupilsightPersonID=log.pupilsightPersonID AND pupilsightAttendanceLogPerson.date=log.date'
            )
            ->where('pupilsightAttendanceLogPerson.timestampTaken=log.maxTimestamp')
            ->where('pupilsightAttendanceLogPerson.date>=:dateStart')
            ->bindValue('dateStart', $dateStart)
            ->where('pupilsightAttendanceLogPerson.date<=:dateEnd')
            ->bindValue('dateEnd', $dateEnd)
            ->groupBy(['pupilsightAttendanceLogPerson.date', 'pupilsightAttendanceCode.name', 'pupilsightAttendanceLogPerson.reason'])
            ->orderBy(['pupilsightAttendanceLogPerson.date', 'pupilsightAttendanceCode.direction DESC', 'pupilsightAttendanceCode.name']);

        if ($countClassAsSchool == 'N') {
            $query->where("pupilsightAttendanceLogPerson.context <> 'Class'");
        }

        if ($rollGroups != array('all')) {
            $query
                ->innerJoin('pupilsightStudentEnrolment', 'pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->where('FIND_IN_SET(pupilsightStudentEnrolment.pupilsightRollGroupID, :rollGroups)')
                ->bindValue('rollGroups', implode(',', $rollGroups))
                ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID')
                ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
        }

        return $this->runQuery($query, $criteria);
    }

    ////SELECT * FROM `pupilsightAttendanceBlocked` WHERE 1,`pupilsightAttendanceBlockID`,`pupilsightRollGroupID`,`pupilsightYearGroupID`,`name`,`type`,`start_date`,`end_date`,`remark`,`pupilsightPersonIDTaker`,`timestampTaken`

    public function selectBlockedAttendanceLogs($criteria, $pupilsightYearGroup, $pupilsightRollGroupID, $sdate, $edate)
    {

        $query = $this
            ->newSelect()
            ->from('pupilsightAttendanceBlocked')
            ->cols([
                'pupilsightAttendanceBlocked.*', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup'
            ])
            ->leftJoin('pupilsightYearGroup', 'pupilsightAttendanceBlocked.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightAttendanceBlocked.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID');
        if (!empty($pupilsightYearGroupID)) {
            $query->where('pupilsightAttendanceBlocked.pupilsightYearGroupID IN ( ' . implode(',', $pupilsightYearGroup) . ' )');
        }
        if (!empty($pupilsightRollGroupID)) {
            $query->where('pupilsightAttendanceBlocked.pupilsightRollGroupID IN ( ' . implode(',', $pupilsightRollGroupID) . ' ) ');
        }
        if (!empty($sdate) && !empty($edate)) {
            $query->where('pupilsightAttendanceBlocked.start_date BETWEEN "' . $sdate . '" AND "' . $edate . '" ');
        }
        // AND pupilsightAttendanceBlocked.pupilsightRollGroupID="' . $pupilsightRollGroupID . '"
        /*  ->where('pupilsightAttendanceBlocked.pupilsightYearGroupID=:pupilsightYearGroupID')
            ->bindValue('pupilsightYearGroupID', $pupilsightYearGroupID)
           ->where('pupilsightAttendanceBlocked.pupilsightRollGroupID=:pupilsightRollGroupID')
            ->bindValue('pupilsightRollGroupID', $pupilsightRollGroupID)
           ->where('pupilsightAttendanceBlocked.start_date=:date OR pupilsightAttendanceBlocked.end_date=:date ')
            ->bindValue('date', $date)
         */
        $query->orderBy(['pupilsightAttendanceBlocked.timestampTaken ASC']);
        // echo $query;
        // die();
        return $this->runQuery($query, $criteria, TRUE);
    }
    public function selectBlockedAttendanceLogsAll($criteria)
    {
        // echo $date;
        $query = $this
            ->newSelect()
            ->from('pupilsightAttendanceBlocked')
            ->cols([
                'pupilsightAttendanceBlocked.*', 'pupilsightYearGroup.nameShort AS yearGroup', "GROUP_CONCAT(DISTINCT pupilsightRollGroup.nameShort SEPARATOR ', ') as rollGroup", 'pupilsightRollGroup.nameShort AS rollGroup1'
            ])
            ->leftJoin('pupilsightYearGroup', 'pupilsightAttendanceBlocked.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightAttendanceBlocked.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')

            ->groupBy(['pupilsightAttendanceBlocked.pupilsightYearGroupID', 'pupilsightAttendanceBlocked.start_date', 'pupilsightAttendanceBlocked.start_date'])
            ->orderBy(['pupilsightAttendanceBlocked.timestampTaken ASC']);


        return $this->runQuery($query, $criteria, TRUE);
    }


    public function select_attendance_not_taken1($criteria, $pupilsightYearGroupID, $pupilsightRollGroupID, $from_date, $to_date)
    {


        $query = $this
            ->newSelect()
            ->from('pupilsightAttendanceLogPerson')
            ->cols([
                'pupilsightAttendanceLogPerson.*', 'pupilsightTTDayDate.date'
            ])
            ->leftJoin('pupilsightTTDayDate', 'pupilsightAttendanceLogPerson.date=pupilsightTTDayDate.date')



            ->orderBy(['pupilsightAttendanceLogPerson.timestampTaken ASC'])
            ->orderBy(['pupilsightTTDayDate.date']);


        return $this->runQuery($query, $criteria, TRUE);








        /* $subSelect = $this
            ->newSelect()
            ->from('pupilsightAttendanceLogPerson')
            ->cols(['pupilsightPersonID', 'date', 'MAX(timestampTaken) as maxTimestamp', 'context'])
            ->where("date>=:dateStart AND date<=:dateEnd")
            ->groupBy(['pupilsightPersonID', 'date']);

        if ($countClassAsSchool == 'N') {
            $subSelect->where("context <> 'Class'");
        }

        $query = $this
            ->newQuery()
            ->from('pupilsightAttendanceLogPerson')
            ->cols([
                'pupilsightAttendanceCode.name', 'pupilsightAttendanceLogPerson.reason', 'count(DISTINCT pupilsightAttendanceLogPerson.pupilsightPersonID) as count', 'pupilsightAttendanceLogPerson.date'
            ])
            ->innerJoin('pupilsightAttendanceCode', 'pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name')
            ->joinSubSelect(
                'INNER',
                $subSelect,
                'log',
                'pupilsightAttendanceLogPerson.pupilsightPersonID=log.pupilsightPersonID AND pupilsightAttendanceLogPerson.date=log.date'
            )
            ->where('pupilsightAttendanceLogPerson.timestampTaken=log.maxTimestamp')
            ->where('pupilsightAttendanceLogPerson.date>=:dateStart')
            ->bindValue('dateStart', $dateStart)
            ->where('pupilsightAttendanceLogPerson.date<=:dateEnd')
            ->bindValue('dateEnd', $dateEnd)
            ->groupBy(['pupilsightAttendanceLogPerson.date', 'pupilsightAttendanceCode.name', 'pupilsightAttendanceLogPerson.reason'])
            ->orderBy(['pupilsightAttendanceLogPerson.date', 'pupilsightAttendanceCode.direction DESC', 'pupilsightAttendanceCode.name']);

        if ($countClassAsSchool == 'N') {
            $query->where("pupilsightAttendanceLogPerson.context <> 'Class'");
        }

        if ($rollGroups != array('all')) {
            $query
                ->innerJoin('pupilsightStudentEnrolment', 'pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->where('FIND_IN_SET(pupilsightStudentEnrolment.pupilsightRollGroupID, :rollGroups)')
                ->bindValue('rollGroups', implode(',', $rollGroups))
                ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID')
                ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
        }

        return $this->runQuery($query, $criteria);
        */
    }

    /*
    public function select_attendance_not_taken(QueryCriteria $criteria,  $pupilsightYearGroupID,$pupilsightRollGroupID,$from_date,$to_date)
    {

        echo $from_date;
        $subSelect = $this
            ->newSelect()
            ->from('pupilsightTTDayRowClass')
            ->cols(['pupilsightTTColumnRow.name as period', 'pupilsightTTColumnRow.timeStart', 'pupilsightTTColumnRow.timeEnd', 'pupilsightTTDayDate.date', 'pupilsightTTDayRowClass.pupilsightCourseClassID', 'pupilsightTTDayRowClass.pupilsightTTDayRowClassID'])
            ->innerJoin('pupilsightTTColumnRow', 'pupilsightTTColumnRow.pupilsightTTColumnRowID=pupilsightTTDayRowClass.pupilsightTTColumnRowID')
            ->innerJoin('pupilsightTTDay', 'pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID AND pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID')
            ->innerJoin('pupilsightTTDayDate', 'pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID')
            ->where('pupilsightTTDayDate.date>=:dateStart')
            ->bindValue('dateStart', $from_date)
            ->where('pupilsightTTDayDate.date<=:dateEnd ORDER BY pupilsightTTDayDate.date')
            ->bindValue('dateEnd', $to_date);
            

        $query = $this
            ->newQuery()
            ->from('pupilsightCourseClassPerson')
            ->cols([
                'pupilsightAttendanceLogPersonID', 'pupilsightAttendanceLogPerson.direction', 'pupilsightAttendanceLogPerson.type', 'pupilsightAttendanceLogPerson.reason',  "'Class' as context", 'pupilsightAttendanceLogPerson.comment', 'pupilsightAttendanceLogPerson.timestampTaken', 'takenBy.title', 'takenBy.preferredName', 'takenBy.surname',
                'pupilsightCourseClass.pupilsightCourseClassID', 'pupilsightCourseClass.nameShort as className', 'pupilsightCourse.nameShort as courseName',
                'timetable.period', '(CASE WHEN timetable.timeStart IS NOT NULL THEN timetable.timeStart ELSE pupilsightAttendanceLogPerson.timestampTaken END) as timeStart', '(CASE WHEN timetable.timeEnd IS NOT NULL THEN timetable.timeEnd ELSE pupilsightAttendanceLogPerson.timestampTaken END) as timeEnd', 'pupilsightAttendanceCode.scope'
            ])
            ->innerJoin('pupilsightCourseClass', 'pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID')
            ->innerJoin('pupilsightCourse', 'pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID')
            ->leftJoin('pupilsightAttendanceLogPerson', "pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID
                AND pupilsightAttendanceLogPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID
                AND pupilsightAttendanceLogPerson.date=:date
                AND pupilsightAttendanceLogPerson.context = 'Class'")
            ->leftJoin('pupilsightAttendanceCode', 'pupilsightAttendanceCode.pupilsightAttendanceCodeID=pupilsightAttendanceLogPerson.pupilsightAttendanceCodeID')
            ->leftJoin('pupilsightPerson as takenBy', 'pupilsightAttendanceLogPerson.pupilsightPersonIDTaker=takenBy.pupilsightPersonID')
            ->joinSubSelect('LEFT', $subSelect, 'timetable', '(timetable.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND timetable.date>=:dateStart )')
           // ->where("pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID")
          //  ->bindValue('pupilsightPersonID', $pupilsightPersonID)
            ->where("pupilsightCourseClassPerson.role = 'Teacher'")
            ->where("pupilsightCourseClass.attendance='Y'")
           
            ->where('NOT (pupilsightAttendanceLogPerson.pupilsightAttendanceLogPersonID IS NULL AND timetable.pupilsightCourseClassID IS NULL)')
            ->bindValue('date', $from_date)
            ->groupBy(['pupilsightAttendanceLogPerson.pupilsightAttendanceLogPersonID', 'timetable.pupilsightTTDayRowClassID']);

        return $this->runQuery($query, $criteria);
    }*/
}
