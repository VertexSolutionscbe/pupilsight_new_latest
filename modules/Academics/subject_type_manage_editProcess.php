<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/subject_type_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/subject_type_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $id = $_POST['id'];
//print_r($id);die();
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM pupilsightDepartmentType WHERE id=:id';
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
            //Validate Inputs
               // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();

    $name = $_POST['name'];
    
   // $udt = date('Y-m-d H:i:s');
            

    if ($name == ''   ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'id' => $id);
                    $sql = 'SELECT * FROM pupilsightDepartmentType WHERE name=:name AND NOT id=:id';
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
                        $data = array('name' => $name, 'id' => $id);
                        $sql = 'UPDATE pupilsightDepartmentType SET name=:name WHERE id=:id';
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
