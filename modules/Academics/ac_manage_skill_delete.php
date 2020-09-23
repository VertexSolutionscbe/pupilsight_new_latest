<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_skill_delete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $_GET['id'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM ac_manage_skill WHERE id=:id';           
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $datas = array('skill_id' => $id);
            $sqls = 'SELECT * FROM subjectSkillMapping WHERE skill_id=:skill_id';           
            $results = $connection2->prepare($sqls);
            $results->execute($datas);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        
    

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else if($results->rowCount() > 0){
            echo "<div class='error'>";
            echo __('This Skill has been mapped to a Subject .');
            echo '</div>';

        }else {
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/ac_manage_skillProcess.php?id=$id", true);
            echo $form->getOutput();
        }
    }
}
