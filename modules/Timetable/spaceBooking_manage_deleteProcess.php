<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTSpaceBookingID = $_GET['pupilsightTTSpaceBookingID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/spaceBooking_manage_delete.php&pupilsightTTSpaceBookingID='.$pupilsightTTSpaceBookingID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/spaceBooking_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable/spaceBooking_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightTTSpaceBookingID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
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
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('pupilsightTTSpaceBookingID' => $pupilsightTTSpaceBookingID);
                    $sql = 'DELETE FROM pupilsightTTSpaceBooking WHERE pupilsightTTSpaceBookingID=:pupilsightTTSpaceBookingID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $URLDelete = $URLDelete.'&return=success0';
                header("Location: {$URLDelete}");
            }
        }
    }
}
