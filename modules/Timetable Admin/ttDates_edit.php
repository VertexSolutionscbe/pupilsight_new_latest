<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttDates_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $page->breadcrumbs
        ->add(__('Tie Days to Dates'), 'ttDates.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Edit Days in Date'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    $dateStamp = $_GET['dateStamp'];
    if ($pupilsightSchoolYearID == '' or $dateStamp == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('date' => date('Y-m-d', $dateStamp));
            $sql = 'SELECT pupilsightTTDay.pupilsightTTDayID, pupilsightTTDay.name AS dayName, pupilsightTT.name AS ttName FROM pupilsightTTDayDate JOIN pupilsightTTDay ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTT ON (pupilsightTTDay.pupilsightTTID=pupilsightTT.pupilsightTTID) WHERE date=:date';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/ttDates_edit_add.php&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID']."&dateStamp=$dateStamp'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
        echo '</div>';

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo __('Timetable');
            echo '</th>';
            echo '<th>';
            echo __('Day');
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

                //COLOR ROW BY STATUS!
                echo "<tr class=$rowNum>";
                echo '<td>';
                echo $row['ttName'];
                echo '</td>';
                echo '<td>';
                echo $row['dayName'];
                echo '</td>';
                echo '<td>';
                echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/ttDates_edit_delete.php&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID']."&dateStamp=$dateStamp&pupilsightTTDayID=".$row['pupilsightTTDayID']."&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
                echo '</td>';
                echo '</tr>';

                ++$count;
            }
            echo '</table>';
        }
    }
}
