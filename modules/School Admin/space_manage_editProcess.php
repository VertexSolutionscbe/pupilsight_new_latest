<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSpaceID = $_GET['pupilsightSpaceID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/space_manage_edit.php&pupilsightSpaceID=$pupilsightSpaceID";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/space_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightSpaceID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightSpaceID' => $pupilsightSpaceID);
            $sql = 'SELECT * FROM pupilsightSpace WHERE pupilsightSpaceID=:pupilsightSpaceID';
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
            $name = $_POST['name'];
            $type = $_POST['type'];
            $capacity = $_POST['capacity'];
            $computer = $_POST['computer'];
            $computerStudent = $_POST['computerStudent'];
            $projector = $_POST['projector'];
            $tv = $_POST['tv'];
            $dvd = $_POST['dvd'];
            $hifi = $_POST['hifi'];
            $speakers = $_POST['speakers'];
            $iwb = $_POST['iwb'];
            $phoneInternal = $_POST['phoneInternal'];
            $phoneExternal = preg_replace('/[^0-9+]/', '', $_POST['phoneExternal']);
            $comment = $_POST['comment'];

            //Validate Inputs
            if ($name == '' or $type == '' or $computer == '' or $computerStudent == '' or $projector == '' or $tv == '' or $dvd == '' or $hifi == '' or $speakers == '' or $iwb == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'pupilsightSpaceID' => $pupilsightSpaceID);
                    $sql = 'SELECT * FROM pupilsightSpace WHERE name=:name AND NOT pupilsightSpaceID=:pupilsightSpaceID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('name' => $name, 'type' => $type, 'capacity' => $capacity, 'computer' => $computer, 'computerStudent' => $computerStudent, 'projector' => $projector, 'tv' => $tv, 'dvd' => $dvd, 'hifi' => $hifi, 'speakers' => $speakers, 'iwb' => $iwb, 'phoneInternal' => $phoneInternal, 'phoneExternal' => $phoneExternal, 'comment' => $comment, 'pupilsightSpaceID' => $pupilsightSpaceID);
                        $sql = 'UPDATE pupilsightSpace SET name=:name, type=:type, capacity=:capacity, computer=:computer, computerStudent=:computerStudent, projector=:projector, tv=:tv, dvd=:dvd , hifi=:hifi, speakers=:speakers, iwb=:iwb, phoneInternal=:phoneInternal, phoneExternal=:phoneExternal, comment=:comment WHERE pupilsightSpaceID=:pupilsightSpaceID';
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
    }
}
