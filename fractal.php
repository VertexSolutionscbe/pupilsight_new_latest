<?php
/*
Pupilsight, Flexible & Open School System
*/


include 'pupilsight.php';

//ambervalley instance key
$instance_key = '50f85fa2941efdc94e91fbbb1e5a5c01';

$result = array();
if (!isset($_POST['instance_key'])) {
    $result = array("status" => 2, "msg" => "Empty Instance Key");
    echo json_encode($result);
    die();
} else {
    $incomingkey =  $_POST['instance_key'];
    if ($incomingkey != $instance_key) {
        $result = array("status" => 2, "msg" => "Invalid Instance Key");
        echo json_encode($result);
        die();
    }
}
$type = "";
if (!isset($_POST['type'])) {
    $result = array("status" => 2, "msg" => "Empty request type");
    echo json_encode($result);
    die();
} else {
    $type = $_POST['type'];
}

//get current year student list
function getStudentList($con)
{
    try {

        $sq = "SELECT a.pupilsightPersonID as studentid, a.officialName, a.email, a.admission_no, 
            f.name as academic, c.name as program , d.name AS class, e.name as section,
            parent1.officialName as fatherName, parent1.email as fatherEmail, 
            parent2.officialName as motherName, parent2.email as motherEmail
            FROM pupilsightPerson AS a 
            LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID=b.pupilsightPersonID 
            LEFT JOIN pupilsightProgram AS c ON b.pupilsightProgramID=c.pupilsightProgramID 
            LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID=d.pupilsightYearGroupID 
            LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID=e.pupilsightRollGroupID 
            
            LEFT JOIN pupilsightSchoolYear AS f ON b.pupilsightSchoolYearID=f.pupilsightSchoolYearID
            
            LEFT JOIN pupilsightFamilyChild AS child ON child.pupilsightPersonID=a.pupilsightPersonID 
            LEFT JOIN pupilsightFamilyRelationship AS adult1 ON adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.relationship= 'Father'
            LEFT JOIN pupilsightPerson as parent1 ON parent1.pupilsightPersonID=adult1.pupilsightPersonID1 AND parent1.status='Full' 
            LEFT JOIN pupilsightFamilyRelationship as adult2 ON adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.relationship= 'Mother'
            LEFT JOIN pupilsightPerson as parent2 ON parent2.pupilsightPersonID=adult2.pupilsightPersonID1 AND parent2.status='Full' 
            WHERE  a.is_delete = '0' 
            AND b.pupilsightSchoolYearID = 1
            GROUP BY a.pupilsightPersonID ORDER BY a.pupilsightPersonID DESC ";
        $query = $con->query($sq);
        return $query->fetchAll();
    } catch (Exception $ex) {
        return $ex->getMessage();
    }
    return "";
}

function getStudentDetails($con, $uid)
{
    try {
        $sq = "SELECT a.pupilsightPersonID as studentid, a.officialName, a.email, a.admission_no, 
            f.name as academic, c.name as program , d.name AS class, e.name as section,
            parent1.officialName as fatherName, parent1.email as fatherEmail, 
            parent2.officialName as motherName, parent2.email as motherEmail
            FROM pupilsightPerson AS a 
            LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID=b.pupilsightPersonID 
            LEFT JOIN pupilsightProgram AS c ON b.pupilsightProgramID=c.pupilsightProgramID 
            LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID=d.pupilsightYearGroupID 
            LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID=e.pupilsightRollGroupID 
            
            LEFT JOIN pupilsightSchoolYear AS f ON b.pupilsightSchoolYearID=f.pupilsightSchoolYearID
            
            LEFT JOIN pupilsightFamilyChild AS child ON child.pupilsightPersonID=a.pupilsightPersonID 
            LEFT JOIN pupilsightFamilyRelationship AS adult1 ON adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.relationship= 'Father'
            LEFT JOIN pupilsightPerson as parent1 ON parent1.pupilsightPersonID=adult1.pupilsightPersonID1 AND parent1.status='Full' 
            LEFT JOIN pupilsightFamilyRelationship as adult2 ON adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.relationship= 'Mother'
            LEFT JOIN pupilsightPerson as parent2 ON parent2.pupilsightPersonID=adult2.pupilsightPersonID1 AND parent2.status='Full' 
            WHERE  a.is_delete = '0' and a.pupilsightPersonID=" . $uid . " AND b.pupilsightSchoolYearID = 1  GROUP BY a.pupilsightPersonID ORDER BY a.pupilsightPersonID DESC ";
        //echo $sq;
        $query = $con->query($sq);
        return $query->fetch();
    } catch (Exception $ex) {
        return $ex->getMessage();
    }
    return "";
}

function getTeacherList($con)
{
    try {
        $sq = "SELECT a.pupilsightPersonID as teacherid, a.officialName, a.email, 
        c.name as program , d.name AS class, e.name as section, group_concat(sub.name) as subjects
        FROM pupilsightPerson AS a 
        LEFT JOIN pupilsightStaff AS s ON a.pupilsightPersonID=s.pupilsightPersonID 
        LEFT JOIN assignstaff_tosubject AS b ON a.pupilsightPersonID=b.pupilsightStaffID 
        LEFT JOIN pupilsightProgram AS c ON b.pupilsightProgramID=c.pupilsightProgramID 
        LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID=d.pupilsightYearGroupID 
        LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID=e.pupilsightRollGroupID 
        LEFT JOIN pupilsightDepartment AS sub ON b.pupilsightdepartmentID=sub.pupilsightdepartmentID 
        WHERE a.pupilsightRoleIDPrimary in(2,6,34,35)
        GROUP BY a.pupilsightPersonID ORDER BY a.pupilsightPersonID DESC";
        $query = $con->query($sq);
        return $query->fetchAll();
    } catch (Exception $ex) {
        return $ex->getMessage();
    }
    return "";
}

function getTeacherDetails($con, $uid)
{
    try {
        $sq = "SELECT a.pupilsightPersonID as teacherid, a.officialName, a.email, 
        c.name as program , d.name AS class, e.name as section, group_concat(sub.name) as subjects
        FROM pupilsightPerson AS a 
        LEFT JOIN pupilsightStaff AS s ON a.pupilsightPersonID=s.pupilsightPersonID 
        LEFT JOIN assignstaff_tosubject AS b ON a.pupilsightPersonID=b.pupilsightStaffID 
        LEFT JOIN pupilsightProgram AS c ON b.pupilsightProgramID=c.pupilsightProgramID 
        LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID=d.pupilsightYearGroupID 
        LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID=e.pupilsightRollGroupID 
        LEFT JOIN pupilsightDepartment AS sub ON b.pupilsightdepartmentID=sub.pupilsightdepartmentID 
        WHERE a.pupilsightPersonID='" . $uid . "'
        GROUP BY a.pupilsightPersonID ORDER BY a.pupilsightPersonID DESC";
        $query = $con->query($sq);
        return $query->fetch();
    } catch (Exception $ex) {
        return $ex->getMessage();
    }
    return "";
}

/* open Description Indicator */
if ($type == 'getStudentList') {
    try {
        $result = getStudentList($connection2);
        //echo $squ;
    } catch (Exception $ex) {
        $result['status'] = 2;
        $result['message'] = $ex->getMessage();
    }
    if ($result) {
        echo json_encode($result);
    }
} elseif ($type == 'getStudentDetails') {
    try {
        $studentid = $_POST['studentid'];
        $result = getStudentDetails($connection2, $studentid);
        //echo $squ;
    } catch (Exception $ex) {
        $result['status'] = 2;
        $result['message'] = $ex->getMessage();
    }
} elseif ($type == 'getTeacherList') {
    try {
        $result = getTeacherList($connection2);
        //echo $squ;
    } catch (Exception $ex) {
        $result['status'] = 2;
        $result['message'] = $ex->getMessage();
    }
} elseif ($type == 'getTeacherDetails') {
    try {
        $teacherid = $_POST['teacherid'];
        $result = getTeacherDetails($connection2, $teacherid);
        //echo $squ;
    } catch (Exception $ex) {
        $result['status'] = 2;
        $result['message'] = $ex->getMessage();
    }
}
if ($result) {
    echo json_encode($result);
}
