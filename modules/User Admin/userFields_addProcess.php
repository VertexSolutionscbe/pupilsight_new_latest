<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/userFields_add.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/userFields_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $name = $_POST['name'];
    $active = $_POST['active'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $options = (isset($_POST['options']))? $_POST['options'] : '';
    if ($type == 'varchar') $options = min(max(0, intval($options)), 255);
    if ($type == 'text') $options = max(0, intval($options));
    $required = $_POST['required'];
    
    $roleCategories = (isset($_POST['roleCategories']))? $_POST['roleCategories'] : array();
    $activePersonStudent = in_array('activePersonStudent', $roleCategories);
    $activePersonStaff = in_array('activePersonStaff', $roleCategories);
    $activePersonParent = in_array('activePersonParent', $roleCategories);
    $activePersonOther = in_array('activePersonOther', $roleCategories);
    
    $activeDataUpdater = $_POST['activeDataUpdater'];
    $activeApplicationForm = $_POST['activeApplicationForm'];
    $enablePublicRegistration = getSettingByScope($connection2, 'User Admin', 'enablePublicRegistration');
    $activePublicRegistration = ($enablePublicRegistration == 'Y' && isset($_POST['activePublicRegistration'])) ? $_POST['activePublicRegistration'] : '0' ;

    //Validate Inputs
    if ($name == '' or $active == '' or $description == '' or $type == '' or $required == '' or $activeDataUpdater == '' or $activeApplicationForm == '' or $activePublicRegistration == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Write to database
        try {
            $data = array('name' => $name, 'active' => $active, 'description' => $description, 'type' => $type, 'options' => $options, 'required' => $required, 'activePersonStudent' => $activePersonStudent, 'activePersonStaff' => $activePersonStaff, 'activePersonParent' => $activePersonParent, 'activePersonOther' => $activePersonOther, 'activeDataUpdater' => $activeDataUpdater, 'activeApplicationForm' => $activeApplicationForm, 'activePublicRegistration' => $activePublicRegistration);
            $sql = 'INSERT INTO pupilsightPersonField SET name=:name, active=:active, description=:description, type=:type, options=:options, required=:required, activePersonStudent=:activePersonStudent, activePersonStaff=:activePersonStaff, activePersonParent=:activePersonParent, activePersonOther=:activePersonOther, activeDataUpdater=:activeDataUpdater, activeApplicationForm=:activeApplicationForm, activePublicRegistration=:activePublicRegistration';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

        //Success 0
        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}
