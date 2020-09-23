<?php
/*
Pupilsight, Flexible & Open School System
*/

function getRole($pupilsightPersonID, $pupilsightDepartmentID, $connection2)
{
    $role = false;
    try {
        $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT * FROM pupilsightDepartmentStaff WHERE pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() == 1) {
        $row = $result->fetch();
        if ($row['role'] != '') {
            $role = $row['role'];
        }
    }

    return $role;
}
