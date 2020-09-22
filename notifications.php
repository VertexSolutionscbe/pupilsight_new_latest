<?php
/*
Pupilsight, Flexible & Open School System
*/

if (!isset($_SESSION[$guid]['username'])) {
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Notifications'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo "<div class='linkTop'>";
    echo "<a onclick='return confirm(\"Are you sure you want to delete these records.\")' href='".$_SESSION[$guid]['absoluteURL']."/notificationsDeleteAllProcess.php'>".__('Delete All Notifications')." <img style='vertical-align: -25%' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'></a>";
    echo '</div>';

    //Get and show newnotifications
    try {
        $dataNotifications = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
        $sqlNotifications = "(SELECT pupilsightNotification.*, pupilsightModule.name AS source FROM pupilsightNotification JOIN pupilsightModule ON (pupilsightNotification.pupilsightModuleID=pupilsightModule.pupilsightModuleID) WHERE pupilsightPersonID=:pupilsightPersonID AND status='New')
		UNION
		(SELECT pupilsightNotification.*, 'System' AS source FROM pupilsightNotification WHERE pupilsightModuleID IS NULL AND pupilsightPersonID=:pupilsightPersonID2 AND status='New')
		ORDER BY timestamp DESC, source, text";
        $resultNotifications = $connection2->prepare($sqlNotifications);
        $resultNotifications->execute($dataNotifications);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    echo '<h2>';
    echo __('New Notifications')." <span style='font-size: 65%; font-style: italic; font-weight: normal'> x".$resultNotifications->rowCount().'</span>';
    echo '</h2>';

    echo "<table cellspacing='0' style='width: 100%'>";
    echo "<tr class='head'>";
    echo "<th style='width: 18%'>";
    echo __('Source');
    echo '</th>';
    echo "<th style='width: 12%'>";
    echo __('Date');
    echo '</th>';
    echo "<th style='width: 51%'>";
    echo __('Message');
    echo '</th>';
    echo "<th style='width: 7%'>";
    echo __('Count');
    echo '</th>';
    echo "<th style='width: 12%'>";
    echo __('Actions');
    echo '</th>';
    echo '</tr>';

    $count = 0;
    $rowNum = 'odd';
    if ($resultNotifications->rowCount() < 1) {
        echo "<tr class=$rowNum>";
        echo '<td colspan=5>';
        echo __('There are no records to display.');
        echo '</td>';
        echo '</tr>';
    } else {
        while ($row = $resultNotifications->fetch() and $count < 20) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

                //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo $row['source'];
            echo '</td>';
            echo '<td>';
            echo dateConvertBack($guid, substr($row['timestamp'], 0, 10));
            echo '</td>';
            echo '<td>';
            echo $row['text'];
            echo '</td>';
            echo '<td>';
            echo $row['count'];
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/notificationsActionProcess.php?action='.urlencode($row['actionLink']).'&pupilsightNotificationID='.$row['pupilsightNotificationID']."'><img title='".__('Action & Archive')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/notificationsDeleteProcess.php?pupilsightNotificationID='.$row['pupilsightNotificationID']."'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
            echo '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';

    //Get and show newnotifications
    try {
        $dataNotifications = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
        $sqlNotifications = "(SELECT pupilsightNotification.*, pupilsightModule.name AS source FROM pupilsightNotification JOIN pupilsightModule ON (pupilsightNotification.pupilsightModuleID=pupilsightModule.pupilsightModuleID) WHERE pupilsightPersonID=:pupilsightPersonID AND status='Archived')
		UNION
		(SELECT pupilsightNotification.*, 'System' AS source FROM pupilsightNotification WHERE pupilsightModuleID IS NULL AND pupilsightPersonID=:pupilsightPersonID2 AND status='Archived')
		ORDER BY timestamp DESC, source, text LIMIT 0, 50";
        $resultNotifications = $connection2->prepare($sqlNotifications);
        $resultNotifications->execute($dataNotifications);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    echo '<h2>';
    echo __('Archived Notifications');
    echo '</h2>';
    echo "<table cellspacing='0' style='width: 100%'>";
    echo "<tr class='head'>";
    echo "<th style='width: 18%'>";
    echo __('Source');
    echo '</th>';
    echo "<th style='width: 12%'>";
    echo __('Date');
    echo '</th>';
    echo "<th style='width: 51%'>";
    echo __('Message');
    echo '</th>';
    echo "<th style='width: 7%'>";
    echo __('Count');
    echo '</th>';
    echo "<th style='width: 12%'>";
    echo __('Actions');
    echo '</th>';
    echo '</tr>';

    $count = 0;
    $rowNum = 'odd';
    if ($resultNotifications->rowCount() < 1) {
        echo "<tr class=$rowNum>";
        echo '<td colspan=5>';
        echo __('There are no records to display.');
        echo '</td>';
        echo '</tr>';
    } else {
        while ($row = $resultNotifications->fetch() and $count < 20) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

			//COLOR ROW BY STATUS!
			echo "<tr class=$rowNum>";
            echo '<td>';
            echo $row['source'];
            echo '</td>';
            echo '<td>';
            echo dateConvertBack($guid, substr($row['timestamp'], 0, 10));
            echo '</td>';
            echo '<td>';
            echo $row['text'];
            echo '</td>';
            echo '<td>';
            echo $row['count'];
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/notificationsActionProcess.php?action='.urlencode($row['actionLink']).'&pupilsightNotificationID='.$row['pupilsightNotificationID']."'><img title='".__('Action')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/notificationsDeleteProcess.php?pupilsightNotificationID='.$row['pupilsightNotificationID']."'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
            echo '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';
}
