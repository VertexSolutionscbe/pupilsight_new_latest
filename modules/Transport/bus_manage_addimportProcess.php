<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . '/bus_manage.php';
if (isActionAccessible($guid, $connection2, '/modules/Transport/bus_manage_addimportProcess.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    if (($_FILES['file']['type'] != 'text/csv') and ($_FILES['file']['type'] != 'text/comma-separated-values') and ($_FILES['file']['type'] != 'text/x-comma-separated-values') and ($_FILES['file']['type'] != 'application/vnd.ms-excel') and ($_FILES['file']['type'] != 'application/csv')) {
        $URL .= "&return=error2";
        header("Location: {$URL}");
    } else {
        $proceed = true;

        $importFail = false;
        $csvFile = $_FILES['file']['tmp_name'];
        $handle = fopen($csvFile, 'r');
        while (($data = fgetcsv($handle, 100000)) !== false) {
            try {
                if (!empty($data[0]) || !empty($data[1]) || !empty($data[2]) || !empty($data[3]) || !empty($data[4]) || !empty($data[5]) || !empty($data[6]) || !empty($data[7]) || !empty($data[8]) || !empty($data[9]) || !empty($data[10]) || !empty($data[11])) {
                    $data = array('vehicle_number' => $data[0], 'name' => $data[1], 'model' => $data[2], 'vtype' => $data[3], 'capacity' => $data[4], 'register_date' => $data[5], 'insurance_exp' => $data[6], 'fc_expiry' => $data[7], 'driver_name' => $data[8], 'driver_mobile' => $data[9], 'coordinator_name' => $data[10], 'coordinator_mobile' => $data[11]);
                    $sql = 'INSERT INTO trans_bus_details SET vehicle_number=:vehicle_number, name=:name, model=:model, vtype=:vtype, capacity=:capacity, register_date=:register_date, insurance_exp=:insurance_exp, fc_expiry=:fc_expiry, driver_name=:driver_name, driver_mobile=:driver_mobile, coordinator_name=:coordinator_name, coordinator_mobile=:coordinator_mobile';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                }
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                //$importFail = true;
                $proceed = false;
            }
        }
        fclose($handle);
        if ($importFail == true) {
            $URL .= "&return=error2";
            header("Location: {$URL}");
        } elseif ($importFail == false) {
            $URL .= "&return=success0";
            header("Location: {$URL}");
        }
    }
}
