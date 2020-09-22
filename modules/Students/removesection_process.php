<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/removesection.php.php&id='.$id;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/student_view.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/removesection.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
       
       
            //Write to database
            try {
              
              
                $data1 = array('pupilsightPersonID' => $id, 'pupilsightRollGroupID' => '');
                $sql1 = 'UPDATE pupilsightStudentEnrolment SET pupilsightRollGroupID=:pupilsightRollGroupID WHERE pupilsightPersonID=:pupilsightPersonID';
                $result1 = $connection2->prepare($sql1);
                $result1->execute($data1);
              //  header('Location: ' . $_SERVER["HTTP_REFERER"] );


              
            } catch (PDOException $e) {
                $URLDelete .= '&return=error2';
                header("Location: {$URLDelete}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
      
    }
}
