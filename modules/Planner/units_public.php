<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// common variables
$makeUnitsPublic = getSettingByScope($connection2, 'Planner', 'makeUnitsPublic');
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
$pupilsightUnitID = $_GET['pupilsightUnitID'] ?? '';

$page->breadcrumbs->add(__('Learn With Us'));

if ($makeUnitsPublic != 'Y') {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    if ($pupilsightSchoolYearID == '') {
        try {
            $data = array();
            $sql = "SELECT * FROM pupilsightSchoolYear WHERE status='Current'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    echo '<h2>';
    echo $pupilsightSchoolYearName;
    echo '</h2>';

    echo "<div class='linkTop'>";
        //Print year picker
        if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/units_public.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
        } else {
            echo __('Previous Year').' ';
        }
		echo ' | ';
		if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
			echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/units_public.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
		} else {
			echo __('Next Year').' ';
		}
    echo '</div>';

    //Fetch units
    try {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightUnitID, pupilsightUnit.pupilsightCourseID, nameShort, pupilsightUnit.name, pupilsightUnit.description, pupilsightCourse.name AS course FROM pupilsightUnit JOIN pupilsightCourse ON pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND sharedPublic='Y' ORDER BY course, name";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    echo "<div class='linkTop'></div>";

    if ($result->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo "<th style='width: 150px'>";
        echo __('Course');
        echo '</th>';
        echo "<th style='width: 150px'>";
        echo __('Name');
        echo '</th>';
        echo "<th style='width: 450px'>";
        echo __('Description');
        echo '</th>';
        echo "<th style='width: 50px'>";
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
            echo $row['course'];
            echo '</td>';
            echo '<td>';
            echo $row['name'];
            echo '</td>';
            echo "<td style='max-width: 270px'>";
            echo $row['description'];
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/units_public_view.php&pupilsightUnitID='.$row['pupilsightUnitID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID&sidebar=false'><img title='".__('View Details')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a>";
            echo '</td>';
            echo '</tr>';

            ++$count;
        }
        echo '</table>';
    }
}
