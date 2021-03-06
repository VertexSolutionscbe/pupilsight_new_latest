<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';

//Load jQuery
echo '<script type="text/javascript" src="'.$_SESSION[$guid]['absoluteURL'].'/lib/jquery/jquery.js"></script>';
echo '<script type="text/javascript" src="'.$_SESSION[$guid]['absoluteURL'].'/lib/jquery/jquery-migrate.min.js"></script>';

$type = '';
if (isset($_GET['type'])) {
    $type = $_GET['type'];
}
$output = '';

if ($type == 'general' or $type == 'lockdown' or $type == 'custom') {
    $output .= "<div style='width: 100%; min-height: 492px; background-color: #f00; color: #fff; margin: 0'>";
        //Check alarm details
        try {
            $data = array();
            $sql = "SELECT * FROM pupilsightAlarm WHERE status='Current'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>";
            $output .= $e->getMessage();
            $output .= '</div>';
        }

    if ($result->rowCount() == 1) { //Alarm details OK
        $row = $result->fetch();

        $output .= "<div style='padding-top: 10px; font-size: 120px; font-weight: bold; font-family: arial, sans; text-align: center'>";
        //Allow alarm sounder to terminate alarm
        $output .= "<div style='height: 20px; margin-bottom: 120px; width: 100%; text-align: right; font-size: 14px'>";
        if ($row['pupilsightPersonID'] == $_SESSION[$guid]['pupilsightPersonID']) {
            $output .= "<p style='padding-right: 20px'><a style='color: #fff' target='_parent' href='".$_SESSION[$guid]['absoluteURL'].'/modules/System Admin/alarm_cancelProcess.php?pupilsightAlarmID='.$row['pupilsightAlarmID']."'>".__('Turn Alarm Off').'</a></p>';
        }
        $output .= '</div>';

        if ($type == 'general') {
            $output .= __('General Alarm!');
            $output .= '<audio loop autoplay volume=3>
						<source src="'.$_SESSION[$guid]['absoluteURL'].'/resources/assets/audio/alarm_general.mp3" type="audio/mpeg">
					</audio>';
        } elseif ($type == 'lockdown') {
            $output .= __('Lockdown!');
            $output .= '<audio loop autoplay volume=3>
						<source src="'.$_SESSION[$guid]['absoluteURL'].'/resources/assets/audio/alarm_lockdown.mp3" type="audio/mpeg">
					</audio>';
        } elseif ($type == 'custom') {
            $output .= __('Alarm!');

            try {
                $dataCustom = array();
                $sqlCustom = "SELECT * FROM pupilsightSetting WHERE scope='System Admin' AND name='customAlarmSound'";
                $resultCustom = $connection2->prepare($sqlCustom);
                $resultCustom->execute($dataCustom);
            } catch (PDOException $e) {
            }
            $rowCustom = $resultCustom->fetch();

            $output .= '<audio loop autoplay volume=3>
						<source src="'.$rowCustom['value'].'" type="audio/mpeg">
					</audio>';
        }
        $output .= '</div>';

        $output .= "<div style='padding: 0 20px; font-family: arial, sans; text-align: center'>";
                //Allow everyone except alarm sounder to confirm receipt
                if ($row['pupilsightPersonID'] != $_SESSION[$guid]['pupilsightPersonID']) {
                    $output .= '<p>';
                        //Check for confirmation
                        try {
                            $dataConfirm = array('pupilsightAlarmID' => $row['pupilsightAlarmID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                            $sqlConfirm = 'SELECT * FROM pupilsightAlarmConfirm WHERE pupilsightAlarmID=:pupilsightAlarmID AND pupilsightPersonID=:pupilsightPersonID';
                            $resultConfirm = $connection2->prepare($sqlConfirm);
                            $resultConfirm->execute($dataConfirm);
                        } catch (PDOException $e) {
                            $output .= "<div class='alert alert-danger'>";
                            $output .= $e->getMessage();
                            $output .= '</div>';
                        }

                    if ($resultConfirm->rowCount() == 0) {
                        $output .= "<a target='_parent' style='font-size: 300%; font-weight: bold; color: #fff' href='".$_SESSION[$guid]['absoluteURL'].'/index_notification_ajax_alarmProcess.php?pupilsightAlarmID='.$row['pupilsightAlarmID']."'>".__('Click here to confirm that you have received this alarm.').'</a><br/>';
                        $output .= '<i>'.__('After confirming receipt, the alarm will continue to be displayed until an administrator has cancelled the alarm.').'</i>';
                    } else {
                        $output .= '<i>'.__('You have successfully confirmed receipt of this alarm, which will continue to be displayed until an administrator has cancelled the alarm.').'</i>';
                    }
                    $output .= '</p>';
                }

                //Show report to those with permission to sound alarm
                if (isActionAccessible($guid, $connection2, '/modules/System Admin/alarm.php')) {
                    $output .= '<h3>';
                    $output .= __('Receipt Confirmation Report');
                    $output .= '</h3>';

                    try {
                        $dataConfirm = array('pupilsightAlarmID' => $row['pupilsightAlarmID']);
                        $sqlConfirm = "SELECT pupilsightPerson.pupilsightPersonID, status, surname, preferredName, pupilsightAlarmConfirmID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightAlarmConfirm ON (pupilsightAlarmConfirm.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightAlarmID=:pupilsightAlarmID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY surname, preferredName";
                        $resultConfirm = $connection2->prepare($sqlConfirm);
                        $resultConfirm->execute($dataConfirm);
                    } catch (PDOException $e) {
                        $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultConfirm->rowcount() < 1) {
                        $output .= "<div class='alert alert-danger'>";
                        $output .= __('There are no records to display.');
                        $output .= '</div>';
                    } else {
                        $output .= "<table cellspacing='0' style='width: 400px; margin: 0 auto'>";
                        $output .= "<tr class='head'>";
                        $output .= "<th style='color: #fff; text-align: left'>";
                        $output .= __('Name').'<br/>';
                        $output .= '</th>';
                        $output .= "<th style='color: #fff; text-align: left'>";
                        $output .= __('Confirmed');
                        $output .= '</th>';
                        $output .= "<th style='color: #fff; text-align: left'>";
                        $output .= __('Actions');
                        $output .= '</th>';
                        $output .= '</tr>';

                        $rowCount = 0;
                        while ($rowConfirm = $resultConfirm->fetch()) {
                            //COLOR ROW BY STATUS!
                                $output .= '<script type="text/javascript">
									$(document).ready(function(){
										setInterval(function() {
											$("#row'.$rowCount.'").load("index_notification_ajax_alarm_tickUpdate.php", {"pupilsightAlarmID": "'.$row['pupilsightAlarmID'].'", "pupilsightPersonID": "'.$rowConfirm['pupilsightPersonID'].'"});
										}, 5000);
									});
								</script>';
                            $output .= "<tr id='row".$rowCount."'>";
                            $output .= "<td style='color: #fff'>";
                            $output .= formatName('', $rowConfirm['preferredName'], $rowConfirm['surname'], 'Staff', true, true).'<br/>';
                            $output .= '</td>';
                            $output .= "<td style='color: #fff'>";
                            if ($row['pupilsightPersonID'] == $rowConfirm['pupilsightPersonID']) {
                                $output .= __('NA');
                            } else {
                                if ($rowConfirm['pupilsightAlarmConfirmID'] != '') {
                                    $output .= "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/> ";
                                }
                            }
                            $output .= '</td>';
                            $output .= "<td style='color: #fff'>";
                            if ($row['pupilsightPersonID'] != $rowConfirm['pupilsightPersonID']) {
                                if ($rowConfirm['pupilsightAlarmConfirmID'] == '') {
                                    $output .= "<a target='_parent' href='".$_SESSION[$guid]['absoluteURL'].'/index_notification_ajax_alarmConfirmProcess.php?pupilsightPersonID='.$rowConfirm['pupilsightPersonID'].'&pupilsightAlarmID='.$row['pupilsightAlarmID']."'><img title='".__('Confirm')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick_light.png'/></a> ";
                                }
                            }
                            $output .= '</td>';
                            $output .= '</tr>';
                            ++$rowCount;
                        }
                        $output .= '</table>';
                    }
                }
        $output .= '</div>';
    }
    $output .= '</div>';
}

echo $output;
