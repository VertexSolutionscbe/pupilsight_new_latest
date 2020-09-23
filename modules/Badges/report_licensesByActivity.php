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
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Badges/report_licensesByActivity.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
         ->add(__m('Licenses By Activity'));

    echo '<h2>';
    echo __m('Choose Activity');
    echo '</h2>';

    $pupilsightActivityID = $_GET['pupilsightActivityID'] ?? null;
    $badgesBadgeID = $_GET['badgesBadgeID'] ?? null;

    $form = Form::create('search', $pupilsight->session->get('absoluteURL','').'/index.php', 'GET');
    $form->setTitle(__('Choose Class'));
    $form->addClass('noIntBorder');

    $form->addHiddenValue('q', '/modules/'.$pupilsight->session->get('module').'/report_licensesByActivity.php');

    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
    $sql = 'SELECT pupilsightActivityID AS value, name FROM pupilsightActivity WHERE pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND active=\'Y\' ORDER BY name';
    $row = $form->addRow();
        $row->addLabel('pupilsightActivityID', __('Activity'));
        $row->addSelect('pupilsightActivityID')->fromQuery($pdo, $sql, $data)->selected($pupilsightActivityID)->required()->placeholder();

    $data = array();
    $sql = 'SELECT badgesBadgeID AS value, name FROM badgesBadge WHERE active=\'Y\' AND license=\'Y\' ORDER BY name';
    $row = $form->addRow();
        $row->addLabel('badgesBadgeID', __m('License'));
        $row->addSelect('badgesBadgeID')->fromQuery($pdo, $sql, $data)->selected($badgesBadgeID)->required()->placeholder();

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if ($pupilsightActivityID != '' && $badgesBadgeID != '') {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        //Check class exists
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT pupilsightActivityID, name FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            echo "<p style='margin-bottom: 0px'><b>".__('Activity').'</b>: '.$row['name'].'</p><br/>';

            //Check badge exists
            try {
                $data = array('badgesBadgeID' => $badgesBadgeID);
                $sql = 'SELECT badgesBadgeID, name FROM badgesBadge WHERE badgesBadgeID=:badgesBadgeID AND active=\'Y\'';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                $row = $result->fetch();
                $badge = $row['name'];

                //Get licenses
                try {
                    $data = array('pupilsightActivityID' => $pupilsightActivityID, 'badgesBadgeID' => $badgesBadgeID);
                    $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, badgesBadgeID
                        FROM pupilsightPerson
                            JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightActivityStudent.status='Accepted')
                            LEFT JOIN badgesBadgeStudent ON (badgesBadgeStudent.pupilsightPersonID=pupilsightActivityStudent.pupilsightPersonID AND badgesBadgeStudent.badgesBadgeID=:badgesBadgeID)
                        WHERE
                            pupilsightPerson.status='Full'
                            AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."')
                            AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."')
                            AND pupilsightActivityStudent.pupilsightActivityID=:pupilsightActivityID
                        ORDER BY surname, preferredName";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                echo "<table class='mini' cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo __('Number');
                echo '</th>';
                echo '<th>';
                echo __('Student');
                echo '</th>';
                echo '<th>';
                echo $badge;
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
                    echo $count;
                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$row['pupilsightPersonID']."'>".formatName('', $row['preferredName'], $row['surname'], 'Student', true).'</a><br/>';
                    echo '</td>';
                    echo '<td>';
                    if (!empty($row['badgesBadgeID'])) {
                        echo "<img title='" . __('Yes'). "' src='./themes/Default/img/iconTick.png' />";
                    }
                    else {
                        echo "<img title='" . __('No'). "' src='./themes/Default/img/iconCross.png' />";
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                if ($count == 0) {
                    echo "<tr class=$rowNum>";
                    echo '<td colspan=3>';
                    echo __('There are no records to display.');
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
    }
}
?>
