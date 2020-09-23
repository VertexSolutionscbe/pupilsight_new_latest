<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_view.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    // Register scripts available to the core, but not included by default
    $page->scripts->add('chart');
    
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        if ($highestAction == 'View ATLs_all') { //ALL STUDENTS
            $page->breadcrumbs->add(__('View All ATLs'));

            $pupilsightPersonID = null;
            if (isset($_GET['pupilsightPersonID'])) {
                $pupilsightPersonID = $_GET['pupilsightPersonID'];
            }

            echo '<h3>';
            echo __('Choose A Student');
            echo '</h3>';

            $form = Form::create("filter", $_SESSION[$guid]['absoluteURL']."/index.php", "get", "noIntBorder fullWidth standardForm");
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('q', '/modules/ATL/atl_view.php');
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('pupilsightPersonID', __('Student'));
                $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]["pupilsightSchoolYearID"], array())->selected($pupilsightPersonID)->placeholder();

            $row = $form->addRow();
                $row->addSearchSubmit($pupilsight->session);

            echo $form->getOutput();

            if (!empty($pupilsightPersonID)) {
                echo '<h3>';
                echo __('ATLs');
                echo '</h3>';

                //Check for access
                try {
                    $dataCheck = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sqlCheck = "SELECT DISTINCT pupilsightPerson.* FROM pupilsightPerson LEFT JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."')";
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($resultCheck->rowCount() != 1) {
                    echo "<div class='error'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                } else {
                    echo getATLRecord($guid, $connection2, $pupilsightPersonID);
                }
            }
        } elseif ($highestAction == 'View ATLs_myChildrens') { //MY CHILDREN
            $page->breadcrumbs->add(__('View My Childrens\'s ATLs'));

            //Test data access field for permission
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() < 1) {
                echo "<div class='error'>";
                echo __('Access denied.');
                echo '</div>';
            } else {
                //Get child list
                $pupilsightPersonID = null;
                $options = array();
                while ($row = $result->fetch()) {
                    try {
                        $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'today' => date('Y-m-d'));
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }
                    while ($rowChild = $resultChild->fetch()) {
                        $options[$rowChild['pupilsightPersonID']]=Format::name('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
                    }
                }

                if (count($options) == 0) {
                    echo "<div class='error'>";
                    echo __('Access denied.');
                    echo '</div>';
                } elseif (count($options) == 1) {
                    $pupilsightPersonID = key($options);
                } else {
                    echo '<h2>';
                    echo __('Choose Student');
                    echo '</h2>';

                    $pupilsightPersonID = (isset($_GET['search']))? $_GET['search'] : null;

                    $form = Form::create("filter", $_SESSION[$guid]['absoluteURL']."/index.php", "get", "noIntBorder fullWidth standardForm");
                    $form->setFactory(DatabaseFormFactory::create($pdo));

                    $form->addHiddenValue('q', '/modules/ATL/atl_view.php');

                    $row = $form->addRow();
                        $row->addLabel('pupilsightPersonID', __('Child'))->description('Choose the child you are registering for.');
                        $row->addSelect('pupilsightPersonID')->fromArray($options)->selected($pupilsightPersonID);

                    $row = $form->addRow();
                        $row->addSearchSubmit($pupilsight->session);

                    echo $form->getOutput();
                }

                $showParentAttainmentWarning = getSettingByScope($connection2, 'Markbook', 'showParentAttainmentWarning');
                $showParentEffortWarning = getSettingByScope($connection2, 'Markbook', 'showParentEffortWarning');

                if (!empty($pupilsightPersonID) and count($options) > 0) {
                    //Confirm access to this student
                    try {
                        $dataChild = array();
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=$pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=".$_SESSION[$guid]['pupilsightPersonID']." AND childDataAccess='Y'";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }
                    if ($resultChild->rowCount() < 1) {
                        echo "<div class='error'>";
                        echo __('The selected record does not exist, or you do not have access to it.');
                        echo '</div>';
                    } else {
                        $rowChild = $resultChild->fetch();
                        echo getATLRecord($guid, $connection2, $pupilsightPersonID);
                    }
                }
            }
        } else { //My ATLS
            $page->breadcrumbs->add(__('View My ATLs'));

            echo '<h3>';
            echo __('ATLs');
            echo '</h3>';

            echo getATLRecord($guid, $connection2, $_SESSION[$guid]['pupilsightPersonID']);
        }
    }
}
