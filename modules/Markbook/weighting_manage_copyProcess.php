<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Markbook/weighting_manage.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/weighting_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $pupilsightWeightingCopyClassID = (isset($_POST['pupilsightWeightingCopyClassID']))? $_POST['pupilsightWeightingCopyClassID'] : null;

    if (empty($_POST)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else if (empty($pupilsightCourseClassID) || empty($pupilsightWeightingCopyClassID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {

        try {
            $data2 = array('pupilsightCourseClassID' => $pupilsightWeightingCopyClassID);
            $sql2 = 'SELECT * FROM pupilsightMarkbookWeight WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
            $result2 = $connection2->prepare($sql2);
            $result2->execute($data2);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result2->rowCount() <= 0) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
            exit();
        } else {

            $partialFail = false;
            while ($weighting = $result2->fetch() ) {

                //Write to database
                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'description' => $weighting['description'], 'type' => $weighting['type'], 'weighting' => $weighting['weighting'], 'reportable' => $weighting['reportable'], 'calculate' => $weighting['calculate'] );

                    $sql = 'INSERT INTO pupilsightMarkbookWeight SET pupilsightCourseClassID=:pupilsightCourseClassID, description=:description, type=:type, weighting=:weighting, reportable=:reportable, calculate=:calculate';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }

                if ($partialFail) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                    exit();
                } else {
                    $URL .= "&return=success0";
                    header("Location: {$URL}");
                }
            }
            
        }
    }
}

?>