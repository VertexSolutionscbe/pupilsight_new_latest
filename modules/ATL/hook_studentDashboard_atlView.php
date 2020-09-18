<?php
/*
Pupilsight, Flexible & Open School System
*/

global $page;

$returnInt = null;

require_once './modules/ATL/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_view.php') == false) {
    //Acess denied
    $returnInt .= "<div class='error'>";
    $returnInt .= 'You do not have access to this action.';
    $returnInt .= '</div>';
} else {
    // Register scripts available to the core, but not included by default
    $page->scripts->add('chart');
    
    $returnInt .= getATLRecord($guid, $connection2, $_SESSION[$guid]['pupilsightPersonID']);
}

return $returnInt;
