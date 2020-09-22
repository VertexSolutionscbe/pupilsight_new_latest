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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function staffHigherEducationRole($pupilsightPersonID, $connection2)
{
    $output = false;

    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT * FROM higherEducationStaff WHERE pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() == 1) {
        $row = $result->fetch();
        if ($row['role'] == 'Coordinator' or $row['role'] == 'Advisor') {
            $output = $row['role'];
        }
    }

    return $output;
}

//Returns true if student is enrolled
function studentEnrolment($pupilsightPersonID, $connection2)
{
    $output = false;

    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT * FROM higherEducationStudent WHERE pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() == 1) {
        $output = true;
    }

    return $output;
}
