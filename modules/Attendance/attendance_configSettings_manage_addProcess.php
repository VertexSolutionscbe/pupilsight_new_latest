<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/attendance_configSettings.php";

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_configSettings_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {

     //die();
    //Proceed!
    //Validate Inputs
    $pupilsightProgramID = (isset($_POST['pupilsightProgramID']))? $_POST['pupilsightProgramID'] : '0';
    $pupilsightSchoolYearID = (isset($_POST['pupilsightSchoolYearID']))? $_POST['pupilsightSchoolYearID'] : '0';
    $attn_type = (isset($_POST['attn_type']))? $_POST['attn_type'] : '0';
    $enable_sms_absent = (isset($_POST['enable_sms_absent']))? $_POST['enable_sms_absent'] : '0';
    $select_sub_mandatory = (isset($_POST['select_sub_mandatory']))? $_POST['select_sub_mandatory'] : '0';
    $display_field_1 = (isset($_POST['display_field_1']))? $_POST['display_field_1'] : '0';
    $enable_sort_display_field_1 = (isset($_POST['enable_sort_display_field_1']))? $_POST['enable_sort_display_field_1'] : '0';
    $display_field_2 = (isset($_POST['display_field_2']))? $_POST['display_field_2'] : '0';
    $enable_sort_display_field_2 = (isset($_POST['enable_sort_display_field_2']))? $_POST['enable_sort_display_field_2'] : '0';
    $default_display = (isset($_POST['default_display']))? $_POST['default_display'] : '0';
    $no_of_session = (isset($_POST['no_of_session']))? $_POST['no_of_session'] : '0';
    $classes = (isset($_POST['classes']))? $_POST['classes'] : NULL;
    $session_no = (isset($_POST['session_no']))? $_POST['session_no'] : '0';
    $session_name = (isset($_POST['session_name']))? $_POST['session_name'] : '0';
    $auto_lock_attendance = (isset($_POST['auto_lock_attendance']))? $_POST['auto_lock_attendance'] : '0';
    $lock_attendance_marking = (isset($_POST['lock_attendance_marking']))? $_POST['lock_attendance_marking'] : '0';
    $sms_template_id = (isset($_POST['sms_template_id']))? $_POST['sms_template_id'] : '0';
    $sms_recipients ="";
    if(isset($_POST['sms_recipients'])){
          $sms_recipients=implode(",", $_POST['sms_recipients']);
    }
    $fromDate='';
    $toDate='';
    if(!empty($lock_attendance_marking)){
        $fromDate=implode("-", array_reverse(explode("/", $_POST['fromDate'])));
        $toDate=implode("-", array_reverse(explode("/", $_POST['toDate'])));
    }
    if($classes == null){
        $URL .= '&return=error1';
        header("Location: {$URL}");
    }
    $pupilsightYearGroupID = implode(',' , $classes);
    if ($pupilsightProgramID == '' or $attn_type == '' or $fromDate>$toDate) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness in current school year
        try {
            foreach ($classes as $key => $cl) {
                $data = array('pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID'=>$cl,'attn_type' => $attn_type,'pupilsightSchoolYearID'=>$pupilsightSchoolYearID);
            $sql = 'SELECT id FROM attn_settings WHERE pupilsightProgramID=:pupilsightProgramID             
            AND FIND_IN_SET(:pupilsightYearGroupID,pupilsightYearGroupID)
                    
             AND  pupilsightSchoolYearID=:pupilsightSchoolYearID AND attn_type=:attn_type';
            $result = $connection2->prepare($sql);
            $result->execute($data);
            if($result->rowCount() > 0){
                $URL .= '&return=error3';
                header("Location: {$URL}");
                exit();
            }
            }

           // print_r($data);die();
        } catch (PDOException $e) {
            $URL .= '&return=error5';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightSchoolYearID'=>$pupilsightSchoolYearID,'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID'=>$pupilsightYearGroupID,'attn_type' => $attn_type, 'enable_sms_absent' => $enable_sms_absent,'select_sub_mandatory'=>$select_sub_mandatory, 'display_field_1' => $display_field_1, 'enable_sort_display_field_1' => $enable_sort_display_field_1, 'display_field_2' => $display_field_2, 'enable_sort_display_field_2' => $enable_sort_display_field_2, 'default_display' => $default_display, 'no_of_session' => $no_of_session,'lock_attendance_marking'=>$lock_attendance_marking,'fromDate'=>$fromDate,'toDate'=>$toDate,'auto_lock_attendance'=>$auto_lock_attendance,'sms_template_id'=>$sms_template_id,'sms_recipients'=>$sms_recipients);
               $sql = 'INSERT INTO attn_settings SET pupilsightSchoolYearID=:pupilsightSchoolYearID,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID,attn_type=:attn_type, enable_sms_absent=:enable_sms_absent, select_sub_mandatory=:select_sub_mandatory,display_field_1=:display_field_1, enable_sort_display_field_1=:enable_sort_display_field_1, display_field_2=:display_field_2, enable_sort_display_field_2=:enable_sort_display_field_2, default_display=:default_display, no_of_session=:no_of_session,lock_attendance_marking=:lock_attendance_marking,fromDate=:fromDate,toDate=:toDate,auto_lock_attendance=:auto_lock_attendance,sms_template_id=:sms_template_id,sms_recipients=:sms_recipients';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                $settingsId = $connection2->lastInsertID();
                if(!empty($session_name)){
                    foreach($session_name as $k=> $d){
                        $sname = $d;
                        $sno = $session_no[$k];
                        if(!empty($sname) && !empty($sno)){
                            $data1 = array('attn_settings_id' => $settingsId, 'session_name' => $sname, 'session_no' => $sno);
                            $sql1 = "INSERT INTO attn_session_settings SET attn_settings_id=:attn_settings_id, session_name=:session_name, session_no=:session_no";
                            $result = $connection2->prepare($sql1);
                            $result->execute($data1);
                        }
                    }
                }    
            } catch (PDOException $e) {
                $URL .= '&return=error222';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 5, '0', STR_PAD_LEFT);

            $URL .= "&return=success0";
            header("Location: {$URL}");
        }
    }
}
