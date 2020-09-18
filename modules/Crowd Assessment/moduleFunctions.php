<?php
/*
Pupilsight, Flexible & Open School System
*/

function getLessons($guid, $connection2, $and = '')
{
    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');

    $fields = 'pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkDetails, date, pupilsightPlannerEntry.pupilsightCourseClassID, homeworkCrowdAssessOtherTeachersRead, homeworkCrowdAssessClassmatesRead, homeworkCrowdAssessOtherStudentsRead, homeworkCrowdAssessSubmitterParentsRead, homeworkCrowdAssessClassmatesParentsRead, homeworkCrowdAssessOtherParentsRead';
    //Get my classes (student, teacher, classmates)
    $data = array('today1' => $today, 'pupilsightPersonID1' => $_SESSION[$guid]['pupilsightPersonID'], 'now1' => $now, 'pupilsightSchoolYearID1' => $_SESSION[$guid]['pupilsightSchoolYearID']);
    $sql = "(SELECT $fields FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE homeworkSubmissionDateOpen<=:today1 AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID1 AND (role='Teacher' OR role='Student') AND homeworkCrowdAssess='Y' AND ADDTIME(date, '1344:00:00.0')>=:now1 AND pupilsightSchoolYearID=:pupilsightSchoolYearID1 $and)";

    //Get other classes if teacher
    try {
        $dataTeacher = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        $sqlTeacher = "SELECT * FROM pupilsightStaff WHERE pupilsightPersonID=:pupilsightPersonID AND type='Teaching'";
        $resultTeacher = $connection2->prepare($sqlTeacher);
        $resultTeacher->execute($dataTeacher);
    } catch (PDOException $e) {
    }
    if ($resultTeacher->rowCount() == 1) {
        $data['today2'] = $today;
        $data['pupilsightSchoolYearID2'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $data['now2'] = $now;
        $sql = $sql." UNION (SELECT $fields FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE homeworkSubmissionDateOpen<=:today2 AND homeworkCrowdAssess='Y' AND ADDTIME(date, '1344:00:00.0')>=:now2 AND pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND homeworkCrowdAssessOtherTeachersRead='Y' $and)";
    }

    //Get other classes if student
    try {
        $dataStudent = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sqlStudent = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
        $resultStudent = $connection2->prepare($sqlStudent);
        $resultStudent->execute($dataStudent);
    } catch (PDOException $e) {
    }
    if ($resultStudent->rowCount() == 1) {
        $data['today3'] = $today;
        $data['pupilsightSchoolYearID3'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $data['now3'] = $now;
        $sql = $sql." UNION (SELECT $fields FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE homeworkSubmissionDateOpen<=:today3 AND homeworkCrowdAssess='Y' AND ADDTIME(date, '1344:00:00.0')>=:now3 AND pupilsightSchoolYearID=:pupilsightSchoolYearID3 AND homeworkCrowdAssessOtherStudentsRead='Y' $and)";
    }

    //Get classes if parent
    try {
        $dataParent = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        $sqlParent = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
        $resultParent = $connection2->prepare($sqlParent);
        $resultParent->execute($dataParent);
    } catch (PDOException $e) {
    }

    if ($resultParent->rowCount() > 0) {
        //Get child list for family
        $childCount = 0;
        while ($rowParent = $resultParent->fetch()) {
            try {
                $dataChild = array('pupilsightFamilyID' => $rowParent['pupilsightFamilyID']);
                $sqlChild = "SELECT pupilsightPerson.pupilsightPersonID, image_240, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY surname, preferredName ";
                $resultChild = $connection2->prepare($sqlChild);
                $resultChild->execute($dataChild);
            } catch (PDOException $e) {
            }
            while ($rowChild = $resultChild->fetch()) {
                //submitters+classmates parents
                $data['today4'.$childCount] = $today;
                $data['pupilsightSchoolYearID4'.$childCount] = $_SESSION[$guid]['pupilsightSchoolYearID'];
                $data['now4'.$childCount] = $now;
                $data['pupilsightPersonID4'.$childCount] = $rowChild['pupilsightPersonID'];
                $sql = $sql." UNION (SELECT $fields FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE homeworkSubmissionDateOpen<=:today4$childCount AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID4$childCount AND role='Student' AND homeworkCrowdAssess='Y' AND ADDTIME(date, '1344:00:00.0')>=:now4$childCount AND pupilsightSchoolYearID=:pupilsightSchoolYearID4$childCount AND (homeworkCrowdAssessSubmitterParentsRead='Y' OR homeworkCrowdAssessClassmatesParentsRead='Y') $and)";
                ++$childCount;
            }
        }
        //Other classes
        $data['today5'] = $today;
        $data['pupilsightSchoolYearID5'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $data['now5'] = $now;
        $sql = $sql." UNION (SELECT $fields FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE homeworkSubmissionDateOpen<=:today5 AND homeworkCrowdAssess='Y' AND ADDTIME(date, '1344:00:00.0')>=:now5 AND pupilsightSchoolYearID=:pupilsightSchoolYearID5 AND homeworkCrowdAssessOtherParentsRead='Y' $and)";
    }

    return array($data, $sql);
}

function getCARole($guid, $connection2, $pupilsightCourseClassID)
{
    $role = '';
    if (getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2) == 'Parent') {
        $role = 'Parent';
        $childInClass = false;

        //Is child of this perosn in this class?
        $count = 0;
        $children = array();

        try {
            $dataParent = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlParent = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
            $resultParent = $connection2->prepare($sqlParent);
            $resultParent->execute($dataParent);
        } catch (PDOException $e) {
        }

        if ($resultParent->rowCount() > 0) {
            //Get child list for family
            while ($rowParent = $resultParent->fetch()) {
                try {
                    $dataChild = array('pupilsightFamilyID' => $rowParent['pupilsightFamilyID']);
                    $sqlChild = "SELECT pupilsightPerson.pupilsightPersonID, image_240, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY surname, preferredName ";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {
                }
                while ($rowChild = $resultChild->fetch()) {
                    try {
                        $dataInClass = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $rowChild['pupilsightPersonID']);
                        $sqlInClass = "SELECT * FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID AND role='Student'";
                        $resultInClass = $connection2->prepare($sqlInClass);
                        $resultInClass->execute($dataInClass);
                    } catch (PDOException $e) {
                    }
                    if ($resultInClass->rowCount() == 1) {
                        $childInClass = true;
                        $rowInClass = $resultInClass->fetch();
                        $children[$count] = $rowInClass['pupilsightPersonID'];
                        ++$count;
                    }
                }
            }
        }
        if ($childInClass == true) {
            $role = 'Parent - Child In Class';
        }
    } else {
        //Check if in staff table as teacher
        try {
            $dataTeacher = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlTeacher = "SELECT * FROM pupilsightStaff WHERE pupilsightPersonID=:pupilsightPersonID AND type='Teaching'";
            $resultTeacher = $connection2->prepare($sqlTeacher);
            $resultTeacher->execute($dataTeacher);
        } catch (PDOException $e) {
        }

        if ($resultTeacher->rowCount() == 1) {
            $role = 'Teacher';
            try {
                $dataRole = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlRole = "SELECT * FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID AND role='Teacher'";
                $resultRole = $connection2->prepare($sqlRole);
                $resultRole->execute($dataRole);
            } catch (PDOException $e) {
            }
            if ($resultRole->rowCount() >= 1) {
                $role = 'Teacher - In Class';
            }
        }

        //Check if student
        try {
            $dataStudent = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlStudent = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $resultStudent = $connection2->prepare($sqlStudent);
            $resultStudent->execute($dataStudent);
        } catch (PDOException $e) {
        }

        if ($resultStudent->rowCount() == 1) {
            $role = 'Student';
            try {
                $dataRole = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlRole = "SELECT * FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID AND role='Student'";
                $resultRole = $connection2->prepare($sqlRole);
                $resultRole->execute($dataRole);
            } catch (PDOException $e) {
            }
            if ($resultRole->rowCount() == 1) {
                $role = 'Student - In Class';
            }
        }
    }

    return $role;
}

function getStudents($guid, $connection2, $role, $pupilsightCourseClassID, $homeworkCrowdAssessOtherTeachersRead, $homeworkCrowdAssessOtherParentsRead, $homeworkCrowdAssessSubmitterParentsRead, $homeworkCrowdAssessClassmatesParentsRead, $homeworkCrowdAssessOtherStudentsRead, $homeworkCrowdAssessClassmatesRead, $and = '')
{
    $data = null;
    $sqlList = null;
    //Fetch and display assessible submissions
    $sqlList = '';
    if (($role == 'Teacher' and $homeworkCrowdAssessOtherTeachersRead == 'Y') or ($role == 'Teacher - In Class')) {
        //Get All students in class
        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
        $sqlList = "SELECT * FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $and ORDER BY surname, preferredName";
    } elseif ($role == 'Parent' and $homeworkCrowdAssessOtherParentsRead == 'Y') {
        //Get all students in class
        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
        $sqlList = "SELECT * FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $and ORDER BY surname, preferredName";
    } elseif ($role == 'Parent - Child In Class') {
        //Get array of children
        $count = 0;
        $children = array();
        try {
            $dataParent = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlParent = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
            $resultParent = $connection2->prepare($sqlParent);
            $resultParent->execute($dataParent);
        } catch (PDOException $e) {
        }
        if ($resultParent->rowCount() > 0) {
            //Get child list for family
            $childCount = 0;
            while ($rowParent = $resultParent->fetch()) {
                try {
                    $dataChild = array('pupilsightFamilyID' => $rowParent['pupilsightFamilyID']);
                    $sqlChild = "SELECT pupilsightPerson.pupilsightPersonID, image_240, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY surname, preferredName ";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {
                }
                while ($rowChild = $resultChild->fetch()) {
                    $children[$count] = $rowChild['pupilsightPersonID'];
                    ++$count;
                }
            }
        }

        if ($homeworkCrowdAssessSubmitterParentsRead == 'Y' and $homeworkCrowdAssessClassmatesParentsRead == 'Y') {
            //Get all students in class
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sqlList = "SELECT * FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $and ORDER BY surname, preferredName";
        } elseif ($homeworkCrowdAssessSubmitterParentsRead == 'Y') {
            //Get only parent's children
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sqlListWhere = 'AND (';
            for ($i = 0; $i < $count; ++$i) {
                $data[$children[$i]] = $children[$i];
                $sqlListWhere .= 'pupilsightCourseClassPerson.pupilsightPersonID=:'.$children[$i].' OR ';
            }
            if ($sqlListWhere == 'AND (') {
                $sqlListWhere = '';
            } else {
                $sqlListWhere = substr($sqlListWhere, 0, -4).')';
            }
            $sqlList = "SELECT * FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $sqlListWhere $and ORDER BY surname, preferredName";
        } elseif ($homeworkCrowdAssessClassmatesParentsRead == 'Y') {
            //Get all children except parent's children
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sqlListWhere = '';
            for ($i = 0; $i < $count; ++$i) {
                $data[$children[$i]] = $children[$i];
                $sqlListWhere .= ' AND NOT pupilsightCourseClassPerson.pupilsightPersonID=:'.$children[$i];
            }
            $sqlList = "SELECT * FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $sqlListWhere $and ORDER BY surname, preferredName";
        }
    } elseif (($role == 'Student' and $homeworkCrowdAssessOtherStudentsRead == 'Y') or ($role == 'Student - In Class' and $homeworkCrowdAssessClassmatesRead == 'Y')) {
        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
        $sqlList = "SELECT * FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $and ORDER BY surname, preferredName";
    } elseif ($role == 'Student - In Class') {
        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID,'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        $sqlList = "SELECT * FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $and ORDER BY surname, preferredName";
    }

    return array($data, $sqlList);
}

function getThread($guid, $connection2, $pupilsightPlannerEntryHomeworkID, $parent, $level, $self, $pupilsightPersonID, $pupilsightPlannerEntryID)
{
    $output = '';

    try {
        if ($parent == null) {
            $dataDiscuss = array('pupilsightPlannerEntryHomeworkID' => $pupilsightPlannerEntryHomeworkID);
            $sqlDiscuss = 'SELECT pupilsightCrowdAssessDiscuss.*, title, surname, preferredName, category FROM pupilsightCrowdAssessDiscuss JOIN pupilsightPerson ON (pupilsightCrowdAssessDiscuss.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightPlannerEntryHomeworkID=:pupilsightPlannerEntryHomeworkID AND pupilsightCrowdAssessDiscussIDReplyTo IS NULL ORDER BY timestamp';
        } else {
            $dataDiscuss = array('pupilsightPlannerEntryHomeworkID' => $pupilsightPlannerEntryHomeworkID, 'parent' => $parent, 'self' => $self);
            $sqlDiscuss = 'SELECT pupilsightCrowdAssessDiscuss.*, title, surname, preferredName, category FROM pupilsightCrowdAssessDiscuss JOIN pupilsightPerson ON (pupilsightCrowdAssessDiscuss.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightPlannerEntryHomeworkID=:pupilsightPlannerEntryHomeworkID AND pupilsightCrowdAssessDiscussIDReplyTo=:parent AND pupilsightCrowdAssessDiscussID=:self ORDER BY timestamp';
        }
        $resultDiscuss = $connection2->prepare($sqlDiscuss);
        $resultDiscuss->execute($dataDiscuss);
    } catch (PDOException $e) {
        $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($level == 0 and $resultDiscuss->rowCount() == 0) {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('This conversation has not yet begun!');
        $output .= '</div>';
    } else {
        while ($rowDiscuss = $resultDiscuss->fetch()) {
            $classExtra = '';
            $namePerson = __('{name} said', [
                'name' => formatName($rowDiscuss['title'], $rowDiscuss['preferredName'], $rowDiscuss['surname'], $rowDiscuss['category'])
            ]);
            $datetimePosted = __('Posted at {hourPosted} on {datePosted}', [
                'hourPosted' => '<b>'.substr($rowDiscuss['timestamp'], 11, 5).'</b>', 
                'datePosted' => '<b>'.dateConvertBack($guid, substr($rowDiscuss['timestamp'], 0, 10)).'</b>'
            ]);
            if ($level == 0) {
                $classExtra = 'chatBoxFirst';
            }

            $output .= "<a name='".$rowDiscuss['pupilsightCrowdAssessDiscussID']."'></a>";
            $output .= "<table class='noIntBorder chatBox $classExtra' cellspacing='0' style='width: ".(755 - ($level * 15)).'px; margin-left: '.($level * 15)."px'>";
            $output .= "<tr>";
            $output .= "<td><i>".$namePerson.'</i>:</td>';
            $output .= "<td style='text-align: right'><i>".$datetimePosted."</i></td>";         
            $output .= "</tr>";
            $output .= "<tr>";
            $output .= "<td style='padding: 1px 4px' colspan=2><b>".$rowDiscuss['comment'].'</b></td>';
            $output .= "</tr>";
            $output .= "<tr>";
            $output .= "<td style='text-align: right' colspan=2><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/crowdAssess_view_discuss_post.php&pupilsightPersonID=$pupilsightPersonID&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&pupilsightPlannerEntryHomeworkID=$pupilsightPlannerEntryHomeworkID&replyTo=".$rowDiscuss['pupilsightCrowdAssessDiscussID']."'>Reply</a></td>";
            $output .= "</tr>";
            $output .= "</table>";

            //Get any replies
            try {
                $dataReplies = array('pupilsightPlannerEntryHomeworkID' => $pupilsightPlannerEntryHomeworkID, 'pupilsightCrowdAssessDiscussID' => $rowDiscuss['pupilsightCrowdAssessDiscussID']);
                $sqlReplies = 'SELECT pupilsightCrowdAssessDiscuss.*, title, surname, preferredName FROM pupilsightCrowdAssessDiscuss JOIN pupilsightPerson ON (pupilsightCrowdAssessDiscuss.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPlannerEntryHomeworkID=:pupilsightPlannerEntryHomeworkID AND pupilsightCrowdAssessDiscussIDReplyTo=:pupilsightCrowdAssessDiscussID ORDER BY timestamp';
                $resultReplies = $connection2->prepare($sqlReplies);
                $resultReplies->execute($dataReplies);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            while ($rowReplies = $resultReplies->fetch()) {
                $output .= getThread($guid, $connection2, $pupilsightPlannerEntryHomeworkID, $rowDiscuss['pupilsightCrowdAssessDiscussID'], ($level + 1), $rowReplies['pupilsightCrowdAssessDiscussID'], $pupilsightPersonID, $pupilsightPlannerEntryID);
            }
        }
    }

    return $output;
}
