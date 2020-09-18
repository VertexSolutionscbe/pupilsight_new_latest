<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';
$pupilsightStaffID = isset($_POST['pupilsightStaffID']) ? $_POST['pupilsightStaffID'] : array();
$dateEnd = isset($_POST['dateEnd']) ? dateConvert($guid, $_POST['dateEnd']) : date('Y-m-d');

$allStaff = isset($_GET['allStaff']) ? $_GET['allStaff'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/staff_manage.php&search=$search&allStaff=$allStaff";

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if (empty($action) || empty($pupilsightStaffID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $pupilsightStaffIDList = is_array($pupilsightStaffID)? implode(',', $pupilsightStaffID) : $pupilsightStaffID;

        if ($action == 'Left') {
            $data = array('pupilsightStaffIDList' => $pupilsightStaffIDList, 'dateEnd' => $dateEnd);
            $sql = "UPDATE pupilsightStaff 
                    JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID) 
                    SET pupilsightPerson.status='Left', pupilsightPerson.dateEnd=:dateEnd
                    WHERE FIND_IN_SET(pupilsightStaffID, :pupilsightStaffIDList)";

            $updated = $pdo->update($sql, $data);

            if ($pdo->getQuerySuccess() == false){
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }
        }

        $URL .= '&return=success0';
        header("Location: {$URL}");
        exit();
    }
}
