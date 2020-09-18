<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

$_SESSION[$guid]['report_student_medicalSummary.php_choices'] = '';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_student_medicalSummary.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Student Medical Data Summary'));

    echo '<p>';
    echo __('This report prints a summary of medical data for the selected students.');
    echo '</p>';

    echo '<h2>';
    echo __('Choose Students');
    echo '</h2>';

    $choices = isset($_POST['pupilsightPersonID'])? $_POST['pupilsightPersonID'] : array();

    $form = Form::create('action',  $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/report_student_medicalSummary.php");

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Students'));
        $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'], array("allStudents" => false, "byName" => true, "byRoll" => true))->required()->placeholder()->selectMultiple()->selected($choices);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if (count($choices) > 0) {
        $_SESSION[$guid]['report_student_medicalSummary.php_choices'] = $choices;

        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlWhere = ' AND (';
            for ($i = 0; $i < count($choices); ++$i) {
                $data[$choices[$i]] = $choices[$i];
                $sqlWhere = $sqlWhere.'pupilsightPerson.pupilsightPersonID=:'.$choices[$i].' OR ';
            }
            $sqlWhere = substr($sqlWhere, 0, -4);
            $sqlWhere = $sqlWhere.')';
            $sql = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightRollGroup.name AS name FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID $sqlWhere ORDER BY surname, preferredName";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        echo "<div class='linkTop'>";
        echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module']."/report_student_medicalSummary_print.php'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
        echo '</div>';

        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Student');
        echo '</th>';
        echo '<th>';
        echo __('Medical Form?');
        echo '</th>';
        echo '<th>';
        echo __('Blood Type');
        echo '</th>';
        echo '<th>';
        echo __('Tetanus').'<br/>';
        echo "<span style='font-size: 80%'><i>".__('10 Years').'</span>';
        echo '</th>';
        echo '<th>';
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

            try {
                $dataForm = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                $sqlForm = 'SELECT * FROM pupilsightPersonMedical WHERE pupilsightPersonID=:pupilsightPersonID';
                $resultForm = $connection2->prepare($sqlForm);
                $resultForm->execute($dataForm);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultForm->rowCount() == 1) {
                $rowForm = $resultForm->fetch();
                echo "<tr class=$rowNum>";
                echo '<td>';
                echo Format::name('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', true);
                echo '</td>';
                echo '<td>';
                echo __('Yes');
                echo '</td>';
                echo '<td>';
                echo $rowForm['bloodType'];
                echo '</td>';
                echo '<td>';
                echo $rowForm['tetanusWithin10Years'];
                echo '</td>';
                echo '<td>';
                            //Get details of last medical form update
                            try {
                                $dataMedical = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                                $sqlMedical = "SELECT * FROM pupilsightPersonMedicalUpdate WHERE pupilsightPersonID=:pupilsightPersonID AND status='Complete' ORDER BY timestamp DESC";
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

                    //Long term medication
                    if ($rowForm['longTermMedication'] == 'Y') {
                        echo "<tr class=$rowNum>";
                        echo '<td></td>';
                        echo "<td colspan=4 style='border-top: 1px solid #aaa'>";
                        echo '<b><i>'.__('Long Term Medication').'</i></b>: '.$rowForm['longTermMedication'].'<br/>';
                        echo '<u><i>'.__('Details').'</i></u>: '.$rowForm['longTermMedicationDetails'].'<br/>';
                        echo '</td>';
                        echo '</tr>';
                    }

                    //Conditions
                    $condCount = 1;
                try {
                    $dataConditions = array('pupilsightPersonMedicalID' => $rowForm['pupilsightPersonMedicalID']);
                    $sqlConditions = 'SELECT * FROM pupilsightPersonMedicalCondition WHERE pupilsightPersonMedicalID=:pupilsightPersonMedicalID';
                    $resultConditions = $connection2->prepare($sqlConditions);
                    $resultConditions->execute($dataConditions);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                while ($rowConditions = $resultConditions->fetch()) {
                    $alert = getAlert($guid, $connection2, $rowConditions['pupilsightAlertLevelID']);
                    if ($alert != false) {
                        $conditionStyle = "style='border-top: 2px solid #".$alert['color']."'";
                        echo "<tr class=$rowNum>";
                        echo '<td></td>';
                        echo "<td colspan=4 $conditionStyle>";
                        echo '<b><i>'.__('Condition')." $condCount</i></b>: ".__($rowConditions['name']).'<br/>';
                        echo '<u><i>'.__('Risk')."</i></u>: <span style='color: #".$alert['color']."; font-weight: bold'>".__($alert['name']).'</span><br/>';
                        if ($rowConditions['triggers'] != '') {
                            echo '<u><i>'.__('Triggers').'</i></u>: '.$rowConditions['triggers'].'<br/>';
                        }
                        if ($rowConditions['reaction'] != '') {
                            echo '<u><i>'.__('Reaction').'</i></u>: '.$rowConditions['reaction'].'<br/>';
                        }
                        if ($rowConditions['response'] != '') {
                            echo '<u><i>'.__('Response').'</i></u>: '.$rowConditions['response'].'<br/>';
                        }
                        if ($rowConditions['medication'] != '') {
                            echo '<u><i>'.__('Medication').'</i></u>: '.$rowConditions['medication'].'<br/>';
                        }
                        if ($rowConditions['lastEpisode'] != '' or $rowConditions['lastEpisodeTreatment'] != '') {
                            echo '<u><i>'.__('Last Episode').'</i></u>: ';
                            if ($rowConditions['lastEpisode'] != '') {
                                echo dateConvertBack($guid, $rowConditions['lastEpisode']);
                            }
                            if ($rowConditions['lastEpisodeTreatment'] != '') {
                                if ($rowConditions['lastEpisode'] != '') {
                                    echo ' | ';
                                }
                                echo $rowConditions['lastEpisodeTreatment'];
                            }
                            echo '<br/>';
                        }

                        if ($rowConditions['comment'] != '') {
                            echo '<u><i>'.__('Comment').'</i></u>: '.$rowConditions['comment'].'<br/>';
                        }
                        echo '</td>';
                        echo '</tr>';
                        ++$condCount;
                    }
                }
            } else {
                echo "<tr class=$rowNum>";
                echo '<td>';
                echo Format::name('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', true);
                echo '</td>';
                echo '<td colspan=4>';
                echo "<span style='color: #ff0000; font-weight: bold'>".__('No').'</span>';
                echo '</td>';
                echo '</tr>';
            }
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
