<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/sketch_manage_attribute.php&id='.$_POST['sketch_id'];

if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    //Acess denied
    echo "<div class='error'>"; 
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $erta_id = $_POST['erta_id'];
    $plugin_id = $_POST['plugin_id'];
    $plugin_val = $_POST['plugin_val'];

    if(!empty($plugin_id) && !empty($erta_id)){

        $data = array('erta_id' => $erta_id);
        $sqldel = 'DELETE FROM examinationReportTemplatePluginAttributeMapping WHERE erta_id=:erta_id';
        $resultdel = $connection2->prepare($sqldel);
        $resultdel->execute($data);

        foreach($plugin_id as $k => $pid){
            $pval = $plugin_val[$pid];
            

            $data1 = array('plugin_id' => $pid, 'erta_id' => $erta_id, 'plugin_val' => $pval);
            $sql1 = "INSERT INTO examinationReportTemplatePluginAttributeMapping SET plugin_id=:plugin_id, erta_id=:erta_id, plugin_val=:plugin_val";
            $result = $connection2->prepare($sql1);
            $result->execute($data1);
        }
        //header("Location: {$URL}");  
    }

}