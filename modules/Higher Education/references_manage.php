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

use Pupilsight\Forms\Form;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffHigherEducationRole($_SESSION[$guid]['pupilsightPersonID'], $connection2);
    if ($role == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the Higher Education programme.'));
    } else {
        if ($role != 'Coordinator') {
            //Acess denied
            $page->addError(__('You do not have permission to access this page.'));
        } else {
            //Proceed!
            $page->breadcrumbs->add(__('Manage References'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

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

            $search = '';
            if (isset($_GET['search'])) {
                $search = $_GET['search'];
            }

            if ($pupilsightSchoolYearID != '') {
                echo "<h2 class='top'>";
                echo $pupilsightSchoolYearName;
                echo '</h2>';

                echo "<div class='linkTop'>";
                    //Print year picker
                    if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>Previous Year</a> ";
                    } else {
                        echo 'Previous Year ';
                    }
                    echo ' | ';
                    if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>Next Year</a> ";
                    } else {
                        echo 'Next Year ';
                    }
                echo '</div>';

                echo "<h3 class='top'>";
                echo __('Search');
                echo '</h3>';
                
                $form = Form::create('search', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
                $form->setClass('noIntBorder fullWidth');

                $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/references_manage.php');

                $row = $form->addRow();
                    $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
                    $row->addTextField('search')->setValue($search);

                $row = $form->addRow();
                    $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

                echo $form->getOutput();

                echo "<h3 class='top'>";
                echo __('View');
                echo '</h3>';
                echo '<p>';
                echo 'The table below shows all references request in the selected school year. Use the "Previous Year" and "Next Year" links to navigate to other years.';
                echo '<p>';

                //Set pagination variable
                $pagination = '';
                if (isset($_GET['page'])) {
                    $pagination = $_GET['page'];
                }
                if ((!is_numeric($pagination)) or $pagination < 1) {
                    $pagination = 1;
                }

                try {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                    $sql = "SELECT higherEducationReference.*, surname, preferredName, title FROM higherEducationReference JOIN pupilsightPerson ON (higherEducationReference.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE higherEducationReference.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' ORDER BY status, timestamp";
                    if ($search != '') {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'search1' => "%$search%", 'search2' => "%$search%", 'search3' => "%$search%");
                        $sql = "SELECT higherEducationReference.*, surname, preferredName, title FROM higherEducationReference JOIN pupilsightPerson ON (higherEducationReference.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE higherEducationReference.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND (preferredName LIKE :search1 OR surname LIKE :search2 OR username LIKE :search3) ORDER BY status, timestamp";
                    }
                    $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($pagination - 1) * $_SESSION[$guid]['pagination']);
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                }

                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/references_manage_addMulti.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Add Multiple Records')."<img title='".__('Add Multiple Records')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new_multi.png'/></a>";
                echo '</div>';

                if ($result->rowCount() < 1) {
                    echo "<div class='warning'>";
                        echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                        printPagination($guid, $result->rowCount(), $pagination, $_SESSION[$guid]['pagination'], 'top', "pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");
                    }

                    echo "<table cellspacing='0' style='width: 100%'>";
                    echo "<tr class='head'>";
                    echo '<th>';
                    echo 'Name<br/>';
                    echo "<span style='font-size: 75%; font-style: italic'>Date</span>";
                    echo '</th>';
                    echo '<th colspan=2>';
                    echo 'Status';
                    echo '</th>';
                    echo '<th>';
                    echo 'Type';
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
                        if ($row['statusNotes'] != '') {
                            echo "<br/><span style='font-size: 75%; font-style: italic'>".$row['statusNotes'].'</span>';
                        }
                        echo '</td>';
                        echo '<td>';
                        echo $row['type'];
                        echo '</td>';
                        echo '<td>';
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage_edit.php&higherEducationReferenceID='.$row['higherEducationReferenceID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID'><img title='Edit' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                        echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage_delete.php&higherEducationReferenceID='.$row['higherEducationReferenceID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID&width=650&height=135'><img title='Delete' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a>";
                        echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage_edit_print.php&higherEducationReferenceID='.$row['higherEducationReferenceID']."'><img title='Print' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';

                    if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                        printPagination($guid, $result->rowCount(), $pagination, $_SESSION[$guid]['pagination'], 'bottom', "pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");
                    }
                }
            }
        }
    }
}
?>
