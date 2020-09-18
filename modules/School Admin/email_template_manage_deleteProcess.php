<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTemplateID = $_GET['pupilsightTemplateID'];
//$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/email_template_manage_delete.php&pupilsightTemplateID='.$pupilsightTemplateID.'&search='.$_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/email_template_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/email_template_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightTemplateID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightTemplateID' => $pupilsightTemplateID);
            $sql = 'SELECT * FROM pupilsightTemplate WHERE pupilsightTemplateID=:pupilsightTemplateID';
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
                $data = array('pupilsightTemplateID' => $pupilsightTemplateID);
                $sql = 'DELETE FROM pupilsightTemplate WHERE pupilsightTemplateID=:pupilsightTemplateID';
                $result = $connection2->prepare($sql);
                $result->execute($data);

            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
