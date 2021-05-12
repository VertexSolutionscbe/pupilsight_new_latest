<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Messenger\GroupGateway;

include '../../pupilsight.php';

$pupilsightGroupID = isset($_GET['pupilsightGroupID'])? $_GET['pupilsightGroupID'] : '';
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/groups_manage_edit.php&pupilsightGroupID=$pupilsightGroupID";

if (isActionAccessible($guid, $connection2, '/modules/Messenger/groups_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    if (empty($pupilsightGroupID)) { 
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        $name = isset($_POST['name'])? $_POST['name'] : '';
        $choices = isset($_POST['members'])? $_POST['members'] : array();
        $choices1 = isset($_POST['staffmembers'])? $_POST['staffmembers'] : array();
        //$choices2 = isset($_POST['parentmembers'])? $_POST['parentmembers'] : array();
        $choices3 = isset($_POST['allmembers'])? $_POST['allmembers'] : array();
        $choices4 = isset($_POST['pupilsightPersonID'])? $_POST['pupilsightPersonID'] : array();
        $choices5 = isset($_POST['pupilsightYearGroupID1'])? $_POST['pupilsightYearGroupID1'] : array();
        $pupilsightSchoolYearID=$_POST['pupilsightSchoolYearID1'];

        if(!empty($_POST['is_chat'])){
            $is_chat = $_POST['is_chat'];
        } else {
            $is_chat = '0';
        }
        if (empty($name)) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        } else {
            $groupGateway = $container->get(GroupGateway::class);

            $highestAction = getHighestGroupedAction($guid, '/modules/Messenger/groups_manage.php', $connection2);
            if ($highestAction == 'Manage Groups_all') {
                $values = $groupGateway->selectGroupByID($pupilsightGroupID);
            } else {
                $values = $groupGateway->selectGroupByIDAndOwner($pupilsightGroupID, $_SESSION[$guid]['pupilsightPersonID']);
            }
                
            if (empty($values)) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit;
            } else {
                $data = array('pupilsightGroupID' => $pupilsightGroupID, 'name' => $name, 'is_chat' => $is_chat);
                $updated = $groupGateway->updateGroup($data);
                $partialFail = false;

                if (count($choices) > 0) {
                    foreach ($choices as $pupilsightPersonID) {
                        $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonID' => $pupilsightPersonID);
                        $inserted = $groupGateway->insertGroupPerson($data);
                        $partialFail &= !$inserted;
                    }
                }
                foreach ($choices1 as $pupilsightPersonID) {
                    $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $inserted = $groupGateway->insertGroupPerson($data);
                    $partialFail &= !$inserted;
                }
                /*foreach ($choices2 as $pupilsightPersonID) {
                    $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $inserted = $groupGateway->insertGroupPerson($data);
                    $partialFail &= !$inserted;
                }*/
                foreach ($choices3 as $pupilsightPersonID) {
                    $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $inserted = $groupGateway->insertGroupPerson($data);
                    $partialFail &= !$inserted;
                }
                foreach ($choices4 as $pupilsightPersonID) {
                    $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $inserted = $groupGateway->insertGroupPerson($data);
                    $partialFail &= !$inserted;
                }

                foreach ($choices5 as $classwithsections) {

                    $choices5classes=explode('-',$classwithsections);

                    $sqls = 'SELECT a.pupilsightPersonID FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $choices5classes[0] . '" AND a.pupilsightYearGroupID = "' . $choices5classes[1] . '" AND a.pupilsightRollGroupID = "' . $choices5classes[2] . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';
                    $results = $connection2->query($sqls);
                    $rowdatastd = $results->fetchAll();

                    foreach ($rowdatastd as $pupilsightPersonID){
                        $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonID' => $pupilsightPersonID['pupilsightPersonID']);
                        $inserted = $groupGateway->insertGroupPerson($data);
                        $partialFail &= !$inserted;
                    }

                }

                if ($partialFail) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                    exit;
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                    exit;
                }
            }
        }
    }
}
