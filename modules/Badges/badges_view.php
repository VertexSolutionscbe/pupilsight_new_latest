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

if (isActionAccessible($guid, $connection2, '/modules/Badges/badges_view.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('View Badges'));

    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) { echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        if ($highestAction == 'View Badges_all') {
            $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? '';

            $form = Form::create('search', $pupilsight->session->get('absoluteURL','').'/index.php', 'GET');
            $form->setTitle(__('Choose Student'));
            $form->addClass('noIntBorder');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('q', '/modules/'.$pupilsight->session->get('module').'/badges_view.php');

            $row = $form->addRow();
                $row->addLabel('pupilsightPersonID', __('Student'));
                $row->addSelectStudent('pupilsightPersonID', $pupilsight->session->get('pupilsightSchoolYearID'))->placeholder()->selected($pupilsightPersonID);

            $row = $form->addRow();
                $row->addSearchSubmit($pupilsight->session);
            
            echo $form->getOutput();

            if ($pupilsightPersonID != '') {
                $output = '';
                echo '<h2>';
                echo __('Badges');
                echo '</h2>';

                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }
                if ($result->rowCount() != 1) {
                    echo "<div class='error'>";
                    echo __('The specified record does not exist.');
                    echo '</div>';
                } else {
                    echo getBadges($connection2, $guid, $pupilsightPersonID);
                }
            }
        } elseif ($highestAction == 'View Badges_my') {
            $output = '';
            echo '<h2>';
            echo __('My Badges');
            echo '</h2>';

            try {
                $data = array('pupilsightPersonID' => $pupilsight->session->get('pupilsightPersonID'));
                $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo __('The specified record does not exist.');
                echo '</div>';
            } else {
                echo getBadges($connection2, $guid, $pupilsight->session->get('pupilsightPersonID'));
            }
        } elseif ($highestAction == 'View Badges_myChildren') {
            $pupilsightPersonID = $pupilsight->session->get('pupilsightPersonID') ?? $_GET['search'];
            
            //Test data access field for permission
            try {
                $data = array('pupilsightPersonID' => $pupilsight->session->get('pupilsightPersonID'));
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
                $count = 0;
                $users = array(
                    $pupilsight->session->get('pupilsightPersonID') => formatName('', $pupilsight->session->get('preferredName'), $pupilsight->session->get('surname'), 'Student', true)
                );
                while ($row = $result->fetch()) {
                    try {
                        $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $pupilsight->session->get('pupilsightSchoolYearID'));
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    while ($rowChild = $resultChild->fetch()) {
                        $users[$rowChild['pupilsightPersonID']] = formatName('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
                        $count ++;
                    }
                }

                echo '<h2>';
                echo __('Choose');
                echo '</h2>';

                $form = Form::create('action', $pupilsight->session->get('absoluteURL','')."/index.php", "get");
                $form->setClass('noIntBorder fullWidth');

                $form->addHiddenValue('address', "/modules/".$pupilsight->session->get('module')."/badges_View.php");
                $form->addHiddenValue('q', $pupilsight->session->get('address'));
        
                $row = $form->addRow();
                    $row->addLabel('search', __('User'));
                    $row->addSelect('search')->fromArray($users)->selected($pupilsightPersonID);

                $row = $form->addRow();
                    $row->addSearchSubmit($pupilsight->session);

                echo $form->getOutput();


                if ($pupilsightPersonID != '' and $count > 0) {
                    //Confirm access to this student
                    try {
                        $dataChild = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonID2' => $pupilsight->session->get('pupilsightPersonID'), 'pupilsightPersonID3' => $pupilsight->session->get('pupilsightPersonID'));
                        $sqlChild = "(SELECT pupilsightPerson.pupilsightPersonID FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y')
                            UNION
                            (SELECT pupilsightPersonID FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID3)
                        ";
                        $resultChild = $connection2->prepare($sqlChild);
                        @$resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($resultChild->rowCount() < 1) {
                        echo "<div class='error'>";
                        echo __('The selected record does not exist, or you do not have access to it.');
                        echo '</div>';
                    } else {
                        $rowChild = $resultChild->fetch();

                        if ($pupilsightPersonID != '') {
                            $output = '';
                            echo '<h2>';
                            echo __('Badges');
                            echo '</h2>';

                            try {
                                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                                $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                echo "<div class='error'>".$e->getMessage().'</div>';
                            }
                            if ($result->rowCount() != 1) {
                                echo "<div class='error'>";
                                echo __('The specified record does not exist.');
                                echo '</div>';
                            } else {
                                $row = $result->fetch();
                                echo getBadges($connection2, $guid, $pupilsightPersonID);
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
