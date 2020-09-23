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

$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];

$URL = $pupilsight->session->get('absoluteURL','').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/badges_grant_add.php&pupilsightPersonID2='.$_GET['pupilsightPersonID2'].'&badgesBadgeID2='.$_GET['badgesBadgeID2']."&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Badges/badges_grant_add.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $pupilsightPersonIDMulti = $_POST['pupilsightPersonIDMulti'] ?? null;
    $badgesBadgeID = $_POST['badgesBadgeID'] ?? '';
    $date = $_POST['date'] ?? '';
    $comment = $_POST['comment'] ?? '';

    if ($pupilsightPersonIDMulti == null or $date == '' or $badgesBadgeID == '' or $pupilsightSchoolYearID == '') {
        //Fail 3
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        $partialFail = false;

        foreach ($pupilsightPersonIDMulti as $pupilsightPersonID) {
            //Write to database
            try {
                $data = array('badgesBadgeID' => $badgesBadgeID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'date' => dateConvert($guid, $date), 'pupilsightPersonID' => $pupilsightPersonID, 'comment' => $comment, 'pupilsightPersonIDCreator' => $pupilsight->session->get('pupilsightPersonID',''));
                $sql = 'INSERT INTO badgesBadgeStudent SET badgesBadgeID=:badgesBadgeID, pupilsightSchoolYearID=:pupilsightSchoolYearID, date=:date, pupilsightPersonID=:pupilsightPersonID, comment=:comment, pupilsightPersonIDCreator=:pupilsightPersonIDCreator';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            $badgesBadgeStudentID = $connection2->lastInsertID();

            //Notify User
            $notificationText = __('Someone has granted you a badge.');
            setNotification($connection2, $guid, $pupilsightPersonID, $notificationText, 'Badges', "/index.php?q=/modules/Badges/badges_view.php&pupilsightPersonID=$pupilsightPersonID");
        }

        if ($partialFail == true) {
            //Fail 5
            $URL .= '&return=error5';
            header("Location: {$URL}");
        } else {
            //Success 0
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
