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

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_write.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Write References'));

    $pupilsightSchoolYearID = null;
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '') {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }
    if (isset($_GET['pupilsightSchoolYearID'])) {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }
        if ($result->rowcount() != 1) {
            $page->addError(__('The specified year does not exist.'));
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    if ($pupilsightSchoolYearID != '') {
        echo "<h2 class='top'>";
        echo $pupilsightSchoolYearName;
        echo '</h2>';

        echo "<div class='linkTop'>";
            //Print year picker
            if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_write.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>Previous Year</a> ";
            } else {
                echo 'Previous Year ';
            }
            echo ' | ';
            if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_write.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>Next Year</a> ";
            } else {
                echo 'Next Year ';
            }
        echo '</div>';


        echo '<p>';
        echo 'The table below shows all references for which your input is required in the selected school year.';
        echo '<p>';

        //Set pagination variable
        $pagination = null;
        if (isset($_GET['page'])) {
            $pagination = $_GET['page'];
        }
        if ((!is_numeric($pagination)) or $pagination < 1) {
            $pagination = 1;
        }

        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "SELECT higherEducationReference.timestamp, higherEducationReference.type AS typeReference, higherEducationReferenceComponent.*, surname, preferredName FROM higherEducationReferenceComponent JOIN higherEducationReference ON (higherEducationReferenceComponent.higherEducationReferenceID=higherEducationReference.higherEducationReferenceID) JOIN pupilsightPerson ON (higherEducationReference.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE higherEducationReference.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND higherEducationReference.status='In Progress' AND higherEducationReferenceComponent.pupilsightPersonID=:pupilsightPersonID ORDER BY higherEducationReferenceComponent.status, timestamp DESC";
            $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($pagination - 1) * $_SESSION[$guid]['pagination']);
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='warning'>";
                echo $e->getMessage();
            echo '</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='success'>";
            echo 'There are no reference requests at current.';
            echo '</div>';
        } else {
            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $pagination, $_SESSION[$guid]['pagination'], 'top', 'pupilsightSchoolYearID=$pupilsightSchoolYearID');
            }

            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo 'Name<br/>';
            echo "<span style='font-size: 75%; font-style: italic'>Date</span>";
            echo '</th>';
            echo '<th colspan=2>';
            echo 'Your Contribution';
            echo '</th>';
            echo '<th>';
            echo 'Type';
            echo '</th>';
            echo '<th>';
            echo 'Perspective';
            echo '</th>';
            echo '<th>';
            echo 'Actions';
            echo '</th>';
            echo '</tr>';

            $count = 0;
            $rowNum = 'odd';
            try {
                $resultPage = $connection2->prepare($sqlPage);
                $resultPage->execute($data);
            } catch (PDOException $e) {
                echo "<div class='warning'>";
                    echo $e->getMessage();
                echo '</div>';
            }
            while ($row = $resultPage->fetch()) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                echo "<tr class=$rowNum>";
                echo '<td>';
                echo formatName('', $row['preferredName'], $row['surname'], 'Student', true).'<br/>';
                echo "<span style='font-size: 75%; font-style: italic'>".dateConvertBack($guid, substr($row['timestamp'], 0, 10)).'</span>';
                echo '</td>';
                echo "<td style='width: 25px'>";
                if ($row['status'] == 'Cancelled') {
                    echo "<img style='margin-right: 3px; float: left' title='Cancelled' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/> ";
                } elseif ($row['status'] == 'Complete') {
                    echo "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/> ";
                } else {
                    echo "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick_light.png'/> ";
                }
                echo '</td>';
                echo '<td>';
                echo '<b>'.$row['status'].'</b>';
                if (isset($row['statusNotes'])) {
                    echo "<br/><span style='font-size: 75%; font-style: italic'>".$row['statusNotes'].'</span>';
                }
                echo '</td>';
                echo '<td>';
                echo $row['typeReference'];
                echo '</td>';
                echo '<td>';
                echo $row['type'].'<br/>';
                if ($row['title'] != '') {
                    echo "<span style='font-size: 75%; font-style: italic'>".$row['title'].'</span>';
                }
                echo '</td>';
                echo '<td>';
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_write_edit.php&higherEducationReferenceComponentID='.$row['higherEducationReferenceComponentID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID'><img title='Edit' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';

            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $pagination, $_SESSION[$guid]['pagination'], 'bottom', "pupilsightSchoolYearID=$pupilsightSchoolYearID");
            }
        }
    }
}
