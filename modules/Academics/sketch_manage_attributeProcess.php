<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    //Acess denied
    echo "<div class='error'>"; 
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    //die();
    $attribute_name = $_POST['attribute_name'];
    $attribute_category = $_POST['attribute_category'];
    $attribute_type = $_POST['attribute_type'];
    $sketch_id = $_POST['sketch_id'];

    $URLNEW = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/sketch_manage_attribute.php&id='.$sketch_id.'';

    try {
        $data = array('attribute_name' => $attribute_name, 'sketch_id' => $sketch_id);
        $sql = 'SELECT * FROM examinationReportTemplateAttributes WHERE attribute_name=:attribute_name AND sketch_id=:sketch_id ';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URLNEW .= '&return=error2';
        header("Location: {$URLNEW}");
        exit();
    }

    if ($result->rowCount() > 0) {
        $URLNEW .= '&return=error3';
        header("Location: {$URLNEW}");
    } else {

        if(!empty($attribute_name) && !empty($attribute_category) && !empty($attribute_type)){
            $data1 = array('sketch_id' => $sketch_id, 'attribute_name' => $attribute_name, 'attribute_category' => $attribute_category, 'attribute_type' => $attribute_type);
            $sql1 = "INSERT INTO examinationReportTemplateAttributes SET sketch_id=:sketch_id, attribute_name=:attribute_name, attribute_category=:attribute_category, attribute_type=:attribute_type";
            $result = $connection2->prepare($sql1);
            $result->execute($data1);
            $AI = $connection2->lastInsertID();

            $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/sketch_manage_attribute_plugin.php';

            $URL .= "&id=$AI";
        }

        header("Location: {$URL}");
    }      
    

}