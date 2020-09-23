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
    $ertc_id = $_POST['ertc_id'];
    $sketch_id = $_POST['sketch_id'];

    if(!empty($ertc_id)){
        $pos = 1;
        foreach($ertc_id as $ertcid){
            $sql = 'SELECT COUNT(id) as kount, id FROM examinationReportTemplateAttributes WHERE sketch_id = '.$sketch_id.' AND ertc_id = '.$ertcid.' ';
            $res = $connection2->query($sql);
            $chkattr = $res->fetch();
            if($chkattr['kount'] == '1'){
                $data2 = array('pos' => $pos, 'id' => $chkattr['id']);
                $sql2 = "UPDATE examinationReportTemplateAttributes SET pos=:pos WHERE id=:id";
                $result2 = $connection2->prepare($sql2);
                $result2->execute($data2);
            } else {
                $data1 = array('sketch_id' => $sketch_id, 'ertc_id' => $ertcid, 'pos' => $pos);
                $sql1 = "INSERT INTO examinationReportTemplateAttributes SET sketch_id=:sketch_id, ertc_id=:ertc_id, pos=:pos";
                $result = $connection2->prepare($sql1);
                $result->execute($data1);
            }

           
            $pos++;
        }
        $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/sketch_manage_attribute_plugin.php';
        header("Location: {$URL}");  
    }

}