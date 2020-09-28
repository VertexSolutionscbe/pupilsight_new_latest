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
class TimetableDayGateway extends Gateway
{
    public function selectTTDaysByID($pupilsightTTID)
    {
        $data = array('pupilsightTTID' => $pupilsightTTID);
        $sql = "SELECT pupilsightTTDay.*, pupilsightTTColumn.name AS columnName 
                FROM pupilsightTTDay 
                JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) 
                WHERE pupilsightTTDay.pupilsightTTID=:pupilsightTTID";

        return $this->db()->select($sql, $data);
    }

    public function selectTTDayRowsByID($pupilsightTTDayID)
    {
        $data = array('pupilsightTTDayID' => $pupilsightTTDayID);
        $sql = "SELECT pupilsightTTColumnRow.*, COUNT(DISTINCT pupilsightTTDayRowClassID) AS classCount
                FROM pupilsightTTDay
                JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTDay.pupilsightTTColumnID)
                LEFT JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID AND pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID)
                WHERE pupilsightTTDay.pupilsightTTDayID=:pupilsightTTDayID 
                GROUP BY pupilsightTTColumnRow.pupilsightTTColumnRowID
                ORDER BY pupilsightTTColumnRow.timeStart, pupilsightTTColumnRow.name";

        return $this->db()->select($sql, $data);  
    }
   public function selectTTDayRowsByIDNew($pupilsightTTDayID)
    {

        $data = array('pupilsightTTDayID' => $pupilsightTTDayID);
        $sql = "SELECT pupilsightTTColumnRow.*, COUNT(DISTINCT pupilsightTTDayRowClassID) AS classCount
                FROM pupilsightTTDay
                JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTDay.pupilsightTTColumnID)
                LEFT JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID AND pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID)
                WHERE pupilsightTTDay.pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTTColumnRow.type='Lesson' GROUP BY pupilsightTTColumnRow.pupilsightTTColumnRowID
                ORDER BY pupilsightTTColumnRow.timeStart, pupilsightTTColumnRow.name";

        return $this->db()->select($sql, $data);  
    }
    public function selectTTDayRowClassesByID($pupilsightTTDayID, $pupilsightTTColumnRowID) {
        $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID);
       
        $sql = "SELECT pupilsightTTDayRowClassID,pupilsightStaffID,  pupilsightSpace.pupilsightSpaceID,pupilsightDepartment.name as subname, pupilsightSpace.name as location, pupilsightProgram.name as progName, pupilsightYearGroup.name as className FROM pupilsightTTDayRowClass 
                
                LEFT JOIN pupilsightSpace ON (pupilsightTTDayRowClass.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID)
                LEFT JOIN pupilsightDepartment ON (pupilsightTTDayRowClass.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID)
                
                LEFT JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID)
                LEFT JOIN pupilsightTT ON (pupilsightTTDay.pupilsightTTID=pupilsightTT.pupilsightTTID)

                LEFT JOIN pupilsightProgram ON (pupilsightTT.pupilsightProgramID=pupilsightProgram.pupilsightProgramID)
                LEFT JOIN pupilsightYearGroup ON (pupilsightTT.pupilsightYearGroupIDList=pupilsightYearGroup.pupilsightYearGroupID)
                
                WHERE pupilsightTTDayRowClass.pupilsightTTColumnRowID=:pupilsightTTColumnRowID 
                AND pupilsightTTDayRowClass.pupilsightTTDayID=:pupilsightTTDayID 
               ";

        return $this->db()->select($sql, $data);  
    }

    public function selectTTDayRowClassTeachersByIDNew($pupilsightStaffID) {
        $data = array('pupilsightStaffID' => $pupilsightStaffID);
        $sql = "SELECT GROUP_CONCAT(pupilsightPerson.officialName) AS staffName
                FROM pupilsightStaff 
                JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
                WHERE pupilsightStaff.pupilsightStaffID IN (".$pupilsightStaffID.") ";
        return $this->db()->select($sql, $data);  
    }

    public function selectTTDayRowClassTeachersByID($pupilsightTTDayRowClassID) {
        $data = array('pupilsightTTDayRowClassID' => $pupilsightTTDayRowClassID);
        $sql = "SELECT DISTINCT title, surname, preferredName, pupilsightTTDayRowClassException.pupilsightPersonID AS exception 
                FROM pupilsightPerson 
                JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
                JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) 
                JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) 
                LEFT JOIN pupilsightTTDayRowClassException ON (pupilsightTTDayRowClassException.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID 
                    AND pupilsightTTDayRowClassException.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
                WHERE pupilsightCourseClassPerson.role='Teacher' 
                AND pupilsightTTDayRowClass.pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID 
                AND pupilsightTTDayRowClassExceptionID IS NULL
                ORDER BY surname, preferredName";

        return $this->db()->select($sql, $data);  
    }

    public function selectTTDayRowClassExceptionsByID($pupilsightTTDayRowClassID) {
        $data = array('pupilsightTTDayRowClassID' => $pupilsightTTDayRowClassID);
        $sql = "SELECT pupilsightTTDayRowClassExceptionID, pupilsightPerson.pupilsightPersonID, surname, preferredName 
                FROM pupilsightTTDayRowClassException 
                JOIN pupilsightPerson ON (pupilsightTTDayRowClassException.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
                WHERE pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID 
                ORDER BY surname, preferredName";

        return $this->db()->select($sql, $data);  
    }

    public function getTTDayByID($pupilsightTTDayID)
    {
        $data = array('pupilsightTTDayID' => $pupilsightTTDayID);
        $sql = "SELECT pupilsightTT.pupilsightTTID, pupilsightSchoolYear.name AS schoolYear, pupilsightTT.name AS ttName, pupilsightTTDay.name, pupilsightTTDay.nameShort, pupilsightTTDay.color, pupilsightTTDay.fontColor, pupilsightTTColumn.pupilsightTTColumnID, pupilsightTTColumn.name AS columnName 
            FROM pupilsightTTDay 
            JOIN pupilsightTT ON (pupilsightTTDay.pupilsightTTID=pupilsightTT.pupilsightTTID) 
            JOIN pupilsightSchoolYear ON (pupilsightTT.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) 
            JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) 
            WHERE pupilsightTTDay.pupilsightTTDayID=:pupilsightTTDayID";

        return $this->db()->selectOne($sql, $data);
    }

    public function getTTDayRowByID($pupilsightTTDayID, $pupilsightTTColumnRowID)
    {
        $data = array('pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
        $sql = "SELECT pupilsightTT.name AS ttName, pupilsightTTDay.name AS dayName, pupilsightTTColumnRow.name AS rowName 
                FROM pupilsightTTDay 
                JOIN pupilsightTT ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) 
                JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) 
                JOIN pupilsightTTColumnRow ON (pupilsightTTColumn.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID) 
                WHERE pupilsightTTDay.pupilsightTTDayID=:pupilsightTTDayID 
                AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID";

        return $this->db()->selectOne($sql, $data);
    }

    public function getTTDayRowClassByID($pupilsightTTDayID, $pupilsightTTColumnRowID, $pupilsightCourseClassID)
    {
        $data = array('pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
        $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightTTDayRowClass.pupilsightTTDayRowClassID 
                FROM pupilsightTTDayRowClass 
                JOIN pupilsightTTDay ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID)
                JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) 
                JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) 
                JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                WHERE pupilsightTTDayRowClass.pupilsightTTDayID=:pupilsightTTDayID
                AND pupilsightTTDayRowClass.pupilsightTTColumnRowID=:pupilsightTTColumnRowID
                AND pupilsightTTDayRowClass.pupilsightCourseClassID=:pupilsightCourseClassID";

        return $this->db()->selectOne($sql, $data);
    }

    public function getTTDayRowClassExceptionByID($pupilsightTTDayRowClassExceptionID)
    {
        $data = array('pupilsightTTDayRowClassExceptionID' => $pupilsightTTDayRowClassExceptionID);
        $sql = "SELECT * FROM pupilsightTTDayRowClassException WHERE pupilsightTTDayRowClassExceptionID=:pupilsightTTDayRowClassExceptionID";

        return $this->db()->selectOne($sql, $data);
    }
}
