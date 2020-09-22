<?php
/*
Pupilsight, Flexible & Open School System
*/

$page->breadcrumbs->add(__('Manage Canned Responses'));

if (isActionAccessible($guid, $connection2, '/modules/Messenger/cannedResponse_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    try {
        $data = array();
        $sql = 'SELECT * FROM pupilsightMessengerCannedResponse ORDER BY subject';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    // echo "<div class='linkTop'>";
    // echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/cannedResponse_manage_add.php'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
    // echo '</div>';
    echo "<div style='height:50px;'><div class='float-right mb-2'><a  href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/cannedResponse_manage_add.php' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  

    if ($result->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Subject');
        echo '</th>';
        echo "<th style='width:110px'>";
        echo __('Action');
        echo '</th>';
        echo '</tr>';

        $count = 0;
        $rowNum = 'odd';
        while ($row = $result->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo $row['subject'];
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/cannedResponse_manage_edit.php&pupilsightMessengerCannedResponseID='.$row['pupilsightMessengerCannedResponseID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/cannedResponse_manage_delete.php&pupilsightMessengerCannedResponseID='.$row['pupilsightMessengerCannedResponseID']."&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a>";
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
