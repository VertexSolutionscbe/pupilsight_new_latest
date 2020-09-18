<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Timetable/spaceBooking_manage_delete.php') == false) {
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
        //Proceed!
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $pupilsightTTSpaceBookingID = $_GET['pupilsightTTSpaceBookingID'];
        if ($pupilsightTTSpaceBookingID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Manage Facility Bookings_allBookings') {
                    $data = array('pupilsightTTSpaceBookingID1' => $pupilsightTTSpaceBookingID, 'pupilsightTTSpaceBookingID2' => $pupilsightTTSpaceBookingID);
                    $sql = "(SELECT pupilsightTTSpaceBooking.*, pupilsightSpace.name AS name, surname, preferredName FROM pupilsightTTSpaceBooking JOIN pupilsightSpace ON (pupilsightTTSpaceBooking.foreignKeyID=pupilsightSpace.pupilsightSpaceID) JOIN pupilsightPerson ON (pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignKey='pupilsightSpaceID' AND pupilsightTTSpaceBookingID=:pupilsightTTSpaceBookingID1) UNION (SELECT pupilsightTTSpaceBooking.*, pupilsightLibraryItem.name AS name, surname, preferredName FROM pupilsightTTSpaceBooking JOIN pupilsightLibraryItem ON (pupilsightTTSpaceBooking.foreignKeyID=pupilsightLibraryItem.pupilsightLibraryItemID) JOIN pupilsightPerson ON (pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignKey='pupilsightLibraryItemID' AND pupilsightTTSpaceBookingID=:pupilsightTTSpaceBookingID2) ORDER BY date, name";
                } else {
                    $data = array('pupilsightPersonID1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightTTSpaceBookingID1' => $pupilsightTTSpaceBookingID, 'pupilsightTTSpaceBookingID2' => $pupilsightTTSpaceBookingID);
                    $sql = "(SELECT pupilsightTTSpaceBooking.*, pupilsightSpace.name AS name, surname, preferredName FROM pupilsightTTSpaceBooking JOIN pupilsightSpace ON (pupilsightTTSpaceBooking.foreignKeyID=pupilsightSpace.pupilsightSpaceID) JOIN pupilsightPerson ON (pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignKey='pupilsightSpaceID' AND pupilsightTTSpaceBooking.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightTTSpaceBookingID=:pupilsightTTSpaceBookingID1) UNION (SELECT pupilsightTTSpaceBooking.*, pupilsightLibraryItem.name AS name, surname, preferredName FROM pupilsightTTSpaceBooking JOIN pupilsightLibraryItem ON (pupilsightTTSpaceBooking.foreignKeyID=pupilsightLibraryItem.pupilsightLibraryItemID) JOIN pupilsightPerson ON (pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignKey='pupilsightLibraryItemID' AND pupilsightTTSpaceBooking.pupilsightPersonID=:pupilsightPersonID2 AND pupilsightTTSpaceBookingID=:pupilsightTTSpaceBookingID2) ORDER BY date, name";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record cannot be found.');
                echo '</div>';
            } else {
                $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/spaceBooking_manage_deleteProcess.php?pupilsightTTSpaceBookingID=$pupilsightTTSpaceBookingID");
                echo $form->getOutput();
            }
        }
    }
}
