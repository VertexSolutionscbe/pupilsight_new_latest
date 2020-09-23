<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$atlColumnID = $_GET['atlColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/atl_manage_delete.php&atlColumnID=$atlColumnID&pupilsightCourseClassID=$pupilsightCourseClassID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/atl_manage.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_manage_delete.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($atlColumnID == '' or $pupilsightCourseClassID == '') {
        //Fail1
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('atlColumnID' => $atlColumnID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'SELECT * FROM atlColumn WHERE atlColumnID=:atlColumnID AND pupilsightCourseClassID=:pupilsightCourseClassID';
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
            //Write to database
            try {
                $data = array('atlColumnID' => $atlColumnID);
                $sql = 'DELETE FROM atlColumn WHERE atlColumnID=:atlColumnID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail 2
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
