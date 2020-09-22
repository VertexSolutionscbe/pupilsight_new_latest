<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
     /*$sqla = 'SELECT * FROM `pupilsightAttendanceLogPerson` WHERE  `pupilsightPersonID`='.$_GET['pid'].' AND date>="'.$_GET['starDate'].'" AND date<="'.$_GET['endDate'].'" GROUP BY date,type ORDER BY date DESC';*/
     $sqla='SELECT alog.*,s.session_name
FROM pupilsightAttendanceLogPerson as alog
LEFT JOIN attn_session_settings as s
ON alog.session_no = s.session_no
WHERE alog.pupilsightPersonID="'.$_GET['pid'].'" AND alog.date>="'.$_GET['starDate'].'" AND alog.date<="'.$_GET['endDate'].'" GROUP BY alog.date,alog.type ORDER BY alog.date DESC';
    $resulta = $connection2->query($sqla);
    $data = $resulta->fetchAll();
   ?>
   
   <table class='table'>
       <thead>
        <tr>
            <th colspan="5">Attendance for : <?php echo  $_GET['name'];?></th>
        </tr>
           <tr>
               <th style="width: 10%">Sl.No</th>
               <th style="width: 24%">Date</th>
               <th style="width: 24%">Status</th>
               <th style="width: 24%">Session</th>
               <th style="width: 24%">Comment</th>
            </th>
           </tr>
       </thead>
   </table>
   <div style="height:300px;overflow:auto;">
   <table class='table'>
      <body >
        <?php if(!empty($data)){
          $i=1;
          foreach ($data as $val) {
            ?>
            <tr>
              <td style="width: 10%"><?php echo $i++;?></td>
              <td style="width: 24%"><?php echo  date("d/m/Y", strtotime($val['date']));?></td>
              <td style="width: 24%"><?php echo $val['type'];?></td>
              <td style="width: 24%"><?php echo  $val['session_name'];?></td>
              <td style="width: 24%"><?php echo $val['comment'];?></td>
            </tr>
            <?php 
          }
         ?>
          <?php
        } else { ?>
        <tr>
          <td colspan="4">No data found.</td>
        </tr>
       <?php } ?>
       </body>
   </table>
 </div>
   <?php
