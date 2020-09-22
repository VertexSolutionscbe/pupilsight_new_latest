<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/house_manage.php';
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/report_students_byHouse.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/house_manage_assign.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs

    $pupilsightYearGroupIDList = (isset($_POST['pupilsightYearGroupIDList']))? $_POST['pupilsightYearGroupIDList'] : '';
    $pupilsightHouseIDList = (isset($_POST['pupilsightHouseIDList']))? $_POST['pupilsightHouseIDList'] : '';
    $balanceYearGroup = (isset($_POST['balanceYearGroup']))? $_POST['balanceYearGroup'] : '';
    $balanceGender = (isset($_POST['balanceGender']))? $_POST['balanceGender'] : '';
    $overwrite = (isset($_POST['overwrite']))? $_POST['overwrite'] : '';

    if (empty($pupilsightYearGroupIDList) || empty($pupilsightHouseIDList) || empty($balanceYearGroup) || empty($balanceGender) || empty($overwrite)) {
        $URL .= "&return=error1";
        header("Location: {$URL}");
        exit;
    } else {
        $partialFail = false;
        $count = 0;

        $pupilsightHouseIDList = (is_array($pupilsightHouseIDList))? implode(',', $pupilsightHouseIDList) : $pupilsightHouseIDList;
        $pupilsightYearGroupIDList = (is_array($pupilsightYearGroupIDList))? implode(',', $pupilsightYearGroupIDList) : $pupilsightYearGroupIDList;

        $yearGroupArray = ($balanceYearGroup == 'Y')? explode(',', $pupilsightYearGroupIDList) : array($pupilsightYearGroupIDList);

        foreach ($yearGroupArray as $pupilsightYearGroupIDs) {

            if ($overwrite == 'Y') {
                // Grab the applicable houses, start all the counters at 0
                try {
                    $data = array('pupilsightHouseIDList' => $pupilsightHouseIDList);
                    $sql = "SELECT pupilsightHouse.pupilsightHouseID as groupBy, pupilsightHouse.pupilsightHouseID, 0 AS total, 0 as totalM, 0 as totalF
                        FROM pupilsightHouse
                        WHERE FIND_IN_SET(pupilsightHouse.pupilsightHouseID, :pupilsightHouseIDList)
                        GROUP BY pupilsightHouse.pupilsightHouseID
                        ORDER BY RAND()";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
            } else {
                // Grab the applicable houses and current totals for this year group (or set of year groups)
                try {
                    $data = array('pupilsightHouseIDList' => $pupilsightHouseIDList, 'pupilsightYearGroupIDs' => $pupilsightYearGroupIDs, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'today' => date('Y-m-d'));
                    $sql = "SELECT pupilsightHouse.pupilsightHouseID as groupBy, pupilsightHouse.pupilsightHouseID, count(pupilsightStudentEnrolment.pupilsightPersonID) AS total, count(CASE WHEN pupilsightPerson.gender='M' THEN pupilsightStudentEnrolment.pupilsightPersonID END) as totalM, count(CASE WHEN pupilsightPerson.gender='F' THEN pupilsightStudentEnrolment.pupilsightPersonID END) as totalF
                        FROM pupilsightHouse
                            LEFT JOIN pupilsightPerson ON (pupilsightPerson.pupilsightHouseID=pupilsightHouse.pupilsightHouseID
                                AND pupilsightPerson.status='Full'
                                AND (pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<=:today)
                                AND (pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd>=:today) )
                            LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND FIND_IN_SET(pupilsightYearGroupID, :pupilsightYearGroupIDs) )
                        WHERE FIND_IN_SET(pupilsightHouse.pupilsightHouseID, :pupilsightHouseIDList)
                        GROUP BY pupilsightHouse.pupilsightHouseID
                        ORDER BY RAND()";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
            }

            $houses = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

            // Build a closure for getting the pupilsightHouseID with the minimum students for a particular group
            $getNextHouse = function($group) use (&$houses) {
                return array_reduce(array_keys($houses), function ($resultID, $currentID) use (&$houses, $group) {
                    $currentValue = $houses[$currentID][$group];
                    $resultValue = $houses[$resultID][$group];

                    return (is_null($resultValue) || $currentValue < $resultValue)? $currentID : $resultID;
                }, key($houses));
            };

            // Grab the list of students
            try {
                $data = array('pupilsightYearGroupIDs' => $pupilsightYearGroupIDs, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'today' => date('Y-m-d'));
                $sql = "SELECT pupilsightStudentEnrolment.pupilsightYearGroupID, pupilsightPerson.gender, pupilsightPerson.pupilsightPersonID, pupilsightPerson.pupilsightHouseID FROM
                        pupilsightPerson
                        JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                        WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                        AND FIND_IN_SET(pupilsightStudentEnrolment.pupilsightYearGroupID, :pupilsightYearGroupIDs)
                        AND pupilsightPerson.status='Full'
                        AND (pupilsightPerson.dateStart IS NULL OR pupilsightPerson.dateStart<=:today)
                        AND (pupilsightPerson.dateEnd IS NULL OR pupilsightPerson.dateEnd>=:today)";

                if ($overwrite == 'N') {
                    $sql .= " AND pupilsightPerson.pupilsightHouseID IS NULL";
                }

                $sql .= " ORDER BY RAND()";

                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            if (!empty($houses) && $result->rowCount() > 0) {

                while ($student = $result->fetch()) {
                    if ($student['gender'] == 'Other' || $student['gender'] == 'Unspecified') {
                        $student['gender'] = random_int(0, 1) == 1? 'M' : 'F';
                    }

                    // Use the closure to grab the next house to fill
                    $group = ($balanceGender == 'Y')? 'total'.$student['gender'] : 'total';
                    $pupilsightHouseID = $getNextHouse($group);

                    if ($pupilsightHouseID !== $student['pupilsightHouseID']) {
                        //Write to database
                        try {
                            $data = array('pupilsightPersonID' => $student['pupilsightPersonID'], 'pupilsightHouseID' => $pupilsightHouseID);
                            $sql = 'UPDATE pupilsightPerson SET pupilsightHouseID=:pupilsightHouseID WHERE pupilsightPersonID=:pupilsightPersonID';
                            $resultUpdate = $connection2->prepare($sql);
                            $resultUpdate->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }

                    // Increment the counters so we're filling up each house
                    $houses[$pupilsightHouseID]['total']++;
                    $houses[$pupilsightHouseID]['total'.$student['gender']]++;
                    $count++;
                }
            }
        }

        if ($partialFail) {
            $URL .= "&return=warning1";
            header("Location: {$URL}");
        } else {
            $URLSuccess .= "&pupilsightYearGroupIDList={$pupilsightYearGroupIDList}&count={$count}";
            $URLSuccess .= "&return=success0";
            header("Location: {$URLSuccess}");
        }
    }
}
