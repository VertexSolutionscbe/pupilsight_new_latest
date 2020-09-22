<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_view_register.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $page->breadcrumbs
            ->add(__('View Activities'), 'activities_view.php')
            ->add(__('Activity Registration'));

        if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_view_register') == false) {
            //Acess denied
            echo "<div class='alert alert-danger'>";
            echo __('You do not have access to this action.');
            echo '</div>';
        } else {
            //Get current role category
            $roleCategory = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);

            //Check access controls
            $access = getSettingByScope($connection2, 'Activities', 'access');

            $pupilsightPersonID = $_GET['pupilsightPersonID'];
            $search = isset($_GET['search'])? $_GET['search'] : '';

            if ($access != 'Register') {
                echo "<div class='alert alert-danger'>";
                echo __('Registration is closed, or you do not have permission to register.');
                echo '</div>';
            } else {
                //Check if school year specified
                $pupilsightActivityID = $_GET['pupilsightActivityID'];
                if ($pupilsightActivityID == 'Y') {
                    echo "<div class='alert alert-danger'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    $mode = $_GET['mode'];

                    if ($_GET['search'] != '' or $pupilsightPersonID != '') {
                        echo "<div class='linkTop'>";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Activities/activities_view.php&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search']."'>".__('Back to Search Results').'</a>';
                        echo '</div>';
                    }

                    //Check Access
                    $continue = false;
                    //Student
                    if ($roleCategory == 'Student' and $highestAction == 'View Activities_studentRegister') {
                        try {
                            $dataStudent = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                            $sqlStudent = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
                            $resultStudent = $connection2->prepare($sqlStudent);
                            $resultStudent->execute($dataStudent);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultStudent->rowCount() == 1) {
                            $rowStudent = $resultStudent->fetch();
                            $pupilsightYearGroupID = $rowStudent['pupilsightYearGroupID'];
                            if ($pupilsightYearGroupID != '') {
                                $continue = true;
                                $and = " AND pupilsightYearGroupIDList LIKE '%$pupilsightYearGroupID%'";
                            }
                        }
                    }
                    //Parent
                    else if ($roleCategory == 'Parent' and $highestAction == 'View Activities_studentRegisterByParent' and $pupilsightPersonID != '') {
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
                            $countChild = 0;
                            while ($values = $result->fetch()) {
                                try {
                                    $dataChild = array('pupilsightFamilyID' => $values['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
                                    $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName ";
                                    $resultChild = $connection2->prepare($sqlChild);
                                    $resultChild->execute($dataChild);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }
                                while ($rowChild = $resultChild->fetch()) {
                                    ++$countChild;
                                    $pupilsightYearGroupID = $rowChild['pupilsightYearGroupID'];
                                }
                            }

                            if ($countChild > 0) {
                                if ($pupilsightYearGroupID != '') {
                                    $continue = true;
                                    $and = " AND pupilsightYearGroupIDList LIKE '%$pupilsightYearGroupID%'";
                                }
                            }
                        }
                    }

                    if ($mode == 'register') {
                        if ($continue == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed due to a database error.');
                            echo '</div>';
                        } else {
                            $today = date('Y-m-d');

                            //Should we show date as term or date?
                            $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
                            if ($dateType == 'Term') {
                                $maxPerTerm = getSettingByScope($connection2, 'Activities', 'maxPerTerm');
                            }

                            try {
                                if ($dateType != 'Date') {
                                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID);
                                    $sql = "SELECT * FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND NOT pupilsightSchoolYearTermIDList='' AND pupilsightActivityID=:pupilsightActivityID AND registration='Y' $and";
                                } else {
                                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID, 'listingStart' => $today, 'listingEnd' => $today);
                                    $sql = "SELECT * FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND listingStart<=:listingStart AND listingEnd>=:listingEnd AND pupilsightActivityID=:pupilsightActivityID AND registration='Y' $and";
                                }
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            if ($result->rowCount() != 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('The selected record does not exist, or you do not have access to it.');
                                echo '</div>';
                            } else {
                                $values = $result->fetch();

                                //Check for existing registration
                                try {
                                    $dataReg = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                    $sqlReg = 'SELECT * FROM pupilsightActivityStudent WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPersonID=:pupilsightPersonID';
                                    $resultReg = $connection2->prepare($sqlReg);
                                    $resultReg->execute($dataReg);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }

                                if ($resultReg->rowCount() > 0) {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('You are already registered for this activity and so cannot register again.');
                                    echo '</div>';
                                } else {
                                    if (isset($_GET['return'])) {
                                        returnProcess($guid, $_GET['return'], null, array('error3' => __('Registration failed because you are already registered in this activity.')));
                                    }

                                    //Check registration limit...
                                    $proceed = true;
                                    if ($dateType == 'Term' and $maxPerTerm > 0) {
                                        $termsList = explode(',', $values['pupilsightSchoolYearTermIDList']);
                                        foreach ($termsList as $term) {
                                            try {
                                                $dataActivityCount = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearTermIDList' => '%'.$term.'%');
                                                $sqlActivityCount = "SELECT * FROM pupilsightActivityStudent JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearTermIDList LIKE :pupilsightSchoolYearTermIDList AND NOT status='Not Accepted'";
                                                $resultActivityCount = $connection2->prepare($sqlActivityCount);
                                                $resultActivityCount->execute($dataActivityCount);
                                            } catch (PDOException $e) {
                                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                            }
                                            if ($resultActivityCount->rowCount() >= $maxPerTerm) {
                                                $proceed = false;
                                            }
                                        }
                                    }

                                    if ($proceed == false) {
                                        echo "<div class='alert alert-danger'>";
                                        echo __('You have subscribed for the maximum number of activities in a term, and so cannot register for this activity.');
                                        echo '</div>';
                                    } else {

                                        echo '<p>';
                                        if (getSettingByScope($connection2, 'Activities', 'enrolmentType') == 'Selection') {
                                            echo __('After you press the Register button below, your application will be considered by a member of staff who will decide whether or not there is space for you in this program.');
                                        } else {
                                            echo __('If there is space on this program you will be accepted immediately upon pressing the Register button below. If there is not, then you will be placed on a waiting list.');
                                        }
                                        echo '</p>';

                                        $form = Form::create('courseEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/activities_view_registerProcess.php?search='.$search);

                                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                                        $form->addHiddenValue('mode', $mode);
                                        $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
                                        $form->addHiddenValue('pupilsightActivityID', $pupilsightActivityID);

                                        $row = $form->addRow();
                                            $row->addLabel('name', __('Activity'));
                                            $row->addTextField('name')->readonly();

                                        if ($dateType != 'Date') {
                                            $schoolTerms = getTerms($connection2, $_SESSION[$guid]['pupilsightSchoolYearID']);
                                            $termList = array_filter(array_map(function($item) use ($schoolTerms) {
                                                $index = array_search($item, $schoolTerms);
                                                return ($index !== false && isset($schoolTerms[$index+1]))? $schoolTerms[$index+1] : '';
                                            }, explode(',', $values['pupilsightSchoolYearTermIDList'])));
                                            $termList = (!empty($termList)) ? implode(', ', $termList) : '-';

                                            $row = $form->addRow();
                                                $row->addLabel('terms', __('Terms'));
                                                $row->addTextField('terms')->readonly()->setValue($termList);
                                        } else {
                                            $row = $form->addRow();
                                                $row->addLabel('programStart', __('Program Start Date'));
                                                $row->addDate('programStart')->readonly();

                                            $row = $form->addRow();
                                                $row->addLabel('programEnd', __('Program End Date'));
                                                $row->addDate('programEnd')->readonly();
                                        }

                                        if (getSettingByScope($connection2, 'Activities', 'payment') != 'None' && getSettingByScope($connection2, 'Activities', 'payment') != 'Single') {
                                                $row = $form->addRow();
                                                    $row->addLabel('payment', __('Cost'))->description(__('For entire programme'));
                                                    $row->addCurrency('payment')->readonly();
                                            }

                                        if (getSettingByScope($connection2, 'Activities', 'backupChoice') == 'Y') {
                                            if ($dateType != 'Date') {
                                                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightActivityID' => $pupilsightActivityID);
                                                $sql = "SELECT DISTINCT pupilsightActivity.pupilsightActivityID as value, pupilsightActivity.name FROM pupilsightActivity JOIN pupilsightStudentEnrolment ON (pupilsightActivity.pupilsightYearGroupIDList LIKE concat( '%', pupilsightStudentEnrolment.pupilsightYearGroupID, '%' )) WHERE pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT pupilsightActivityID=:pupilsightActivityID AND NOT pupilsightSchoolYearTermIDList='' AND pupilsightActivity.active='Y' $and ORDER BY name";
                                            } else {
                                                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightActivityID' => $pupilsightActivityID, 'listingStart' => $today, 'listingEnd' => $today);
                                                $sql = "SELECT DISTINCT pupilsightActivity.pupilsightActivityID as value, pupilsightActivity.name FROM pupilsightActivity JOIN pupilsightStudentEnrolment ON (pupilsightActivity.pupilsightYearGroupIDList LIKE concat( '%', pupilsightStudentEnrolment.pupilsightYearGroupID, '%' )) WHERE pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND NOT pupilsightActivityID=:pupilsightActivityID AND listingStart<=:listingStart AND listingEnd>=:listingEnd AND active='Y' $and ORDER BY name";
                                            }
                                            $result = $pdo->executeQuery($data, $sql);

                                            $row = $form->addRow();
                                                $row->addLabel('pupilsightActivityIDBackup', __('Backup Choice'))
                                                    ->description(sprintf(__('In case %1$s is full.'), $values['name']));
                                                $row->addSelect('pupilsightActivityIDBackup')
                                                    ->fromResults($result)
                                                    ->required($result->rowCount() > 0)
                                                    ->placeholder();
                                        }

                                        $row = $form->addRow();
                                            $row->addSubmit(__('Register'));

                                        $form->loadAllValuesFrom($values);

                                        echo $form->getOutput();
                                    }
                                }
                            }
                        }
                    } elseif ($mode = 'unregister') {
                        if ($continue == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed due to a database error.');
                            echo '</div>';
                        } else {
                            $today = date('Y-m-d');

                            //Should we show date as term or date?
                            $dateType = getSettingByScope($connection2, 'Activities', 'dateType');

                            try {
                                if ($dateType != 'Date') {
                                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightActivityID' => $pupilsightActivityID);
                                    $sql = "SELECT DISTINCT pupilsightActivity.* FROM pupilsightActivity JOIN pupilsightStudentEnrolment ON (pupilsightActivity.pupilsightYearGroupIDList LIKE concat( '%', pupilsightStudentEnrolment.pupilsightYearGroupID, '%' )) WHERE pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightActivityID=:pupilsightActivityID AND NOT pupilsightSchoolYearTermIDList='' AND active='Y' $and";
                                } else {
                                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightActivityID' => $pupilsightActivityID, 'listingStart' => $today, 'listingEnd' => $today);
                                    $sql = "SELECT DISTINCT pupilsightActivity.* FROM pupilsightActivity JOIN pupilsightStudentEnrolment ON (pupilsightActivity.pupilsightYearGroupIDList LIKE concat( '%', pupilsightStudentEnrolment.pupilsightYearGroupID, '%' )) WHERE pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightActivityID=:pupilsightActivityID AND listingStart<=:listingStart AND listingEnd>=:listingEnd AND active='Y' $and";
                                }
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            if ($result->rowCount() != 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('The selected record does not exist, or you do not have access to it.');
                                echo '</div>';
                            } else {
                                $values = $result->fetch();

                                //Check for existing registration
                                try {
                                    $dataReg = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $pupilsightPersonID);
                                    $sqlReg = 'SELECT * FROM pupilsightActivityStudent WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPersonID=:pupilsightPersonID';
                                    $resultReg = $connection2->prepare($sqlReg);
                                    $resultReg->execute($dataReg);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }

                                if ($resultReg->rowCount() < 1) {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('You are not currently registered for this activity and so cannot unregister.');
                                    echo '</div>';
                                } else {
                                    if (isset($_GET['return'])) {
                                        returnProcess($guid, $_GET['return'], null, null);
                                    }

                                    $form = Form::create('courseEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/activities_view_registerProcess.php?search='.$search);
                                    $form->removeClass('smallIntBorder');

                                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                                    $form->addHiddenValue('mode', $mode);
                                    $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
                                    $form->addHiddenValue('pupilsightActivityID', $pupilsightActivityID);

                                    $form->addRow()->addContent(sprintf(__('Are you sure you want to unregister from activity "%1$s"? If you try to reregister later you may lose a space already assigned to you.'), $values['name']))->wrap('<strong>', '</strong>');

                                    $row = $form->addRow();
                                        $row->addSubmit(__('Unregister'));

                                    echo $form->getOutput();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
