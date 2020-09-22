<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_view.php') == false) {
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
        $page->breadcrumbs->add(__('View Activities'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, array('success0' => __('Registration was successful.'), 'success1' => __('Unregistration was successful.'), 'success2' => __('Registration was successful, but the activity is full, so you are on the waiting list.')));
        }

        //Get current role category
        $roleCategory = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);

        //Check access controls
        $access = getSettingByScope($connection2, 'Activities', 'access');
        $hideExternalProviderCost = getSettingByScope($connection2, 'Activities', 'hideExternalProviderCost');

        if (!($access == 'View' or $access == 'Register')) {
            echo "<div class='alert alert-danger'>";
            echo __('Activity listing is currently closed.');
            echo '</div>';
        } else {
            if ($access == 'View') {
                echo "<div class='alert alert-warning'>";
                echo __('Registration is currently closed, but you can still view activities.');
                echo '</div>';
            }

            $disableExternalProviderSignup = getSettingByScope($connection2, 'Activities', 'disableExternalProviderSignup');
            if ($disableExternalProviderSignup == 'Y') {
                echo "<div class='alert alert-warning'>";
                echo __('Please check activity details for instructions on how to register for activities offered by outside providers.');
                echo '</div>';
            }

            //If student, set pupilsightPersonID to self
            if ($roleCategory == 'Student' and $highestAction == 'View Activities_studentRegister') {
                $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
            }
            //IF PARENT, SET UP LIST OF CHILDREN
            $countChild = 0;
            if ($roleCategory == 'Parent' and $highestAction == 'View Activities_studentRegisterByParent') {
                $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? '';
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
                    $options = array();
                    while ($row = $result->fetch()) {
                        try {
                            $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                            $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                            $resultChild = $connection2->prepare($sqlChild);
                            $resultChild->execute($dataChild);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultChild->rowCount() > 0) {
                            if ($resultChild->rowCount() == 1) {
                                $rowChild = $resultChild->fetch();
                                $pupilsightPersonID = $rowChild['pupilsightPersonID'];
                                $options[$rowChild['pupilsightPersonID']] = formatName('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
                                ++$countChild;
                            }
                            else {
                                while ($rowChild = $resultChild->fetch()) {
                                    $options[$rowChild['pupilsightPersonID']] = formatName('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
                                    ++$countChild;
                                }
                            }
                        }
                    }

                    if ($countChild == 0) {
                        echo "<div class='alert alert-danger'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    }
                }
            }

            echo '<h2>';
            echo __('Filter & Search');
            echo '</h2>';

            $search = $_GET['search'] ?? '';

            $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php','get');
            $form->setClass('noIntBorder fullWidth');

            $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/activities_view.php");

            if ($countChild > 0 and $roleCategory == 'Parent' and $highestAction == 'View Activities_studentRegisterByParent') {
                $row = $form->addRow();
                    $row->addLabel('pupilsightPersonID', __('Child'))->description(__('Choose the child you are registering for.'));
                    $row->addSelect('pupilsightPersonID')->fromArray($options)->selected($pupilsightPersonID)->placeholder(($countChild > 1)? '' : null);
            }

            $row = $form->addRow();
                $row->addLabel('search', __('Search'))->description('Activity name.');
                $row->addTextField('search')->setValue($search)->maxLength(20);

            $row = $form->addRow()
			->addClass('right_align');
                $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

            echo $form->getOutput();

            echo '<h2>';
            echo __('Activities');
            echo '</h2>';

            //Set pagination variable
            $page = $_GET['page'] ?? 1;

            if ((!is_numeric($page)) or $page < 1) {
                $page = 1;
            }

            $today = date('Y-m-d');

            //Set special where params for different roles and permissions
            $continue = true;
            $and = '';
            if ($roleCategory == 'Student' and $highestAction == 'View Activities_studentRegister') {
                $continue = false;
                try {
                    $dataStudent = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
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
            if ($roleCategory == 'Parent' and $highestAction == 'View Activities_studentRegisterByParent' and $pupilsightPersonID != '' and $countChild > 0) {
                $continue = false;

                //Confirm access to this student
                try {
                    $dataChild = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($resultChild->rowCount() == 1) {
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
            }

            if ($continue == false) {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                //Should we show date as term or date?
                $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
                if ($dateType == 'Term') {
                    $maxPerTerm = getSettingByScope($connection2, 'Activities', 'maxPerTerm');
                }

                try {
                    if ($dateType != 'Date') {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                        $sql = "SELECT * FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND NOT pupilsightSchoolYearTermIDList='' $and ORDER BY pupilsightSchoolYearTermIDList, name";
                    } else {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'listingStart' => $today, 'listingEnd' => $today);
                        $sql = "SELECT * FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND listingStart<=:listingStart AND listingEnd>=:listingEnd $and ORDER BY name";
                    }
                    if ($search != '') {
                        if ($dateType != 'Date') {
                            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'search' => "%$search%");
                            $sql = "SELECT * FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND NOT pupilsightSchoolYearTermIDList='' AND name LIKE :search $and ORDER BY pupilsightSchoolYearTermIDList, name";
                        } else {
                            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'listingStart' => $today, 'listingEnd' => $today, 'search' => "%$search%");
                            $sql = "SELECT * FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND listingStart<=:listingStart AND listingEnd>=:listingEnd AND name LIKE :search $and ORDER BY name";
                        }
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);

                if ($result->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                        printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top', "search=$search");
                    }

                    if ($dateType == 'Term' and $maxPerTerm > 0 and (($roleCategory == 'Student' and $highestAction == 'View Activities_studentRegister') or ($roleCategory == 'Parent' and $highestAction == 'View Activities_studentRegisterByParent' and $pupilsightPersonID != '' and $countChild > 0))) {
                        echo "<div class='alert alert-warning'>";
                        echo __("Remember, each student can register for no more than $maxPerTerm activities per term. Your current registration count by term is:");
                        $terms = getTerms($connection2, $_SESSION[$guid]['pupilsightSchoolYearID']);
                        echo '<ul>';
                        for ($i = 0; $i < count($terms); $i = $i + 2) {
                            echo '<li>';
                            echo '<b>'.$terms[($i + 1)].':</b> ';

                            try {
                                $dataActivityCount = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearTermIDList' => '%'.$terms[$i].'%');
                                $sqlActivityCount = "SELECT * FROM pupilsightActivityStudent JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearTermIDList LIKE :pupilsightSchoolYearTermIDList AND NOT status='Not Accepted'";
                                $resultActivityCount = $connection2->prepare($sqlActivityCount);
                                $resultActivityCount->execute($dataActivityCount);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            if ($resultActivityCount->rowCount() >= 0) {
                                echo $resultActivityCount->rowCount().' activities';
                            }
                            echo '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }

                    echo "<table cellspacing='0' class='table display data-table text-nowrap' style='width: 100%;'>";
                    echo '<thead>';
                    echo "<tr class='head'>";
                    echo '<th>';
                    echo __('Activity');
                    echo '</th>';
                    echo '<th>';
                    echo __('Provider');
                    echo '</th>';
                    echo '<th>';
                    if ($dateType != 'Date') {
                        echo __('Terms').'<br/>';
                    } else {
                        echo __('Dates').'<br/>';
                    }
                    echo "<span style='font-style: italic; font-size: 85%'>";
                    echo __('Days');
                    echo '</span>';
                    echo '</th>';
                    echo "<th style='width: 100px'>";
                    echo __('Years');
                    echo '</th>';
                    echo '<th>';
                    echo __('Cost').'<br/>';
                    echo "<span style='font-style: italic; font-size: 85%'>".$_SESSION[$guid]['currency'].'</span>';
                    echo '</th>';
                    if (($roleCategory == 'Student' and $highestAction == 'View Activities_studentRegister') or ($roleCategory == 'Parent' and $highestAction == 'View Activities_studentRegisterByParent' and $pupilsightPersonID != '' and $countChild > 0)) {
                        echo '<th>';
                        echo __('Enrolment');
                        echo '</th>';
                    }
                    echo "<th style='width: 80px'>";
                    echo __('Actions');
                    echo '</th>';
                    echo '</tr>';
					 echo '</thead>';

                    $count = 0;
                    $rowNum = 'odd';
                    try {
                        $resultPage = $connection2->prepare($sqlPage);
                        $resultPage->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    while ($row = $resultPage->fetch()) {
                        if ($count % 2 == 0) {
                            $rowNum = 'even';
                        } else {
                            $rowNum = 'odd';
                        }

                        $rowEnrol = null;
                        if (($roleCategory == 'Student' and $highestAction == 'View Activities_studentRegister') or ($roleCategory == 'Parent' and $highestAction == 'View Activities_studentRegisterByParent' and $pupilsightPersonID != '' and $countChild > 0)) {
                            try {
                                $dataEnrol = array('pupilsightActivityID' => $row['pupilsightActivityID'], 'pupilsightPersonID' => $pupilsightPersonID);
                                $sqlEnrol = 'SELECT * FROM pupilsightActivityStudent WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPersonID=:pupilsightPersonID';
                                $resultEnrol = $connection2->prepare($sqlEnrol);
                                $resultEnrol->execute($dataEnrol);
                            } catch (PDOException $e) {
                            }
                            if ($resultEnrol->rowCount() > 0) {
                                $rowEnrol = $resultEnrol->fetch();
                                $rowNum = 'current';
                            }
                        }

                        ++$count;

                        //COLOR ROW BY STATUS!
                        echo "<tr class=$rowNum>";
                        echo '<td>';
                        echo $row['name'].'<br/>';
                        echo '<i>'.trim($row['type']).'</i>';
                        echo '</td>';
                        echo '<td>';
                        if ($row['provider'] == 'School') {
                            echo $_SESSION[$guid]['organisationNameShort'];
                        } else {
                            echo __('External');
                        }
                        echo '</td>';
                        echo '<td>';
                        if ($dateType != 'Date') {
                            $terms = getTerms($connection2, $_SESSION[$guid]['pupilsightSchoolYearID'], true);
                            $termList = '';
                            for ($i = 0; $i < count($terms); $i = $i + 2) {
                                if (is_numeric(strpos($row['pupilsightSchoolYearTermIDList'], $terms[$i]))) {
                                    $termList .= $terms[($i + 1)].'<br/>';
                                }
                            }
                            echo $termList;
                        } else {
                            echo formatDateRange($row['programStart'], $row['programEnd']);
                        }

                        echo "<span style='font-style: italic; font-size: 85%'>";
                        try {
                            $dataSlots = array('pupilsightActivityID' => $row['pupilsightActivityID']);
                            $sqlSlots = 'SELECT DISTINCT nameShort, sequenceNumber FROM pupilsightActivitySlot JOIN pupilsightDaysOfWeek ON (pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID) WHERE pupilsightActivityID=:pupilsightActivityID ORDER BY sequenceNumber';
                            $resultSlots = $connection2->prepare($sqlSlots);
                            $resultSlots->execute($dataSlots);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        $count2 = 0;
                        while ($rowSlots = $resultSlots->fetch()) {
                            if ($count2 > 0) {
                                echo ', ';
                            }
                            echo __($rowSlots['nameShort']);
                            ++$count2;
                        }
                        if ($count2 == 0) {
                            echo '<i>'.__('None').'</i>';
                        }
                        echo '</span>';
                        echo '</td>';
                        echo '<td>';
                        echo getYearGroupsFromIDList($guid, $connection2, $row['pupilsightYearGroupIDList']);
                        echo '</td>';
                        echo '<td>';
                        if ($hideExternalProviderCost == 'Y' and $row['provider'] == 'External') {
                            echo '<i>'.__('See activity details').'</i>';
                        } else {
                            if ($row['payment'] == 0) {
                                echo '<i>'.__('None').'</i>';
                            } else {
                                if (substr($_SESSION[$guid]['currency'], 4) != '') {
                                    echo substr($_SESSION[$guid]['currency'], 4);
                                }
                                echo number_format($row['payment'], 2)."<br/>";
                                echo __($row['paymentType'])."<br/>";
                                if ($row['paymentFirmness'] != 'Finalised') {
                                    echo __($row['paymentFirmness'])."<br/>";
                                }
                            }
                        }
                        echo '</td>';
                        if (($roleCategory == 'Student' and $highestAction == 'View Activities_studentRegister') or ($roleCategory == 'Parent' and $highestAction == 'View Activities_studentRegisterByParent' and $pupilsightPersonID != '' and $countChild > 0)) {
                            echo '<td>';
                            if ($row['provider'] == 'External' and $disableExternalProviderSignup == 'Y') {
                                echo '<i>'.__('See activity details').'</i>';
                            } elseif ($row['registration'] == 'N') {
                                echo __('Closed').'<br/>';
                            } else {
                                echo $rowEnrol['status'];
                            }
                            echo '</td>';
                        }
                        echo '<td>';
                        echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/activities_view_full.php&pupilsightActivityID='.$row['pupilsightActivityID']."&width=1000&height=550'><i title='".__('View Details')."' class='mdi mdi-eye-outline mdi-24px'></i></a> ";
                        $signup = true;
                        if ($access == 'View') {
                            $signup = false;
                        }
                        if ($row['registration'] == 'N') {
                            $signup = false;
                        }
                        if ($row['provider'] == 'External' and $disableExternalProviderSignup == 'Y') {
                            $signup = false;
                        }
                        if ($signup) {
                            if (($roleCategory == 'Student' and $highestAction == 'View Activities_studentRegister') or ($roleCategory == 'Parent' and $highestAction == 'View Activities_studentRegisterByParent' and $pupilsightPersonID != '' and $countChild > 0)) {
                                if ($resultEnrol->rowCount() < 1) {
                                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/activities_view_register.php&pupilsightPersonID=$pupilsightPersonID&search=".$search.'&mode=register&pupilsightActivityID='.$row['pupilsightActivityID']."'><img title='".__('Register')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/attendance.png'/></a> ";
                                } else {
                                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/activities_view_register.php&pupilsightPersonID=$pupilsightPersonID&search=".$search.'&mode=unregister&pupilsightActivityID='.$row['pupilsightActivityID']."'><img title='".__('Unregister')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
                                }
                            }
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';

                    if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                        printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom', "search=$search");
                    }
                }
            }
        }
    }
}
?>
