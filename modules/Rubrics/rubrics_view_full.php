<?php
/*
Pupilsight, Flexible & Open School System
*/

//Rubric includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_view_full.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //Check if school year specified
    $pupilsightRubricID = $_GET['pupilsightRubricID'];
    if ($pupilsightRubricID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data3 = array('pupilsightRubricID' => $pupilsightRubricID);
            $sql3 = 'SELECT * FROM pupilsightRubric WHERE pupilsightRubricID=:pupilsightRubricID';
            $result3 = $connection2->prepare($sql3);
            $result3->execute($data3);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result3->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            //Let's go!
            $row3 = $result3->fetch();

            echo "<h2 style='margin-bottom: 10px;'>";
            echo $row3['name'].'<br/>';
            echo '</h2>';

            echo rubricView($guid, $connection2, $pupilsightRubricID, false);
        }
    }
}
