<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        if ($highestAction == 'View External Assessments_myChildrens') { //MY CHILDREN
            $page->breadcrumbs->add(__('View My Childrens\'s External Assessments'));

            //Test data access field for permission
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('Access denied.');
                echo '</div>';
            } else {
                //Get child list
                $count = 0;
                $options = array();
                while ($row = $result->fetch()) {
                    try {
                        $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => date('Y-m-d'));
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL  OR dateEnd>=:date) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    while ($rowChild = $resultChild->fetch()) {
                        $options[$rowChild['pupilsightPersonID']]=Format::name('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
                    }
                }

                if (count($options) == 0) {
                    echo "<div class='alert alert-danger'>";
                    echo __('Access denied.');
                    echo '</div>';
                } elseif (count($options) == 1) {
                    $pupilsightPersonID = key($options);
                } else {
                    echo '<h2>';
                    echo 'Choose Student';
                    echo '</h2>';

                    $pupilsightPersonID = (isset($_GET['search']))? $_GET['search'] : null;

                    $form = Form::create("filter", $_SESSION[$guid]['absoluteURL']."/index.php", "get");
                    $form->setClass('noIntBorder fullWidth standardForm');

                    $form->addHiddenValue('q', '/modules/Formal Assessment/externalAssessment_view.php');
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                    
                    $row = $form->addRow();
                        $row->addLabel('search', __('Student'));
                        $row->addSelect('search')->fromArray($options)->selected($pupilsightPersonID)->placeholder();

                    $row = $form->addRow();
                        $row->addSearchSubmit($pupilsight->session);

                    echo $form->getOutput();
                }

                $showParentAttainmentWarning = getSettingByScope($connection2, 'Markbook', 'showParentAttainmentWarning');
                $showParentEffortWarning = getSettingByScope($connection2, 'Markbook', 'showParentEffortWarning');

                if ($pupilsightPersonID != '' and count($options) > 0) {
                    //Confirm access to this student
                    try {
                        $dataChild = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultChild->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The selected record does not exist, or you do not have access to it.');
                        echo '</div>';
                    } else {
                        $rowChild = $resultChild->fetch();
                        externalAssessmentDetails($guid, $pupilsightPersonID, $connection2, null, false);
                    }
                }
            }
        } else { //My External Assessments
            $page->breadcrumbs->add(__('View My External Assessments'));

            echo '<h3>';
            echo __('External Assessments');
            echo '</h3>';

            echo externalAssessmentDetails($guid, $_SESSION[$guid]['pupilsightPersonID'], $connection2, null, false);
        }
    }
}
?>
