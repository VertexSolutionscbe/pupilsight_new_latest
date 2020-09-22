<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_student_emergencySummary_print.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $choices = $_SESSION[$guid]['report_student_emergencySummary.php_choices'];

    if (count($choices) > 0) {
        echo '<h2>';
        echo __('Student Emergency Data Summary');
        echo '</h2>';
        echo '<p>';
        echo __('This report prints a summary of emergency data for the selected students. In case of emergency, please try to contact parents first, and if they cannot be reached then contact the listed emergency contacts.');
        echo '</p>';

        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlWhere = ' AND (';
            for ($i = 0; $i < count($choices); ++$i) {
                $data[$choices[$i]] = $choices[$i];
                $sqlWhere = $sqlWhere.'pupilsightPerson.pupilsightPersonID=:'.$choices[$i].' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -4);
            $sqlWhere = $sqlWhere.')';
            $sql = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightRollGroup.name AS name, emergency1Name, emergency1Number1, emergency1Number2, emergency1Relationship, emergency2Name, emergency2Number1, emergency2Number2, emergency2Relationship FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID $sqlWhere ORDER BY surname, preferredName";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        echo "<div class='linkTop'>";
        echo "<a href='javascript:window.print()'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
        echo '</div>';

        echo "<table class='mini' cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Student');
        echo '</th>';
        echo '<th colspan=3>';
        echo __('Last Update');
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

            echo "<tr class=$rowNum>";
            echo '<td>';
            echo Format::name('', $row['preferredName'], $row['surname'], 'Student', true);
            echo '</td>';
            echo '<td colspan=3>';
			//Get details of last personal data form update
			try {
				$dataMedical = array('pupilsightPersonID' => $row['pupilsightPersonID']);
				$sqlMedical = "SELECT * FROM pupilsightPersonUpdate WHERE pupilsightPersonID=:pupilsightPersonID AND status='Complete' ORDER BY timestamp DESC";
				$resultMedical = $connection2->prepare($sqlMedical);
				$resultMedical->execute($dataMedical);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}
            if ($resultMedical->rowCount() > 0) {
                $rowMedical = $resultMedical->fetch();
				//Is last update more recent than 90 days?
				if (substr($rowMedical['timestamp'], 0, 10) > date('Y-m-d', (time() - (90 * 24 * 60 * 60)))) {
					echo dateConvertBack($guid, substr($rowMedical['timestamp'], 0, 10));
				} else {
					echo "<span style='color: #ff0000; font-weight: bold'>".dateConvertBack($guid, substr($rowMedical['timestamp'], 0, 10)).'</span>';
				}
            } else {
                echo "<span style='color: #ff0000; font-weight: bold'>".__('NA').'</span>';
            }
            echo '</td>';
            echo '</tr>';

            echo "<tr class=$rowNum>";
            echo '<td></td>';
            echo "<td style='border-top: 1px solid #aaa; vertical-align: top'>";
            echo '<b><i>'.__('Parents').'</i></b><br/>';
            try {
                $dataFamily = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                $sqlFamily = 'SELECT pupilsightFamilyID FROM pupilsightFamilyChild WHERE pupilsightPersonID=:pupilsightPersonID';
                $resultFamily = $connection2->prepare($sqlFamily);
                $resultFamily->execute($dataFamily);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            while ($rowFamily = $resultFamily->fetch()) {
                try {
                    $dataFamily2 = array('pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                    $sqlFamily2 = 'SELECT pupilsightPerson.* FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightPerson.pupilsightPersonID=pupilsightFamilyAdult.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID ORDER BY contactPriority, surname, preferredName';
                    $resultFamily2 = $connection2->prepare($sqlFamily2);
                    $resultFamily2->execute($dataFamily2);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                while ($rowFamily2 = $resultFamily2->fetch()) {
                    echo '<u>'.Format::name($rowFamily2['title'], $rowFamily2['preferredName'], $rowFamily2['surname'], 'Parent').'</u><br/>';
                    $numbers = 0;
                    for ($i = 1; $i < 5; ++$i) {
                        if ($rowFamily2['phone'.$i] != '') {
                            if ($rowFamily2['phone'.$i.'Type'] != '') {
                                echo '<i>'.$rowFamily2['phone'.$i.'Type'].':</i> ';
                            }
                            if ($rowFamily2['phone'.$i.'CountryCode'] != '') {
                                echo '+'.$rowFamily2['phone'.$i.'CountryCode'].' ';
                            }
                            echo $rowFamily2['phone'.$i].'<br/>';
                            ++$numbers;
                        }
                    }
                    if ($numbers == 0) {
                        echo "<span style='font-size: 85%; font-style: italic'>".__('No number available.').'</span><br/>';
                    }
                }
            }
            echo '</td>';
            echo "<td style='border-top: 1px solid #aaa; vertical-align: top'>";
            echo '<b><i>'.__('Emergency Contact 1').'</i></b><br/>';
            echo '<u><i>'.__('Name').'</i></u>: '.$row['emergency1Name'].'<br/>';
            echo '<u><i>'.__('Number').'</i></u>: '.$row['emergency1Number1'].'<br/>';
            if ($row['emergency1Number2'] !== '') {
                echo '<u><i>'.__('Number 2').'</i></u>: '.$row['emergency1Number2'].'<br/>';
            }
            if ($row['emergency1Relationship'] !== '') {
                echo '<u><i>'.__('Relationship').'</i></u>: '.$row['emergency1Relationship'].'<br/>';
            }
            echo '</td>';
            echo "<td style='border-top: 1px solid #aaa; vertical-align: top'>";
            echo '<b><i>'.__('Emergency Contact 2').'</i></b><br/>';
            echo '<u><i>'.__('Name').'</i></u>: '.$row['emergency2Name'].'<br/>';
            echo '<u><i>'.__('Number').'</i></u>: '.$row['emergency2Number1'].'<br/>';
            if ($row['emergency2Number2'] !== '') {
                echo '<u><i>'.__('Number 2').'</i></u>: '.$row['emergency2Number2'].'<br/>';
            }
            if ($row['emergency2Relationship'] !== '') {
                echo '<u><i>'.__('Relationship').'</i></u>: '.$row['emergency2Relationship'].'<br/>';
            }
            echo '</td>';
            echo '</tr>';
        }
        if ($count == 0) {
            echo "<tr class=$rowNum>";
            echo '<td colspan=2>';
            echo __('There are no records to display.');
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
