<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/resources_view_full.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //Get class variable
    $pupilsightResourceID = $_GET['pupilsightResourceID'];
    if ($pupilsightResourceID == '') {
        echo "<div class='alert alert-warning'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    }
    //Check existence of and access to this class.
    else {
        try {
            $data = array('pupilsightResourceID' => $pupilsightResourceID);
            $sql = 'SELECT * FROM pupilsightResource WHERE pupilsightResourceID=:pupilsightResourceID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-warning'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();

            echo '<h1>';
            echo $row['name'];
            echo '</h1>';

            echo $row['content'];
        }
    }
}
