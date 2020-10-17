<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightSchoolYearIDNext = $_GET['pupilsightSchoolYearIDNext'];
$URL = $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/School Admin/rollGroup_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearIDNext";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/rollGroup_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school years specified (current and next)
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearIDNext == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //GET CURRENT ROLL GROUPS
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() < 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            $partialFail = false;
            $nameAlreadyExists = 0;     //this will be changed to `1` if any name or short name already exists
            while ($row = $result->fetch()) {
                //Write to database 
                //Added by Anant
                //Don't write if name or short name already exists
                if (isSectionNameAdded($connection2, $row["name"], $row['nameShort'], $pupilsightSchoolYearIDNext)) {
                    try {
                        $dataInsert = array('pupilsightSchoolYearID' => $pupilsightSchoolYearIDNext, 'name' => $row['name'], 'nameShort' => $row['nameShort'], 'pupilsightPersonIDTutor' => $row['pupilsightPersonIDTutor'], 'pupilsightPersonIDTutor2' => $row['pupilsightPersonIDTutor2'], 'pupilsightPersonIDTutor3' => $row['pupilsightPersonIDTutor3'], 'pupilsightSpaceID' => $row['pupilsightSpaceID'], 'website' => $row['website']);
                        $sqlInsert = 'INSERT INTO pupilsightRollGroup SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, pupilsightPersonIDTutor=:pupilsightPersonIDTutor, pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2, pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3, pupilsightSpaceID=:pupilsightSpaceID, pupilsightRollGroupIDNext=NULL, website=:website';
                        $resultInsert = $connection2->prepare($sqlInsert);
                        $resultInsert->execute($dataInsert);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                } else {
                    $nameAlreadyExists = 1;
                }
            }

            if ($partialFail == true) {
                $URL .= "&return=error5&nameAlreadyExists=" . $nameAlreadyExists;
                header("Location: {$URL}");
            } else {
                $URL .= "&return=success0&nameAlreadyExists=" . $nameAlreadyExists;
                header("Location: {$URL}");
            }
        }
    }
}

//Added by Anant
//check if roll group name or short name exists
function isSectionNameAdded($connection2, $name, $nameShort, $pupilsightSchoolYearIDNext)
{
    $data1 = ['name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearIDNext];
    $query1 = "SELECT count(pupilsightRollGroupID) FROM pupilsightRollGroup WHERE name = :name AND pupilsightSchoolYearID = :pupilsightSchoolYearID";
    $result1 = $connection2->prepare($query1);
    $result1->execute($data1);

    $data2 = ['nameShort' => $nameShort, 'pupilsightSchoolYearID' => $pupilsightSchoolYearIDNext];
    $query2 = "SELECT count(pupilsightRollGroupID) FROM pupilsightRollGroup WHERE nameShort = :nameShort AND pupilsightSchoolYearID = :pupilsightSchoolYearID";
    $result2 = $connection2->prepare($query2);
    $result2->execute($data2);

    //return true if name and short name don't exist
    if ($result1->fetchColumn() == 0 && $result2->fetchColumn() == 0)
        return true;
    else
        return false;
}
