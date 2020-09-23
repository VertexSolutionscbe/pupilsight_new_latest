<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightMarkbookWeightID = (isset($_POST['pupilsightMarkbookWeightID']))? $_POST['pupilsightMarkbookWeightID'] : null;
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/weighting_manage_delete.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightMarkbookWeightID=$pupilsightMarkbookWeightID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/weighting_manage.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/weighting_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if (empty($_POST)) {
        $URL .= '&return=warning1';
        header("Location: {$URL}");
    } else if (empty($pupilsightCourseClassID) || empty($pupilsightMarkbookWeightID)) {
        $URL .= '&return=warning1';
        header("Location: {$URL}");
    } else {

        try {
            $data2 = array('pupilsightMarkbookWeightID' => $pupilsightMarkbookWeightID);
            $sql2 = 'SELECT type FROM pupilsightMarkbookWeight WHERE pupilsightMarkbookWeightID=:pupilsightMarkbookWeightID';
            $result2 = $connection2->prepare($sql2);
            $result2->execute($data2);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result2->rowCount() != 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightMarkbookWeightID' => $pupilsightMarkbookWeightID);
                $sql = 'DELETE FROM pupilsightMarkbookWeight WHERE pupilsightMarkbookWeightID=:pupilsightMarkbookWeightID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete .= "&return=success0";
            header("Location: {$URLDelete}");
        }
    }
}

?>