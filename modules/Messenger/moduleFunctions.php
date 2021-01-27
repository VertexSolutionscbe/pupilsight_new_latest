<?php
/*
Pupilsight, Flexible & Open School System
*/

//Helps builds report array for setting pupilsightMessengerReceipt
function reportAdd($report, $emailReceipt, $pupilsightPersonID, $targetType, $targetID, $contactType, $contactDetail)
{
    if ($contactDetail != '' and is_null($contactDetail) == false) {
        $unique = true;
        foreach ($report as $reportEntry) {
            if ($reportEntry[4] == $contactDetail)
                $unique = false;
        }

        if ($unique) {
            $count = count($report);
            $report[$count][0] = $pupilsightPersonID;
            $report[$count][1] = $targetType;
            $report[$count][2] = $targetID;
            $report[$count][3] = $contactType;
            $report[$count][4] = $contactDetail;
            if ($contactType == 'Email' and $emailReceipt == 'Y') {
                $report[$count][5] = randomPassword(40);
            } else {
                $report[$count][5] = null;
            }
        }
    }

    return $report;
}

//Build an email signautre for the specified user
function getSignature($guid, $connection2, $pupilsightPersonID)
{
    $return = false;

    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT pupilsightStaff.*, surname, preferredName, initials FROM pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() == 1) {
        $row = $result->fetch();

        $return = '<br/><br/>----<br/>';
        $return .= "<span style='font-weight: bold; color: #447CAA'>" . formatName('', $row['preferredName'], $row['surname'], 'Student') . '</span><br/>';
        $return .= "<span style='font-style: italic'>";
        if ($row['jobTitle'] != '') {
            $return .= $row['jobTitle'] . '<br/>';
        }
        $return .= $_SESSION[$guid]['organisationName'] . '<br/>';
        $return .= '</span>';
        $return .= '----<br/>';
    }

    return $return;
}

function getEmailSignature($guid, $connection2, $pupilsightPersonID)
{
    $return = false;
    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT pupilsightStaff.*, surname, preferredName, initials,emailSignature,emailSignature FROM pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() == 1) {
        $row = $result->fetch();
        if ($row['emailSignature'] != '') {
            /*$return = '<br/><br/>----<br/>';*/
            $return .= $row['emailSignature'];
            /* $return .= '----<br/>';*/
        }
    }

    return $return;
}

function getSmsSignature($guid, $connection2, $pupilsightPersonID)
{
    $return = false;
    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT pupilsightStaff.*, surname, preferredName, initials,smsSignature FROM pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() == 1) {
        $row = $result->fetch();
        if ($row['smsSignature'] != '') {
            /*  $return = '----\n';*/
            $return .= $row['smsSignature'];
        }
    }
    return $return;
}

//Mode may be "print" (return table of messages), "count" (return message count) or "result" (return database query result)

function getMessages($guid, $connection2, $mode = '', $date = '', $fromdate = '', $msgcategory = '')
{
    $return = '';
    $dataPosts = array();

    if ($msgcategory == 'All') {
        $msgcategory = ' ';
    } else {
        $msgcategory = " AND pupilsightMessenger.messengercategory = '$msgcategory'";
    }
    //echo $msgcategory;
    if ($date == '') {
        $date = date('Y-m-d');
    }
    //to meet jira id 1047 and few others which request to have date set
    if ($fromdate == '') {
        $fromdate = date('Y-m-d');
    }
    if ($fromdate != $date) {


        if ($mode != 'print' and $mode != 'count' and $mode != 'result') {
            $mode = 'print';
        }

        //Work out all role categories this user has, ignoring "Other"
        $roles = $_SESSION[$guid]['pupilsightRoleIDAll'];
        $roleCategory = '';
        $staff = false;
        $student = false;
        $parent = false;
        for ($i = 0; $i < count($roles); ++$i) {
            $roleCategory = getRoleCategory($roles[$i][0], $connection2);
            if ($roleCategory == 'Staff') {
                $staff = true;
            } elseif ($roleCategory == 'Student') {
                $student = true;
            } elseif ($roleCategory == 'Parent') {
                $parent = true;
            }
        }

        //If parent get a list of student IDs
        if ($parent) {
            $children = '(';
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }
            while ($row = $result->fetch()) {
                try {
                    $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {
                }
                while ($rowChild = $resultChild->fetch()) {
                    $children .= 'pupilsightPersonID=' . $rowChild['pupilsightPersonID'] . ' OR ';
                }
            }
            if ($children != '(') {
                $children = substr($children, 0, -4) . ')';
            } else {
                $children = false;
            }
        }

        //My roles
        $roles = $_SESSION[$guid]['pupilsightRoleIDAll'];
        $sqlWhere = '(';
        if (count($roles) > 0) {
            for ($i = 0; $i < count($roles); ++$i) {
                $dataPosts['role' . $roles[$i][0]] = $roles[$i][0];
                $sqlWhere .= 'id=:role' . $roles[$i][0] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
        }
        if ($sqlWhere != '(') {
            $dataPosts['date1'] = $date;
            $dataPosts['date2'] = $date;
            $dataPosts['date3'] = $date;
            $sqlPosts = "(SELECT pupilsightMessenger.*, title, surname, preferredName, authorRole.category AS category, image_240, concat('Role: ', pupilsightRole.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole AS authorRole ON (pupilsightPerson.pupilsightRoleIDPrimary=authorRole.pupilsightRoleID) JOIN pupilsightRole ON (pupilsightMessengerTarget.id=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Role' $msgcategory AND $sqlWhere AND ((messageWall_date1 BETWEEN $fromdate AND :date1) OR (messageWall_date2 BETWEEN $fromdate AND :date2) OR (messageWall_date3 BETWEEN $fromdate AND :date3)) )";
            //      print_r($sqlPosts); die();
        }

        //My role categories
        try {
            $dataRoleCategory = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlRoleCategory = "SELECT DISTINCT pupilsightRole.category FROM pupilsightRole JOIN pupilsightPerson ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE pupilsightPersonID=:pupilsightPersonID";
            $resultRoleCategory = $connection2->prepare($sqlRoleCategory);
            $resultRoleCategory->execute($dataRoleCategory);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $sqlWhere = '(';
        if ($resultRoleCategory->rowCount() > 0) {
            $i = 0;
            while ($rowRoleCategory = $resultRoleCategory->fetch()) {
                $dataPosts['role' . $rowRoleCategory['category']] = $rowRoleCategory['category'];
                $sqlWhere .= 'id=:role' . $rowRoleCategory['category'] . ' OR ';
                ++$i;
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
        }
        if ($sqlWhere != '(') {
            $dataPosts['date1'] = $date;
            $dataPosts['date2'] = $date;
            $dataPosts['date3'] = $date;
            $sqlPosts = $sqlPosts . " UNION (SELECT DISTINCT pupilsightMessenger.*, title, surname, preferredName, authorRole.category AS category, image_240, concat('Role Category: ', pupilsightRole.category) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole AS authorRole ON (pupilsightPerson.pupilsightRoleIDPrimary=authorRole.pupilsightRoleID) JOIN pupilsightRole ON (pupilsightMessengerTarget.id=pupilsightRole.category) WHERE pupilsightMessengerTarget.type='Role Category' $msgcategory AND $sqlWhere AND ((messageWall_date1 BETWEEN $fromdate AND :date1) OR (messageWall_date2 BETWEEN $fromdate AND :date2) OR (messageWall_date3 BETWEEN $fromdate AND :date3)) )";
        }

        //My year groups
        if ($staff) {
            $dataPosts['date4'] = $date;
            $dataPosts['date5'] = $date;
            $dataPosts['date6'] = $date;
            $dataPosts['pupilsightSchoolYearID0'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID0'] = $_SESSION[$guid]['pupilsightPersonID'];
            // Include staff by courses taught in the same year group.

            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, 'Year Groups' AS source
                FROM pupilsightMessenger
                JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
                JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                JOIN pupilsightCourse ON (FIND_IN_SET(pupilsightMessengerTarget.id, pupilsightCourse.pupilsightYearGroupIDList))
                JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                JOIN pupilsightStaff ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID)
                WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID0 
                AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID0 $msgcategory 
                AND pupilsightMessengerTarget.type='Year Group' AND pupilsightMessengerTarget.staff='Y' AND
                ((messageWall_date1 BETWEEN $fromdate AND :date4) OR (messageWall_date2 BETWEEN $fromdate AND :date5) OR (messageWall_date3 BETWEEN $fromdate AND :date6))
                GROUP BY pupilsightMessenger.pupilsightMessengerID )";

            // Include staff who are tutors of any student in the same year group.
            $sqlPosts .= "UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, 'Year Groups' AS source
                FROM pupilsightMessenger
                JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
                JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightYearGroupID=pupilsightMessengerTarget.id)
                JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID)
                JOIN pupilsightStaff ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightStaff.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightStaff.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightStaff.pupilsightPersonID)
                WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID0  
                AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID0 $msgcategory 
                AND pupilsightMessengerTarget.type='Year Group' AND pupilsightMessengerTarget.staff='Y' AND
                ((messageWall_date1 BETWEEN $fromdate AND :date4) OR (messageWall_date2 BETWEEN $fromdate AND :date5) OR (messageWall_date3 BETWEEN $fromdate AND :date6))
                GROUP BY pupilsightMessenger.pupilsightMessengerID)";
            //print_r($sqlPosts);
        }

        if ($student) {
            $dataPosts['date7'] = $date;
            $dataPosts['date8'] = $date;
            $dataPosts['date9'] = $date;
            $dataPosts['pupilsightSchoolYearID1'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID1'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Year Group ', pupilsightYearGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightYearGroupID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightMessengerTarget.type='Year Group' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date7) OR (messageWall_date2 BETWEEN $fromdate AND :date8) OR (messageWall_date3 BETWEEN $fromdate AND :date9)) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID1 AND students='Y' )";
        }
        if ($parent and $children != false) {
            $dataPosts['date10'] = $date;
            $dataPosts['date11'] = $date;
            $dataPosts['date12'] = $date;
            $dataPosts['pupilsightSchoolYearID2'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Year Group: ', pupilsightYearGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightYearGroupID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE " . preg_replace('/pupilsightPersonID/', 'pupilsightStudentEnrolment.pupilsightPersonID', $children) . " AND pupilsightMessengerTarget.type='Year Group' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date10) OR (messageWall_date2 BETWEEN $fromdate AND :date11) OR (messageWall_date3 BETWEEN $fromdate AND :date12)) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND parents='Y' )";
        }

        //My roll groups
        if ($staff) {
            $sqlWhere = '(';
            try {
                $dataRollGroup = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonIDTutor' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlRollGroup = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND (pupilsightPersonIDTutor=:pupilsightPersonIDTutor OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3)';
                $resultRollGroup = $connection2->prepare($sqlRollGroup);
                $resultRollGroup->execute($dataRollGroup);
            } catch (PDOException $e) {
            }
            if ($resultRollGroup->rowCount() > 0) {
                while ($rowRollGroup = $resultRollGroup->fetch()) {
                    $dataPosts['roll' . $rowRollGroup['pupilsightRollGroupID']] = $rowRollGroup['pupilsightRollGroupID'];
                    $sqlWhere .= 'id=:roll' . $rowRollGroup['pupilsightRollGroupID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date13'] = $date;
                    $dataPosts['date14'] = $date;
                    $dataPosts['date15'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightRollGroup ON (pupilsightMessengerTarget.id=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightMessengerTarget.type='Roll Group' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date13) OR (messageWall_date2 BETWEEN $fromdate AND :date14) OR (messageWall_date3 BETWEEN $fromdate AND :date15)) AND $sqlWhere AND staff='Y' )";
                }
            }
        }
        if ($student) {
            $dataPosts['date16'] = $date;
            $dataPosts['date17'] = $date;
            $dataPosts['date18'] = $date;
            $dataPosts['pupilsightSchoolYearID3'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID2'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightRollGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID2 AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID3 AND pupilsightMessengerTarget.type='Roll Group' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date16) OR (messageWall_date2 BETWEEN $fromdate AND :date17) OR (messageWall_date3 BETWEEN $fromdate AND :date18)) AND students='Y' )";
        }
        if ($parent and $children != false) {
            $dataPosts['date19'] = $date;
            $dataPosts['date20'] = $date;
            $dataPosts['date21'] = $date;
            $dataPosts['pupilsightSchoolYearID4'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightRollGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE " . preg_replace('/pupilsightPersonID/', 'pupilsightStudentEnrolment.pupilsightPersonID', $children) . " AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID4 $msgcategory AND pupilsightMessengerTarget.type='Roll Group' AND ((messageWall_date1 BETWEEN $fromdate AND :date19) OR (messageWall_date2 BETWEEN $fromdate AND :date20) OR (messageWall_date3 BETWEEN $fromdate AND :date21)) AND parents='Y' )";
        }

        //My courses
        //First check for any course, then do specific parent check
        try {
            $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlClasses = "SELECT DISTINCT pupilsightCourseClass.pupilsightCourseID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '%- Left'";
            $resultClasses = $connection2->prepare($sqlClasses);
            $resultClasses->execute($dataClasses);
        } catch (PDOException $e) {
        }
        $sqlWhere = '(';
        if ($resultClasses->rowCount() > 0) {
            while ($rowClasses = $resultClasses->fetch()) {
                $dataPosts['course' . $rowClasses['pupilsightCourseID']] = $rowClasses['pupilsightCourseID'];
                $sqlWhere .= 'id=:course' . $rowClasses['pupilsightCourseID'] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
            if ($sqlWhere != '(') {
                if ($staff) {
                    $dataPosts['date22'] = $date;
                    $dataPosts['date23'] = $date;
                    $dataPosts['date24'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date22) OR (messageWall_date2 BETWEEN $fromdate AND :date23) OR (messageWall_date3 BETWEEN $fromdate AND :date24)) AND $sqlWhere AND staff='Y' )";
                }
                if ($student) {
                    $dataPosts['date25'] = $date;
                    $dataPosts['date26'] = $date;
                    $dataPosts['date27'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory  AND ((messageWall_date1 BETWEEN $fromdate AND :date25) OR (messageWall_date2 BETWEEN $fromdate AND :date26) OR (messageWall_date3 BETWEEN $fromdate AND :date27)) AND $sqlWhere AND students='Y' )";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlClasses = 'SELECT DISTINCT pupilsightCourseClass.pupilsightCourseID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightCourseClassPerson.pupilsightPersonID', $children) . " AND NOT role LIKE '%- Left'";
                $resultClasses = $connection2->prepare($sqlClasses);
                $resultClasses->execute($dataClasses);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultClasses->rowCount() > 0) {
                while ($rowClasses = $resultClasses->fetch()) {
                    $dataPosts['course' . $rowClasses['pupilsightCourseID']] = $rowClasses['pupilsightCourseID'];
                    $sqlWhere .= 'id=:course' . $rowClasses['pupilsightCourseID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date28'] = $date;
                    $dataPosts['date29'] = $date;
                    $dataPosts['date30'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date28) OR (messageWall_date2 BETWEEN $fromdate AND :date29) OR (messageWall_date3 BETWEEN $fromdate AND :date30)) AND $sqlWhere AND parents='Y' )";
                }
            }
        }

        //My classes
        //First check for any role, then do specific parent check
        try {
            $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlClasses = "SELECT pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '%- Left'";
            $resultClasses = $connection2->prepare($sqlClasses);
            $resultClasses->execute($dataClasses);
        } catch (PDOException $e) {
        }
        $sqlWhere = '(';
        if ($resultClasses->rowCount() > 0) {
            while ($rowClasses = $resultClasses->fetch()) {
                $dataPosts['class' . $rowClasses['pupilsightCourseClassID']] = $rowClasses['pupilsightCourseClassID'];
                $sqlWhere .= 'id=:class' . $rowClasses['pupilsightCourseClassID'] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
            if ($sqlWhere != '(') {
                if ($staff) {
                    $dataPosts['date31'] = $date;
                    $dataPosts['date32'] = $date;
                    $dataPosts['date33'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date31) OR (messageWall_date2 BETWEEN $fromdate AND :date32) OR (messageWall_date3 BETWEEN $fromdate AND :date33)) AND $sqlWhere AND staff='Y' )";
                }
                if ($student) {
                    $dataPosts['date34'] = $date;
                    $dataPosts['date35'] = $date;
                    $dataPosts['date36'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date34) OR (messageWall_date2 BETWEEN $fromdate AND :date35) OR (messageWall_date3 BETWEEN $fromdate AND :date36)) AND $sqlWhere AND students='Y' )";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlClasses = 'SELECT pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightCourseClassPerson.pupilsightPersonID', $children) . " AND NOT role LIKE '%- Left'";
                $resultClasses = $connection2->prepare($sqlClasses);
                $resultClasses->execute($dataClasses);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultClasses->rowCount() > 0) {
                while ($rowClasses = $resultClasses->fetch()) {
                    $dataPosts['class' . $rowClasses['pupilsightCourseClassID']] = $rowClasses['pupilsightCourseClassID'];
                    $sqlWhere .= 'id=:class' . $rowClasses['pupilsightCourseClassID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date37'] = $date;
                    $dataPosts['date38'] = $date;
                    $dataPosts['date39'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date37) OR (messageWall_date2 BETWEEN $fromdate AND :date38) OR (messageWall_date3 BETWEEN $fromdate AND :date39)) AND $sqlWhere AND parents='Y' )";
                }
            }
        }

        //My activities
        if ($staff) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlActivities = 'SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStaff.pupilsightPersonID=:pupilsightPersonID';
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date40'] = $date;
                    $dataPosts['date41'] = $date;
                    $dataPosts['date42'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date40) OR (messageWall_date2 BETWEEN $fromdate AND :date41) OR (messageWall_date3 BETWEEN $fromdate AND :date42)) AND $sqlWhere AND staff='Y' )";
                }
            }
        }
        if ($student) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlActivities = "SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID AND status='Accepted'";
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date43'] = $date;
                    $dataPosts['date44'] = $date;
                    $dataPosts['date45'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date43) OR (messageWall_date2 BETWEEN $fromdate AND :date44) OR (messageWall_date3 BETWEEN $fromdate AND :date45)) AND $sqlWhere AND students='Y' )";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlActivities = 'SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightActivityStudent.pupilsightPersonID', $children) . " AND status='Accepted'";
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date46'] = $date;
                    $dataPosts['date47'] = $date;
                    $dataPosts['date48'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date46) OR (messageWall_date2 BETWEEN $fromdate AND :date47) OR (messageWall_date3 BETWEEN $fromdate AND :date48)) AND $sqlWhere AND parents='Y' )";
                }
            }
        }

        //Houses
        $dataPosts['date49'] = $date;
        $dataPosts['date50'] = $date;
        $dataPosts['date51'] = $date;
        $dataPosts['pupilsightPersonID3'] = $_SESSION[$guid]['pupilsightPersonID'];
        $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, concat('Houses: ', pupilsightHouse.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightPerson AS inHouse ON (pupilsightMessengerTarget.id=inHouse.pupilsightHouseID) JOIN pupilsightHouse ON (pupilsightPerson.pupilsightHouseID=pupilsightHouse.pupilsightHouseID)WHERE pupilsightMessengerTarget.type='Houses' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date49) OR (messageWall_date2 BETWEEN $fromdate AND :date50) OR (messageWall_date3 BETWEEN $fromdate AND :date51)) AND inHouse.pupilsightPersonID=:pupilsightPersonID3 )";

        //Individuals
        $dataPosts['date52'] = $date;
        $dataPosts['date53'] = $date;
        $dataPosts['date54'] = $date;
        $dataPosts['pupilsightPersonID4'] = $_SESSION[$guid]['pupilsightPersonID'];
        $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, 'Individual: You' AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightPerson AS individual ON (pupilsightMessengerTarget.id=individual.pupilsightPersonID) WHERE pupilsightMessengerTarget.type='Individuals' $msgcategory AND ((messageWall_date1 BETWEEN $fromdate AND :date52) OR (messageWall_date2 BETWEEN $fromdate AND :date53) OR (messageWall_date3 BETWEEN $fromdate AND :date54)) AND individual.pupilsightPersonID=:pupilsightPersonID4 )";


        //Attendance
        if ($student) {
            try {
                $dataAttendance = array("pupilsightPersonID" => $_SESSION[$guid]['pupilsightPersonID'], "selectedDate" => $date, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "nowDate" => date("Y-m-d"));
                $sqlAttendance = "SELECT galp.pupilsightAttendanceLogPersonID, galp.type, galp.date FROM pupilsightAttendanceLogPerson AS galp JOIN pupilsightStudentEnrolment AS gse ON (galp.pupilsightPersonID=gse.pupilsightPersonID) JOIN pupilsightPerson AS gp ON (gse.pupilsightPersonID=gp.pupilsightPersonID) WHERE gp.status='Full' AND (gp.dateStart IS NULL OR gp.dateStart<=:nowDate) AND (gp.dateEnd IS NULL OR gp.dateEnd>=:nowDate) AND gse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND galp.date=:selectedDate AND galp.pupilsightPersonID=:pupilsightPersonID ORDER BY galp.pupilsightAttendanceLogPersonID DESC LIMIT 1";
                $resultAttendance = $connection2->prepare($sqlAttendance);
                $resultAttendance->execute($dataAttendance);
            } catch (PDOException $e) {
            }

            if ($resultAttendance->rowCount() > 0) {
                $studentAttendance = $resultAttendance->fetch();
                $dataPosts['date55'] = $date;
                $dataPosts['date56'] = $date;
                $dataPosts['date57'] = $date;
                $dataPosts['attendanceType1'] = $studentAttendance['type'] . ' ' . $date;
                $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, concat('Attendance:', pupilsightMessengerTarget.id) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Attendance' $msgcategory AND pupilsightMessengerTarget.id=:attendanceType1 AND ((messageWall_date1 BETWEEN $fromdate AND :date55) OR (messageWall_date2 BETWEEN $fromdate AND :date56) OR (messageWall_date3 BETWEEN $fromdate AND :date57)) )";
            }
        }
        if ($parent and $children != false) {
            try {
                $dataAttendance = array("pupilsightPersonID" => $_SESSION[$guid]['pupilsightPersonID'], "selectedDate" => $date, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "nowDate" => date("Y-m-d"));
                $sqlAttendance = "SELECT galp.pupilsightAttendanceLogPersonID, galp.type, gp.firstName FROM pupilsightAttendanceLogPerson AS galp JOIN pupilsightStudentEnrolment AS gse ON (galp.pupilsightPersonID=gse.pupilsightPersonID) JOIN pupilsightPerson AS gp ON (gse.pupilsightPersonID=gp.pupilsightPersonID) WHERE gp.status='Full' AND (gp.dateStart IS NULL OR gp.dateStart<=:nowDate) AND (gp.dateEnd IS NULL OR gp.dateEnd>=:nowDate) AND gse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND galp.date=:selectedDate AND " . preg_replace('/pupilsightPersonID/', 'galp.pupilsightPersonID', $children) . " ORDER BY galp.pupilsightAttendanceLogPersonID DESC LIMIT 1";
                $resultAttendance = $connection2->prepare($sqlAttendance);
                $resultAttendance->execute($dataAttendance);
            } catch (PDOException $e) {
            }

            if ($resultAttendance->rowCount() > 0) {
                $studentAttendance = $resultAttendance->fetch();
                $dataPosts['date57'] = $date;
                $dataPosts['date58'] = $date;
                $dataPosts['date59'] = $date;
                $dataPosts['attendanceType2'] = $studentAttendance['type'] . ' ' . $date;
                $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, concat('Attendance:', pupilsightMessengerTarget.id, ' for ', '" . $studentAttendance['firstName'] . "') AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Attendance' $msgcategory AND pupilsightMessengerTarget.id=:attendanceType2 AND ((messageWall_date1 BETWEEN $fromdate AND :date57) OR (messageWall_date2 BETWEEN $fromdate AND :date58) OR (messageWall_date3 BETWEEN $fromdate AND :date59) ))";
            }
        }

        // Groups
        if ($staff) {
            $dataPosts['date60'] = $date;
            $dataPosts['pupilsightPersonID5'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID5 
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.staff='Y' $msgcategory 
        AND ((messageWall_date1 BETWEEN $fromdate AND :date60) OR (messageWall_date2 BETWEEN $fromdate AND :date60) OR (messageWall_date3 BETWEEN $fromdate AND :date60)) )";
        }
        if ($student) {
            $dataPosts['date61'] = $date;
            $dataPosts['pupilsightPersonID6'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID6 
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.students='Y' $msgcategory 
        AND ((messageWall_date1 BETWEEN $fromdate AND :date61) OR (messageWall_date2 BETWEEN $fromdate AND :date61) OR (messageWall_date3 BETWEEN $fromdate AND :date61)) )";
        }
        if ($parent and $children != false) {
            $childrenQuery = str_replace('pupilsightPersonID', 'pupilsightGroupPerson.pupilsightPersonID', $children);
            $dataPosts['date62'] = $date;
            $dataPosts['pupilsightPersonID7'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE (pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID7 OR $childrenQuery)
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.parents='Y' $msgcategory 
        AND ((messageWall_date1 BETWEEN $fromdate AND :date62) OR (messageWall_date2 BETWEEN $fromdate AND :date62) OR (messageWall_date3 BETWEEN $fromdate AND :date62)) )";
        }

        // Transport
        if ($staff) {
            $dataPosts['date63'] = $date;
            $dataPosts['pupilsightPersonID8'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE transportee.pupilsightPersonID=:pupilsightPersonID8 
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.staff='Y' $msgcategory
        AND ((messageWall_date1 BETWEEN $fromdate AND :date63) OR (messageWall_date2 BETWEEN $fromdate AND :date63) OR (messageWall_date3 BETWEEN $fromdate AND :date63)) )";
        }
        if ($student) {
            $dataPosts['date64'] = $date;
            $dataPosts['pupilsightPersonID9'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE transportee.pupilsightPersonID=:pupilsightPersonID9 
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.students='Y' $msgcategory
        AND ((messageWall_date1 BETWEEN $fromdate AND :date64) OR (messageWall_date2 BETWEEN $fromdate AND :date64) OR (messageWall_date3 BETWEEN $fromdate AND :date64)) )";
        }
        if ($parent and $children != false) {
            $childrenQuery = str_replace('pupilsightPersonID', 'transportee.pupilsightPersonID', $children);
            $dataPosts['date65'] = $date;
            $dataPosts['pupilsightPersonID10'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE (transportee.pupilsightPersonID=:pupilsightPersonID10 OR $childrenQuery)
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.parents='Y' $msgcategory
        AND ((messageWall_date1 BETWEEN $fromdate AND :date65) OR (messageWall_date2 BETWEEN $fromdate AND :date65) OR (messageWall_date3 BETWEEN $fromdate AND :date65)) )";
        }

        //SPIT OUT RESULTS
        if ($mode == 'result') {
            $resultReturn = array();
            $resultReturn[0] = $dataPosts;
            $resultReturn[1] = $sqlPosts . ' ORDER BY  pupilsightMessengerID desc ';

            return serialize($resultReturn);
        } else {
            $count = 0;
            try {
                $sqlPosts = $sqlPosts . ' ORDER BY  pupilsightMessengerID desc ';
                $resultPosts = $connection2->prepare($sqlPosts);
                $resultPosts->execute($dataPosts);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }

            if ($resultPosts->rowCount() < 1) {
                $return .= "<div class='alert alert-warning'>";
                $return .= __('There are no records to display.');
                $return .= '</div>';
            } else {
                $output = array();
                $last = '';
                while ($rowPosts = $resultPosts->fetch()) {
                    if ($last == $rowPosts['pupilsightMessengerID']) {
                        $output[($count - 1)]['source'] = $output[($count - 1)]['source'] . '<br/>' . $rowPosts['source'];
                    } else {
                        $output[$count]['photo'] = $rowPosts['image_240'];
                        $output[$count]['subject'] = $rowPosts['subject'];
                        $output[$count]['details'] = $rowPosts['body'];
                        $output[$count]['author'] = formatName($rowPosts['title'], $rowPosts['preferredName'], $rowPosts['surname'], $rowPosts['category']);
                        $output[$count]['source'] = $rowPosts['source'];
                        $output[$count]['pupilsightMessengerID'] = $rowPosts['pupilsightMessengerID'];
                        $output[$count]['pupilsightPersonID'] = $rowPosts['pupilsightPersonID'];
                        $output[$count]['date'] = $rowPosts['messageWall_date1'];
                        $output[$count]['date1'] = $rowPosts['messageWall_date2'];
                        $output[$count]['date2'] = $rowPosts['messageWall_date3'];

                        ++$count;
                        $last = $rowPosts['pupilsightMessengerID'];
                    }
                }

                $return .= "<table cellspacing='0' style='margin-top: 10px'>";
                $return .= '<tr>';
                $return .= "<th style='text-align: center'>";
                $return .= __('Date');
                $return .= '</th>';
                $return .= '<th>';
                $return .= __('Message');
                $return .= '</th>';
                $return .= "<th style='text-align: center'>";
                $return .= __('Posted By');
                $return .= '</th>';
                $return .= '</tr>';
                $rowCount = 0;
                $rowNum = 'odd';
                for ($i = 0; $i < count($output); ++$i) {
                    if ($rowCount % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$rowCount;
                    $return .= "<tr class=$rowNum>";
                    $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                    //$return .= "<a name='".$output[$i]['pupilsightMessengerID']."'></a>";
                    //$return .= getUserPhoto($guid, $output[$i]['photo'], 75).'<br/>';

                    $return .= date('d/m/Y', strtotime($output[$i]['date'])) . '<br/>';
                    if ($output[$i]['date1'] != null) {
                        $return .= date('d/m/Y', strtotime($output[$i]['date1'])) . '<br/>';
                    }
                    if ($output[$i]['date2'] != null) {
                        $return .= date('d/m/Y', strtotime($output[$i]['date2'])) . '<br/>';
                    }

                    //$return .= '<b><u>'.__('Shared Via').'</b></u><br/>';
                    //$return .= $output[$i]['source'].'<br/><br/>';
                    $return .= '</td>';
                    $return .= "<td style='border-left: none; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 640px'>";
                    $return .= "<h3 style='margin-top: 3px'>";
                    $return .= $output[$i]['subject'];
                    $return .= '</h3>';
                    $return .= '</p>';
                    $return .= $output[$i]['details'];
                    $return .= '</p>';
                    $return .= '</td>';
                    $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                    $return .= '<b><u>' . __('Posted By') . '</b></u><br/>';
                    $return .= $output[$i]['author'] . '<br/><br/>';
                    $return .= '</td>';
                    $return .= '</tr>';
                }
                $return .= '</table>';
            }
            if ($mode == 'print') {
                return $return;
            } else {
                return $count;
            }
        }
    } else {
        if ($mode != 'print' and $mode != 'count' and $mode != 'result') {
            $mode = 'print';
        }

        //Work out all role categories this user has, ignoring "Other"
        $roles = $_SESSION[$guid]['pupilsightRoleIDAll'];
        $roleCategory = '';
        $staff = false;
        $student = false;
        $parent = false;
        for ($i = 0; $i < count($roles); ++$i) {
            $roleCategory = getRoleCategory($roles[$i][0], $connection2);
            if ($roleCategory == 'Staff') {
                $staff = true;
            } elseif ($roleCategory == 'Student') {
                $student = true;
            } elseif ($roleCategory == 'Parent') {
                $parent = true;
            }
        }

        //If parent get a list of student IDs
        if ($parent) {
            $children = '(';
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }
            while ($row = $result->fetch()) {
                try {
                    $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {
                }
                while ($rowChild = $resultChild->fetch()) {
                    $children .= 'pupilsightPersonID=' . $rowChild['pupilsightPersonID'] . ' OR ';
                }
            }
            if ($children != '(') {
                $children = substr($children, 0, -4) . ')';
            } else {
                $children = false;
            }
        }

        //My roles
        $roles = $_SESSION[$guid]['pupilsightRoleIDAll'];
        $sqlWhere = '(';
        if (count($roles) > 0) {
            for ($i = 0; $i < count($roles); ++$i) {
                $dataPosts['role' . $roles[$i][0]] = $roles[$i][0];
                $sqlWhere .= 'id=:role' . $roles[$i][0] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
        }
        if ($sqlWhere != '(') {
            $dataPosts['date1'] = $date;
            $dataPosts['date2'] = $date;
            $dataPosts['date3'] = $date;
            $sqlPosts = "(SELECT pupilsightMessenger.*, title, surname, preferredName, authorRole.category AS category, image_240, concat('Role: ', pupilsightRole.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole AS authorRole ON (pupilsightPerson.pupilsightRoleIDPrimary=authorRole.pupilsightRoleID) JOIN pupilsightRole ON (pupilsightMessengerTarget.id=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Role'AND $sqlWhere AND ((messageWall_date1 =:date1 OR messageWall_date2=:date2 OR messageWall_date3 =:date3)) )";
            //print_r($sqlPosts);
        }

        //My role categories
        try {
            $dataRoleCategory = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlRoleCategory = "SELECT DISTINCT pupilsightRole.category FROM pupilsightRole JOIN pupilsightPerson ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE pupilsightPersonID=:pupilsightPersonID";
            $resultRoleCategory = $connection2->prepare($sqlRoleCategory);
            $resultRoleCategory->execute($dataRoleCategory);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $sqlWhere = '(';
        if ($resultRoleCategory->rowCount() > 0) {
            $i = 0;
            while ($rowRoleCategory = $resultRoleCategory->fetch()) {
                $dataPosts['role' . $rowRoleCategory['category']] = $rowRoleCategory['category'];
                $sqlWhere .= 'id=:role' . $rowRoleCategory['category'] . ' OR ';
                ++$i;
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
        }
        if ($sqlWhere != '(') {
            $dataPosts['date1'] = $date;
            $dataPosts['date2'] = $date;
            $dataPosts['date3'] = $date;
            $sqlPosts = $sqlPosts . " UNION (SELECT DISTINCT pupilsightMessenger.*, title, surname, preferredName, authorRole.category AS category, image_240, concat('Role Category: ', pupilsightRole.category) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole AS authorRole ON (pupilsightPerson.pupilsightRoleIDPrimary=authorRole.pupilsightRoleID) JOIN pupilsightRole ON (pupilsightMessengerTarget.id=pupilsightRole.category) WHERE pupilsightMessengerTarget.type='Role Category' $msgcategory AND $sqlWhere AND ((messageWall_date1 =:date1 OR messageWall_date2=:date2 OR messageWall_date3 =:date3)))";
        }

        //My year groups
        if ($staff) {
            $dataPosts['date4'] = $date;
            $dataPosts['date5'] = $date;
            $dataPosts['date6'] = $date;
            $dataPosts['pupilsightSchoolYearID0'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID0'] = $_SESSION[$guid]['pupilsightPersonID'];
            // Include staff by courses taught in the same year group.
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, 'Year Groups' AS source
                FROM pupilsightMessenger
                JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
                JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                JOIN pupilsightCourse ON (FIND_IN_SET(pupilsightMessengerTarget.id, pupilsightCourse.pupilsightYearGroupIDList))
                JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                JOIN pupilsightStaff ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID)
                WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID0 $msgcategory 
                AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID0
                AND pupilsightMessengerTarget.type='Year Group' AND pupilsightMessengerTarget.staff='Y' AND
                ((messageWall_date1 =:date4 OR messageWall_date2=:date5 OR messageWall_date3 =:date6))
                GROUP BY pupilsightMessenger.pupilsightMessengerID )";
            // Include staff who are tutors of any student in the same year group.
            $sqlPosts .= "UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, 'Year Groups' AS source
                FROM pupilsightMessenger
                JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
                JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightYearGroupID=pupilsightMessengerTarget.id)
                JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID)
                JOIN pupilsightStaff ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightStaff.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightStaff.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightStaff.pupilsightPersonID)
                WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID0 $msgcategory 
                AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID0
                AND pupilsightMessengerTarget.type='Year Group' AND pupilsightMessengerTarget.staff='Y' AND
                ((messageWall_date1 =:date4 OR messageWall_date2=:date5 OR messageWall_date3 =:date6))
                GROUP BY pupilsightMessenger.pupilsightMessengerID)";
            //print_r($sqlPosts);
        }
        if ($student) {
            $dataPosts['date7'] = $date;
            $dataPosts['date8'] = $date;
            $dataPosts['date9'] = $date;
            $dataPosts['pupilsightSchoolYearID1'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID1'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Year Group ', pupilsightYearGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightYearGroupID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID1 $msgcategory AND pupilsightMessengerTarget.type='Year Group' AND ((messageWall_date1 =:date7 OR messageWall_date2=:date8 OR messageWall_date3 =:date9)) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID1 AND students='Y')";
        }
        if ($parent and $children != false) {
            $dataPosts['date10'] = $date;
            $dataPosts['date11'] = $date;
            $dataPosts['date12'] = $date;
            $dataPosts['pupilsightSchoolYearID2'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Year Group: ', pupilsightYearGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightYearGroupID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE " . preg_replace('/pupilsightPersonID/', 'pupilsightStudentEnrolment.pupilsightPersonID', $children) . " AND pupilsightMessengerTarget.type='Year Group' $msgcategory AND ((messageWall_date1 =:date10 OR messageWall_date2=:date11 OR messageWall_date3 =:date12)) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND parents='Y')";
        }

        //My roll groups
        if ($staff) {
            $sqlWhere = '(';
            try {
                $dataRollGroup = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonIDTutor' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlRollGroup = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND (pupilsightPersonIDTutor=:pupilsightPersonIDTutor OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3)';
                $resultRollGroup = $connection2->prepare($sqlRollGroup);
                $resultRollGroup->execute($dataRollGroup);
            } catch (PDOException $e) {
            }
            if ($resultRollGroup->rowCount() > 0) {
                while ($rowRollGroup = $resultRollGroup->fetch()) {
                    $dataPosts['roll' . $rowRollGroup['pupilsightRollGroupID']] = $rowRollGroup['pupilsightRollGroupID'];
                    $sqlWhere .= 'id=:roll' . $rowRollGroup['pupilsightRollGroupID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date13'] = $date;
                    $dataPosts['date14'] = $date;
                    $dataPosts['date15'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightRollGroup ON (pupilsightMessengerTarget.id=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightMessengerTarget.type='Roll Group' $msgcategory AND ((messageWall_date1 =:date13 OR messageWall_date2=:date14 OR messageWall_date3 =:date15)) AND $sqlWhere AND staff='Y')";
                }
            }
        }
        if ($student) {
            $dataPosts['date16'] = $date;
            $dataPosts['date17'] = $date;
            $dataPosts['date18'] = $date;
            $dataPosts['pupilsightSchoolYearID3'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID2'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightRollGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID2 $msgcategory AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID3 AND pupilsightMessengerTarget.type='Roll Group' AND ((messageWall_date1 =:date16 OR messageWall_date2=:date17 OR messageWall_date3 =:date18)) AND students='Y')";
        }
        if ($parent and $children != false) {
            $dataPosts['date19'] = $date;
            $dataPosts['date20'] = $date;
            $dataPosts['date21'] = $date;
            $dataPosts['pupilsightSchoolYearID4'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightRollGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE " . preg_replace('/pupilsightPersonID/', 'pupilsightStudentEnrolment.pupilsightPersonID', $children) . " AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID4 AND pupilsightMessengerTarget.type='Roll Group' $msgcategory AND ((messageWall_date1 =:date19 OR messageWall_date2=:date20 OR messageWall_date3 =:date21)) AND parents='Y')";
        }

        //My courses
        //First check for any course, then do specific parent check
        try {
            $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlClasses = "SELECT DISTINCT pupilsightCourseClass.pupilsightCourseID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '%- Left'";
            $resultClasses = $connection2->prepare($sqlClasses);
            $resultClasses->execute($dataClasses);
        } catch (PDOException $e) {
        }
        $sqlWhere = '(';
        if ($resultClasses->rowCount() > 0) {
            while ($rowClasses = $resultClasses->fetch()) {
                $dataPosts['course' . $rowClasses['pupilsightCourseID']] = $rowClasses['pupilsightCourseID'];
                $sqlWhere .= 'id=:course' . $rowClasses['pupilsightCourseID'] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
            if ($sqlWhere != '(') {
                if ($staff) {
                    $dataPosts['date22'] = $date;
                    $dataPosts['date23'] = $date;
                    $dataPosts['date24'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory AND ((messageWall_date1 =:date22 OR messageWall_date2=:date23 OR messageWall_date3 =:date24)) AND $sqlWhere AND staff='Y')";
                }
                if ($student) {
                    $dataPosts['date25'] = $date;
                    $dataPosts['date26'] = $date;
                    $dataPosts['date27'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory AND ((messageWall_date1 =:date25 OR messageWall_date2=:date26 OR messageWall_date3 =:date27)) AND $sqlWhere AND students='Y')";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlClasses = 'SELECT DISTINCT pupilsightCourseClass.pupilsightCourseID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightCourseClassPerson.pupilsightPersonID', $children) . " AND NOT role LIKE '%- Left'";
                $resultClasses = $connection2->prepare($sqlClasses);
                $resultClasses->execute($dataClasses);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultClasses->rowCount() > 0) {
                while ($rowClasses = $resultClasses->fetch()) {
                    $dataPosts['course' . $rowClasses['pupilsightCourseID']] = $rowClasses['pupilsightCourseID'];
                    $sqlWhere .= 'id=:course' . $rowClasses['pupilsightCourseID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date28'] = $date;
                    $dataPosts['date29'] = $date;
                    $dataPosts['date30'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory AND ((messageWall_date1 =:date28 OR messageWall_date2=:date29 OR messageWall_date3 =:date30)) AND $sqlWhere AND parents='Y')";
                }
            }
        }

        //My classes
        //First check for any role, then do specific parent check
        try {
            $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlClasses = "SELECT pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '%- Left'";
            $resultClasses = $connection2->prepare($sqlClasses);
            $resultClasses->execute($dataClasses);
        } catch (PDOException $e) {
        }
        $sqlWhere = '(';
        if ($resultClasses->rowCount() > 0) {
            while ($rowClasses = $resultClasses->fetch()) {
                $dataPosts['class' . $rowClasses['pupilsightCourseClassID']] = $rowClasses['pupilsightCourseClassID'];
                $sqlWhere .= 'id=:class' . $rowClasses['pupilsightCourseClassID'] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
            if ($sqlWhere != '(') {
                if ($staff) {
                    $dataPosts['date31'] = $date;
                    $dataPosts['date32'] = $date;
                    $dataPosts['date33'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory AND ((messageWall_date1 =:date31 OR messageWall_date2=:date32 OR messageWall_date3 =:date33)) AND $sqlWhere AND staff='Y')";
                }
                if ($student) {
                    $dataPosts['date34'] = $date;
                    $dataPosts['date35'] = $date;
                    $dataPosts['date36'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory AND ((messageWall_date1 =:date34 OR messageWall_date2=:date35 OR messageWall_date3 =:date36)) AND $sqlWhere AND students='Y')";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlClasses = 'SELECT pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightCourseClassPerson.pupilsightPersonID', $children) . " AND NOT role LIKE '%- Left'";
                $resultClasses = $connection2->prepare($sqlClasses);
                $resultClasses->execute($dataClasses);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultClasses->rowCount() > 0) {
                while ($rowClasses = $resultClasses->fetch()) {
                    $dataPosts['class' . $rowClasses['pupilsightCourseClassID']] = $rowClasses['pupilsightCourseClassID'];
                    $sqlWhere .= 'id=:class' . $rowClasses['pupilsightCourseClassID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date37'] = $date;
                    $dataPosts['date38'] = $date;
                    $dataPosts['date39'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory AND ((messageWall_date1 =:date37 OR messageWall_date2=:date38 OR messageWall_date3 =:date39)) AND $sqlWhere AND parents='Y')";
                }
            }
        }

        //My activities
        if ($staff) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlActivities = 'SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStaff.pupilsightPersonID=:pupilsightPersonID';
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date40'] = $date;
                    $dataPosts['date41'] = $date;
                    $dataPosts['date42'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory AND ((messageWall_date1 =:date40 OR messageWall_date2=:date41 OR messageWall_date3 =:date42)) AND $sqlWhere AND staff='Y')";
                }
            }
        }
        if ($student) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlActivities = "SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID AND status='Accepted'";
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date43'] = $date;
                    $dataPosts['date44'] = $date;
                    $dataPosts['date45'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory AND ((messageWall_date1 =:date43 OR messageWall_date2=:dat44 OR messageWall_date3 =:date45)) AND $sqlWhere AND students='Y')";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlActivities = 'SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightActivityStudent.pupilsightPersonID', $children) . " AND status='Accepted'";
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date46'] = $date;
                    $dataPosts['date47'] = $date;
                    $dataPosts['date48'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory AND ((messageWall_date1 =:date46 OR messageWall_date2=:date47 OR messageWall_date3 =:date48)) AND $sqlWhere AND parents='Y')";
                }
            }
        }

        //Houses
        $dataPosts['date49'] = $date;
        $dataPosts['date50'] = $date;
        $dataPosts['date51'] = $date;
        $dataPosts['pupilsightPersonID3'] = $_SESSION[$guid]['pupilsightPersonID'];
        $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Houses: ', pupilsightHouse.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightPerson AS inHouse ON (pupilsightMessengerTarget.id=inHouse.pupilsightHouseID) JOIN pupilsightHouse ON (pupilsightPerson.pupilsightHouseID=pupilsightHouse.pupilsightHouseID)WHERE pupilsightMessengerTarget.type='Houses' $msgcategory AND ((messageWall_date1 =:date49 OR messageWall_date2=:date50 OR messageWall_date3 =:date51)) AND inHouse.pupilsightPersonID=:pupilsightPersonID3)";

        //Individuals
        $dataPosts['date52'] = $date;
        $dataPosts['date53'] = $date;
        $dataPosts['date54'] = $date;
        $dataPosts['pupilsightPersonID4'] = $_SESSION[$guid]['pupilsightPersonID'];
        $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, 'Individual: You' AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightPerson AS individual ON (pupilsightMessengerTarget.id=individual.pupilsightPersonID) WHERE pupilsightMessengerTarget.type='Individuals' $msgcategory AND ((messageWall_date1 =:date52 OR messageWall_date2=:date53 OR messageWall_date3 =:date54)) AND individual.pupilsightPersonID=:pupilsightPersonID4)";


        //Attendance
        if ($student) {
            try {
                $dataAttendance = array("pupilsightPersonID" => $_SESSION[$guid]['pupilsightPersonID'], "selectedDate" => $date, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "nowDate" => date("Y-m-d"));
                $sqlAttendance = "SELECT galp.pupilsightAttendanceLogPersonID, galp.type, galp.date FROM pupilsightAttendanceLogPerson AS galp JOIN pupilsightStudentEnrolment AS gse ON (galp.pupilsightPersonID=gse.pupilsightPersonID) JOIN pupilsightPerson AS gp ON (gse.pupilsightPersonID=gp.pupilsightPersonID) WHERE gp.status='Full' AND (gp.dateStart IS NULL OR gp.dateStart<=:nowDate) AND (gp.dateEnd IS NULL OR gp.dateEnd>=:nowDate) AND gse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND galp.date=:selectedDate AND galp.pupilsightPersonID=:pupilsightPersonID ORDER BY galp.pupilsightAttendanceLogPersonID DESC LIMIT 1";
                $resultAttendance = $connection2->prepare($sqlAttendance);
                $resultAttendance->execute($dataAttendance);
            } catch (PDOException $e) {
            }

            if ($resultAttendance->rowCount() > 0) {
                $studentAttendance = $resultAttendance->fetch();
                $dataPosts['date55'] = $date;
                $dataPosts['date56'] = $date;
                $dataPosts['date57'] = $date;
                $dataPosts['attendanceType1'] = $studentAttendance['type'] . ' ' . $date;
                $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Attendance:', pupilsightMessengerTarget.id) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Attendance' $msgcategory AND pupilsightMessengerTarget.id=:attendanceType1 AND ((messageWall_date1 =:date55 OR messageWall_date2=:date56 OR messageWall_date3 =:date57)) )";
            }
        }
        if ($parent and $children != false) {
            try {
                $dataAttendance = array("pupilsightPersonID" => $_SESSION[$guid]['pupilsightPersonID'], "selectedDate" => $date, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "nowDate" => date("Y-m-d"));
                $sqlAttendance = "SELECT galp.pupilsightAttendanceLogPersonID, galp.type, gp.firstName FROM pupilsightAttendanceLogPerson AS galp JOIN pupilsightStudentEnrolment AS gse ON (galp.pupilsightPersonID=gse.pupilsightPersonID) JOIN pupilsightPerson AS gp ON (gse.pupilsightPersonID=gp.pupilsightPersonID) WHERE gp.status='Full' AND (gp.dateStart IS NULL OR gp.dateStart<=:nowDate) AND (gp.dateEnd IS NULL OR gp.dateEnd>=:nowDate) AND gse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND galp.date=:selectedDate AND " . preg_replace('/pupilsightPersonID/', 'galp.pupilsightPersonID', $children) . " ORDER BY galp.pupilsightAttendanceLogPersonID DESC LIMIT 1";
                $resultAttendance = $connection2->prepare($sqlAttendance);
                $resultAttendance->execute($dataAttendance);
            } catch (PDOException $e) {
            }

            if ($resultAttendance->rowCount() > 0) {
                $studentAttendance = $resultAttendance->fetch();
                $dataPosts['date57'] = $date;
                $dataPosts['date58'] = $date;
                $dataPosts['date59'] = $date;
                $dataPosts['attendanceType2'] = $studentAttendance['type'] . ' ' . $date;
                $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Attendance:', pupilsightMessengerTarget.id, ' for ', '" . $studentAttendance['firstName'] . "') AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Attendance' $msgcategory AND pupilsightMessengerTarget.id=:attendanceType2 AND ((messageWall_date1 =:date57 OR messageWall_date2=:date58 OR messageWall_date3 =:date59)) )";
            }
        }

        // Groups
        if ($staff) {
            $dataPosts['date60'] = $date;
            $dataPosts['pupilsightPersonID5'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID5 $msgcategory 
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.staff='Y'
        AND ((messageWall_date1 =:date60 OR messageWall_date2=:date60 OR messageWall_date3 =:date60)) )";
        }
        if ($student) {
            $dataPosts['date61'] = $date;
            $dataPosts['pupilsightPersonID6'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID6 $msgcategory
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.students='Y'
        AND ((messageWall_date1 =:date61 OR messageWall_date2=:date61 OR messageWall_date3 =:date61)) )";
        }
        if ($parent and $children != false) {
            $childrenQuery = str_replace('pupilsightPersonID', 'pupilsightGroupPerson.pupilsightPersonID', $children);
            $dataPosts['date62'] = $date;
            $dataPosts['pupilsightPersonID7'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, pupilsightRole.category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE (pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID7 OR $childrenQuery)
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.parents='Y' $msgcategory
        AND ((messageWall_date1 =:date62 OR messageWall_date2=:date62 OR messageWall_date3 =:date62)) )";
        }

        // Transport
        if ($staff) {
            $dataPosts['date63'] = $date;
            $dataPosts['pupilsightPersonID8'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE transportee.pupilsightPersonID=:pupilsightPersonID8 $msgcategory
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.staff='Y'
        AND ((messageWall_date1 =:date63 OR messageWall_date2=:date63 OR messageWall_date3 =:date63)) )";
        }
        if ($student) {
            $dataPosts['date64'] = $date;
            $dataPosts['pupilsightPersonID9'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE transportee.pupilsightPersonID=:pupilsightPersonID9 $msgcategory
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.students='Y'
        AND ((messageWall_date1 =:date64 OR messageWall_date2=:date64 OR messageWall_date3 =:date64)) )";
        }
        if ($parent and $children != false) {
            $childrenQuery = str_replace('pupilsightPersonID', 'transportee.pupilsightPersonID', $children);
            $dataPosts['date65'] = $date;
            $dataPosts['pupilsightPersonID10'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightRole.category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE (transportee.pupilsightPersonID=:pupilsightPersonID10 OR $childrenQuery)
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.parents='Y' $msgcategory
        AND ((messageWall_date1 =:date65 OR messageWall_date2=:date65 OR messageWall_date3 =:date65)) )";
        }

        //SPIT OUT RESULTS
        if ($mode == 'result') {
            $resultReturn = array();
            $resultReturn[0] = $dataPosts;
            $resultReturn[1] = $sqlPosts . ' ORDER BY /*subject,*/ pupilsightMessengerID desc /*, source*/';

            return serialize($resultReturn);
        } else {
            $count = 0;
            try {
                $sqlPosts = $sqlPosts . ' ORDER BY /*subject,*/ pupilsightMessengerID desc /*, source*/';
                $resultPosts = $connection2->prepare($sqlPosts);
                $resultPosts->execute($dataPosts);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }

            if ($resultPosts->rowCount() < 1) {
                $return .= "<div class='alert alert-warning'>";
                $return .= __('There are no records to display.');
                $return .= '</div>';
            } else {
                $output = array();
                $last = '';
                while ($rowPosts = $resultPosts->fetch()) {
                    if ($last == $rowPosts['pupilsightMessengerID']) {
                        $output[($count - 1)]['source'] = $output[($count - 1)]['source'] . '<br/>' . $rowPosts['source'];
                    } else {
                        $output[$count]['photo'] = $rowPosts['image_240'];
                        $output[$count]['subject'] = $rowPosts['subject'];
                        $output[$count]['details'] = $rowPosts['body'];
                        $output[$count]['author'] = formatName($rowPosts['title'], $rowPosts['preferredName'], $rowPosts['surname'], $rowPosts['category']);
                        $output[$count]['source'] = $rowPosts['source'];
                        $output[$count]['pupilsightMessengerID'] = $rowPosts['pupilsightMessengerID'];
                        $output[$count]['pupilsightPersonID'] = $rowPosts['pupilsightPersonID'];
                        $output[$count]['date'] = $rowPosts['messageWall_date1'];
                        $output[$count]['date1'] = $rowPosts['messageWall_date2'];
                        $output[$count]['date2'] = $rowPosts['messageWall_date3'];

                        ++$count;
                        $last = $rowPosts['pupilsightMessengerID'];
                    }
                }

                $return .= "<table cellspacing='0' style='margin-top: 10px'>";
                $return .= '<tr>';
                $return .= "<th style='text-align: center'>";
                $return .= __('Date');
                $return .= '</th>';
                $return .= '<th>';
                $return .= __('Message');
                $return .= '</th>';
                $return .= "<th style='text-align: center'>";
                $return .= __('Posted By');
                $return .= '</th>';
                $return .= '</tr>';
                $rowCount = 0;
                $rowNum = 'odd';
                for ($i = 0; $i < count($output); ++$i) {
                    if ($rowCount % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$rowCount;
                    $return .= "<tr class=$rowNum>";
                    $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                    //$return .= "<a name='".$output[$i]['pupilsightMessengerID']."'></a>";
                    //$return .= getUserPhoto($guid, $output[$i]['photo'], 75).'<br/>';
                    $return .= date('d/m/Y', strtotime($output[$i]['date'])) . '<br/>';
                    if ($output[$i]['date1'] != null) {
                        $return .= date('d/m/Y', strtotime($output[$i]['date1'])) . '<br/>';
                    }
                    if ($output[$i]['date2'] != null) {
                        $return .= date('d/m/Y', strtotime($output[$i]['date2'])) . '<br/>';
                    }

                    //$return .= '<b><u>'.__('Shared Via').'</b></u><br/>';
                    //$return .= $output[$i]['source'].'<br/><br/>';
                    $return .= '</td>';
                    $return .= "<td style='border-left: none; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 640px'>";
                    $return .= "<h3 style='margin-top: 3px'>";
                    $return .= $output[$i]['subject'];
                    $return .= '</h3>';
                    $return .= '</p>';
                    $return .= $output[$i]['details'];
                    $return .= '</p>';
                    $return .= '</td>';
                    $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                    $return .= '<b><u>' . __('Posted By') . '</b></u><br/>';
                    $return .= $output[$i]['author'] . '<br/><br/>';
                    $return .= '</td>';
                    $return .= '</tr>';
                }
                $return .= '</table>';
            }
            if ($mode == 'print') {
                return $return;
            } else {
                return $count;
            }
        }
    }
}

function getMessages1($guid, $connection2, $mode = '', $date = '', $fromdate = '', $msgcategory = '', $msgtype = '')
{
    $return = '';
    $return1 = '';
    $dataPosts = array();

    if ($msgtype == 'sms') {
        $msgtype = " AND pupilsightMessenger.sms = 'Y' ";
    } elseif ($msgtype == 'email') {
        $msgtype = " AND pupilsightMessenger.email = 'Y' ";
    } elseif ($msgtype == 'msgwall') {
        $msgtype = " AND pupilsightMessenger.messageWall = 'Y' ";
    } else {
        $msgtype = ' ';
    }
    //echo $msgtype;

    if ($msgcategory == 'All') {
        $msgcategory = ' ';
    } else {
        $msgcategory = " AND pupilsightMessenger.messengercategory = '$msgcategory'";
    }
    //echo $msgcategory;
    if ($date == '') {
        $date = date('Y-m-d');
    }
    //to meet jira id 1047 and few others which request to have date set
    if ($fromdate == '') {
        $fromdate = date('Y-m-d');
    }
    if ($fromdate != $date) {


        if ($mode != 'print' and $mode != 'count' and $mode != 'result') {
            $mode = 'print';
        }

        //Work out all role categories this user has, ignoring "Other"
        $roles = $_SESSION[$guid]['pupilsightRoleIDAll'];
        $roleCategory = '';
        $staff = false;
        $student = false;
        $parent = false;
        for ($i = 0; $i < count($roles); ++$i) {
            $roleCategory = getRoleCategory($roles[$i][0], $connection2);
            if ($roleCategory == 'Staff') {
                $staff = true;
            } elseif ($roleCategory == 'Student') {
                $student = true;
            } elseif ($roleCategory == 'Parent') {
                $parent = true;
            }
        }

        //If parent get a list of student IDs
        if ($parent) {
            $children = '(';
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }
            while ($row = $result->fetch()) {
                try {
                    $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {
                }
                while ($rowChild = $resultChild->fetch()) {
                    $children .= 'pupilsightPersonID=' . $rowChild['pupilsightPersonID'] . ' OR ';
                }
            }
            if ($children != '(') {
                $children = substr($children, 0, -4) . ')';
            } else {
                $children = false;
            }
        }

        //My roles
        $roles = $_SESSION[$guid]['pupilsightRoleIDAll'];
        $sqlWhere = '(';
        if (count($roles) > 0) {
            for ($i = 0; $i < count($roles); ++$i) {
                $dataPosts['role' . $roles[$i][0]] = $roles[$i][0];
                $sqlWhere .= 'id=:role' . $roles[$i][0] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
        }
        if ($sqlWhere != '(') {
            $dataPosts['date1'] = $date;
            $dataPosts['date2'] = $date;
            $dataPosts['date3'] = $date;
            $sqlPosts = "(SELECT pupilsightMessenger.*, title, surname, preferredName, authorRole.category AS category, image_240, concat('Role: ', pupilsightRole.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole AS authorRole ON (pupilsightPerson.pupilsightRoleIDPrimary=authorRole.pupilsightRoleID) JOIN pupilsightRole ON (pupilsightMessengerTarget.id=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Role' $msgcategory $msgtype AND $sqlWhere AND ((messageWall_date1 BETWEEN $fromdate AND :date1) OR (messageWall_date2 BETWEEN $fromdate AND :date2) OR (messageWall_date3 BETWEEN $fromdate AND :date3)) )";
            //      print_r($sqlPosts); die();
        }

        //My role categories
        try {
            $dataRoleCategory = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlRoleCategory = "SELECT DISTINCT category FROM pupilsightRole JOIN pupilsightPerson ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE pupilsightPersonID=:pupilsightPersonID";
            $resultRoleCategory = $connection2->prepare($sqlRoleCategory);
            $resultRoleCategory->execute($dataRoleCategory);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $sqlWhere = '(';
        if ($resultRoleCategory->rowCount() > 0) {
            $i = 0;
            while ($rowRoleCategory = $resultRoleCategory->fetch()) {
                $dataPosts['role' . $rowRoleCategory['category']] = $rowRoleCategory['category'];
                $sqlWhere .= 'id=:role' . $rowRoleCategory['category'] . ' OR ';
                ++$i;
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
        }
        if ($sqlWhere != '(') {
            $dataPosts['date1'] = $date;
            $dataPosts['date2'] = $date;
            $dataPosts['date3'] = $date;
            $sqlPosts = $sqlPosts . " UNION (SELECT DISTINCT pupilsightMessenger.*, title, surname, preferredName, authorRole.category AS category, image_240, concat('Role Category: ', pupilsightRole.category) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole AS authorRole ON (pupilsightPerson.pupilsightRoleIDPrimary=authorRole.pupilsightRoleID) JOIN pupilsightRole ON (pupilsightMessengerTarget.id=pupilsightRole.category) WHERE pupilsightMessengerTarget.type='Role Category' $msgcategory $msgtype AND $sqlWhere AND ((messageWall_date1 BETWEEN $fromdate AND :date1) OR (messageWall_date2 BETWEEN $fromdate AND :date2) OR (messageWall_date3 BETWEEN $fromdate AND :date3)) )";
        }

        //My year groups
        if ($staff) {
            $dataPosts['date4'] = $date;
            $dataPosts['date5'] = $date;
            $dataPosts['date6'] = $date;
            $dataPosts['pupilsightSchoolYearID0'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID0'] = $_SESSION[$guid]['pupilsightPersonID'];
            // Include staff by courses taught in the same year group.
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, 'Year Groups' AS source
                FROM pupilsightMessenger
                JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
                JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                JOIN pupilsightCourse ON (FIND_IN_SET(pupilsightMessengerTarget.id, pupilsightCourse.pupilsightYearGroupIDList))
                JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                JOIN pupilsightStaff ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID)
                WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID0 
                AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID0 $msgcategory $msgtype 
                AND pupilsightMessengerTarget.type='Year Group' AND pupilsightMessengerTarget.staff='Y' AND
                ((messageWall_date1 BETWEEN $fromdate AND :date4) OR (messageWall_date2 BETWEEN $fromdate AND :date5) OR (messageWall_date3 BETWEEN $fromdate AND :date6))
                GROUP BY pupilsightMessenger.pupilsightMessengerID )";
            // Include staff who are tutors of any student in the same year group.
            $sqlPosts .= "UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, 'Year Groups' AS source
                FROM pupilsightMessenger
                JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
                JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightYearGroupID=pupilsightMessengerTarget.id)
                JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID)
                JOIN pupilsightStaff ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightStaff.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightStaff.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightStaff.pupilsightPersonID)
                WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID0  
                AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID0 $msgcategory $msgtype 
                AND pupilsightMessengerTarget.type='Year Group' AND pupilsightMessengerTarget.staff='Y' AND
                ((messageWall_date1 BETWEEN $fromdate AND :date4) OR (messageWall_date2 BETWEEN $fromdate AND :date5) OR (messageWall_date3 BETWEEN $fromdate AND :date6))
                GROUP BY pupilsightMessenger.pupilsightMessengerID)";
            //print_r($sqlPosts);
        }
        if ($student) {
            $dataPosts['date7'] = $date;
            $dataPosts['date8'] = $date;
            $dataPosts['date9'] = $date;
            $dataPosts['pupilsightSchoolYearID1'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID1'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Year Group ', pupilsightYearGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightYearGroupID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightMessengerTarget.type='Year Group' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date7) OR (messageWall_date2 BETWEEN $fromdate AND :date8) OR (messageWall_date3 BETWEEN $fromdate AND :date9)) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID1 AND students='Y' )";
        }
        if ($parent and $children != false) {
            $dataPosts['date10'] = $date;
            $dataPosts['date11'] = $date;
            $dataPosts['date12'] = $date;
            $dataPosts['pupilsightSchoolYearID2'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Year Group: ', pupilsightYearGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightYearGroupID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE " . preg_replace('/pupilsightPersonID/', 'pupilsightStudentEnrolment.pupilsightPersonID', $children) . " AND pupilsightMessengerTarget.type='Year Group' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date10) OR (messageWall_date2 BETWEEN $fromdate AND :date11) OR (messageWall_date3 BETWEEN $fromdate AND :date12)) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND parents='Y' )";
        }

        //My roll groups
        if ($staff) {
            $sqlWhere = '(';
            try {
                $dataRollGroup = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonIDTutor' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlRollGroup = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND (pupilsightPersonIDTutor=:pupilsightPersonIDTutor OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3)';
                $resultRollGroup = $connection2->prepare($sqlRollGroup);
                $resultRollGroup->execute($dataRollGroup);
            } catch (PDOException $e) {
            }
            if ($resultRollGroup->rowCount() > 0) {
                while ($rowRollGroup = $resultRollGroup->fetch()) {
                    $dataPosts['roll' . $rowRollGroup['pupilsightRollGroupID']] = $rowRollGroup['pupilsightRollGroupID'];
                    $sqlWhere .= 'id=:roll' . $rowRollGroup['pupilsightRollGroupID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date13'] = $date;
                    $dataPosts['date14'] = $date;
                    $dataPosts['date15'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightRollGroup ON (pupilsightMessengerTarget.id=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightMessengerTarget.type='Roll Group' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date13) OR (messageWall_date2 BETWEEN $fromdate AND :date14) OR (messageWall_date3 BETWEEN $fromdate AND :date15)) AND $sqlWhere AND staff='Y' )";
                }
            }
        }
        if ($student) {
            $dataPosts['date16'] = $date;
            $dataPosts['date17'] = $date;
            $dataPosts['date18'] = $date;
            $dataPosts['pupilsightSchoolYearID3'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID2'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightRollGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID2 AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID3 AND pupilsightMessengerTarget.type='Roll Group' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date16) OR (messageWall_date2 BETWEEN $fromdate AND :date17) OR (messageWall_date3 BETWEEN $fromdate AND :date18)) AND students='Y' )";
        }
        if ($parent and $children != false) {
            $dataPosts['date19'] = $date;
            $dataPosts['date20'] = $date;
            $dataPosts['date21'] = $date;
            $dataPosts['pupilsightSchoolYearID4'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightRollGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE " . preg_replace('/pupilsightPersonID/', 'pupilsightStudentEnrolment.pupilsightPersonID', $children) . " AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID4 $msgcategory $msgtype AND pupilsightMessengerTarget.type='Roll Group' AND ((messageWall_date1 BETWEEN $fromdate AND :date19) OR (messageWall_date2 BETWEEN $fromdate AND :date20) OR (messageWall_date3 BETWEEN $fromdate AND :date21)) AND parents='Y' )";
        }

        //My courses
        //First check for any course, then do specific parent check
        try {
            $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlClasses = "SELECT DISTINCT pupilsightCourseClass.pupilsightCourseID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '%- Left'";
            $resultClasses = $connection2->prepare($sqlClasses);
            $resultClasses->execute($dataClasses);
        } catch (PDOException $e) {
        }
        $sqlWhere = '(';
        if ($resultClasses->rowCount() > 0) {
            while ($rowClasses = $resultClasses->fetch()) {
                $dataPosts['course' . $rowClasses['pupilsightCourseID']] = $rowClasses['pupilsightCourseID'];
                $sqlWhere .= 'id=:course' . $rowClasses['pupilsightCourseID'] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
            if ($sqlWhere != '(') {
                if ($staff) {
                    $dataPosts['date22'] = $date;
                    $dataPosts['date23'] = $date;
                    $dataPosts['date24'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date22) OR (messageWall_date2 BETWEEN $fromdate AND :date23) OR (messageWall_date3 BETWEEN $fromdate AND :date24)) AND $sqlWhere AND staff='Y' )";
                }
                if ($student) {
                    $dataPosts['date25'] = $date;
                    $dataPosts['date26'] = $date;
                    $dataPosts['date27'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory  $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date25) OR (messageWall_date2 BETWEEN $fromdate AND :date26) OR (messageWall_date3 BETWEEN $fromdate AND :date27)) AND $sqlWhere AND students='Y' )";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlClasses = 'SELECT DISTINCT pupilsightCourseClass.pupilsightCourseID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightCourseClassPerson.pupilsightPersonID', $children) . " AND NOT role LIKE '%- Left'";
                $resultClasses = $connection2->prepare($sqlClasses);
                $resultClasses->execute($dataClasses);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultClasses->rowCount() > 0) {
                while ($rowClasses = $resultClasses->fetch()) {
                    $dataPosts['course' . $rowClasses['pupilsightCourseID']] = $rowClasses['pupilsightCourseID'];
                    $sqlWhere .= 'id=:course' . $rowClasses['pupilsightCourseID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date28'] = $date;
                    $dataPosts['date29'] = $date;
                    $dataPosts['date30'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date28) OR (messageWall_date2 BETWEEN $fromdate AND :date29) OR (messageWall_date3 BETWEEN $fromdate AND :date30)) AND $sqlWhere AND parents='Y' )";
                }
            }
        }

        //My classes
        //First check for any role, then do specific parent check
        try {
            $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlClasses = "SELECT pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '%- Left'";
            $resultClasses = $connection2->prepare($sqlClasses);
            $resultClasses->execute($dataClasses);
        } catch (PDOException $e) {
        }
        $sqlWhere = '(';
        if ($resultClasses->rowCount() > 0) {
            while ($rowClasses = $resultClasses->fetch()) {
                $dataPosts['class' . $rowClasses['pupilsightCourseClassID']] = $rowClasses['pupilsightCourseClassID'];
                $sqlWhere .= 'id=:class' . $rowClasses['pupilsightCourseClassID'] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
            if ($sqlWhere != '(') {
                if ($staff) {
                    $dataPosts['date31'] = $date;
                    $dataPosts['date32'] = $date;
                    $dataPosts['date33'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date31) OR (messageWall_date2 BETWEEN $fromdate AND :date32) OR (messageWall_date3 BETWEEN $fromdate AND :date33)) AND $sqlWhere AND staff='Y' )";
                }
                if ($student) {
                    $dataPosts['date34'] = $date;
                    $dataPosts['date35'] = $date;
                    $dataPosts['date36'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date34) OR (messageWall_date2 BETWEEN $fromdate AND :date35) OR (messageWall_date3 BETWEEN $fromdate AND :date36)) AND $sqlWhere AND students='Y' )";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlClasses = 'SELECT pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightCourseClassPerson.pupilsightPersonID', $children) . " AND NOT role LIKE '%- Left'";
                $resultClasses = $connection2->prepare($sqlClasses);
                $resultClasses->execute($dataClasses);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultClasses->rowCount() > 0) {
                while ($rowClasses = $resultClasses->fetch()) {
                    $dataPosts['class' . $rowClasses['pupilsightCourseClassID']] = $rowClasses['pupilsightCourseClassID'];
                    $sqlWhere .= 'id=:class' . $rowClasses['pupilsightCourseClassID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date37'] = $date;
                    $dataPosts['date38'] = $date;
                    $dataPosts['date39'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date37) OR (messageWall_date2 BETWEEN $fromdate AND :date38) OR (messageWall_date3 BETWEEN $fromdate AND :date39)) AND $sqlWhere AND parents='Y' )";
                }
            }
        }

        //My activities
        if ($staff) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlActivities = 'SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStaff.pupilsightPersonID=:pupilsightPersonID';
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date40'] = $date;
                    $dataPosts['date41'] = $date;
                    $dataPosts['date42'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date40) OR (messageWall_date2 BETWEEN $fromdate AND :date41) OR (messageWall_date3 BETWEEN $fromdate AND :date42)) AND $sqlWhere AND staff='Y' )";
                }
            }
        }
        if ($student) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlActivities = "SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID AND status='Accepted'";
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date43'] = $date;
                    $dataPosts['date44'] = $date;
                    $dataPosts['date45'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date43) OR (messageWall_date2 BETWEEN $fromdate AND :date44) OR (messageWall_date3 BETWEEN $fromdate AND :date45)) AND $sqlWhere AND students='Y' )";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlActivities = 'SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightActivityStudent.pupilsightPersonID', $children) . " AND status='Accepted'";
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date46'] = $date;
                    $dataPosts['date47'] = $date;
                    $dataPosts['date48'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date46) OR (messageWall_date2 BETWEEN $fromdate AND :date47) OR (messageWall_date3 BETWEEN $fromdate AND :date48)) AND $sqlWhere AND parents='Y' )";
                }
            }
        }

        //Houses
        $dataPosts['date49'] = $date;
        $dataPosts['date50'] = $date;
        $dataPosts['date51'] = $date;
        $dataPosts['pupilsightPersonID3'] = $_SESSION[$guid]['pupilsightPersonID'];
        $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Houses: ', pupilsightHouse.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightPerson AS inHouse ON (pupilsightMessengerTarget.id=inHouse.pupilsightHouseID) JOIN pupilsightHouse ON (pupilsightPerson.pupilsightHouseID=pupilsightHouse.pupilsightHouseID)WHERE pupilsightMessengerTarget.type='Houses' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date49) OR (messageWall_date2 BETWEEN $fromdate AND :date50) OR (messageWall_date3 BETWEEN $fromdate AND :date51)) AND inHouse.pupilsightPersonID=:pupilsightPersonID3 )";

        //Individuals
        $dataPosts['date52'] = $date;
        $dataPosts['date53'] = $date;
        $dataPosts['date54'] = $date;
        $dataPosts['pupilsightPersonID4'] = $_SESSION[$guid]['pupilsightPersonID'];
        $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, 'Individual: You' AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightPerson AS individual ON (pupilsightMessengerTarget.id=individual.pupilsightPersonID) WHERE pupilsightMessengerTarget.type='Individuals' $msgcategory $msgtype AND ((messageWall_date1 BETWEEN $fromdate AND :date52) OR (messageWall_date2 BETWEEN $fromdate AND :date53) OR (messageWall_date3 BETWEEN $fromdate AND :date54)) AND individual.pupilsightPersonID=:pupilsightPersonID4 )";


        //Attendance
        if ($student) {
            try {
                $dataAttendance = array("pupilsightPersonID" => $_SESSION[$guid]['pupilsightPersonID'], "selectedDate" => $date, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "nowDate" => date("Y-m-d"));
                $sqlAttendance = "SELECT galp.pupilsightAttendanceLogPersonID, galp.type, galp.date FROM pupilsightAttendanceLogPerson AS galp JOIN pupilsightStudentEnrolment AS gse ON (galp.pupilsightPersonID=gse.pupilsightPersonID) JOIN pupilsightPerson AS gp ON (gse.pupilsightPersonID=gp.pupilsightPersonID) WHERE gp.status='Full' AND (gp.dateStart IS NULL OR gp.dateStart<=:nowDate) AND (gp.dateEnd IS NULL OR gp.dateEnd>=:nowDate) AND gse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND galp.date=:selectedDate AND galp.pupilsightPersonID=:pupilsightPersonID ORDER BY galp.pupilsightAttendanceLogPersonID DESC LIMIT 1";
                $resultAttendance = $connection2->prepare($sqlAttendance);
                $resultAttendance->execute($dataAttendance);
            } catch (PDOException $e) {
            }

            if ($resultAttendance->rowCount() > 0) {
                $studentAttendance = $resultAttendance->fetch();
                $dataPosts['date55'] = $date;
                $dataPosts['date56'] = $date;
                $dataPosts['date57'] = $date;
                $dataPosts['attendanceType1'] = $studentAttendance['type'] . ' ' . $date;
                $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Attendance:', pupilsightMessengerTarget.id) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Attendance' $msgcategory $msgtype AND pupilsightMessengerTarget.id=:attendanceType1 AND ((messageWall_date1 BETWEEN $fromdate AND :date55) OR (messageWall_date2 BETWEEN $fromdate AND :date56) OR (messageWall_date3 BETWEEN $fromdate AND :date57)) )";
            }
        }
        if ($parent and $children != false) {
            try {
                $dataAttendance = array("pupilsightPersonID" => $_SESSION[$guid]['pupilsightPersonID'], "selectedDate" => $date, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "nowDate" => date("Y-m-d"));
                $sqlAttendance = "SELECT galp.pupilsightAttendanceLogPersonID, galp.type, gp.firstName FROM pupilsightAttendanceLogPerson AS galp JOIN pupilsightStudentEnrolment AS gse ON (galp.pupilsightPersonID=gse.pupilsightPersonID) JOIN pupilsightPerson AS gp ON (gse.pupilsightPersonID=gp.pupilsightPersonID) WHERE gp.status='Full' AND (gp.dateStart IS NULL OR gp.dateStart<=:nowDate) AND (gp.dateEnd IS NULL OR gp.dateEnd>=:nowDate) AND gse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND galp.date=:selectedDate AND " . preg_replace('/pupilsightPersonID/', 'galp.pupilsightPersonID', $children) . " ORDER BY galp.pupilsightAttendanceLogPersonID DESC LIMIT 1";
                $resultAttendance = $connection2->prepare($sqlAttendance);
                $resultAttendance->execute($dataAttendance);
            } catch (PDOException $e) {
            }

            if ($resultAttendance->rowCount() > 0) {
                $studentAttendance = $resultAttendance->fetch();
                $dataPosts['date57'] = $date;
                $dataPosts['date58'] = $date;
                $dataPosts['date59'] = $date;
                $dataPosts['attendanceType2'] = $studentAttendance['type'] . ' ' . $date;
                $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Attendance:', pupilsightMessengerTarget.id, ' for ', '" . $studentAttendance['firstName'] . "') AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Attendance' $msgcategory $msgtype AND pupilsightMessengerTarget.id=:attendanceType2 AND ((messageWall_date1 BETWEEN $fromdate AND :date57) OR (messageWall_date2 BETWEEN $fromdate AND :date58) OR (messageWall_date3 BETWEEN $fromdate AND :date59) ))";
            }
        }

        // Groups
        if ($staff) {
            $dataPosts['date60'] = $date;
            $dataPosts['pupilsightPersonID5'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID5 
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.staff='Y' $msgcategory $msgtype 
        AND ((messageWall_date1 BETWEEN $fromdate AND :date60) OR (messageWall_date2 BETWEEN $fromdate AND :date60) OR (messageWall_date3 BETWEEN $fromdate AND :date60)) )";
        }
        if ($student) {
            $dataPosts['date61'] = $date;
            $dataPosts['pupilsightPersonID6'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID6 
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.students='Y' $msgcategory $msgtype 
        AND ((messageWall_date1 BETWEEN $fromdate AND :date61) OR (messageWall_date2 BETWEEN $fromdate AND :date61) OR (messageWall_date3 BETWEEN $fromdate AND :date61)) )";
        }
        if ($parent and $children != false) {
            $childrenQuery = str_replace('pupilsightPersonID', 'pupilsightGroupPerson.pupilsightPersonID', $children);
            $dataPosts['date62'] = $date;
            $dataPosts['pupilsightPersonID7'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE (pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID7 OR $childrenQuery)
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.parents='Y' $msgcategory $msgtype 
        AND ((messageWall_date1 BETWEEN $fromdate AND :date62) OR (messageWall_date2 BETWEEN $fromdate AND :date62) OR (messageWall_date3 BETWEEN $fromdate AND :date62)) )";
        }

        // Transport
        if ($staff) {
            $dataPosts['date63'] = $date;
            $dataPosts['pupilsightPersonID8'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE transportee.pupilsightPersonID=:pupilsightPersonID8 
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.staff='Y' $msgcategory $msgtype 
        AND ((messageWall_date1 BETWEEN $fromdate AND :date63) OR (messageWall_date2 BETWEEN $fromdate AND :date63) OR (messageWall_date3 BETWEEN $fromdate AND :date63)) )";
        }
        if ($student) {
            $dataPosts['date64'] = $date;
            $dataPosts['pupilsightPersonID9'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE transportee.pupilsightPersonID=:pupilsightPersonID9 
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.students='Y' $msgcategory $msgtype 
        AND ((messageWall_date1 BETWEEN $fromdate AND :date64) OR (messageWall_date2 BETWEEN $fromdate AND :date64) OR (messageWall_date3 BETWEEN $fromdate AND :date64)) )";
        }
        if ($parent and $children != false) {
            $childrenQuery = str_replace('pupilsightPersonID', 'transportee.pupilsightPersonID', $children);
            $dataPosts['date65'] = $date;
            $dataPosts['pupilsightPersonID10'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE (transportee.pupilsightPersonID=:pupilsightPersonID10 OR $childrenQuery)
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.parents='Y' $msgcategory $msgtype 
        AND ((messageWall_date1 BETWEEN $fromdate AND :date65) OR (messageWall_date2 BETWEEN $fromdate AND :date65) OR (messageWall_date3 BETWEEN $fromdate AND :date65)) )";
        }

        //SPIT OUT RESULTS
        if ($mode == 'result') {
            $resultReturn = array();
            $resultReturn[0] = $dataPosts;
            $resultReturn[1] = $sqlPosts . ' ORDER BY  pupilsightMessengerID desc ';

            return serialize($resultReturn);
        } else {
            $count = 0;
            try {
                $sqlPosts = $sqlPosts . ' ORDER BY  pupilsightMessengerID desc ';
                $resultPosts = $connection2->prepare($sqlPosts);
                $resultPosts->execute($dataPosts);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }

            if ($resultPosts->rowCount() < 1) {
                $return .= "<div class='alert alert-warning'>";
                $return .= __('There are no records to display.');
                $return .= '</div>';
            } else {
                $output = array();
                $last = '';
                $rowCount = 0;
                while ($rowPosts = $resultPosts->fetch()) {
                    if ($last == $rowPosts['pupilsightMessengerID']) {
                        $output[($count - 1)]['source'] = $output[($count - 1)]['source'] . '<br/>' . $rowPosts['source'];
                    } else {
                        $output[$count]['photo'] = $rowPosts['image_240'];
                        $output[$count]['subject'] = $rowPosts['subject'];
                        $output[$count]['details'] = $rowPosts['body'];
                        $output[$count]['author'] = formatName($rowPosts['title'], $rowPosts['preferredName'], $rowPosts['surname'], $rowPosts['category']);
                        $output[$count]['source'] = $rowPosts['source'];
                        $output[$count]['pupilsightMessengerID'] = $rowPosts['pupilsightMessengerID'];
                        $output[$count]['pupilsightPersonID'] = $rowPosts['pupilsightPersonID'];
                        $output[$count]['date'] = $rowPosts['messageWall_date1'];
                        $output[$count]['date1'] = $rowPosts['messageWall_date2'];
                        $output[$count]['date2'] = $rowPosts['messageWall_date3'];
                        $output[$count]['messengercategory'] = $rowPosts['messengercategory'];
                        $output[$count]['messageWall'] = $rowPosts['messageWall'];
                        $output[$count]['email'] = $rowPosts['email'];
                        $output[$count]['sms'] = $rowPosts['sms'];

                        ++$count;
                        $last = $rowPosts['pupilsightMessengerID'];
                    }
                }

                $return .= "<table cellspacing='0' style='margin-top: 10px'; width='100%';>";
                $return .= '<tr>';
                $return .= "<th style='text-align: center'>";
                $return .= __('Date');
                $return .= '</th>';
                $return .= '<th>';
                $return .= __('Message');
                $return .= '</th>';
                $return .= "<th style='text-align: center'>";
                $return .= __('Posted By');
                $return .= '</th>';
                $return .= '</tr>';

                $rowNum = 'odd';
                for ($i = 0; $i < count($output); ++$i) {
                    //exploding to compare date as Between query function is not working
                    $p = explode('-', $output[$i]['date']);
                    if ($output[$i]['date1'] != null) {
                        $p1 = explode('-', $output[$i]['date1']);
                    }
                    if ($output[$i]['date2'] != null) {
                        $p2 = explode('-', $output[$i]['date2']);
                    }
                    $pp = explode('-', $fromdate);
                    $ppp = explode('-', $date);
                    if ($output[$i]['date2'] != null) {
                        if (($p[0] >= $pp[0] && $p[1] >= $pp[1] && $p[2] >= $pp[2] && $p[0] <= $ppp[0] && $p[1] <= $ppp[1] && $p[2] <= $ppp[2]) or ($p1[0] >= $pp[0] && $p1[1] >= $pp[1] && $p1[2] >= $pp[2] && $p1[0] <= $ppp[0] && $p1[1] <= $ppp[1] && $p1[2] <= $ppp[2]) or ($p2[0] >= $pp[0] && $p2[1] >= $pp[1] && $p2[2] >= $pp[2] && $p2[0] <= $ppp[0] && $p2[1] <= $ppp[1] && $p2[2] <= $ppp[2])) {
                            if ($rowCount % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }
                            ++$rowCount;
                            $return .= "<tr class=$rowNum>";
                            $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                            //$return .= "<a name='".$output[$i]['pupilsightMessengerID']."'></a>";
                            //$return .= getUserPhoto($guid, $output[$i]['photo'], 75).'<br/>';

                            $return .= date('d/m/Y', strtotime($output[$i]['date'])) . '<br/>';
                            if ($output[$i]['date1'] != null) {
                                $return .= date('d/m/Y', strtotime($output[$i]['date1'])) . '<br/>';
                            }
                            if ($output[$i]['date2'] != null) {
                                $return .= date('d/m/Y', strtotime($output[$i]['date2'])) . '<br/>';
                            }

                            //$return .= '<b><u>'.__('Shared Via').'</b></u><br/>';
                            //$return .= $output[$i]['source'].'<br/><br/>';
                            $return .= '</td>';
                            $return .= "<td style='border-left: none; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 640px'>";
                            $return .= "<span class='badge bg-blue-lt'>";
                            if ($output[$i]['sms'] == 'Y') {
                                $return .= 'SMS';
                            }
                            if ($output[$i]['email'] == 'Y') {
                                $return .= 'Email';
                            }
                            if ($output[$i]['messageWall'] == 'Y') {
                                $return .= 'Message Wall';
                            }
                            $return .= "</span>";
                            $return .= "<span class='badge bg-azure-lt' style='margin-left: 10px;'>" . $output[$i]['messengercategory'] . "</span>";
                            $return .= "<h3 style='margin-top: 3px'>";
                            $return .= $output[$i]['subject'];
                            $return .= '</h3>';
                            $return .= '</p>';
                            $return .= $output[$i]['details'];
                            $return .= '</p>';
                            $return .= '</td>';
                            $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                            $return .= '<b><u>' . __('Posted By') . '</b></u><br/>';
                            $return .= $output[$i]['author'] . '<br/><br/>';
                            $return .= '</td>';
                            $return .= '</tr>';
                        }
                    } elseif ($output[$i]['date1'] != null) {
                        if (($p[0] >= $pp[0] && $p[1] >= $pp[1] && $p[2] >= $pp[2] && $p[0] <= $ppp[0] && $p[1] <= $ppp[1] && $p[2] <= $ppp[2]) or ($p1[0] >= $pp[0] && $p1[1] >= $pp[1] && $p1[2] >= $pp[2] && $p1[0] <= $ppp[0] && $p1[1] <= $ppp[1] && $p1[2] <= $ppp[2])) {
                            if ($rowCount % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }
                            ++$rowCount;
                            $return .= "<tr class=$rowNum>";
                            $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                            //$return .= "<a name='".$output[$i]['pupilsightMessengerID']."'></a>";
                            //$return .= getUserPhoto($guid, $output[$i]['photo'], 75).'<br/>';

                            $return .= date('d/m/Y', strtotime($output[$i]['date'])) . '<br/>';
                            if ($output[$i]['date1'] != null) {
                                $return .= date('d/m/Y', strtotime($output[$i]['date1'])) . '<br/>';
                            }
                            if ($output[$i]['date2'] != null) {
                                $return .= date('d/m/Y', strtotime($output[$i]['date2'])) . '<br/>';
                            }

                            //$return .= '<b><u>'.__('Shared Via').'</b></u><br/>';
                            //$return .= $output[$i]['source'].'<br/><br/>';
                            $return .= '</td>';
                            $return .= "<td style='border-left: none; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 640px'>";
                            $return .= "<span class='badge bg-blue-lt'>";
                            if ($output[$i]['sms'] == 'Y') {
                                $return .= 'SMS';
                            }
                            if ($output[$i]['email'] == 'Y') {
                                $return .= 'Email';
                            }
                            if ($output[$i]['messageWall'] == 'Y') {
                                $return .= 'Message Wall';
                            }
                            $return .= "</span>";
                            $return .= "<span class='badge bg-azure-lt' style='margin-left: 10px;'>" . $output[$i]['messengercategory'] . "</span>";
                            $return .= "<h3 style='margin-top: 3px'>";
                            $return .= $output[$i]['subject'];
                            $return .= '</h3>';
                            $return .= '</p>';
                            $return .= $output[$i]['details'];
                            $return .= '</p>';
                            $return .= '</td>';
                            $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                            $return .= '<b><u>' . __('Posted By') . '</b></u><br/>';
                            $return .= $output[$i]['author'] . '<br/><br/>';
                            $return .= '</td>';
                            $return .= '</tr>';
                        }
                    } else {
                        if ($p[0] >= $pp[0] && $p[1] >= $pp[1] && $p[2] >= $pp[2] && $p[0] <= $ppp[0] && $p[1] <= $ppp[1] && $p[2] <= $ppp[2]) {
                            if ($rowCount % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }
                            ++$rowCount;
                            $return .= "<tr class=$rowNum>";
                            $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                            //$return .= "<a name='".$output[$i]['pupilsightMessengerID']."'></a>";
                            //$return .= getUserPhoto($guid, $output[$i]['photo'], 75).'<br/>';

                            $return .= date('d/m/Y', strtotime($output[$i]['date'])) . '<br/>';
                            if ($output[$i]['date1'] != null) {
                                $return .= date('d/m/Y', strtotime($output[$i]['date1'])) . '<br/>';
                            }
                            if ($output[$i]['date2'] != null) {
                                $return .= date('d/m/Y', strtotime($output[$i]['date2'])) . '<br/>';
                            }

                            //$return .= '<b><u>'.__('Shared Via').'</b></u><br/>';
                            //$return .= $output[$i]['source'].'<br/><br/>';
                            $return .= '</td>';
                            $return .= "<td style='border-left: none; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 640px'>";
                            $return .= "<span class='badge bg-blue-lt'>";
                            if ($output[$i]['sms'] == 'Y') {
                                $return .= 'SMS';
                            }
                            if ($output[$i]['email'] == 'Y') {
                                $return .= 'Email';
                            }
                            if ($output[$i]['messageWall'] == 'Y') {
                                $return .= 'Message Wall';
                            }
                            $return .= "</span>";
                            $return .= "<span class='badge bg-azure-lt' style='margin-left: 10px;'>" . $output[$i]['messengercategory'] . "</span>";
                            $return .= "<h3 style='margin-top: 3px'>";
                            $return .= $output[$i]['subject'];
                            $return .= '</h3>';
                            $return .= '</p>';
                            $return .= $output[$i]['details'];
                            $return .= '</p>';
                            $return .= '</td>';
                            $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                            $return .= '<b><u>' . __('Posted By') . '</b></u><br/>';
                            $return .= $output[$i]['author'] . '<br/><br/>';
                            $return .= '</td>';
                            $return .= '</tr>';
                        }
                    }
                }

                $return .= '</table>';
            }
            if ($mode == 'print') {
                if ($rowCount == 0) {
                    $return1 .= "<div class='alert alert-warning'>";
                    $return1 .= __('There are no records to display.');
                    $return1 .= '</div>';
                    return $return1;
                }else{
                    return $return;
                }

            } else {
                return $count;
            }
        }
    } else {
        if ($mode != 'print' and $mode != 'count' and $mode != 'result') {
            $mode = 'print';
        }

        //Work out all role categories this user has, ignoring "Other"
        $roles = $_SESSION[$guid]['pupilsightRoleIDAll'];
        $roleCategory = '';
        $staff = false;
        $student = false;
        $parent = false;
        for ($i = 0; $i < count($roles); ++$i) {
            $roleCategory = getRoleCategory($roles[$i][0], $connection2);
            if ($roleCategory == 'Staff') {
                $staff = true;
            } elseif ($roleCategory == 'Student') {
                $student = true;
            } elseif ($roleCategory == 'Parent') {
                $parent = true;
            }
        }

        //If parent get a list of student IDs
        if ($parent) {
            $children = '(';
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }
            while ($row = $result->fetch()) {
                try {
                    $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {
                }
                while ($rowChild = $resultChild->fetch()) {
                    $children .= 'pupilsightPersonID=' . $rowChild['pupilsightPersonID'] . ' OR ';
                }
            }
            if ($children != '(') {
                $children = substr($children, 0, -4) . ')';
            } else {
                $children = false;
            }
        }

        //My roles
        $roles = $_SESSION[$guid]['pupilsightRoleIDAll'];
        $sqlWhere = '(';
        if (count($roles) > 0) {
            for ($i = 0; $i < count($roles); ++$i) {
                $dataPosts['role' . $roles[$i][0]] = $roles[$i][0];
                $sqlWhere .= 'id=:role' . $roles[$i][0] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
        }
        if ($sqlWhere != '(') {
            $dataPosts['date1'] = $date;
            $dataPosts['date2'] = $date;
            $dataPosts['date3'] = $date;
            $sqlPosts = "(SELECT pupilsightMessenger.*, title, surname, preferredName, authorRole.category AS category, image_240, concat('Role: ', pupilsightRole.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole AS authorRole ON (pupilsightPerson.pupilsightRoleIDPrimary=authorRole.pupilsightRoleID) JOIN pupilsightRole ON (pupilsightMessengerTarget.id=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Role'AND $sqlWhere AND ((messageWall_date1 =:date1 OR messageWall_date2=:date2 OR messageWall_date3 =:date3)) )";
            //print_r($sqlPosts);
        }

        //My role categories
        try {
            $dataRoleCategory = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlRoleCategory = "SELECT DISTINCT category FROM pupilsightRole JOIN pupilsightPerson ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE pupilsightPersonID=:pupilsightPersonID";
            $resultRoleCategory = $connection2->prepare($sqlRoleCategory);
            $resultRoleCategory->execute($dataRoleCategory);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $sqlWhere = '(';
        if ($resultRoleCategory->rowCount() > 0) {
            $i = 0;
            while ($rowRoleCategory = $resultRoleCategory->fetch()) {
                $dataPosts['role' . $rowRoleCategory['category']] = $rowRoleCategory['category'];
                $sqlWhere .= 'id=:role' . $rowRoleCategory['category'] . ' OR ';
                ++$i;
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
        }
        if ($sqlWhere != '(') {
            $dataPosts['date1'] = $date;
            $dataPosts['date2'] = $date;
            $dataPosts['date3'] = $date;
            $sqlPosts = $sqlPosts . " UNION (SELECT DISTINCT pupilsightMessenger.*, title, surname, preferredName, authorRole.category AS category, image_240, concat('Role Category: ', pupilsightRole.category) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole AS authorRole ON (pupilsightPerson.pupilsightRoleIDPrimary=authorRole.pupilsightRoleID) JOIN pupilsightRole ON (pupilsightMessengerTarget.id=pupilsightRole.category) WHERE pupilsightMessengerTarget.type='Role Category' $msgcategory $msgtype AND $sqlWhere AND ((messageWall_date1 =:date1 OR messageWall_date2=:date2 OR messageWall_date3 =:date3)))";
        }

        //My year groups
        if ($staff) {
            $dataPosts['date4'] = $date;
            $dataPosts['date5'] = $date;
            $dataPosts['date6'] = $date;
            $dataPosts['pupilsightSchoolYearID0'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID0'] = $_SESSION[$guid]['pupilsightPersonID'];
            // Include staff by courses taught in the same year group.
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, 'Year Groups' AS source
                FROM pupilsightMessenger
                JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
                JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                JOIN pupilsightCourse ON (FIND_IN_SET(pupilsightMessengerTarget.id, pupilsightCourse.pupilsightYearGroupIDList))
                JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                JOIN pupilsightStaff ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID)
                WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID0 $msgcategory $msgtype 
                AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID0
                AND pupilsightMessengerTarget.type='Year Group' AND pupilsightMessengerTarget.staff='Y' AND
                ((messageWall_date1 =:date4 OR messageWall_date2=:date5 OR messageWall_date3 =:date6))
                GROUP BY pupilsightMessenger.pupilsightMessengerID )";
            // Include staff who are tutors of any student in the same year group.
            $sqlPosts .= "UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, 'Year Groups' AS source
                FROM pupilsightMessenger
                JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
                JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightYearGroupID=pupilsightMessengerTarget.id)
                JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID)
                JOIN pupilsightStaff ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightStaff.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightStaff.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightStaff.pupilsightPersonID)
                WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID0 $msgcategory $msgtype 
                AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID0
                AND pupilsightMessengerTarget.type='Year Group' AND pupilsightMessengerTarget.staff='Y' AND
                ((messageWall_date1 =:date4 OR messageWall_date2=:date5 OR messageWall_date3 =:date6))
                GROUP BY pupilsightMessenger.pupilsightMessengerID)";
            //print_r($sqlPosts);
        }
        if ($student) {
            $dataPosts['date7'] = $date;
            $dataPosts['date8'] = $date;
            $dataPosts['date9'] = $date;
            $dataPosts['pupilsightSchoolYearID1'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID1'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Year Group ', pupilsightYearGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightYearGroupID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID1 $msgcategory $msgtype AND pupilsightMessengerTarget.type='Year Group' AND ((messageWall_date1 =:date7 OR messageWall_date2=:date8 OR messageWall_date3 =:date9)) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID1 AND students='Y')";
        }
        if ($parent and $children != false) {
            $dataPosts['date10'] = $date;
            $dataPosts['date11'] = $date;
            $dataPosts['date12'] = $date;
            $dataPosts['pupilsightSchoolYearID2'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Year Group: ', pupilsightYearGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightYearGroupID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE " . preg_replace('/pupilsightPersonID/', 'pupilsightStudentEnrolment.pupilsightPersonID', $children) . " AND pupilsightMessengerTarget.type='Year Group' $msgcategory $msgtype AND ((messageWall_date1 =:date10 OR messageWall_date2=:date11 OR messageWall_date3 =:date12)) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND parents='Y')";
        }

        //My roll groups
        if ($staff) {
            $sqlWhere = '(';
            try {
                $dataRollGroup = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonIDTutor' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlRollGroup = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND (pupilsightPersonIDTutor=:pupilsightPersonIDTutor OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3)';
                $resultRollGroup = $connection2->prepare($sqlRollGroup);
                $resultRollGroup->execute($dataRollGroup);
            } catch (PDOException $e) {
            }
            if ($resultRollGroup->rowCount() > 0) {
                while ($rowRollGroup = $resultRollGroup->fetch()) {
                    $dataPosts['roll' . $rowRollGroup['pupilsightRollGroupID']] = $rowRollGroup['pupilsightRollGroupID'];
                    $sqlWhere .= 'id=:roll' . $rowRollGroup['pupilsightRollGroupID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date13'] = $date;
                    $dataPosts['date14'] = $date;
                    $dataPosts['date15'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightRollGroup ON (pupilsightMessengerTarget.id=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightMessengerTarget.type='Roll Group' $msgcategory $msgtype AND ((messageWall_date1 =:date13 OR messageWall_date2=:date14 OR messageWall_date3 =:date15)) AND $sqlWhere AND staff='Y')";
                }
            }
        }
        if ($student) {
            $dataPosts['date16'] = $date;
            $dataPosts['date17'] = $date;
            $dataPosts['date18'] = $date;
            $dataPosts['pupilsightSchoolYearID3'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $dataPosts['pupilsightPersonID2'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightRollGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID2 $msgcategory $msgtype AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID3 AND pupilsightMessengerTarget.type='Roll Group' AND ((messageWall_date1 =:date16 OR messageWall_date2=:date17 OR messageWall_date3 =:date18)) AND students='Y')";
        }
        if ($parent and $children != false) {
            $dataPosts['date19'] = $date;
            $dataPosts['date20'] = $date;
            $dataPosts['date21'] = $date;
            $dataPosts['pupilsightSchoolYearID4'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Roll Group: ', pupilsightRollGroup.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightMessengerTarget.id=pupilsightStudentEnrolment.pupilsightRollGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE " . preg_replace('/pupilsightPersonID/', 'pupilsightStudentEnrolment.pupilsightPersonID', $children) . " AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID4 AND pupilsightMessengerTarget.type='Roll Group' $msgcategory $msgtype AND ((messageWall_date1 =:date19 OR messageWall_date2=:date20 OR messageWall_date3 =:date21)) AND parents='Y')";
        }

        //My courses
        //First check for any course, then do specific parent check
        try {
            $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlClasses = "SELECT DISTINCT pupilsightCourseClass.pupilsightCourseID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '%- Left'";
            $resultClasses = $connection2->prepare($sqlClasses);
            $resultClasses->execute($dataClasses);
        } catch (PDOException $e) {
        }
        $sqlWhere = '(';
        if ($resultClasses->rowCount() > 0) {
            while ($rowClasses = $resultClasses->fetch()) {
                $dataPosts['course' . $rowClasses['pupilsightCourseID']] = $rowClasses['pupilsightCourseID'];
                $sqlWhere .= 'id=:course' . $rowClasses['pupilsightCourseID'] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
            if ($sqlWhere != '(') {
                if ($staff) {
                    $dataPosts['date22'] = $date;
                    $dataPosts['date23'] = $date;
                    $dataPosts['date24'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory $msgtype AND ((messageWall_date1 =:date22 OR messageWall_date2=:date23 OR messageWall_date3 =:date24)) AND $sqlWhere AND staff='Y')";
                }
                if ($student) {
                    $dataPosts['date25'] = $date;
                    $dataPosts['date26'] = $date;
                    $dataPosts['date27'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory $msgtype AND ((messageWall_date1 =:date25 OR messageWall_date2=:date26 OR messageWall_date3 =:date27)) AND $sqlWhere AND students='Y')";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlClasses = 'SELECT DISTINCT pupilsightCourseClass.pupilsightCourseID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightCourseClassPerson.pupilsightPersonID', $children) . " AND NOT role LIKE '%- Left'";
                $resultClasses = $connection2->prepare($sqlClasses);
                $resultClasses->execute($dataClasses);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultClasses->rowCount() > 0) {
                while ($rowClasses = $resultClasses->fetch()) {
                    $dataPosts['course' . $rowClasses['pupilsightCourseID']] = $rowClasses['pupilsightCourseID'];
                    $sqlWhere .= 'id=:course' . $rowClasses['pupilsightCourseID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date28'] = $date;
                    $dataPosts['date29'] = $date;
                    $dataPosts['date30'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Course: ', pupilsightCourse.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourse ON (pupilsightMessengerTarget.id=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Course' $msgcategory $msgtype AND ((messageWall_date1 =:date28 OR messageWall_date2=:date29 OR messageWall_date3 =:date30)) AND $sqlWhere AND parents='Y')";
                }
            }
        }

        //My classes
        //First check for any role, then do specific parent check
        try {
            $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlClasses = "SELECT pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '%- Left'";
            $resultClasses = $connection2->prepare($sqlClasses);
            $resultClasses->execute($dataClasses);
        } catch (PDOException $e) {
        }
        $sqlWhere = '(';
        if ($resultClasses->rowCount() > 0) {
            while ($rowClasses = $resultClasses->fetch()) {
                $dataPosts['class' . $rowClasses['pupilsightCourseClassID']] = $rowClasses['pupilsightCourseClassID'];
                $sqlWhere .= 'id=:class' . $rowClasses['pupilsightCourseClassID'] . ' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -3) . ')';
            if ($sqlWhere != '(') {
                if ($staff) {
                    $dataPosts['date31'] = $date;
                    $dataPosts['date32'] = $date;
                    $dataPosts['date33'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory $msgtype AND ((messageWall_date1 =:date31 OR messageWall_date2=:date32 OR messageWall_date3 =:date33)) AND $sqlWhere AND staff='Y')";
                }
                if ($student) {
                    $dataPosts['date34'] = $date;
                    $dataPosts['date35'] = $date;
                    $dataPosts['date36'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory $msgtype AND ((messageWall_date1 =:date34 OR messageWall_date2=:date35 OR messageWall_date3 =:date36)) AND $sqlWhere AND students='Y')";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlClasses = 'SELECT pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightCourseClassPerson.pupilsightPersonID', $children) . " AND NOT role LIKE '%- Left'";
                $resultClasses = $connection2->prepare($sqlClasses);
                $resultClasses->execute($dataClasses);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultClasses->rowCount() > 0) {
                while ($rowClasses = $resultClasses->fetch()) {
                    $dataPosts['class' . $rowClasses['pupilsightCourseClassID']] = $rowClasses['pupilsightCourseClassID'];
                    $sqlWhere .= 'id=:class' . $rowClasses['pupilsightCourseClassID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date37'] = $date;
                    $dataPosts['date38'] = $date;
                    $dataPosts['date39'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Class: ', pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightCourseClass ON (pupilsightMessengerTarget.id=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightMessengerTarget.type='Class' $msgcategory $msgtype AND ((messageWall_date1 =:date37 OR messageWall_date2=:date38 OR messageWall_date3 =:date39)) AND $sqlWhere AND parents='Y')";
                }
            }
        }

        //My activities
        if ($staff) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlActivities = 'SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStaff.pupilsightPersonID=:pupilsightPersonID';
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date40'] = $date;
                    $dataPosts['date41'] = $date;
                    $dataPosts['date42'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory $msgtype AND ((messageWall_date1 =:date40 OR messageWall_date2=:date41 OR messageWall_date3 =:date42)) AND $sqlWhere AND staff='Y')";
                }
            }
        }
        if ($student) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlActivities = "SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID AND status='Accepted'";
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date43'] = $date;
                    $dataPosts['date44'] = $date;
                    $dataPosts['date45'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory $msgtype AND ((messageWall_date1 =:date43 OR messageWall_date2=:dat44 OR messageWall_date3 =:date45)) AND $sqlWhere AND students='Y')";
                }
            }
        }
        if ($parent and $children != false) {
            try {
                $dataActivities = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlActivities = 'SELECT pupilsightActivity.pupilsightActivityID FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND ' . preg_replace('/pupilsightPersonID/', 'pupilsightActivityStudent.pupilsightPersonID', $children) . " AND status='Accepted'";
                $resultActivities = $connection2->prepare($sqlActivities);
                $resultActivities->execute($dataActivities);
            } catch (PDOException $e) {
            }
            $sqlWhere = '(';
            if ($resultActivities->rowCount() > 0) {
                while ($rowActivities = $resultActivities->fetch()) {
                    $dataPosts['activity' . $rowActivities['pupilsightActivityID']] = $rowActivities['pupilsightActivityID'];
                    $sqlWhere .= 'id=:activity' . $rowActivities['pupilsightActivityID'] . ' OR ';
                }
                $sqlWhere = substr($sqlWhere, 0, -3) . ')';
                if ($sqlWhere != '(') {
                    $dataPosts['date46'] = $date;
                    $dataPosts['date47'] = $date;
                    $dataPosts['date48'] = $date;
                    $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat('Activity: ', pupilsightActivity.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightActivity ON (pupilsightMessengerTarget.id=pupilsightActivity.pupilsightActivityID) WHERE pupilsightMessengerTarget.type='Activity' $msgcategory $msgtype AND ((messageWall_date1 =:date46 OR messageWall_date2=:date47 OR messageWall_date3 =:date48)) AND $sqlWhere AND parents='Y')";
                }
            }
        }

        //Houses
        $dataPosts['date49'] = $date;
        $dataPosts['date50'] = $date;
        $dataPosts['date51'] = $date;
        $dataPosts['pupilsightPersonID3'] = $_SESSION[$guid]['pupilsightPersonID'];
        $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Houses: ', pupilsightHouse.name) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightPerson AS inHouse ON (pupilsightMessengerTarget.id=inHouse.pupilsightHouseID) JOIN pupilsightHouse ON (pupilsightPerson.pupilsightHouseID=pupilsightHouse.pupilsightHouseID)WHERE pupilsightMessengerTarget.type='Houses' $msgcategory $msgtype AND ((messageWall_date1 =:date49 OR messageWall_date2=:date50 OR messageWall_date3 =:date51)) AND inHouse.pupilsightPersonID=:pupilsightPersonID3)";

        //Individuals
        $dataPosts['date52'] = $date;
        $dataPosts['date53'] = $date;
        $dataPosts['date54'] = $date;
        $dataPosts['pupilsightPersonID4'] = $_SESSION[$guid]['pupilsightPersonID'];
        $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, 'Individual: You' AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightPerson AS individual ON (pupilsightMessengerTarget.id=individual.pupilsightPersonID) WHERE pupilsightMessengerTarget.type='Individuals' $msgcategory $msgtype AND ((messageWall_date1 =:date52 OR messageWall_date2=:date53 OR messageWall_date3 =:date54)) AND individual.pupilsightPersonID=:pupilsightPersonID4)";


        //Attendance
        if ($student) {
            try {
                $dataAttendance = array("pupilsightPersonID" => $_SESSION[$guid]['pupilsightPersonID'], "selectedDate" => $date, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "nowDate" => date("Y-m-d"));
                $sqlAttendance = "SELECT galp.pupilsightAttendanceLogPersonID, galp.type, galp.date FROM pupilsightAttendanceLogPerson AS galp JOIN pupilsightStudentEnrolment AS gse ON (galp.pupilsightPersonID=gse.pupilsightPersonID) JOIN pupilsightPerson AS gp ON (gse.pupilsightPersonID=gp.pupilsightPersonID) WHERE gp.status='Full' AND (gp.dateStart IS NULL OR gp.dateStart<=:nowDate) AND (gp.dateEnd IS NULL OR gp.dateEnd>=:nowDate) AND gse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND galp.date=:selectedDate AND galp.pupilsightPersonID=:pupilsightPersonID ORDER BY galp.pupilsightAttendanceLogPersonID DESC LIMIT 1";
                $resultAttendance = $connection2->prepare($sqlAttendance);
                $resultAttendance->execute($dataAttendance);
            } catch (PDOException $e) {
            }

            if ($resultAttendance->rowCount() > 0) {
                $studentAttendance = $resultAttendance->fetch();
                $dataPosts['date55'] = $date;
                $dataPosts['date56'] = $date;
                $dataPosts['date57'] = $date;
                $dataPosts['attendanceType1'] = $studentAttendance['type'] . ' ' . $date;
                $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Attendance:', pupilsightMessengerTarget.id) AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Attendance' $msgcategory $msgtype AND pupilsightMessengerTarget.id=:attendanceType1 AND ((messageWall_date1 =:date55 OR messageWall_date2=:date56 OR messageWall_date3 =:date57)) )";
            }
        }
        if ($parent and $children != false) {
            try {
                $dataAttendance = array("pupilsightPersonID" => $_SESSION[$guid]['pupilsightPersonID'], "selectedDate" => $date, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "nowDate" => date("Y-m-d"));
                $sqlAttendance = "SELECT galp.pupilsightAttendanceLogPersonID, galp.type, gp.firstName FROM pupilsightAttendanceLogPerson AS galp JOIN pupilsightStudentEnrolment AS gse ON (galp.pupilsightPersonID=gse.pupilsightPersonID) JOIN pupilsightPerson AS gp ON (gse.pupilsightPersonID=gp.pupilsightPersonID) WHERE gp.status='Full' AND (gp.dateStart IS NULL OR gp.dateStart<=:nowDate) AND (gp.dateEnd IS NULL OR gp.dateEnd>=:nowDate) AND gse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND galp.date=:selectedDate AND " . preg_replace('/pupilsightPersonID/', 'galp.pupilsightPersonID', $children) . " ORDER BY galp.pupilsightAttendanceLogPersonID DESC LIMIT 1";
                $resultAttendance = $connection2->prepare($sqlAttendance);
                $resultAttendance->execute($dataAttendance);
            } catch (PDOException $e) {
            }

            if ($resultAttendance->rowCount() > 0) {
                $studentAttendance = $resultAttendance->fetch();
                $dataPosts['date57'] = $date;
                $dataPosts['date58'] = $date;
                $dataPosts['date59'] = $date;
                $dataPosts['attendanceType2'] = $studentAttendance['type'] . ' ' . $date;
                $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Attendance:', pupilsightMessengerTarget.id, ' for ', '" . $studentAttendance['firstName'] . "') AS source FROM pupilsightMessenger JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID) JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightMessengerTarget.type='Attendance' $msgcategory $msgtype AND pupilsightMessengerTarget.id=:attendanceType2 AND ((messageWall_date1 =:date57 OR messageWall_date2=:date58 OR messageWall_date3 =:date59)) )";
            }
        }

        // Groups
        if ($staff) {
            $dataPosts['date60'] = $date;
            $dataPosts['pupilsightPersonID5'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID5 $msgcategory $msgtype 
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.staff='Y'
        AND ((messageWall_date1 =:date60 OR messageWall_date2=:date60 OR messageWall_date3 =:date60)) )";
        }
        if ($student) {
            $dataPosts['date61'] = $date;
            $dataPosts['pupilsightPersonID6'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID6 $msgcategory $msgtype 
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.students='Y'
        AND ((messageWall_date1 =:date61 OR messageWall_date2=:date61 OR messageWall_date3 =:date61)) )";
        }
        if ($parent and $children != false) {
            $childrenQuery = str_replace('pupilsightPersonID', 'pupilsightGroupPerson.pupilsightPersonID', $children);
            $dataPosts['date62'] = $date;
            $dataPosts['pupilsightPersonID7'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, title, surname, preferredName, category, image_240, concat(pupilsightGroup.name, ' Group') AS source
        FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightGroup ON (pupilsightMessengerTarget.id=pupilsightGroup.pupilsightGroupID)
        JOIN pupilsightGroupPerson ON (pupilsightGroup.pupilsightGroupID=pupilsightGroupPerson.pupilsightGroupID)
        WHERE (pupilsightGroupPerson.pupilsightPersonID=:pupilsightPersonID7 OR $childrenQuery)
        AND pupilsightMessengerTarget.type='Group' AND pupilsightMessengerTarget.parents='Y' $msgcategory $msgtype 
        AND ((messageWall_date1 =:date62 OR messageWall_date2=:date62 OR messageWall_date3 =:date62)) )";
        }

        // Transport
        if ($staff) {
            $dataPosts['date63'] = $date;
            $dataPosts['pupilsightPersonID8'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE transportee.pupilsightPersonID=:pupilsightPersonID8 $msgcategory $msgtype 
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.staff='Y'
        AND ((messageWall_date1 =:date63 OR messageWall_date2=:date63 OR messageWall_date3 =:date63)) )";
        }
        if ($student) {
            $dataPosts['date64'] = $date;
            $dataPosts['pupilsightPersonID9'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE transportee.pupilsightPersonID=:pupilsightPersonID9 $msgcategory $msgtype 
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.students='Y'
        AND ((messageWall_date1 =:date64 OR messageWall_date2=:date64 OR messageWall_date3 =:date64)) )";
        }
        if ($parent and $children != false) {
            $childrenQuery = str_replace('pupilsightPersonID', 'transportee.pupilsightPersonID', $children);
            $dataPosts['date65'] = $date;
            $dataPosts['pupilsightPersonID10'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sqlPosts = $sqlPosts . " UNION (SELECT pupilsightMessenger.*, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, category, pupilsightPerson.image_240, concat('Transport ', transportee.transport) AS source FROM pupilsightMessenger
        JOIN pupilsightMessengerTarget ON (pupilsightMessengerTarget.pupilsightMessengerID=pupilsightMessenger.pupilsightMessengerID)
        JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        JOIN pupilsightPerson as transportee ON (pupilsightMessengerTarget.id=transportee.transport)
        WHERE (transportee.pupilsightPersonID=:pupilsightPersonID10 OR $childrenQuery)
        AND pupilsightMessengerTarget.type='Transport' AND pupilsightMessengerTarget.parents='Y' $msgcategory $msgtype 
        AND ((messageWall_date1 =:date65 OR messageWall_date2=:date65 OR messageWall_date3 =:date65)) )";
        }

        //SPIT OUT RESULTS
        if ($mode == 'result') {
            $resultReturn = array();
            $resultReturn[0] = $dataPosts;
            $resultReturn[1] = $sqlPosts . ' ORDER BY /*subject,*/ pupilsightMessengerID desc /*, source*/';

            return serialize($resultReturn);
        } else {
            $count = 0;
            try {
                $sqlPosts = $sqlPosts . ' ORDER BY /*subject,*/ pupilsightMessengerID desc /*, source*/';
                $resultPosts = $connection2->prepare($sqlPosts);
                $resultPosts->execute($dataPosts);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }

            if ($resultPosts->rowCount() < 1) {
                $return .= "<div class='alert alert-warning'>";
                $return .= __('There are no records to display.');
                $return .= '</div>';
            } else {
                $output = array();
                $last = '';
                while ($rowPosts = $resultPosts->fetch()) {
                    if ($last == $rowPosts['pupilsightMessengerID']) {
                        $output[($count - 1)]['source'] = $output[($count - 1)]['source'] . '<br/>' . $rowPosts['source'];
                    } else {
                        $output[$count]['photo'] = $rowPosts['image_240'];
                        $output[$count]['subject'] = $rowPosts['subject'];
                        $output[$count]['details'] = $rowPosts['body'];
                        $output[$count]['author'] = formatName($rowPosts['title'], $rowPosts['preferredName'], $rowPosts['surname'], $rowPosts['category']);
                        $output[$count]['source'] = $rowPosts['source'];
                        $output[$count]['pupilsightMessengerID'] = $rowPosts['pupilsightMessengerID'];
                        $output[$count]['pupilsightPersonID'] = $rowPosts['pupilsightPersonID'];
                        $output[$count]['date'] = $rowPosts['messageWall_date1'];
                        $output[$count]['date1'] = $rowPosts['messageWall_date2'];
                        $output[$count]['date2'] = $rowPosts['messageWall_date3'];
                        $output[$count]['messengercategory'] = $rowPosts['messengercategory'];
                        $output[$count]['messageWall'] = $rowPosts['messageWall'];
                        $output[$count]['email'] = $rowPosts['email'];
                        $output[$count]['sms'] = $rowPosts['sms'];

                        ++$count;
                        $last = $rowPosts['pupilsightMessengerID'];
                    }
                }

                $return .= "<table cellspacing='0' style='margin-top: 10px'; width='100%';>";
                $return .= '<tr>';
                $return .= "<th style='text-align: center'>";
                $return .= __('Date');
                $return .= '</th>';
                $return .= '<th>';
                $return .= __('Message');
                $return .= '</th>';
                $return .= "<th style='text-align: center'>";
                $return .= __('Posted By');
                $return .= '</th>';
                $return .= '</tr>';
                $rowCount = 0;
                $rowNum = 'odd';
                for ($i = 0; $i < count($output); ++$i) {
                    if ($rowCount % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$rowCount;
                    $return .= "<tr class=$rowNum>";
                    $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                    //$return .= "<a name='".$output[$i]['pupilsightMessengerID']."'></a>";
                    //$return .= getUserPhoto($guid, $output[$i]['photo'], 75).'<br/>';
                    $return .= date('d/m/Y', strtotime($output[$i]['date'])) . '<br/>';
                    if ($output[$i]['date1'] != null) {
                        $return .= date('d/m/Y', strtotime($output[$i]['date1'])) . '<br/>';
                    }
                    if ($output[$i]['date2'] != null) {
                        $return .= date('d/m/Y', strtotime($output[$i]['date2'])) . '<br/>';
                    }

                    //$return .= '<b><u>'.__('Shared Via').'</b></u><br/>';
                    //$return .= $output[$i]['source'].'<br/><br/>';
                    $return .= '</td>';
                    $return .= "<td style='border-left: none; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 640px'>";
                    $return .= "<span class='badge bg-blue-lt'>";
                    if ($output[$i]['sms'] == 'Y') {
                        $return .= 'SMS';
                    }
                    if ($output[$i]['email'] == 'Y') {
                        $return .= 'Email';
                    }
                    if ($output[$i]['messageWall'] == 'Y') {
                        $return .= 'Message Wall';
                    }
                    $return .= "</span>";
                    $return .= "<span class='badge bg-azure-lt' style='margin-left: 10px;'>" . $output[$i]['messengercategory'] . "</span>";
                    $return .= "<h3 style='margin-top: 3px'>";
                    $return .= $output[$i]['subject'];
                    $return .= '</h3>';
                    $return .= '</p>';
                    $return .= $output[$i]['details'];
                    $return .= '</p>';
                    $return .= '</td>';
                    $return .= "<td style='text-align: center; vertical-align: top; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid #666; width: 100px'>";
                    $return .= '<b><u>' . __('Posted By') . '</b></u><br/>';
                    $return .= $output[$i]['author'] . '<br/><br/>';
                    $return .= '</td>';
                    $return .= '</tr>';
                }
                $return .= '</table>';
            }
            if ($mode == 'print') {
                return $return;
            } else {
                return $count;
            }
        }
    }
}
