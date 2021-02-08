<?php
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;
use Pupilsight\Contracts\Comms\SMS;

$mail = $container->get(Mailer::class);
$session = $container->get('session');
$organisationAdministratorEmail = $_SESSION[$guid]['organisationAdministratorEmail'];
$organisationAdministratorName = $_SESSION[$guid]['organisationAdministratorName'];
if (isset($_POST['type'])) {
  $type = trim($_POST['type']);
  switch ($type) {
    case "send_sms_or_email":
      $status = array();
      $testID = $_POST['testID'];
      $testName = $_POST['testName'];
      $send_sms = '';
      $send_email = '';
      $sms_group = '';
      $email_group = '';
      if (isset($_POST['sms_usr'])) {
        $sms_group = $_POST['sms_usr'];
      }
      if (isset($_POST['email_usr'])) {
        $email_group = $_POST['email_usr'];
      }
      $subject = "Examination marks-" . $testName;
      if (isset($_POST['sms']) || isset($_POST['email'])) {
        if (!empty($sms_group) || !empty($email_group)) {
          $sql = 'SELECT a.*, b.officialName as student_name, b.email as student_email, b.phone1 as student_phone FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID  WHERE a.test_id = ' . $testID . '  GROUP BY a.pupilsightPersonID ';

          $result = $connection2->query($sql);
          $data = $result->fetchAll();
          foreach ($data as $k => $dt) {
            $sql1 = 'SELECT a.*, b.officialName, b.email, b.phone1  FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE pupilsightPersonID2 = ' . $dt['pupilsightPersonID'] . ' ';
            $result1 = $connection2->query($sql1);
            $data1 = $result1->fetchAll();
            foreach ($data1 as $pd) {
              if ($pd['relationship'] == 'Father') {
                $data[$k]['father_name'] = $pd['officialName'];
                $data[$k]['father_email'] = $pd['email'];
                $data[$k]['father_phone'] = $pd['phone1'];
              }

              if ($pd['relationship'] == 'Mother') {
                $data[$k]['mother_name'] = $pd['officialName'];
                $data[$k]['mother_email'] = $pd['email'];
                $data[$k]['mother_phone'] = $pd['phone1'];
              }

              if ($pd['relationship'] == 'Other') {
                $data[$k]['other_name'] = $pd['officialName'];
                $data[$k]['other_email'] = $pd['email'];
                $data[$k]['other_phone'] = $pd['phone1'];
              }
            }
            $sqlm = 'SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                    LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
                    WHERE a.test_id = ' . $testID . ' AND a.pupilsightPersonID = ' . $dt['pupilsightPersonID'] . ' GROUP by a.pupilsightDepartmentID';
            // $sqlm = 'SELECT a.*, b.name as subject_name, c.skill_display_name FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id WHERE test_id = '.$testID.' AND pupilsightPersonID = '.$dt['pupilsightPersonID'].' ';
            $resultm = $connection2->query($sqlm);
            $datam = $resultm->fetchAll();
            if (!empty($datam)) {
              $data[$k]['marks'] = $datam;
            }
          } // for each loop
          if (!empty($data)) {
            foreach ($data as $val) {
              //send message to email
              $sms_p = 'Dear Parents ' . $testName . ' results for ' . $val['student_name'] . ' ';
              $sms_s = 'Dear Student ' . $testName . ' results for ' . $val['student_name'] . ' ';
              $marks = $val['marks'];
              foreach ($marks as $m) {
                $sms_p .= " " . $m['subject_name'] . " - " . ceil($m['marks_obtained']) . "/" . ceil($m['max_marks']) . ",";
                $sms_s .= " " . $m['subject_name'] . " - " . ceil($m['marks_obtained']) . "/" . ceil($m['max_marks']) . ",";
              }
              //send sms
              if (!empty($sms_group)) {
                foreach ($sms_group as $sg) {
                  if ($sg == "Student") {
                    if (!empty($val['phone1'])) {
                      send_sms($container, $val['phone1'], $sms_s);
                    }
                  } else if ($sg == "Father") {
                    if (!empty($val['father_phone'])) {
                      send_sms($container, $val['father_phone'], $sms_p);
                    } else if ($sg == "Other") {
                      if (!empty($val['other_phone'])) {
                        send_sms($container, $val['other_phone'], $sms_p);
                      }
                    } else {
                      if (!empty($val['mother_phone'])) {
                        send_sms($container, $val['mother_phone'], $sms_p);
                      }
                    }
                  }
                }
              }
              //send email
              if (!empty($email_group)) {
                foreach ($email_group as $sendEmail) {
                  if ($sendEmail == "Father") {
                    if (!empty($val['father_email'])) {
                      send_email($val['father_email'], $subject, $sms_p, $mail, $organisationAdministratorEmail, $organisationAdministratorName);
                    }
                  } else if ($sendEmail == "Mother") {
                    if (!empty($val['mother_email'])) {
                      send_email($val['mother_email'], $subject, $sms_p, $mail, $organisationAdministratorEmail, $organisationAdministratorName);
                    }
                  } else {
                    if (!empty($val['other_email'])) {
                      send_email($val['other_email'], $subject, $sms_p, $mail, $organisationAdministratorEmail, $organisationAdministratorName);
                    }
                  }
                }
              }
            } //for each 
            $status['status'] = "ok";
          } else {
            $status['status'] = "error";
            $status['msg'] = "No results found!";
          }
        } else {
          $status['status'] = "error";
          $status['msg'] = "Please select related options";
        }
      } else {
        $status['status'] = "error";
        $status['msg'] = "Please Select SMS/Email check box !";
      }
      echo json_encode($status);
      break;
    case "testEmail":
      $to = $_POST['email'];
      $mailerSMTPUsername = $_POST['mailerSMTPUsername'];
      $subject = "Test email From Third Party Settings";
      $emailSignature = $_POST['emailSignature'];
      $mailerSMTPUsername = $_POST['mailerSMTPUsername'];
      $t_msg = "Test email From Third Party Settings";
      $t_msg .= "<br/><br/><br/>" . $emailSignature;
      $status = send_email($to, $subject, $t_msg, $mail, $mailerSMTPUsername, "Pupil Sight");
      if (!empty($status)) {
        echo "Email Sent Successfully.";
      } else {
        echo "Email not sent...";
      }
      break;
    case "testSms":
      $smsGateway = $_POST['smsGateway'];
      $smsSenderID = $_POST['smsSenderID'];
      $mobile = $_POST['mobile'];
      $msg = "Test SMS From Third Party Settings";
      if (!empty($smsGateway)) {
        if (!empty($smsSenderID)) {
          if (!empty($mobile)) {
            if ($smsGateway == "Gupshup") {
              $url = "http://enterprise.smsgupshup.com/GatewayAPI/rest?&mask=" . $smsSenderID . "&send_to=" . $mobile . "&msg=" . rawurlencode($msg) . "&method=SendMessage&msg_type=Text&userid=2000185422&auth_scheme=plain&password=StUX6pEkz";
              echo $response = send_sms_check($url);
            } else if ($smsGateway == "Karix") {
              echo "We will updated still pending";
            } else {
              echo $smsGateway . " SMS Gateway integration not done";
            }
          }
        } else {
          echo "Please Enter SMS SENDER ID";
        }
      } else {
        echo "Please select SMS Gateway and Try again";
      }
      break;
    default:
      echo "Invalid request";
  }
} else {
  echo "Request type is missing";
}

function send_email($to, $subject, $body, $mail, $organisationAdministratorEmail, $organisationAdministratorName)
{
  $tos = $to;
  //$tos="chinna.e@thoughtnet.in";
  $mail->SetFrom($organisationAdministratorEmail, $organisationAdministratorName);
  $mail->AddAddress($tos);
  $mail->CharSet = 'UTF-8';
  $mail->Encoding = 'base64';
  //$mail->AddAttachment($uploadfile);                        // Optional name
  $mail->isHTML(true);
  $mail->Subject = $subject;
  $mail->Body = $body;
  $res = $mail->Send();
  return $res;
}
function send_sms($container, $mobile, $msg)
{
  //$mobile="8777281040";
  /*
    $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
    $urls .= "&send_to=" .$mobile;
    $urls .= "&msg=" . rawurlencode($msg);
    $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
    $resms = file_get_contents($urls);
    */
  $sms = $container->get(SMS::class);
  $res = $sms->sendSMSPro($mobile, $msg);
}

function send_sms_check($urls)
{
  //$mobile="8777281040";
  /* $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
    $urls .= "&send_to=" .$mobile;
    $urls .= "&msg=" . rawurlencode($msg);
    $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";*/
  return $resms = file_get_contents($urls);
}
