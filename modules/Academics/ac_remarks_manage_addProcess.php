<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address']).'/ac_manage_remarks.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_remarks_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    //Proceed!

  /*  echo "<pre>";
    print_r($_POST);exit;*/
    $remarkcode = $_POST['rcode'];
    $description = $_POST['description'];
    $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
    $skill = $_POST['skill'];


    
    if ($remarkcode == '' or $description == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
       
        //Check unique inputs for uniquness
      /*  try {
            $data = array( 'remarkcode' => $remarkcode);
            $sql = 'SELECT * FROM acRemarks WHERE  remarkcode=:remarkcode';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
        */

      /*  if ($result->rowCount() > 0) {
			$URL_BACK= $_SERVER['HTTP_REFERER'];
            $URL_BACK .= '&return=error7';		
            header("Location: {$URL_BACK}");
        } else {
            */
            //Check for other currents
            
              //SELECT * FROM `acRemarks` WHERE ,`id`,`remarkcode`,`description`,`pupilsightDepartmentID`,`skill` 
                try {
                    $data = array('remarkcode' => $remarkcode, 'description' => $description, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'skill' => $skill);
                    $sql = "INSERT INTO acRemarks SET remarkcode=:remarkcode, description=:description, pupilsightDepartmentID=:pupilsightDepartmentID, skill=:skill";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }

               
                $URL .= "&return=success0";
			   
				header("Location: {$URL}");
           
       // }
    }
}
