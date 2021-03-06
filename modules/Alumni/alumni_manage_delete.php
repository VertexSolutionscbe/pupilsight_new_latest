<?php
/*
Pupilsight, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Alumni/alumni_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
      ->add(__('Manage Alumni'), 'alumni_manage.php')
      ->add(__('Delete'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $alumniAlumnusID = $_GET['alumniAlumnusID'];
    if ($alumniAlumnusID == '') { echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('alumniAlumnusID' => $alumniAlumnusID);
            $sql = 'SELECT * FROM alumniAlumnus WHERE alumniAlumnusID=:alumniAlumnusID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            //Let's go!
            $row = $result->fetch();

            if ($_GET['graduatingYear'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Alumni/alumni_manage.php&graduatingYear='.$_GET['graduatingYear']."'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/alumni_manage_deleteProcess.php?alumniAlumnusID=$alumniAlumnusID&graduatingYear=".$_GET['graduatingYear']);
            echo $form->getOutput();
        }
    }
}
