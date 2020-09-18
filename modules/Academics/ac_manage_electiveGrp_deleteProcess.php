<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$getsql1 = 'SELECT * FROM ac_elective_group WHERE id = '.$id.'';
$getresult1 = $connection2->query($getsql1);
$getdata1 = $getresult1->fetch();

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/manage_elective_group.php&sid='.$getdata1['pupilsightSchoolYearID'].'&pid='.$getdata1['pupilsightProgramID'].'&cid='.$getdata1['pupilsightYearGroupID'].'';


if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_electiveGrp_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM ac_elective_group WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('id' => $id);
                $sql = 'DELETE FROM ac_elective_group WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $datasec = array('ac_elective_group_id' => $id);
                $sqlsec = 'DELETE FROM ac_elective_group_section WHERE ac_elective_group_id=:ac_elective_group_id';
                $resultsec = $connection2->prepare($sqlsec);
                $resultsec->execute($datasec);

                $datasub = array('ac_elective_group_id' => $id);
                $sqlsub = 'DELETE FROM ac_elective_group_subjects WHERE ac_elective_group_id=:ac_elective_group_id';
                $resultsub = $connection2->prepare($sqlsub);
                $resultsub->execute($datasub);

            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URL = $URL.'&return=success0';
            header("Location: {$URL}");
        }
    }
}
