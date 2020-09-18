<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Markbook/markbook_edit.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_copy.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $pupilsightMarkbookCopyClassID = (isset($_GET['pupilsightMarkbookCopyClassID']))? $_GET['pupilsightMarkbookCopyClassID'] : null;
    $copyColumnID = (isset($_POST['copyColumnID']))? $_POST['copyColumnID'] : null;

    if (empty($_POST)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else if (empty($pupilsightCourseClassID) || empty($pupilsightMarkbookCopyClassID) || empty($copyColumnID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {

        try {
            $data2 = array('pupilsightCourseClassID' => $pupilsightMarkbookCopyClassID);
            $sql2 = 'SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
            $result2 = $connection2->prepare($sql2);
            $result2->execute($data2);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result2->rowCount() <= 0) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
            exit();
        } else {

            $partialFail = false;
            while ($column = $result2->fetch() ) {

                // Only include the selected columns
                if ( isset($copyColumnID[ $column['pupilsightMarkbookColumnID'] ]) && $column['pupilsightMarkbookColumnID'] == true ) {

                    //Write to database
                    try {
                        $date = (!empty($_POST['date']))? dateConvert($guid, $_POST['date']) : date('Y-m-d');
                        $data = array('pupilsightUnitID' => $column['pupilsightUnitID'], 'pupilsightPlannerEntryID' => $column['pupilsightPlannerEntryID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'name' => $column['name'], 'description' => $column['description'], 'type' => $column['type'], 'date' => $date, 'sequenceNumber' => $column['sequenceNumber'], 'attainment' => $column['attainment'], 'pupilsightScaleIDAttainment' => $column['pupilsightScaleIDAttainment'], 'attainmentWeighting' => $column['attainmentWeighting'], 'attainmentRaw' => $column['attainmentRaw'], 'attainmentRawMax' => $column['attainmentRawMax'], 'effort' => $column['effort'], 'pupilsightScaleIDEffort' => $column['pupilsightScaleIDEffort'], 'pupilsightRubricIDAttainment' => $column['pupilsightRubricIDAttainment'], 'pupilsightRubricIDEffort' => $column['pupilsightRubricIDEffort'], 'comment' => $column['comment'], 'uploadedResponse' => $column['uploadedResponse'], 'viewableStudents' => $column['viewableStudents'], 'viewableParents' => $column['viewableParents'], 'attachment' => $column['attachment'], 'pupilsightPersonIDCreator' => $column['pupilsightPersonIDCreator'], 'pupilsightPersonIDLastEdit' => $column['pupilsightPersonIDLastEdit'], 'pupilsightSchoolYearTermID' => $column['pupilsightSchoolYearTermID']);
                    $sql = 'INSERT INTO pupilsightMarkbookColumn SET pupilsightUnitID=:pupilsightUnitID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightCourseClassID=:pupilsightCourseClassID, name=:name, description=:description, type=:type, date=:date, sequenceNumber=:sequenceNumber, attainment=:attainment, pupilsightScaleIDAttainment=:pupilsightScaleIDAttainment, attainmentWeighting=:attainmentWeighting, attainmentRaw=:attainmentRaw, attainmentRawMax=:attainmentRawMax, effort=:effort, pupilsightScaleIDEffort=:pupilsightScaleIDEffort, pupilsightRubricIDAttainment=:pupilsightRubricIDAttainment, pupilsightRubricIDEffort=:pupilsightRubricIDEffort, comment=:comment, uploadedResponse=:uploadedResponse, viewableStudents=:viewableStudents, viewableParents=:viewableParents, attachment=:attachment, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit, pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }

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
