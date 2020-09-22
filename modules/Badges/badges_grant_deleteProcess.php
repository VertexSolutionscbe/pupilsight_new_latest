<?php
/*
Pupilsight, Flexible & Open School System
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

include '../../pupilsight.php';


$badgesBadgeStudentID = $_GET['badgesBadgeStudentID'] ?? '';
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'] ?? '';
$URL = $pupilsight->session->get('absoluteURL','').'/index.php?q=/modules/'.getModuleName($_POST['address'])."/badges_grant_delete.php&badgesBadgeStudentID=$badgesBadgeStudentID&pupilsightPersonID2=".$_GET['pupilsightPersonID2'].'&badgesBadgeID2='.$_GET['badgesBadgeID2']."&pupilsightSchoolYearID=$pupilsightSchoolYearID";
$URLDelete = $pupilsight->session->get('absoluteURL','').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/badges_grant.php&pupilsightPersonID2='.$_GET['pupilsightPersonID2'].'&badgesBadgeID2='.$_GET['badgesBadgeID2']."&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Badges/badges_grant_delete.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($badgesBadgeStudentID == '') {
        //Fail1
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('badgesBadgeStudentID' => $badgesBadgeStudentID);
            $sql = 'SELECT * FROM badgesBadgeStudent WHERE badgesBadgeStudentID=:badgesBadgeStudentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            //Fail2
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            //Fail 2
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            $row = $result->fetch();

            //Write to database
            try {
                $data = array('badgesBadgeStudentID' => $badgesBadgeStudentID);
                $sql = 'DELETE FROM badgesBadgeStudent WHERE badgesBadgeStudentID=:badgesBadgeStudentID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail2
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Success 0
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
