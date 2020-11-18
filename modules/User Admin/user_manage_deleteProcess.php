<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/user_manage_delete.php&pupilsightPersonID='.$pupilsightPersonID.'&search='.$_GET['search'];
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/user_manage.php&search='.$_GET['search'];

if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
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
                $sqlp = 'SELECT pupilsightPersonID1 FROM pupilsightFamilyRelationship WHERE pupilsightPersonID2 = '.$pupilsightPersonID.' ';
                $resultp = $connection2->query($sqlp);
                $parentData = $resultp->fetchALL();

                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'DELETE FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                if(!empty($parentData)){
                    foreach($parentData as $pd){
                        $data = array('pupilsightPersonID' => $pd['pupilsightPersonID1']);
                        $sql = 'DELETE FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    }
                }

                $data = array('pupilsightPersonID2' => $pupilsightPersonID);
                $sql = 'DELETE FROM pupilsightFamilyRelationship WHERE pupilsightPersonID2=:pupilsightPersonID2';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $data = array('pupilsightPersonID1' => $pupilsightPersonID);
                $sql = 'DELETE FROM pupilsightFamilyRelationship WHERE pupilsightPersonID1=:pupilsightPersonID1';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
