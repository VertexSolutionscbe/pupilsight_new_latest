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
use Pupilsight\Forms\DatabaseFormFactory;
//Module includes
include './modules/'.$pupilsight->session->get('module').'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Badges/badges_grant.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $page->breadcrumbs->add(__('Grant Badges'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? $pupilsight->session->get('pupilsightSchoolYearID');
    if (isset($_GET['pupilsightSchoolYearID'])) {

    }
    if ($pupilsightSchoolYearID == $pupilsight->session->get('pupilsightSchoolYearID')) {
        $pupilsightSchoolYearName = $pupilsight->session->get('pupilsightSchoolYearName');
    }

    if ($pupilsightSchoolYearID != $pupilsight->session->get('pupilsightSchoolYearID')) {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        if ($result->rowcount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    if ($pupilsightSchoolYearID != '') {
        echo '<h2>';
        echo $pupilsightSchoolYearName;
        echo '</h2>';

        echo "<div class='linkTop'>";
        //Print year picker
        if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$pupilsight->session->get('absoluteURL').'/index.php?q=/modules/'.$pupilsight->session->get('module').'/badges_grant.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
        } else {
            echo __('Previous Year').' ';
        }
        echo ' | ';
        if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$pupilsight->session->get('absoluteURL').'/index.php?q=/modules/'.$pupilsight->session->get('module').'/badges_grant.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
        } else {
            echo __('Next Year').' ';
        }
        echo '</div>';

        $pupilsightPersonID2 = $_GET['pupilsightPersonID2'] ?? '';
        $badgesBadgeID2 = $_GET['badgesBadgeID2'] ?? '';
        $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'] ?? '';
        $type = $_GET['type'] ?? '';

        $form = Form::create('grantbadges',$pupilsight->session->get('absoluteURL').'/index.php?q=/modules/Badges/badges_grant.php','GET');
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addClass('noIntBorder');

        $form->setTitle(__('Filter'));
        $form->addRow();

        $row = $form->addRow();
        $row->addLabel('pupilsightPersonID2',__('User'));
        $row->addSelectStudent('pupilsightPersonID2', $pupilsight->session->get('pupilsightSchoolYearID'))->selected($pupilsightPersonID2)->placeholder();

        $sql = "SELECT badgesBadgeID as value, name, category FROM badgesBadge WHERE active='Y' ORDER BY category, name";
        $row = $form->addRow();
        $row->addLabel('badgesBadgeID2',__('Badges'));
        $row->addSelect('badgesBadgeID2')->fromQuery($pdo, $sql, [], 'category')->selected($badgesBadgeID2)->placeholder();

        $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session);

        $form->addHiddenValue('q',$_GET['q']);
        $form->addRow();
        echo $form->getOutput();
        ?>


        <?php


        echo '<h3>';
        echo __('Badges');
        echo '</h3>';
        //Set pagination variable
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        if ((!is_numeric($page)) or $page < 1) {
            $page = 1;
        }

        //Get pupilsightHookID for link to Student Profile
        $pupilsightHookID = null;
        try {
            $dataHook = array();
            $sqlHook = "SELECT pupilsightHookID FROM pupilsightHook WHERE name='Badges' AND type='Student Profile' AND pupilsightModuleID=(SELECT pupilsightModuleID FROM pupilsightModule WHERE name='Badges')";
            $resultHook = $connection2->prepare($sqlHook);
            $resultHook->execute($dataHook);
        } catch (PDOException $e) {

        }
        if ($resultHook->rowCount() == 1) {
            $rowHook = $resultHook->fetch();
            $pupilsightHookID = $rowHook['pupilsightHookID'];
        }

        //Search with filters applied
        try {
            $data = array();
            $sqlWhere = 'AND ';
            if ($pupilsightPersonID2 != '') {
                $data['pupilsightPersonID'] = $pupilsightPersonID2;
                $sqlWhere .= 'badgesBadgeStudent.pupilsightPersonID=:pupilsightPersonID AND ';
            }
            if ($badgesBadgeID2 != '') {
                $data['badgesBadgeID2'] = $badgesBadgeID2;
                $sqlWhere .= 'badgesBadge.badgesBadgeID=:badgesBadgeID2 AND ';
            }
            $sqlWhere = $sqlWhere == 'AND ' ? '' : substr($sqlWhere, 0, -5);

            $data['pupilsightSchoolYearID2'] = $pupilsightSchoolYearID;
            $sql = "SELECT badgesBadge.*, badgesBadgeStudent.*, surname, preferredName FROM badgesBadge JOIN badgesBadgeStudent ON (badgesBadgeStudent.badgesBadgeID=badgesBadge.badgesBadgeID) JOIN pupilsightPerson ON (badgesBadgeStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE badgesBadgeStudent.pupilsightSchoolYearID=:pupilsightSchoolYearID2 $sqlWhere ORDER BY timestamp DESC";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        $sqlPage = $sql.' LIMIT '.$pupilsight->session->get('pagination').' OFFSET '.(($page - 1) * $pupilsight->session->get('pagination'));

        echo "<div class='linkTop'>";
        // echo "<a href='".$pupilsight->session->get('absoluteURL').'/index.php?q=/modules/'.$pupilsight->session->get('module')."/badges_grant_add.php&pupilsightPersonID2=$pupilsightPersonID2&badgesBadgeID2=$badgesBadgeID2&pupilsightSchoolYearID=$pupilsightSchoolYearID'>".__('Add')."<img style='margin: 0 0 -4px 5px' title='".__('Add')."' src='./themes/".$pupilsight->session->get('pupilsightThemeName')."/img/page_new.png'/></a>";

        echo "<div style='height:50px;'><div class='float-right mb-2'>";  
        echo "&nbsp;&nbsp;<a href='".$pupilsight->session->get('absoluteURL').'/index.php?q=/modules/'.$pupilsight->session->get('module')."/badges_grant_add.php&pupilsightPersonID2=$pupilsightPersonID2&badgesBadgeID2=$badgesBadgeID2&pupilsightSchoolYearID=$pupilsightSchoolYearID' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";
        echo '</div>';

        if ($result->rowCount() < 1) {
            echo "<div class='error'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            if ($result->rowCount() > $pupilsight->session->get('pagination')) {
                printPagination($guid, $result->rowCount(), $page, $pupilsight->session->get('pagination'), 'top', "pupilsightPersonID2=$pupilsightPersonID2&badgesBadgeID2=$badgesBadgeID2&pupilsightSchoolYearID=$pupilsightSchoolYearID");
            }

            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo "<th style='width: 180px'>";
            echo __('Badges');
            echo '</th>';
            echo '<th>';
            echo __('Student');
            echo '</th>';
            echo '<th>';
            echo __('Date');
            echo '</th>';
            echo "<th style='min-width: 70px'>";
            echo __('Actions');
            echo '</th>';
            echo '</tr>';

            $count = 0;
            $rowNum = 'odd';
            try {
                $resultPage = $connection2->prepare($sqlPage);
                $resultPage->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            while ($row = $resultPage->fetch()) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                //COLOR ROW BY STATUS!
                echo "<tr class=$rowNum>";
                echo "<td style='font-weight: bold; text-align: center'>";
                if ($row['logo'] != '') {
                    echo "<img class='user' style='margin-bottom: 10px; max-width: 150px' src='".$pupilsight->session->get('absoluteURL').'/'.$row['logo']."'/>";
                } else {
                    echo "<img class='user' style='margin-bottom: 10px; max-width: 150px' src='".$pupilsight->session->get('absoluteURL').'/themes/'.$pupilsight->session->get('pupilsightThemeName')."/img/anonymous_240_square.jpg'/>";
                }
                echo $row['name'];
                echo '</td>';
                echo '<td>';
                echo "<div style='padding: 2px 0px'><b><a href='index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=".$row['pupilsightPersonID']."&hook=Badges&module=Badges&action=View Badges_all&pupilsightHookID=$pupilsightHookID&search=&allStudents=&sort=surname, preferredName'>".formatName('', $row['preferredName'], $row['surname'], 'Student', true).'</a><br/></div>';
                echo '</td>';
                echo '<td>';
                echo dateConvertBack($guid, $row['date']).'<br/>';
                echo '</td>';
                echo '<td>';
                echo "<a class='thickbox' href='".$pupilsight->session->get('absoluteURL').'/fullscreen.php?q=/modules/'.$pupilsight->session->get('module').'/badges_grant_delete.php&badgesBadgeStudentID='.$row['badgesBadgeStudentID']."&pupilsightPersonID2=$pupilsightPersonID2&badgesBadgeID2=$badgesBadgeID2&pupilsightSchoolYearID=$pupilsightSchoolYearID&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$pupilsight->session->get('pupilsightThemeName')."/img/garbage.png'/></a> ";
                echo "<script type='text/javascript'>";
                echo '$(document).ready(function(){';
                echo "\$(\".comment-$count\").hide();";
                echo "\$(\".show_hide-$count\").fadeIn(1000);";
                echo "\$(\".show_hide-$count\").click(function(){";
                echo "\$(\".comment-$count\").fadeToggle(1000);";
                echo '});';
                echo '});';
                echo '</script>';
                if ($row['comment'] != '') {
                    echo "<a title='".__('View Description')."' class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$pupilsight->session->get('absoluteURL')."/themes/Default/img/page_down.png' alt='".__('Show Comment')."' onclick='return false;' /></a>";
                }
                echo '</td>';
                echo '</tr>';
                if ($row['comment'] != '') {
                    echo "<tr class='comment-$count' id='comment-$count'>";
                    echo '<td colspan=4>';
                    if ($row['comment'] != '') {
                        echo '<b>'.__('Comment').'</b><br/>';
                        echo nl2brr($row['comment']).'<br/><br/>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';

            if ($result->rowCount() > $pupilsight->session->get('pagination')) {
                printPagination($guid, $result->rowCount(), $page, $pupilsight->session->get('pagination'), 'bottom', "pupilsightPersonID2=$pupilsightPersonID2&badgesBadgeID2=$badgesBadgeID2&pupilsightSchoolYearID=$pupilsightSchoolYearID");
            }
        }
    }
}
?>
