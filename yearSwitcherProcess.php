<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

// Gibbon system-wide include
require_once './pupilsight.php';

//$URL = './index.php';
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'] ?? null;

$pupilsight->session->set('pageLoads', null);
$URL = $_SERVER['HTTP_REFERER'];



//Check for parameter
if (empty($pupilsightSchoolYearID)) {
    $URL .= '?return=error0';
    header("Location: {$URL}");
    exit;
} else {
    try {
        $data = array('pupilsightRoleID' => $pupilsight->session->get('pupilsightRoleIDCurrent'));
        $sql = "SELECT futureYearsLogin, pastYearsLogin FROM pupilsightRole WHERE pupilsightRoleID=:pupilsightRoleID";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    //Test to see if username exists and is unique
    if ($result->rowCount() == 1) {
        $row = $result->fetch();

        if ($row['futureYearsLogin'] != 'Y' and $row['pastYearsLogin'] != 'Y') { //NOT ALLOWED DUE TO CONTROLS ON ROLE, KICK OUT!
            $URL .= '?return=error0';
            header("Location: {$URL}");
            exit();
        } else {
            //Get details on requested school year
            try {
                $dataYear = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                $sqlYear = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $resultYear = $connection2->prepare($sqlYear);
                $resultYear->execute($dataYear);
            } catch (PDOException $e) { }

            //Get current year sequenceNumber
            try {
                $dataYearCurrent = array();
                $sqlYearCurrent = "SELECT * FROM pupilsightSchoolYear WHERE status='Current'";
                $resultYearCurrent = $connection2->prepare($sqlYearCurrent);
                $resultYearCurrent->execute($dataYearCurrent);
            } catch (PDOException $e) { }

            //Check number of rows returned.
            //If it is not 1, show error
            if (!($resultYear->rowCount() == 1) && !($resultYearCurrent->rowCount() == 1)) {
                $URL .= '?return=error0';
                header("Location: {$URL}");
                exit;
            }
            //Else get year details
            else {
                $rowYear = $resultYear->fetch();
                $rowYearCurrent = $resultYearCurrent->fetch();
                if ($row['futureYearsLogin'] != 'Y' and $rowYearCurrent['sequenceNumber'] < $rowYear['sequenceNumber']) { //POSSIBLY NOT ALLOWED DUE TO CONTROLS ON ROLE, CHECK YEAR
                    $URL .= '?return=error0';
                    header("Location: {$URL}");
                    exit();
                } elseif ($row['pastYearsLogin'] != 'Y' and $rowYearCurrent['sequenceNumber'] > $rowYear['sequenceNumber']) { //POSSIBLY NOT ALLOWED DUE TO CONTROLS ON ROLE, CHECK YEAR
                    $URL .= '?return=error0';
                    header("Location: {$URL}");
                    exit();
                } else { //ALLOWED
                    $pupilsight->session->set('pupilsightSchoolYearID', $rowYear['pupilsightSchoolYearID']);
                    $pupilsight->session->set('pupilsightSchoolYearName', $rowYear['name']);
                    $pupilsight->session->set('pupilsightSchoolYearSequenceNumber', $rowYear['sequenceNumber']);

                    // Reload cached FF actions
                    $pupilsight->session->cacheFastFinderActions($pupilsight->session->get('pupilsightRoleIDCurrent'));

                    // Clear the main menu from session cache
                    $pupilsight->session->forget('menuMainItems');

                    //$URL .= '?return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}