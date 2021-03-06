<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_space_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $pupilsightSpaceID = isset($_REQUEST['pupilsightSpaceID']) ? $_REQUEST['pupilsightSpaceID'] : '';
        $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : null;
        $pupilsightTTID = isset($_REQUEST['pupilsightTTID']) ? $_REQUEST['pupilsightTTID'] : null;

        try {
            $data = array('pupilsightSpaceID' => $pupilsightSpaceID);
            $sql = 'SELECT * FROM pupilsightSpace WHERE pupilsightSpaceID=:pupilsightSpaceID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified room does not seem to exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();

            $page->breadcrumbs
                ->add(__('View Timetable by Facility'), 'tt_space.php')
                ->add($row['name']);

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Timetable/tt_space.php&search=$search'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $ttDate = null;
            if (isset($_REQUEST['ttDate'])) {
                $date = dateConvert($guid, $_REQUEST['ttDate']);
                $ttDate = strtotime('last Sunday +1 day', strtotime($date));
            }

            if (isset($_POST['fromTT'])) {
                if ($_POST['fromTT'] == 'Y') {
                    if (isset($_POST['spaceBookingCalendar'])) {
                        if ($_POST['spaceBookingCalendar'] == 'on' or $_POST['spaceBookingCalendar'] == 'Y') {
                            $_SESSION[$guid]['viewCalendarSpaceBooking'] = 'Y';
                        } else {
                            $_SESSION[$guid]['viewCalendarSpaceBooking'] = 'N';
                        }
                    } else {
                        $_SESSION[$guid]['viewCalendarSpaceBooking'] = 'N';
                    }
                }
            }

            $tt = renderTTSpace($guid, $connection2, $pupilsightSpaceID, $pupilsightTTID, false, $ttDate, '/modules/Timetable/tt_space_view.php', "&pupilsightSpaceID=$pupilsightSpaceID&search=$search");

            if ($tt != false) {
                echo $tt;
            } else {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            }
        }
    }
}
