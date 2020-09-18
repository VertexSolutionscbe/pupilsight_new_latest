<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_students_new') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Letters Home by Roll Group'));

    try {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightRollGroup.nameShort AS rollGroup, pupilsightFamily.pupilsightFamilyID, pupilsightFamily.name AS familyName
            FROM pupilsightPerson
                JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                LEFT JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                LEFT JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
            WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                AND pupilsightPerson.status='Full'
            ORDER BY rollGroup, surname, preferredName";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }
    if ($result->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        $siblings = array();
        $currentRollGroup = '';
        $lastRollGroup = '';
        $count = 0;
        $countTotal = 0;
        $rowNum = 'odd';
        while ($row = $result->fetch()) {
            $currentRollGroup = $row['rollGroup'];

            //SPLIT INTO ROLL GROUPS
            if ($currentRollGroup != $lastRollGroup) {
                if ($lastRollGroup != '') {
                    echo '</table>';
                }
                echo '<h2>'.$row['rollGroup'].'</h2>';
                $count = 0;
                $rowNum = 'odd';
                echo "<table cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo __('Total Count');
                echo '</th>';
                echo '<th>';
                echo __('Form Count');
                echo '</th>';
                echo '<th>';
                echo __('Student');
                echo '</th>';
                echo '<th>';
                echo __('Younger Siblings');
                echo '</th>';
                echo '<th>';
                echo __('Family');
                echo '</th>';
                echo '<th>';
                echo __('Sibling Count');
                echo '</th>';
                echo '</tr>';
            }
            $lastRollGroup = $row['rollGroup'];

            //PUMP OUT STUDENT DATA
            //Check for older siblings
            $proceed = false;
            try {
                $dataSibling = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightFamilyID' => $row['pupilsightFamilyID']);
                $sqlSibling = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightFamily.name, pupilsightFamily.pupilsightFamilyID
                    FROM pupilsightPerson
                        JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                        JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                        JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
                    WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                        AND pupilsightPerson.status='Full'
                        AND pupilsightFamily.pupilsightFamilyID=:pupilsightFamilyID
                    ORDER BY pupilsightFamily.pupilsightFamilyID, dob";
                $resultSibling = $connection2->prepare($sqlSibling);
                $resultSibling->execute($dataSibling);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultSibling->rowCount() == 1) {
                $proceed = true;
            } else {
                $rowSibling = $resultSibling->fetch();
                if ($rowSibling['pupilsightPersonID'] == $row['pupilsightPersonID']) {
                    $proceed = true;
                }
                else { //Store sibling away for later use
                    $siblings[$rowSibling['pupilsightFamilyID']][$row['pupilsightPersonID']] = Format::name('', $row['preferredName'], $row['surname'], 'Student', true);
                }
            }

            if ($proceed == true) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                echo "<tr class=$rowNum>";
                echo "<td style='width: 20%'>";
                echo $countTotal + 1;
                echo '</td>';
                echo "<td style='width: 20%'>";
                echo $count + 1;
                echo '</td>';
                echo '<td>';
                echo Format::name('', $row['preferredName'], $row['surname'], 'Student', true);
                echo '</td>';
                echo '<td>';
                if (!empty($siblings[$row['pupilsightFamilyID']]) && is_array($siblings[$row['pupilsightFamilyID']])) {
                    foreach ($siblings[$row['pupilsightFamilyID']] AS $sibling) {
                        echo $sibling."</br>";
                    }
                }
                echo '</td>';
                echo '<td>';
                echo $row['familyName'];
                echo '</td>';
                echo "<td style='width: 20%'>";
                echo $resultSibling->rowCount() - 1;
                echo '</td>';
                echo '</tr>';
                ++$count;
                ++$countTotal;
            }
        }
        echo '</table>';
    }
}
