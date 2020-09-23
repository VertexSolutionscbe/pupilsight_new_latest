<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$id = $session->get('changeRoute_id');

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Transport/change_transport_route.php') != false) {
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
    // $id = $_GET['id'];
    // print_r($id);die();
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = $id;
            $sql = 'SELECT * FROM trans_route_assign WHERE id=:id';
            $result = $connection2->prepare($sql);
            // print_r($result);die();
            $result->execute($data);
     
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

print_r($result->rowCount());die();
        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/change_transport_route_edit.php?id=$id", true);
            echo $form->getOutput();
        }
    }
}
