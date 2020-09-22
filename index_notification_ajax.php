<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';

$output = '';

$themeName = 'Default';
if (isset($_SESSION[$guid]['pupilsightThemeName'])) {
    $themeName = $_SESSION[$guid]['pupilsightThemeName'];
}

if (isset($_SESSION[$guid]) == false or isset($_SESSION[$guid]['pupilsightPersonID']) == false) {
    $output .= "<a class='inactive' title='".__('Notifications')."' href='#'><img class='minorLinkIcon' style='margin-left: 2px; opacity: 0.2; vertical-align: -75%' src='./themes/Default/img/notifications.png'></a>";
} else {
    //CHECK FOR SYSTEM ALARM
    if (isset($_SESSION[$guid]['pupilsightRoleIDCurrentCategory'])) {
        if ($_SESSION[$guid]['pupilsightRoleIDCurrentCategory'] == 'Staff') {
            $alarm = getSettingByScope($connection2, 'System', 'alarm');
            if ($alarm == 'General' or $alarm == 'Lockdown' or $alarm == 'Custom') {
                $type = 'general';
                if ($alarm == 'Lockdown') {
                    $type = 'lockdown';
                } elseif ($alarm == 'Custom') {
                    $type = 'custom';
                }
                $output .= "<script>
					if ($('div#TB_window').is(':visible')==true && $('div#TB_window').attr('class')!='alarm') {
						$(\"#TB_window\").remove();
						$(\"body\").append(\"<div id='TB_window'></div>\");
					}
					if ($('div#TB_window').is(':visible')===false) {
						var url = '".$_SESSION[$guid]['absoluteURL'].'/index_notification_ajax_alarm.php?type='.$type."&KeepThis=true&TB_iframe=true&width=1000&height=500';
						tb_show('', url);
						$('div#TB_window').addClass('alarm') ;
					}
				</script>";
            } else {
                $output .= "<script>
					if ($('div#TB_window').is(':visible')==true && $('div#TB_window').attr('class')=='alarm') {
						tb_remove();
					}
				</script>";
            }
        }
    }

    //GET & SHOW NOTIFICATIONS
    try {
        $dataNotifications = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
        $sqlNotifications = "(SELECT pupilsightNotification.*, pupilsightModule.name AS source FROM pupilsightNotification JOIN pupilsightModule ON (pupilsightNotification.pupilsightModuleID=pupilsightModule.pupilsightModuleID) WHERE pupilsightPersonID=:pupilsightPersonID AND status='New')
		UNION
		(SELECT pupilsightNotification.*, 'System' AS source FROM pupilsightNotification WHERE pupilsightModuleID IS NULL AND pupilsightPersonID=:pupilsightPersonID2 AND status='New')
		ORDER BY timestamp DESC, source, text";
        $resultNotifications = $connection2->prepare($sqlNotifications);
        $resultNotifications->execute($dataNotifications);
    } catch (PDOException $e) {
        $return .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($resultNotifications->rowCount() > 0) {
        $output .= "<a class='inline-block relative mr-4' title='".__('Notifications')."' href='./index.php?q=notifications.php'><span class='badge -mr-2 right-0'>".$resultNotifications->rowCount()."</span><img style='margin-left: 2px; vertical-align: -75%' src='./themes/".$themeName."/img/notifications.png'></a>";
    } else {
        $output .= "<a class='inactive inline-block relative mr-4' title='".__('Notifications')."' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=notifications.php'><img class='minorLinkIcon' style='margin-left: 2px; opacity: 0.2; vertical-align: -75%' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/notifications.png'></a>";
    }
}

echo $output;
