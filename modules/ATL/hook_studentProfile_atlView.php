<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once './modules/ATL/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_view.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    // Register scripts available to the core, but not included by default
    $page->scripts->add('chart');
    
    echo getATLRecord($guid, $connection2, $pupilsightPersonID);
}
