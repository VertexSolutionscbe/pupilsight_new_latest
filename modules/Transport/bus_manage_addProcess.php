<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

echo $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/bus_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/bus_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $vehicle_number = $_POST['vehicle_number'];
    $name = $_POST['name'];
    $model = $_POST['model'];
    $vtype = $_POST['vtype'];
    $capacity = $_POST['capacity'];
    $regdt = explode('/', $_POST['register_date']);
    $register_date= date('Y-m-d', strtotime(implode('-', array_reverse($regdt))));

    $ins_exp = explode('/', $_POST['insurance_exp']);
    $insurance_exp  = date('Y-m-d', strtotime(implode('-', array_reverse($ins_exp))));

    $fc_exp = explode('/', $_POST['fc_expiry']);
    $fc_expiry  = date('Y-m-d', strtotime(implode('-', array_reverse($fc_exp))));
    
    $driver_name = $_POST['driver_name'];
    $driver_mobile = $_POST['driver_mobile'];
    $coordinator_name = $_POST['coordinator_name'];
    $coordinator_mobile = $_POST['coordinator_mobile'];
   // $photo = $_POST['photo'];


 
  //  $cdt = date('Y-m-d H:i:s');
    
    if ($vehicle_number == ''  or $name == '' or $model == ''  or $vtype == '' or $capacity == '' or $regdt == ''  or $ins_exp == '' or $fc_exp == '' or $driver_mobile=='') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('vehicle_number' => $vehicle_number);
            $sql = 'SELECT * FROM trans_bus_details WHERE vehicle_number=:vehicle_number';
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
               

                $attachment = '';
                //Move attached image  file, if there is one
                if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0){
                    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
                    $filename = $_FILES["file"]["name"];
                    $filetype = $_FILES["file"]["type"];
                    $filesize = $_FILES["file"]["size"];
                   
                    // Verify file extension
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");


                    $filename = time() . '_' .  $_FILES["file"]["name"];
                    $fileTarget = "uploads/" . $filename;	
                     // Verify MYME type of the file
                        if(in_array($filetype, $allowed)){
                            // Check whether file exists before uploading it
                           
                                move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/" . $filename);
                              //  echo "Your file was uploaded successfully.";
                           
                        } else{
                           // echo "Error: There was a problem uploading your file. Please try again."; 
                        }
                    } else{
                       // echo "Error: " . $_FILES["file"]["error"];
                    }


                  //  echo  "file:".$fileTarget ;

              //  echo "<img src='".$fileTarget."'/>";

  //`trans_bus_details`(`id`, `vehicle_number`, `name`, `model`, `vtype`, `capacity`, `register_date`, `insurance_exp`, `fc_expiry`, `driver_name`, `driver_mobile`, `coordinator_name`, `coordinator_mobile`, `photo`, `cdt`, `udt`)

                $data = array('vehicle_number' => $vehicle_number, 'name' => $name, 'model' => $model, 'vtype' => $vtype, 'capacity' => $capacity, 'register_date' => $register_date, 'insurance_exp' => $insurance_exp, 'fc_expiry' => $fc_expiry, 'driver_name' => $driver_name, 'driver_mobile' => $driver_mobile, 'coordinator_name' => $coordinator_name, 'coordinator_mobile' => $coordinator_mobile, 'photo' => $fileTarget);
                
                $sql = 'INSERT INTO trans_bus_details SET vehicle_number=:vehicle_number, name=:name, model=:model, vtype=:vtype, capacity=:capacity, register_date=:register_date, insurance_exp=:insurance_exp, fc_expiry=:fc_expiry, driver_name=:driver_name, driver_mobile=:driver_mobile, coordinator_name=:coordinator_name, coordinator_mobile=:coordinator_mobile, photo=:photo';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                $strId = $connection2->lastInsertID();

               
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';
            } catch (PDOException $e) {
                $URL .= '&return=error9';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0";
            header("Location: {$URL}");
        }
    }
}
