<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Crowd Assessment/crowdAssess.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('View All Assessments'));
    
    $sql = getLessons($guid, $connection2);

    try {
        $result = $connection2->prepare($sql[1]);
        $result->execute($sql[0]);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    echo '<p>';
    echo __('The list below shows all lessons in which there is work that you can crowd assess.');
    echo '</p>';

    if ($result->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are currently no lessons to for you to crowd assess.');
        echo '</div>';
    } else {
        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Class');
        echo '</th>';
        echo '<th>';
        echo __('Lesson').'</br>';
        echo "<span style='font-size: 85%; font-style: italic'>".__('Unit').'</span>';
        echo '</th>';
        echo '<th>';
        echo __('Date');
        echo '</th>';
        echo '<th>';
        echo __('Actions');
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
            echo $row['course'].'.'.$row['class'];
            echo '</td>';
            echo '<td>';
            echo '<b>'.$row['name'].'</b><br/>';
            echo "<span style='font-size: 85%; font-style: italic'>";
            if ($row['pupilsightUnitID'] != '') {
                try {
                    $dataUnit = array('pupilsightUnitID' => $row['pupilsightUnitID']);
                    $sqlUnit = 'SELECT * FROM pupilsightUnit WHERE pupilsightUnitID=:pupilsightUnitID';
                    $resultUnit = $connection2->prepare($sqlUnit);
                    $resultUnit->execute($dataUnit);
                } catch (PDOException $e) {
                }
                if ($resultUnit->rowCount() == 1) {
                    $rowUnit = $resultUnit->fetch();
                    echo $rowUnit['name'];
                }
            }
            echo '</span>';
            echo '</td>';
            echo '<td>';
            echo dateConvertBack($guid, $row['date']);
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/crowdAssess_view.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."'><i class='mdi mdi-eye-outline mdi-24px' style='font-size:20px;'></i></a> ";
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
