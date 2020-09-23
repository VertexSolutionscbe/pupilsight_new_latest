<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\Prefab\RollGroupTable;

if (isActionAccessible($guid, $connection2, '/modules/Roll Groups/rollGroups_details.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'];
    if ($pupilsightRollGroupID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightRollGroupID' => $pupilsightRollGroupID);
            $sql = 'SELECT pupilsightSchoolYear.pupilsightSchoolYearID, pupilsightRollGroupID, pupilsightSchoolYear.name as yearName, pupilsightRollGroup.name, pupilsightRollGroup.nameShort, pupilsightPersonIDTutor, pupilsightPersonIDTutor2, pupilsightPersonIDTutor3, pupilsightPersonIDEA, pupilsightPersonIDEA2, pupilsightPersonIDEA3, pupilsightSpace.name AS space, website FROM pupilsightRollGroup JOIN pupilsightSchoolYear ON (pupilsightRollGroup.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) LEFT JOIN pupilsightSpace ON (pupilsightRollGroup.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroupID=:pupilsightRollGroupID ORDER BY sequenceNumber, pupilsightRollGroup.name';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
            echo '</div>';
        } else {
            $row = $result->fetch();

            $page->breadcrumbs
                ->add(__('View Roll Groups'), 'rollGroups.php')
                ->add($row['name']);

            echo '<h3>';
            echo __('Basic Information');
            echo '</h3>';

            echo "<table class='table'>";
            echo '<tr>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Name').'</span><br/>';
            echo '<i>'.$row['name'].'</i>';
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Tutors').'</span><br/>';
            try {
                $dataTutor = array('pupilsightPersonID1' => $row['pupilsightPersonIDTutor'], 'pupilsightPersonID2' => $row['pupilsightPersonIDTutor2'], 'pupilsightPersonID3' => $row['pupilsightPersonIDTutor3']);
                $sqlTutor = 'SELECT pupilsightPersonID, surname, preferredName, image_240 FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID1 OR pupilsightPersonID=:pupilsightPersonID2 OR pupilsightPersonID=:pupilsightPersonID3';
                $resultTutor = $connection2->prepare($sqlTutor);
                $resultTutor->execute($dataTutor);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            $primaryTutor240 = '';
            while ($rowTutor = $resultTutor->fetch()) {
                if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php')) {
                    echo "<i><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID='.$rowTutor['pupilsightPersonID']."'>".formatName('', $rowTutor['preferredName'], $rowTutor['surname'], 'Staff', false, true).'</a></i>';
                } else {
                    echo '<i>'.formatName('', $rowTutor['preferredName'], $rowTutor['surname'], 'Staff', false, true);
                }
                if ($rowTutor['pupilsightPersonID'] == $row['pupilsightPersonIDTutor']) {
                    $primaryTutor240 = $rowTutor['image_240'];
                    if ($resultTutor->rowCount() > 1) {
                        echo ' ('.__('Main Tutor').')';
                    }
                }
                echo '</i><br/>';
            }
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Educational Assistants').'</span><br/>';
            try {
                $dataTutor = array('pupilsightPersonID1' => $row['pupilsightPersonIDEA'], 'pupilsightPersonID2' => $row['pupilsightPersonIDEA2'], 'pupilsightPersonID3' => $row['pupilsightPersonIDEA3']);
                $sqlTutor = 'SELECT pupilsightPersonID, surname, preferredName, image_240 FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID1 OR pupilsightPersonID=:pupilsightPersonID2 OR pupilsightPersonID=:pupilsightPersonID3';
                $resultTutor = $connection2->prepare($sqlTutor);
                $resultTutor->execute($dataTutor);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            while ($rowTutor = $resultTutor->fetch()) {
                if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php')) {
                    echo "<i><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID='.$rowTutor['pupilsightPersonID']."'>".formatName('', $rowTutor['preferredName'], $rowTutor['surname'], 'Staff', false, true).'</a></i>';
                } else {
                    echo '<i>'.formatName('', $rowTutor['preferredName'], $rowTutor['surname'], 'Staff', false, true);
                }
                echo '</i><br/>';
            }
            echo '</td>';
            echo '</tr>';
            echo "<td style='width: 33%; vertical-align: top' colspan=3>";
            echo "<span class='form-label'>".__('Location').'</span><br/>';
            echo '<i>'.$row['space'].'</i>';
            echo '</td>';
            echo '</tr>';
            if ($row['website'] != '') {
                echo '<tr>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>";
                echo "<span class='form-label'>".__('Website').'</span><br/>';
                echo "<a target='_blank' href='".$row['website']."'>".$row['website'].'</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';

            $sortBy = $_GET['sortBy'] ?? 'rollOrder, surname, preferredName';

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

            $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->setTitle(__('Filters'));
            $form->setClass('noIntBorder fullWidth');

            $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/rollGroups_details.php");
            $form->addHiddenValue('pupilsightRollGroupID', $pupilsightRollGroupID);

            $row = $form->addRow();
                $row->addLabel('sortBy', __('Sort By'));
                $row->addSelect('sortBy')->fromArray(array('rollOrder, surname, preferredName' => __('Roll Order'), 'surname, preferredName' => __('Surname'), 'preferredName, surname' => __('Preferred Name')))->selected($sortBy)->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit(__('Go'))->addClass('submit_align')->prepend(sprintf('<a href="%s" class="right">%s</a> &nbsp;', $_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&pupilsightRollGroupID=$pupilsightRollGroupID", __('Clear Form')));

            echo $form->getOutput();

            // Students
            $table = $container->get(RollGroupTable::class);
            $table->build($pupilsightRollGroupID, true, true, $sortBy);

            echo $table->getOutput();

            //Set sidebar
            $_SESSION[$guid]['sidebarExtra'] = getUserPhoto($guid, $primaryTutor240, 240);
        }
    }
}
?>
