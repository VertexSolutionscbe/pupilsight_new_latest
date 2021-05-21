<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];

$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/check_status.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/check_status.php') == false) {
    $URLDelete .= '&return=error0';
    header("Location: {$URLDelete}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URLDelete .= '&return=error1';
        header("Location: {$URLDelete}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM campaign_payment_attachment WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URLDelete .= '&return=error2';
            header("Location: {$URLDelete}");
            exit();
        }

        if ($result->rowCount() != 1) {
            
            $URLDelete .= '&return=error2';
            header("Location: {$URLDelete}");
        } else {
            //Write to database
            try {
                $data = array('id' => $id);
                $sql = 'DELETE FROM campaign_payment_attachment WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                
            } catch (PDOException $e) {
                $URLDelete .= '&return=error2';
                header("Location: {$URLDelete}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
