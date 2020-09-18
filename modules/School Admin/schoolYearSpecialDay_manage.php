<?php
/*
Pupilsight, Flexible & Open School System
*/

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearSpecialDay_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Special Days'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    if ($pupilsightSchoolYearID != $_SESSION[$guid]['pupilsightSchoolYearID']) {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    if ($pupilsightSchoolYearID != '') {
        echo '<h2>';
        echo $pupilsightSchoolYearName;
        echo '</h2>';

        echo "<div class='linkTop'>";
            //Print year picker
            if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/schoolYearSpecialDay_manage.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
        echo ' | ';
        if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/schoolYearSpecialDay_manage.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
        } else {
            echo __('Next Year').' ';
        }
        echo '</div>';

          

        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY sequenceNumber';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        echo "<div style='height:50px;'><div class='float-right mb-2'><a href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/schoolYearSpecialDay_manage_add.php&pupilsightSchoolYearID=&dateStamp=&pupilsightSchoolYearTermID=&firstDay=&lastDay=&addtype=2' class='thickbox btn btn-primary'>Add</a><div class='float-none'></div></div></div>";

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no terms in the specified year.');
            echo '</div>';
        } else {
            while ($row = $result->fetch()) {
                echo '<h3>';
                echo $row['name'];
                echo '</h3>';
                $firstDayStamp = dateConvertToTimestamp($row['firstDay']);
                $lastDayStamp = dateConvertToTimestamp($row['lastDay']);

                //Count back to first Monday before first day
                $startDayStamp = $firstDayStamp;
                while (date('D', $startDayStamp) != 'Mon') {
                    $startDayStamp = strtotime('-1 day', $startDayStamp);
                }

                //Count forward to first Sunday after last day
                $endDayStamp = $lastDayStamp;
                while (date('D', $endDayStamp) != 'Sun') {
                    $endDayStamp = strtotime('+1 day', $endDayStamp);
                }

                //Get the special days
                try {
                    $dataSpecial = array('firstDay' => $row['firstDay'], 'lastDay' => $row['lastDay']);
                    $sqlSpecial = 'SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date BETWEEN :firstDay AND :lastDay ORDER BY date';
                    $resultSpecial = $connection2->prepare($sqlSpecial);
                    $resultSpecial->execute($dataSpecial);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($resultSpecial->rowCount() > 0) {
                    $rowSpecial = $resultSpecial->fetch();
                }

                //Check which days are school days
                $days = array();
                $days['Mon'] = 'Y';
                $days['Tue'] = 'Y';
                $days['Wed'] = 'Y';
                $days['Thu'] = 'Y';
                $days['Fri'] = 'Y';
                $days['Sat'] = 'Y';
                $days['Sun'] = 'Y';
                try {
                    $dataDays = array();
                    $sqlDays = "SELECT * FROM pupilsightDaysOfWeek WHERE schoolDay='N'";
                    $resultDays = $connection2->prepare($sqlDays);
                    $resultDays->execute($dataDays);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                while ($rowDays = $resultDays->fetch()) {
                    if ($rowDays['nameShort'] == 'Mon') {
                        $days['Mon'] = 'N';
                    } elseif ($rowDays['nameShort'] == 'Tue') {
                        $days['Tue'] = 'N';
                    } elseif ($rowDays['nameShort'] == 'Wed') {
                        $days['Wed'] = 'N';
                    } elseif ($rowDays['nameShort'] == 'Thu') {
                        $days['Thu'] = 'N';
                    } elseif ($rowDays['nameShort'] == 'Fri') {
                        $days['Fri'] = 'N';
                    } elseif ($rowDays['nameShort'] == 'Sat') {
                        $days['Sat'] = 'N';
                    } elseif ($rowDays['nameShort'] == 'Sun') {
                        $days['Sun'] = 'N';
                    }
                }

                $count = 1;
                echo "<table cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo "<th style='width: 14px'>";
                echo __('Monday');
                echo '</th>';
                echo "<th style='width: 14px'>";
                echo __('Tuesday');
                echo '</th>';
                echo "<th style='width: 14px'>";
                echo __('Wednesday');
                echo '</th>';
                echo "<th style='width: 14px'>";
                echo __('Thursday');
                echo '</th>';
                echo "<th style='width: 14px'>";
                echo __('Friday');
                echo '</th>';
                echo "<th style='width: 14px'>";
                echo __('Saturday');
                echo '</th>';
                echo "<th style='width: 15px'>";
                echo __('Sunday');
                echo '</th>';
                echo '</tr>';

                $specialDayStamp = null;
                for ($i = $startDayStamp; $i <= $endDayStamp;$i = strtotime('+1 day', $i)) {
                    if (date('D', $i) == 'Mon') {
                        echo "<tr style='height: 60px'>";
                    }

                    if (isset($rowSpecial)) {
                        if ($rowSpecial == true) {
                            $specialDayStamp = dateConvertToTimestamp($rowSpecial['date']);
                        }
                    }
                     //or $days[date('D', $i)] == 'N'
                    if ($i < $firstDayStamp or $i > $lastDayStamp ) {
                        echo "<td style='background-color: #bbbbbb'>";
                        echo '</td>';

                        if ($i == $specialDayStamp) {
                            $rowSpecial = $resultSpecial->fetch();
                        }
                    } else {
                        if ($i == $specialDayStamp) {
                             echo "<td style='text-align: center; background-color: #eeeeee; font-size: 10px'>";
                            echo "<span style='color: #ff0000'>".dateConvertBack($guid, date('Y-m-d', $i)).'<br/>'.$rowSpecial['name'].'</span>';
                            echo '<br/>';
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/schoolYearSpecialDay_manage_edit.php&pupilsightSchoolYearSpecialDayID='.$rowSpecial['pupilsightSchoolYearSpecialDayID']."&pupilsightSchoolYearTermID=".$row['pupilsightSchoolYearTermID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID'><i title='Edit' class='mdi mdi-pencil-box-outline mdi-24px '></i></a> ";
                            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/schoolYearSpecialDay_manage_delete.php&pupilsightSchoolYearSpecialDayID='.$rowSpecial['pupilsightSchoolYearSpecialDayID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID&width=650&height=135'><i title='Delete' class='mdi mdi-trash-can-outline mdi-24px '></i></a> ";
                            $rowSpecial = $resultSpecial->fetch();
                            echo "</td>";
                        } else if( $days[date('D', $i)] == 'N') {
                              echo "<td style='background-color: #bbbbbb'>";
                        echo '</td>';
                        } else {
                             echo "<td style='text-align: center; background-color: #eeeeee; font-size: 10px'>";
                            echo "<span style='color: #000000'>".dateConvertBack($guid, date('Y-m-d', $i)).'<br/>'.__('School Day').'</span>';
                            echo '<br/>';
                            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/schoolYearSpecialDay_manage_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&dateStamp=".$i.'&pupilsightSchoolYearTermID='.$row['pupilsightSchoolYearTermID']."&firstDay=$firstDayStamp&lastDay=$lastDayStamp&addtype=1'><i title='Add' class='mdi mdi-plus-circle-outline mdi-24px'></i></a> ";
                            echo '</td>';
                        }
                    }

                    if (date('D', $i) == 'Sun') {
                        echo '</tr>';
                    }
                    ++$count;
                }

                echo '</table>';
            }
        }
    }
}
