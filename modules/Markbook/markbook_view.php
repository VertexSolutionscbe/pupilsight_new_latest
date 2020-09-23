<?php
/*
Pupilsight, Flexible & Open School System
*/
//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    // Register scripts available to the core, but not included by default
    $page->scripts->add('chart');
    
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    $highestAction2 = getHighestGroupedAction($guid, '/modules/Markbook/markbook_edit.php', $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $enableModifiedAssessment = getSettingByScope($connection2, 'Markbook', 'enableModifiedAssessment');
        $alert = getAlert($guid, $connection2, 002);

        // Define a randomized lock for this script
        define("MARKBOOK_VIEW_LOCK", sha1( $highestAction . $_SESSION[$guid]['pupilsightPersonID'] ) . date('zWy') );

        //VIEW ACCESS TO ALL MARKBOOK DATA
        if ($highestAction == 'View Markbook_allClassesAllData' || $highestAction == 'View Markbook_myClasses') {
            require __DIR__ . '/markbook_view_allClassesAllData.php';
        }
        //VIEW ACCESS TO MY OWN MARKBOOK DATA
        elseif ($highestAction == 'View Markbook_myMarks') {
            require __DIR__ . '/markbook_view_myMarks.php';
        }
        //VIEW ACCESS TO MY CHILDREN'S MARKBOOK DATA
        elseif ($highestAction == 'View Markbook_viewMyChildrensClasses') {
            require __DIR__ . '/markbook_view_viewMyChildrensClasses.php';
        }
    }
}
?>
