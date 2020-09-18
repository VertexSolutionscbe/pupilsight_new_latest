<?php 
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');
if(isset($_POST['type'])){
    $type=trim($_POST['type']);
    switch ($type) {
    case "attendanceByRollGroupFormData":
        $data=$_POST;
        $session->forget(['attendanceByRollGroupFormData']);
        $session->set('attendanceByRollGroupFormData', $data);
    break;
    case "saveAttendance":
       print_r($_POST);
    break;
    case "clearAttendance":
         $section_id=$_POST['section_id'];
         $group_id=$_POST['group_id'];
         $date=$_POST['date'];
        if(!empty($section_id) && !empty($date) && !empty($group_id)){
          $sql1 = 'DELETE FROM pupilsightAttendanceLogRollGroup WHERE pupilsightRollGroupID="'.$group_id.'" AND session_no="'.$section_id.'" AND date LIKE "'.$date.'"';
          $result1 = $connection2->query($sql1);
          $result1->execute();
          if($result1){
          $sql2 = 'DELETE FROM pupilsightAttendanceLogPerson WHERE session_no="'.$section_id.'"  AND date LIKE "'.$date.'"';
          $result2 = $connection2->query($sql2);
          $result2->execute();
            echo "Attendance cleared successfully.";
          } else {
            echo "Clear attendance operation not done";
          }
        } else {
           echo "Clear attendance operation not done";
        }
    break;
    case "exportExcelSummary_byDate":
      $dateStart=$_POST['dateStart'];
      $dateEnd=$_POST['dateEnd'];
      $pupilsightProgramID=$_POST['pupilsightProgramID'];
      $pupilsightYearGroupID=$_POST['pupilsightYearGroupID'];
      $pupilsightRollGroupID=$_POST['pupilsightRollGroupID'];
      $today = date('Y-m-d');
      $countClassAsSchool =$_POST['countClassAsSchool'];
     $pupilsightAttendanceCodeID = (isset($_REQUEST["pupilsightAttendanceCodeID"]))? $_REQUEST["pupilsightAttendanceCodeID"] : 0;
      $reportType = (empty($pupilsightAttendanceCodeID))? 'types' : 'reasons';
      if (!empty($pupilsightAttendanceCodeID)) {
            $dataCodes = array( 'pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID);
            $sqlCodes = "SELECT * FROM pupilsightAttendanceCode WHERE pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID";
        } else {
            $dataCodes = array();
            $sqlCodes = "SELECT * FROM pupilsightAttendanceCode WHERE active = 'Y' AND reportable='Y' ORDER BY sequenceNumber ASC, name";
        }

        $resultCodes = $pdo->executeQuery($dataCodes, $sqlCodes);
        if ($resultCodes->rowCount() == 0) {
           echo "There are no attendance codes defined";
          } else if ($dateStart > $today || $dateEnd > $today) {
            echo "The specified date is in the future: it must be today or earlier.";
          } else {
            $dataSchoolDays = array( 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlSchoolDays = "SELECT COUNT(DISTINCT CASE WHEN date>=pupilsightSchoolYear.firstDay AND date<=pupilsightSchoolYear.lastDay THEN date END) as total, COUNT(DISTINCT CASE WHEN date>=:dateStart AND date <=:dateEnd THEN date END) as dateRange FROM pupilsightAttendanceLogPerson, pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE date>=pupilsightSchoolYearTerm.firstDay AND date <= pupilsightSchoolYearTerm.lastDay AND date <= NOW() AND pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID";
            $resultSchoolDays = $connection2->prepare($sqlSchoolDays);
            $resultSchoolDays->execute($dataSchoolDays);
            $schoolDayCounts = $resultSchoolDays->fetch();
            $data = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'],'pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$pupilsightYearGroupID,'pupilsightRollGroupID'=>$pupilsightRollGroupID);
            $sqlPieces = array();

            if ($reportType == 'types') {
            $attendanceCodes = array();
            $i = 0;
            while( $type = $resultCodes->fetch() ) {
            $typeIdentifier = "`".str_replace("`","``",$type['nameShort'])."`";
            $data['type'.$i] = $type['name'];
            $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceCode.name=:type".$i." THEN date END) AS ".$typeIdentifier;
            $attendanceCodes[ $type['direction'] ][] = $type;
            $i++;
            }
            // print_r($sqlPieces);die();
            }
            else if ($reportType == 'reasons') {
            $attendanceCodeInfo = $resultCodes->fetch();
            $attendanceReasons = explode(',', getSettingByScope($connection2, 'Attendance', 'attendanceReasons') );

            for($i = 0; $i < count($attendanceReasons); $i++) {
            $reasonIdentifier = "`".str_replace("`","``",$attendanceReasons[$i])."`";
            $data['reason'.$i] = $attendanceReasons[$i];
            $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceLogPerson.reason=:reason".$i." THEN date END) AS ".$reasonIdentifier;
            }

            $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceLogPerson.reason='' THEN date END) AS `No Reason`";
            $attendanceReasons[] = 'No Reason';
            }

            $sqlSelect = implode( ',', $sqlPieces );

            // query
            $groupBy = 'GROUP BY pupilsightAttendanceLogPerson.pupilsightPersonID';
            $orderBy = ' ORDER BY LENGTH(rollGroup), rollGroup, surname, preferredName';
            $sql = "SELECT pupilsightPerson.pupilsightPersonID AS stuid,pupilsightPerson.pupilsightPersonID, pupilsightRollGroup.nameShort AS rollGroup, surname,officialName, preferredName, $sqlSelect FROM pupilsightAttendanceLogPerson JOIN pupilsightAttendanceCode ON (pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name) JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE date>=:dateStart AND date<=:dateEnd AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightProgramID=:pupilsightProgramID AND pupilsightStudentEnrolment.pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightStudentEnrolment.pupilsightRollGroupID=:pupilsightRollGroupID";
            if ($countClassAsSchool == 'N') {
                $sql .= " AND NOT context='Class'";
            }
            $sql .= ' '. $groupBy . ' '. $orderBy;
            $result = $connection2->prepare($sql);
            $result->execute($data);
            if($result->rowCount() >= 1) { 
                $dates_sql = 'SELECT date FROM `pupilsightAttendanceLogPerson` WHERE  date>="'.$dateStart.'" AND date<="'.$dateEnd.'" GROUP BY date ORDER BY date ASC';
                $dates_res = $connection2->query($dates_sql);
                $dates_data = $dates_res->fetchAll();
                ?>
            <table id="excelexport" style="width: 100%">
             <tr class='head'>
                 <td colspan="100">From : <?php echo date('m-d-Y',strtotime($dateStart));?> To : <?php echo date('m-d-Y',strtotime($dateEnd));?> </td>
             </tr>
            <?php 
            echo "<tr class='head'>"; 

            echo '<th style="width:80px" rowspan=2>';
            echo "Sl.No";
            echo '</th>';
          
            echo '<th rowspan=2>';
            echo'Name';
            echo '</th>';
            echo '<th rowspan=2>';
            echo'Student Id/Admisison No';
            echo '</th>';

            if ($reportType == 'types') {
                /*echo '<th colspan='.count($attendanceCodes['In']).' class="columnDivider" style="text-align:center;">';
                echo'IN';
                echo '</th>';
                echo '<th colspan='.count($attendanceCodes['Out']).' class="columnDivider" style="text-align:center;">';
                echo 'OUT';
                echo '</th>';*/
            } else if ($reportType == 'reasons') {
                echo '<th colspan='.count($attendanceReasons).' class="columnDivider" style="text-align:center;">';
                echo $attendanceCodeInfo['name'];
                echo '</th>';
            }
            echo '</tr>';
            echo '<tr class="head" style="min-height:80px;">';
            if ($reportType == 'types') {

                for($i = 0; $i < count($attendanceCodes['In']); $i++ ) {
                    echo '<th class="'.( $i == 0? 'verticalHeader columnDivider' : 'verticalHeader').'">';
                        echo "Total ".$attendanceCodes['In'][$i]['name'];
                    echo '</th>';
                }

                for( $i = 0; $i < count($attendanceCodes['Out']); $i++ ) {
                    echo '<th class="'.( $i == 0? 'verticalHeader columnDivider' : 'verticalHeader').'" >';
                         echo "Total ".$attendanceCodes['Out'][$i]['name'];
                    echo '</th>';
                }
            } else if ($reportType == 'reasons') {
                for( $i = 0; $i < count($attendanceReasons); $i++ ) {
                    echo '<th class="'.( $i == 0? 'verticalHeader columnDivider' : 'verticalHeader').'">';
                        echo '<div class="verticalText">';
                        echo $attendanceReasons[$i];
                        echo '</div>';
                    echo '</th>';
                }
            }
            foreach($dates_data  as  $val) {
                echo "<th>".date('d/m/Y',strtotime($val['date']))."</th>";
            }
            echo '</tr>';
            $k=1;
            while ($row = $result->fetch()) {
                // ROW
                echo "<tr>";
                echo "<td>".$k++."</td>";
               /* echo '<td>';
                    echo $row['rollGroup'];
                echo '</td>';*/
                echo '<td>';
                    echo $row['officialName'];
                echo '</td>';
                echo '<td>';
                    echo $row['pupilsightPersonID'];
                echo '</td>';

                if ($reportType == 'types') {
                    for( $i = 0; $i < count($attendanceCodes['In']); $i++ ) {
                        echo '<td class="center '.( $i == 0? 'columnDivider' : '').'">';
                            echo $row[ $attendanceCodes['In'][$i]['nameShort'] ];
                        echo '</td>';
                    }

                    for( $i = 0; $i < count($attendanceCodes['Out']); $i++ ) {
                        echo '<td class="center '.( $i == 0? 'columnDivider' : '').'">';
                            echo $row[ $attendanceCodes['Out'][$i]['nameShort'] ];
                        echo '</td>';
                    }
                } else if ($reportType == 'reasons') {
                    for( $i = 0; $i < count($attendanceReasons); $i++ ) {
                        echo '<td class="center '.( $i == 0? 'columnDivider' : '').'">';
                            echo $row[ $attendanceReasons[$i] ];
                        echo '</td>';
                    }
                }
                foreach($dates_data  as  $val) {
                    $dates_s = 'SELECT type FROM `pupilsightAttendanceLogPerson` WHERE  date="'.$val['date'].'" AND pupilsightPersonID="'.$row['pupilsightPersonID'].'" GROUP BY date ORDER BY date ASC';
                    $result_s = $connection2->query($dates_s);
                    $type_data = $result_s->fetch();
                echo "<td>";
                if(!empty($type_data['type'])){
                    echo $type_data['type'];
                }
                echo "</td>";
                }
                echo '</tr>';

            }
            ?>
            </table>
              <?php 
            }//data row ends
            //query ends 
          }//encds here
    ?>
    <?php
    break;
    case "getSubjectsFromPeriod":
            $pro = $_POST['val'];
            $cls = $_POST['cls'];
            $sec = $_POST['sec'];
            $sqlt = 'SELECT d.name,c.pupilsightDepartmentID FROM pupilsightTT AS a LEFT JOIN pupilsightTTDay AS b ON a.pupilsightTTID = b.pupilsightTTID LEFT JOIN pupilsightTTDayRowClass AS c ON b.pupilsightTTDayID = c.pupilsightTTDayID LEFT JOIN pupilsightDepartment as d ON c.pupilsightDepartmentID = d.pupilsightDepartmentID WHERE a.pupilsightProgramID = "'.$pro.'" AND a.pupilsightYearGroupIDList = "'.$cls.'"';
            if(!empty($sec)){
            $sqlt.= 'AND a.pupilsightRollGroupIDList = "'.$sec.'"';
            }
            $resultt = $connection2->query($sqlt);  
            $subjects = $resultt->fetchAll();
            // print_r($subjects);die();
            $data = '<option value="">Select Subjects</option>';
            if(!empty($subjects)){
            foreach ($subjects as $k => $st) {
            $data .= '<option value="' . $st['pupilsightDepartmentID'] . '">' . $st['name'] . '</option>';
            }
            }
            echo $data;
    break;
    case "del_attendance_configSettings":
          $id=$_POST['id'];
            try {
            $data = array('id' => $id);
            $sql = 'DELETE FROM attn_settings WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $data2 = array('attn_settings_id' => $id);
            $sql2 = 'DELETE FROM attn_session_settings WHERE attn_settings_id=:attn_settings_id';
            $result2 = $connection2->prepare($sql2);
            $result2->execute($data2);
            echo "success";
            } catch (PDOException $e) {
              echo "Database Error";
            }
    break;
    default:
      echo "Invalid request";
    }
} else {
  echo "Request type is missing";
}
?>


