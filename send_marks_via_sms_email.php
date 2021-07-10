<?php
include 'db.php';

$uid = $_GET['uid'];
$studentId = $_GET['stuId'];
$testId = $_GET['testId'];
$type = $_GET['type'];


if (!empty($testId) && !empty($studentId) && !empty($uid)) {
  $studentData = getAllStudent($conn, $studentId);
  $stuId = explode(',', $studentId);
  $testData = getTestDetails($conn, $testId);
  $gradeData = getGradeData($conn);
  $senderID = getSenderIdData($conn);
  if (!empty($testData)) {
    $pupilsightSchoolYearID = $testData['pupilsightSchoolYearID'];
    $pupilsightProgramID = $testData['pupilsightProgramID'];
    $pupilsightYearGroupID = $testData['pupilsightYearGroupID'];
    $pupilsightRollGroupID = $testData['pupilsightRollGroupID'];
    $test_name = $testData['test_name'];
    $class = $testData['class'];
    $section = $testData['section'];

    $subjectData = getAllSubjects($conn, $testId, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID);

    $marksData = getAllMarks($conn, $testId);
    // echo '<pre>';
    // print_r($gradeData);
    // die();
    $stuMarksData = array();
    $gradeId = '';
    $grade_name = '';
    $marks_obtained = '';
    $mark_data = '';
    $subjectName = '';
    $skill_name = '';
    $max_marks = '';
    $remarks = '';
    $sub_skill_name = '';
    $content = array();

    foreach ($stuId as $pupilsightPersonID) {
      //echo $pupilsightPersonID.'<br>';
      if (array_key_exists($pupilsightPersonID, $marksData) && array_key_exists($pupilsightPersonID, $studentData)) {

        try {
          $studentDetails = $studentData[$pupilsightPersonID];
          $stuMarksData = $marksData[$pupilsightPersonID];
          if (!empty($stuMarksData) && !empty($studentDetails)) {
            $studentName = $studentDetails['officialName'];
            $father_email = $studentDetails['father_email'];
            $father_phone = $studentDetails['father_phone'];
            $mother_email = $studentDetails['mother_email'];
            $mother_phone = $studentDetails['mother_phone'];
            $mark_data = array();
            foreach ($stuMarksData as $stmks) {
              if (!empty($stmks['marks_obtained']) || !empty($stmks['gradeId']) || !empty($stmks['remarks'])) {
                try {
                  $pupilsightDepartmentID = $stmks['pupilsightDepartmentID'];
                  $skill_id = $stmks['skill_id'];
                  $marks_obtained = $stmks['marks_obtained'];
                  $gradeId = $stmks['gradeId'];
                  $remarks = $stmks['remarks'];
                  if (empty($skill_id)) {
                    $skill_id = "0";
                  }
                  if (!empty($gradeId) && array_key_exists((int)$gradeId, $gradeData)) {
                    $grade_name = $gradeData[(int)$gradeId];
                  }
                  $subData = $subjectData[$pupilsightDepartmentID . '-' . $skill_id];
                  if (!empty($subData)) {
                    $subjectName = $subData['subject_name'];
                    $skill_name = $subData['skill_name'];
                    $max_marks = $subData['max_marks'];
                    $assesment_method = $subData['assesment_method'];
                    if (!empty($subjectName) && !empty($skill_name)) {
                      $sub_skill_name = $subjectName . ' (' . $skill_name . ')';
                    }

                    if (!empty($subjectName) && empty($skill_name)) {
                      $sub_skill_name = $subjectName;
                    }

                    if ($assesment_method == 'Marks') {
                      if (!empty($marks_obtained)) {
                        $mark_data[] = $sub_skill_name . ' - ' . $marks_obtained . '/' . $max_marks;
                      }
                    } else if ($assesment_method == 'Grade') {
                      $mark_data[] = $sub_skill_name . ' - ' . $grade_name;
                    } else {
                      $mark_data[] = $sub_skill_name . ' - ' . $remarks;
                    }
                  }
                  //echo $pupilsightDepartmentID.'-'.$skill_id.'-'.$assesment_method.'-'.$marks_obtained.'-'.$grade_name.'-'.$gradeId.'-'.$remarks.'</br>';
                } catch (Exception $ex) {
                  //print_r($ex);
                }
              }
            }

            if (!empty($mark_data)) {
              
              $dtc = array();
              $dtc["student_id"] = $pupilsightPersonID;
              $dtc["student_name"] = $studentName;
              $dtc["father_email"] = $father_email;
              $dtc["father_phone"] = $father_phone;
              $dtc["mother_email"] = $mother_email;
              $dtc["mother_phone"] = $mother_phone;
              if ($type == "sms") {
                $senderID = ' - '.$senderID;
                $marksValue = implode(', ', $mark_data);
              } else if ($type == "email") {
                $senderID = '';
                $marksValue = implode(', </br>', $mark_data);
              }
              $dtc["msg"] = 'Dear Parent </br>' . $test_name . '  Result For ' . $studentName . ' , ' . $class . '-' . $section . ' : ' . $marksValue .' '.$senderID ;
              $content[] = $dtc;
            }
          }
        } catch (Exception $ex) {
          //print_r($ex);
        }
      }
    }
  }

  // echo '<pre>';
  // print_r($content);
  // die();
  if ($content) {
    if ($type == "sms") {
      sendSMS($content, $uid);
    } else if ($type == "email") {
      sendEmail($content, $uid);
    }
  }
} else {
  echo 'No Data Found!';
}

function sendEmail($dcontent, $uid)
{
  try {
    include 'service.php';
    $sqi = "insert into user_email_sms_sent_details(type, sent_to, pupilsightPersonID, email, subject, description, uid) ";
    $sqi .= "values";
    $len = count($dcontent);
    $i = 0;
    //$len = 2;
    $mail_post_link = getBaseURL() . "/pupilsight/mail_send.php";
    //echo "post link " . $mail_post_link;
    while ($i < $len) {
      $content = $dcontent[$i];
      $email = $content[$i]["father_email"];
      //$email = "bikash@thoughtnet.in";
      if (!empty($email)) {
        $ed = array("to" => $email, "subject" => "Marks for " . $content["student_name"], "body" => $content['msg']);
        curl_post($mail_post_link, $ed);
        //smsGateway($conn, $res['activeGateway'], $res['senderid'], $smsCount, $phone, $content['msg'], $content['student_id'], $uid, true);
        $sqi .= '(2, 1, ' . $content['student_id'] . ', "' . $email . '", "Marks for ' . $content["student_name"] . '" ,"' . stripslashes($content['msg']) . '", ' . $uid . ') , ';
      }
      $email = $content[$i]["mother_email"];
      //$email = "bikash@thoughtnet.in";
      if (!empty($email)) {
        $ed = array("to" => $email, "subject" => "Marks for " . $content["student_name"], "body" => $content['msg']);
        curl_post($mail_post_link, $ed);
        //smsGateway($conn, $res['activeGateway'], $res['senderid'], $smsCount, $phone, $content['msg'], $content['student_id'], $uid, true);
        $sqi .= '(2, 1, ' . $content['student_id'] . ', "' . $email . '", "Marks for ' . $content["student_name"] . '", "' . stripslashes($content['msg']) . '", ' . $uid . ') , ';
      }
      $i++;
    }
    $sqi = rtrim($sqi, " ,");
    $conn->query($sqi);
    //echo "<br>" . $sqi;
    //print_r($res);
  } catch (Exception $ex) {
    print_r($ex->getMessage());
  }
}

function getBaseURL()
{
  if (isset($_SERVER['HTTPS'])) {
    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
  } else {
    $protocol = 'http';
  }
  return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function sendSMS($dcontent, $uid)
{
  try {
    include 'service_sms.php';
    $res = smsGatewayConfig($conn);
    $i = 0;
    $smsCount = (int)$res['smsCount'];

    $sqi = "insert into user_email_sms_sent_details(type, sent_to, pupilsightPersonID, phone, description, uid) ";
    $sqi .= "values";
    $len = count($dcontent);
    //$len = 2;
    while ($i < $len) {
      $content = $dcontent[$i];
      $phone = $content[$i]["father_phone"];
      //$phone = "8867776787";
      if (!empty($phone)) {
        $smsCount++;
        smsGateway($conn, $res['activeGateway'], $res['senderid'], $smsCount, $phone, $content['msg'], $content['student_id'], $uid, true);
        $sqi .= '(1, 1, ' . $content['student_id'] . ', ' . $phone . ', "' . stripslashes($content['msg']) . '", ' . $uid . ') , ';
      }
      $phone = $content[$i]["mother_phone"];
      //$phone = "9883928942";
      if (!empty($phone)) {
        $smsCount++;
        smsGateway($conn, $res['activeGateway'], $res['senderid'], $smsCount, $phone, $content['msg'], $content['student_id'], $uid, true);
        $sqi .= '(1, 1, ' . $content['student_id'] . ', ' . $phone . ', "' . stripslashes($content['msg']) . '", ' . $uid . ') , ';
      }
      $i++;
    }
    $sqi = rtrim($sqi, " ,");
    $conn->query($sqi);
    //echo "<br>" . $sqi;
    //print_r($res);
  } catch (Exception $ex) {
    print_r($ex->getMessage());
  }
}

function getAllMarks($conn, $testId)
{
  $sql = 'SELECT * FROM examinationMarksEntrybySubject  WHERE test_id = ' . $testId . ' ';
  $resource = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($resource)) {
    $resultData[] = $row;
  }
  $result = array();
  if (!empty($resultData)) {
    foreach ($resultData as $res) {
      $result[$res['pupilsightPersonID']][] = $res;
    }
  }
  return $result;
}

function getAllSubjects($conn, $testId, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID)
{
  $sql = 'SELECT a.*, b.subject_display_name as subject_name, c.name as skill_name FROM examinationSubjectToTest AS a LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN ac_manage_skill AS c ON a.skill_id = c.id  WHERE a.is_tested = "1" AND a.test_id = ' . $testId . ' AND b.pupilsightSchoolYearID = ' . $pupilsightSchoolYearID . ' AND b.pupilsightProgramID = ' . $pupilsightProgramID . ' AND b.pupilsightYearGroupID = ' . $pupilsightYearGroupID . ' ';
  $resource = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($resource)) {
    $resultData[] = $row;
  }
  $result = array();
  if (!empty($resultData)) {
    foreach ($resultData as $res) {
      $result[$res['pupilsightDepartmentID'] . '-' . $res['skill_id']] = $res;
    }
  }
  return $result;
}

function getTestDetails($conn, $testId)
{
  $sql = 'SELECT a.name as test_name, b.*, c.name as class, d.name as section FROM examinationTest AS a LEFT JOIN examinationTestAssignClass AS b ON a.id = b.test_id LEFT JOIN pupilsightYearGroup AS c ON b.pupilsightYearGroupID = c.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS d ON b.pupilsightRollGroupID = d.pupilsightRollGroupID  WHERE a.id = ' . $testId . ' ';
  $resource = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($resource)) {
    $result = $row;
  }
  return $result;
}

function getAllStudent($conn, $studentId)
{
  $sql = "SELECT a.officialName, a.pupilsightPersonID, parent1.email as father_email, parent1.phone1 as father_phone, parent2.email as mother_email, parent2.phone1 as mother_phone FROM pupilsightPerson AS a 
  LEFT JOIN pupilsightFamilyChild AS child ON child.pupilsightPersonID=a.pupilsightPersonID 
  LEFT JOIN pupilsightFamilyRelationship AS adult1 ON adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.relationship= 'Father'
  LEFT JOIN pupilsightPerson as parent1 ON parent1.pupilsightPersonID=adult1.pupilsightPersonID1 AND parent1.status='Full' 
  LEFT JOIN pupilsightFamilyRelationship as adult2 ON adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.relationship= 'Mother'
  LEFT JOIN pupilsightPerson as parent2 ON parent2.pupilsightPersonID=adult2.pupilsightPersonID1 AND parent2.status='Full' 
  WHERE  a.is_delete = '0' AND a.pupilsightPersonID IN (" . $studentId . ") ";
  $resource = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($resource)) {
    $resultData[] = $row;
  }
  $result = array();
  if (!empty($resultData)) {
    foreach ($resultData as $res) {
      $result[$res['pupilsightPersonID']] = $res;
    }
  }
  return $result;
}

function getGradeData($conn)
{
  $sql = 'SELECT id, grade_name FROM examinationGradeSystemConfiguration';
  $resource = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($resource)) {
    $resultData[] = $row;
  }
  $result = array();
  if (!empty($resultData)) {
    foreach ($resultData as $res) {
      $result[$res['id']] = $res['grade_name'];
    }
  }
  return $result;
}

function getSenderIdData($conn)
{
  $sql = 'SELECT value FROM pupilsightSetting WHERE name = "smsSenderID" ';
  $resource = $conn->query($sql);
  $senderId = '';
  while ($row = mysqli_fetch_assoc($resource)) {
    $senderId = $row['value'];
  }
  
  return $senderId;
}
