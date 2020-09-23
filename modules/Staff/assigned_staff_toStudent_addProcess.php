<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . '/assign_student_toStaff.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_student_toStaff.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $student_id = $_POST['student_id'];
    $pupilsightStaffID = $_POST['staff_id'];
    $pupilsightPersonID = explode(',', $student_id);
    $cnt = count($pupilsightPersonID);
    if ($pupilsightStaffID == ''  or $cnt == 0) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness

        try {
            foreach ($pupilsightPersonID as $stu) {
                $data1 = array('pupilsightPersonID' => $stu,'pupilsightStaffID' => $pupilsightStaffID);
                $sql1 = 'SELECT * FROM assignstudent_tostaff WHERE pupilsightPersonID=:pupilsightPersonID AND  pupilsightStaffID=:pupilsightStaffID';
                $result1 = $connection2->prepare($sql1);
                //  print_r($data1);die();
                $result1->execute($data1);
            }
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result1->rowCount() > 0) {
//echo "already";exit;
            $URL .= '&return=error7';

            header("Location: {$URL}");
        } else {
            try {

              //  echo "yes insert";exit;
                foreach ($pupilsightPersonID as $stu) {

                    $data = array('pupilsightStaffID' => $pupilsightStaffID, 'pupilsightPersonID' => $stu);

                    $sql = 'INSERT INTO assignstudent_tostaff SET pupilsightStaffID=:pupilsightStaffID, pupilsightPersonID=:pupilsightPersonID';

                    $result = $connection2->prepare($sql);

                    $result->execute($data);
                }
            } catch (PDOException $e) {
                $URL .= '&return=error9';
                header("Location: {$URL}");
                exit();
            }

            $URL .= "&return=success0";
            header("Location: {$URL}");
        }
    }
}
