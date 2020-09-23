<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
 /*echo '<pre>';
 print_r($_POST);
 echo '</pre>';die();*/
 $ids = $_POST['staff_id'];//array

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/remove_assined_staff.php&id='.$id;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/assign_staff_toClassSection.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/remove_assined_staffProcess.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    $cnt =count($ids);
    if ($cnt == 0) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
       
       

                  //Write to database
              try {
                //
              foreach($ids as $id){
              
              $data = array('id' => $id);
              $sql = 'DELETE FROM assignstaff_toclasssection WHERE id=:id ';
              $result = $connection2->prepare($sql);
              $result->execute($data);
              }

             
          } catch (PDOException $e) {
              $URLDelete .= '&return=error2';
              header("Location: {$URLDelete}");
              exit();
          }
      
          $URLDelete = $URLDelete.'&return=success0';
          header("Location: {$URLDelete}");





    }
}
