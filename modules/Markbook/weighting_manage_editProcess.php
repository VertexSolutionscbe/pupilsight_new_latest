<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/weighting_manage.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/weighting_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=warning1';
        header("Location: {$URL}");
    } else if (empty($pupilsightCourseClassID)) {
        $URL .= '&return=warning1';
        header("Location: {$URL}");
    } else {

        $pupilsightMarkbookWeightID = (isset($_GET['pupilsightMarkbookWeightID']))? $_GET['pupilsightMarkbookWeightID'] : null;
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

            $description = (isset($_POST['description']))? $_POST['description'] : null;
            $type = (isset($_POST['type']))? $_POST['type'] : null;
            $weighting = (isset($_POST['weighting']))? floatval($_POST['weighting']) : 0;
            $weighting = max(0, min(100, $weighting) );
            $reportable = (isset($_POST['reportable']))? $_POST['reportable'] : null;
            $calculate = (isset($_POST['calculate']))? $_POST['calculate'] : null;

            if ( empty($description) || empty($type) || empty($reportable) || empty($calculate) || $weighting === '' ) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'description' => $description, 'type' => $type, 'weighting' => $weighting, 'reportable' => $reportable, 'calculate' => $calculate, 'pupilsightMarkbookWeightID' => $pupilsightMarkbookWeightID );
                    $sql = 'UPDATE pupilsightMarkbookWeight SET pupilsightCourseClassID=:pupilsightCourseClassID, description=:description, type=:type, weighting=:weighting, reportable=:reportable, calculate=:calculate WHERE pupilsightMarkbookWeightID=:pupilsightMarkbookWeightID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= "&return=success0";
                header("Location: {$URL}");
            }
        }
    }
}

?>