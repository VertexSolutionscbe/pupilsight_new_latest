<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable/report_viewAvailableTeachers.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('View Available Teachers'));

    echo '<h2>';
    echo __('Choose Options');
    echo '</h2>';

    $pupilsightTTID = null;
    if (isset($_GET['pupilsightTTID'])) {
        $pupilsightTTID = $_GET['pupilsightTTID'];
    }
    $ttDate = null;
    if (isset($_GET['ttDate'])) {
        $ttDate = $_GET['ttDate'];
    }
    if ($ttDate == '') {
        $ttDate = date($_SESSION[$guid]['i18n']['dateFormatPHP']);
    }

    $viewBy = (isset($_GET['viewBy']))? $_GET['viewBy'] : '';

    $form = Form::create('viewAvailableTeachers', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/report_viewAvailableTeachers.php');

    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
    $sql = 'SELECT pupilsightTTID as value, name FROM pupilsightTT WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name';

    $row = $form->addRow();
        $row->addLabel('pupilsightTTID', __('Timetable'));
        $row->addSelect('pupilsightTTID')->fromQuery($pdo, $sql, $data)->required()->placeholder(__('Please select...'))->selected($pupilsightTTID);

    $row = $form->addRow();
        $row->addLabel('viewBy', __('View'));
        $row->addSelect('viewBy')->fromArray(array('username' => __('Username'), 'name' => __('Name') ))->selected($viewBy);

    $row = $form->addRow();
        $row->addLabel('ttDate', __('Date'));
        $row->addDate('ttDate')->setValue($ttDate);

    $row = $form->addRow();
        $row->addSubmit();

    echo $form->getOutput();


    if ($pupilsightTTID != '') {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightTTID' => $pupilsightTTID);
            $sql = 'SELECT * FROM pupilsightTT WHERE pupilsightTTID=:pupilsightTTID AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
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
            $row = $result->fetch();
            $startDayStamp = strtotime(dateConvert($guid, $ttDate));

            //Check which days are school days
            $daysInWeek = 0;
            $days = array();
            $timeStart = '';
            $timeEnd = '';
            try {
                $dataDays = array();
                $sqlDays = "SELECT * FROM pupilsightDaysOfWeek WHERE schoolDay='Y' ORDER BY sequenceNumber";
                $resultDays = $connection2->prepare($sqlDays);
                $resultDays->execute($dataDays);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            $days = $resultDays->fetchAll();
            $daysInWeek = $resultDays->rowCount();
            foreach ($days as $day) {
                if ($timeStart == '' or $timeEnd == '') {
                    $timeStart = $day['schoolStart'];
                    $timeEnd = $day['schoolEnd'];
                } else {
                    if ($day['schoolStart'] < $timeStart) {
                        $timeStart = $day['schoolStart'];
                    }
                    if ($day['schoolEnd'] > $timeEnd) {
                        $timeEnd = $day['schoolEnd'];
                    }
                }
            }

            //Count back to first dayOfWeek before specified calendar date
            while (date('D', $startDayStamp) != $days[0]['nameShort']) {
                $startDayStamp = $startDayStamp - 86400;
            }

            //Count forward to the end of the week
            $endDayStamp = $startDayStamp + (86400 * ($daysInWeek - 1));

            $schoolCalendarAlpha = 0.85;
            $ttAlpha = 1.0;

            //Max diff time for week based on timetables
            try {
                $dataDiff = array('date1' => date('Y-m-d', ($startDayStamp + (86400 * 0))), 'date2' => date('Y-m-d', ($endDayStamp + (86400 * 1))), 'pupilsightTTID' => $row['pupilsightTTID']);
                $sqlDiff = 'SELECT DISTINCT pupilsightTTColumn.pupilsightTTColumnID FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) WHERE (date>=:date1 AND date<=:date2) AND pupilsightTTID=:pupilsightTTID';
                $resultDiff = $connection2->prepare($sqlDiff);
                $resultDiff->execute($dataDiff);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            while ($rowDiff = $resultDiff->fetch()) {
                try {
                    $dataDiffDay = array('pupilsightTTColumnID' => $rowDiff['pupilsightTTColumnID']);
                    $sqlDiffDay = 'SELECT * FROM pupilsightTTColumnRow WHERE pupilsightTTColumnID=:pupilsightTTColumnID ORDER BY timeStart';
                    $resultDiffDay = $connection2->prepare($sqlDiffDay);
                    $resultDiffDay->execute($dataDiffDay);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                while ($rowDiffDay = $resultDiffDay->fetch()) {
                    if ($rowDiffDay['timeStart'] < $timeStart) {
                        $timeStart = $rowDiffDay['timeStart'];
                    }
                    if ($rowDiffDay['timeEnd'] > $timeEnd) {
                        $timeEnd = $rowDiffDay['timeEnd'];
                    }
                }
            }

            //Final calc
            $diffTime = strtotime($timeEnd) - strtotime($timeStart);
            $width = (ceil(690 / $daysInWeek) - 20).'px';

            $count = 0;

            echo "<table class='mini' cellspacing='0' style='width: 760px; margin: 0px 0px 30px 0px;'>";
            echo "<tr class='head'>";
            echo "<th style='vertical-align: top; width: 70px; text-align: center'>";
            //Calculate week number
            $week = getWeekNumber($startDayStamp, $connection2, $guid);
            if ($week != false) {
                echo __('Week').' '.$week.'<br/>';
            }
            echo "<span style='font-weight: normal; font-style: italic;'>".__('Time').'<span>';
            echo '</th>';
            $count = 0;
            foreach ($days as $day) {
                if ($count == 0) {
                    $firstSequence = $day['sequenceNumber'];
                }
                $dateCorrection = ($day['sequenceNumber'] - 1)-($firstSequence-1);

                echo "<th style='vertical-align: top; text-align: center; width: ".(550 / $daysInWeek)."px'>";
                echo __($day['nameShort']).'<br/>';
                echo "<span style='font-size: 80%; font-style: italic'>".date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $dateCorrection))).'</span><br/>';
                echo '</th>';
                $count++ ;
            }
            echo '</tr>';

            echo "<tr style='height:".(ceil($diffTime / 60) + 14)."px'>";
            echo "<td style='height: 300px; width: 75px; text-align: center; vertical-align: top'>";
            echo "<div style='position: relative; width: 71px'>";
            $countTime = 0;
            $time = $timeStart;
            echo "<div style='position: absolute; top: -3px; width: 71px ; border: none; height: 60px; margin: 0px; padding: 0px; font-size: 92%'>";
            echo substr($time, 0, 5).'<br/>';
            echo '</div>';
            $time = date('H:i:s', strtotime($time) + 3600);
            $spinControl = 0;
            while ($time <= $timeEnd and $spinControl < (23 - substr($timeStart, 0, 2))) {
                ++$countTime;
                echo "<div style='position: absolute; top:".(($countTime * 60) - 5)."px ; width: 71px ; border: none; height: 60px; margin: 0px; padding: 0px; font-size: 92%'>";
                echo substr($time, 0, 5).'<br/>';
                echo '</div>';
                $time = date('H:i:s', strtotime($time) + 3600);
                ++$spinControl;
            }

            echo '</div>';
            echo '</td>';

            //Check to see if week is at all in term time...if it is, then display the grid
            $isWeekInTerm = false;
            try {
                $dataTerm = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlTerm = 'SELECT pupilsightSchoolYearTerm.firstDay, pupilsightSchoolYearTerm.lastDay FROM pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $resultTerm = $connection2->prepare($sqlTerm);
                $resultTerm->execute($dataTerm);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            $weekStart = date('Y-m-d', ($startDayStamp + (86400 * 0)));
            $weekEnd = date('Y-m-d', ($startDayStamp + (86400 * 6)));
            while ($rowTerm = $resultTerm->fetch()) {
                if ($weekStart <= $rowTerm['firstDay'] and $weekEnd >= $rowTerm['firstDay']) {
                    $isWeekInTerm = true;
                } elseif ($weekStart >= $rowTerm['firstDay'] and $weekEnd <= $rowTerm['lastDay']) {
                    $isWeekInTerm = true;
                } elseif ($weekStart <= $rowTerm['lastDay'] and $weekEnd >= $rowTerm['lastDay']) {
                    $isWeekInTerm = true;
                }
            }
            if ($isWeekInTerm == true) {
                $blank = false;
            }

            //Run through days of the week
            foreach ($days as $day) {
                $dayOut = '';
                $zCount = 0;

                if ($day['schoolDay'] == 'Y') {
                    $dateCorrection = ($day['sequenceNumber'] - 1)-($firstSequence-1);

                    //Check to see if day is term time
                    $isDayInTerm = false;
                    try {
                        $dataTerm = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                        $sqlTerm = 'SELECT pupilsightSchoolYearTerm.firstDay, pupilsightSchoolYearTerm.lastDay FROM pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID';
                        $resultTerm = $connection2->prepare($sqlTerm);
                        $resultTerm->execute($dataTerm);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    while ($rowTerm = $resultTerm->fetch()) {
                        if (date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))) >= $rowTerm['firstDay'] and date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))) <= $rowTerm['lastDay']) {
                            $isDayInTerm = true;
                        }
                    }

                    if ($isDayInTerm == true) {
                        //Check for school closure day
                        try {
                            $dataClosure = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))));
                            $sqlClosure = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date and type='School Closure'";
                            $resultClosure = $connection2->prepare($sqlClosure);
                            $resultClosure->execute($dataClosure);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultClosure->rowCount() == 1) {
                            $rowClosure = $resultClosure->fetch();
                            $dayOut .= "<td style='text-align: center; vertical-align: top; font-size: 11px'>";
                            $dayOut .= "<div style='position: relative'>";
                            $dayOut .= "<div style='z-index: $zCount; position: absolute; top: 0; width: $width ; border: 1px solid rgba(136,136,136,$ttAlpha); height: ".ceil($diffTime / 60)."px; margin: 0px; padding: 0px; background-color: rgba(255,196,202,$ttAlpha)'>";
                            $dayOut .= "<div style='position: relative; top: 50%'>";
                            $dayOut .= "<span style='color: rgba(255,0,0,$ttAlpha);'>".$rowClosure['name'].'</span>';
                            $dayOut .= '</div>';
                            $dayOut .= '</div>';
                            $dayOut .= '</div>';
                            $dayOut .= '</td>';
                        } else {
                            $schoolCalendarAlpha = 0.85;
                            $ttAlpha = 1.0;

                            $date = date('Y/m/d', ($startDayStamp + (86400 * $dateCorrection)));

                            $output = '';
                            $blank = true;
                            //Get day start and end!
                            $dayTimeStart = '';
                            $dayTimeEnd = '';
                            try {
                                $dataDiff = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))), 'pupilsightTTID' => $pupilsightTTID);
                                $sqlDiff = 'SELECT timeStart, timeEnd FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumn.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID) WHERE date=:date AND pupilsightTTID=:pupilsightTTID';
                                $resultDiff = $connection2->prepare($sqlDiff);
                                $resultDiff->execute($dataDiff);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            while ($rowDiff = $resultDiff->fetch()) {
                                if ($dayTimeStart == '') {
                                    $dayTimeStart = $rowDiff['timeStart'];
                                }
                                if ($rowDiff['timeStart'] < $dayTimeStart) {
                                    $dayTimeStart = $rowDiff['timeStart'];
                                }
                                if ($dayTimeEnd == '') {
                                    $dayTimeEnd = $rowDiff['timeEnd'];
                                }
                                if ($rowDiff['timeEnd'] > $dayTimeEnd) {
                                    $dayTimeEnd = $rowDiff['timeEnd'];
                                }
                            }

                            $dayDiffTime = strtotime($dayTimeEnd) - strtotime($dayTimeStart);

                            $startPad = strtotime($dayTimeStart) - strtotime($timeStart);

                            $dayOut .= "<td style='text-align: center; vertical-align: top; font-size: 11px'>";
                            try {
                                $dataDay = array('pupilsightTTID' => $pupilsightTTID, 'date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))));
                                $sqlDay = 'SELECT pupilsightTTDay.pupilsightTTDayID FROM pupilsightTTDayDate JOIN pupilsightTTDay ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightTTID=:pupilsightTTID AND date=:date';
                                $resultDay = $connection2->prepare($sqlDay);
                                $resultDay->execute($dataDay);
                            } catch (PDOException $e) {
                                $dayOut .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            if ($resultDay->rowCount() == 1) {
                                $rowDay = $resultDay->fetch();
                                $zCount = 0;
                                $dayOut .= "<div style='position: relative;'>";

                                    //Draw outline of the day
                                    try {
                                        $dataPeriods = array('pupilsightTTDayID' => $rowDay['pupilsightTTDayID'], 'date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))));
                                        $sqlPeriods = 'SELECT pupilsightTTColumnRow.pupilsightTTColumnRowID, pupilsightTTColumnRow.name, timeStart, timeEnd, type, date FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) WHERE pupilsightTTDayDate.pupilsightTTDayID=:pupilsightTTDayID AND date=:date ORDER BY timeStart, timeEnd';
                                        $resultPeriods = $connection2->prepare($sqlPeriods);
                                        $resultPeriods->execute($dataPeriods);
                                    } catch (PDOException $e) {
                                        $dayOut .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                while ($rowPeriods = $resultPeriods->fetch()) {
                                    $isSlotInTime = false;
                                    if ($rowPeriods['timeStart'] <= $dayTimeStart and $rowPeriods['timeEnd'] > $dayTimeStart) {
                                        $isSlotInTime = true;
                                    } elseif ($rowPeriods['timeStart'] >= $dayTimeStart and $rowPeriods['timeEnd'] <= $dayTimeEnd) {
                                        $isSlotInTime = true;
                                    } elseif ($rowPeriods['timeStart'] < $dayTimeEnd and $rowPeriods['timeEnd'] >= $dayTimeEnd) {
                                        $isSlotInTime = true;
                                    }

                                    if ($isSlotInTime == true) {
                                        $effectiveStart = $rowPeriods['timeStart'];
                                        $effectiveEnd = $rowPeriods['timeEnd'];
                                        if ($dayTimeStart > $rowPeriods['timeStart']) {
                                            $effectiveStart = $dayTimeStart;
                                        }
                                        if ($dayTimeEnd < $rowPeriods['timeEnd']) {
                                            $effectiveEnd = $dayTimeEnd;
                                        }

                                        $width = (ceil(690 / $daysInWeek) - 20).'px';
                                        $height = ceil((strtotime($effectiveEnd) - strtotime($effectiveStart)) / 60).'px';
                                        $top = ceil(((strtotime($effectiveStart) - strtotime($dayTimeStart)) + $startPad) / 60).'px';
                                        $bg = "rgba(238,238,238,$ttAlpha)";
                                        if ((date('H:i:s') > $effectiveStart) and (date('H:i:s') < $effectiveEnd) and $rowPeriods['date'] == date('Y-m-d')) {
                                            $bg = "rgba(179,239,194,$ttAlpha)";
                                        }
                                        $style = '';
                                        if ($rowPeriods['type'] == 'Lesson') {
                                            $style = '';
                                        }
                                        $dayOut .= "<div style='color: rgba(0,0,0,$ttAlpha); z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid rgba(136,136,136, $ttAlpha); height: $height; margin: 0px; padding: 0px; background-color: $bg; color: rgba(136,136,136, $ttAlpha) $style'>";
                                        if ($height > 15) {
                                            $dayOut .= $rowPeriods['name'].'<br/>';
                                        }
                                        if ($rowPeriods['type'] == 'Lesson') {
                                            $vacancies = '';
                                            try {
                                                $dataSelect = array();
                                                $sqlSelect = "SELECT pupilsightPerson.pupilsightPersonID, initials, username, surname, preferredName FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' and type='Teaching' ORDER BY preferredName, surname, initials";
                                                $resultSelect = $connection2->prepare($sqlSelect);
                                                $resultSelect->execute($dataSelect);
                                            } catch (PDOException $e) {
                                            }
                                            while ($rowSelect = $resultSelect->fetch()) {
                                                try {
                                                    $dataUnique = array('pupilsightTTDayID' => $rowDay['pupilsightTTDayID'], 'pupilsightTTColumnRowID' => $rowPeriods['pupilsightTTColumnRowID'], 'pupilsightPersonID' => $rowSelect['pupilsightPersonID']);
                                                    $sqlUnique = "SELECT * FROM pupilsightTTDayRowClass JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightTTDayRowClass.pupilsightCourseClassID) LEFT JOIN pupilsightTTDayRowClassException ON (pupilsightTTDayRowClassException.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID AND pupilsightTTDayRowClassException.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightTTDayRowClassExceptionID IS NULL";
                                                    $resultUnique = $connection2->prepare($sqlUnique);
                                                    $resultUnique->execute($dataUnique);
                                                } catch (PDOException $e) {
                                                }
                                                if ($resultUnique->rowCount() < 1) {

                                                    if ($viewBy == 'name') {
                                                        $vacancies .= Format::name('', $rowSelect['preferredName'], $rowSelect['surname'], 'Staff').', ';
                                                    }
                                                    else if ($viewBy == 'username') {
                                                        $vacancies .= $rowSelect['username'].', ';
                                                    }
                                                    else if (isset($rowSelect['initials'])) {
                                                        $vacancies .= $rowSelect['initials'].', ';
                                                    } else {
                                                        $vacancies .= $rowSelect['username'].', ';
                                                    }
                                                }
                                            }
                                            $vacancies = substr($vacancies, 0, -2);
                                            $dayOut .= "<div title='".htmlPrep($vacancies)."' style='color: black; font-weight: normal;line-height: 0.9'>";
                                            if (strlen($vacancies) <= 50) {
                                                $dayOut .= $vacancies;
                                            } else {
                                                $dayOut .= substr($vacancies, 0, 50).'...';
                                            }

                                            $dayOut .= '</div>';
                                        }
                                        $dayOut .= '</div>';
                                        ++$zCount;
                                    }
                                }
                            }
                            $dayOut .= '</td>';
                        }
                    } else {
                        $dayOut .= "<td style='text-align: center; vertical-align: top; font-size: 11px'>";
                        $dayOut .= "<div style='position: relative'>";
                        $dayOut .= "<div style='position: absolute; top: 0; width: $width ; border: 1px solid rgba(136,136,136,$ttAlpha); height: ".ceil($diffTime / 60)."px; margin: 0px; padding: 0px; background-color: rgba(255,196,202,$ttAlpha)'>";
                        $dayOut .= "<div style='position: relative; top: 50%'>";
                        $dayOut .= "<span style='color: rgba(255,0,0,$ttAlpha);'>".__('School Closed').'</span>';
                        $dayOut .= '</div>';
                        $dayOut .= '</div>';
                        $dayOut .= '</div>';
                        $dayOut .= '</td>';
                    }

                    if ($day == '') {
                        $dayOut .= "<td style='text-align: center; vertical-align: top; font-size: 11px'></td>";
                    }

                    echo $dayOut;

                    ++$count;
                }
            }

            echo '</tr>';
            echo "<tr style='height: 1px'>";
            echo "<td style='vertical-align: top; width: 70px; text-align: center; border-top: 1px solid #888'>";
            echo '</td>';
            echo "<td colspan=$daysInWeek style='vertical-align: top; width: 70px; text-align: center; border-top: 1px solid #888'>";
            echo '</td>';
            echo '</tr>';
            echo '</table>';
        }
    }
}
