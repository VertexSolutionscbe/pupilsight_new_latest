<?php

use Pupilsight\Domain\Messenger\GroupGateway;

//print_r($_GET);die();
//die();
$pupilsightGroupID = (isset($_GET['pupilsightGroupID']))? $_GET['pupilsightGroupID'] : null;
$pupilsightPersonIDs = (isset($_GET['tid']))? $_GET['tid'] : null;
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Messenger/groups_manage_edit.php&pupilsightGroupID='.$pupilsightGroupID;

$pp= sizeof($pupilsightPersonIDs);


$p=0;
    if (sizeof($pupilsightPersonIDs) > 0) {
        foreach ($pupilsightPersonIDs as $key => $pupilsightPersonID) {


            /*try {
                $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = "DELETE FROM pupilsightGroupPerson WHERE pupilsightGroupID=:pupilsightGroupID AND pupilsightPersonID=:pupilsightPersonID";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }*/
                //Proceed!
                $groupGateway = $container->get(GroupGateway::class);

                    $deleted = $groupGateway->deleteGroupPerson($pupilsightGroupID, $pupilsightPersonID);

$p++;
                }

            }


else{
    echo "Something went wrong";
    exit;
}
    echo "Deleted Successfully";
    exit;
