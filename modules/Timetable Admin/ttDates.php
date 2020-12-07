<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttDates.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Tie Days to Dates'));

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
        if ($result->rowcount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $schoolYear = $result->fetch();
            $pupilsightSchoolYearID = $schoolYear['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $schoolYear['name'];
        }
    }

    if ($pupilsightSchoolYearID != '') {
        echo '<h2>';
        echo $pupilsightSchoolYearName;
        echo '</h2>';
        echo '<p>';
        echo __('To multi-add a single timetable day to multiple dates, use the checkboxes in the relevant dates, and then press the Submit button at the bottom of the page.');
        echo '</p>';

        echo "<div class='linkTop'>";
            //Print year picker
            if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/ttDates.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
        echo ' | ';
        if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/ttDates.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
        } else {
            echo __('Next Year').' ';
        }
        echo '</div>';

        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {

            $form = Form::create('ttDates', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/ttDates_addMultiProcess.php?pupilsightSchoolYearID='.$pupilsightSchoolYearID);
            $form->setClass('w-full blank');
            
            $form->addHiddenValue('q', $_SESSION[$guid]['address']);

            while ($values = $result->fetch()) {
                $row = $form->addRow()->addHeading($values['name']);

                list($firstDayYear, $firstDayMonth, $firstDayDay) = explode('-', $values['firstDay']);
                $firstDayStamp = mktime(0, 0, 0, $firstDayMonth, $firstDayDay, $firstDayYear);
                list($lastDayYear, $lastDayMonth, $lastDayDay) = explode('-', $values['lastDay']);
                $lastDayStamp = mktime(0, 0, 0, $lastDayMonth, $lastDayDay, $lastDayYear);

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
                    $dataSpecial = array('pupilsightSchoolYearTermID' => $values['pupilsightSchoolYearTermID']);
                    $sqlSpecial = 'SELECT date, type, name FROM pupilsightSchoolYearSpecialDay WHERE pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID ORDER BY date';
                    $resultSpecial = $connection2->prepare($sqlSpecial);
                    $resultSpecial->execute($dataSpecial);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                $specialDays = $resultSpecial->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

                // Get the TT day names
                try {
                    $dataDay = array();
                    $sqlDay = 'SELECT date,pupilsightTTDay.pupilsightTTDayID, pupilsightTTDay.nameShort AS dayName, pupilsightTT.nameShort AS ttName FROM pupilsightTTDayDate JOIN pupilsightTTDay ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTT ON (pupilsightTTDay.pupilsightTTID=pupilsightTT.pupilsightTTID)';
                    $resultDay = $connection2->prepare($sqlDay);
                    $resultDay->execute($dataDay);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                $ttDays = $resultDay->fetchAll(\PDO::FETCH_GROUP);

				//Check which days are school days
                try {
                    $dataDays = array();
                    $sqlDays = "SELECT nameShort, schoolDay FROM pupilsightDaysOfWeek";
                    $resultDays = $connection2->prepare($sqlDays);
                    $resultDays->execute($dataDays);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                $days = $resultDays->fetchAll(\PDO::FETCH_KEY_PAIR);

                $count = 1;

                $table = $form->addRow()->addTable()->setClass('fullWidth');
                $row = $table->addHeaderRow();

                for ($i = 1; $i < 8; ++$i) {
                    $dowLong = date('l', strtotime("Sunday +$i days"));
                    $dowShort = date('D', strtotime("Sunday +$i days"));

                    $script = '<script type="text/javascript">';
                    $script .= '$(function () {';
                    $script .= "$('#checkall".$dowShort.$values['nameShort']."').click(function () {";
                    $script .= "if($('.".$dowShort.$values['nameShort'].":checkbox').attr('checked')){";
                    $script .= "$('.".$dowShort.$values['nameShort'].":checkbox').prop('checked',false)";
                    $script .='}else{';
                    $script .= "$('.".$dowShort.$values['nameShort'].":checkbox').attr('checked', this.checked);";
                    $script .='}';
                    $script .= '});';
                    $script .= '});';
                    $script .= '</script>';

                    // $column = $row->addColumn();
                    // $column->addContent()->addClass('textCenter');
                    $row->addCheckbox('checkall'.$dowShort.$values['nameShort'])->prepend(__($dowLong).'<br/>')->append($script)->addClass('textCenter');
                }

                for ($i = $startDayStamp; $i <= $endDayStamp;$i = strtotime('+1 day', $i)) {
                    $date = date('Y-m-d', $i);
                    $dayOfWeek = date('D', $i);
                    $formattedDate = date($_SESSION[$guid]['i18n']['dateFormatPHP'], $i);

                    if ($dayOfWeek == 'Mon') {
                        $row = $table->addRow();
                    }

                    if ($i < $firstDayStamp or $i > $lastDayStamp or $days[$dayOfWeek] == 'N') {
                        $row->addContent('')->addClass('ttDates textCenter');
                    } else {
                        if (isset($specialDays[$date]) and $specialDays[$date]['type'] == 'School Closure') {
                            $row->addContent($formattedDate)
                                ->append('<br/>')
                                ->append($specialDays[$date]['name'])
                                ->addClass('ttDates textCenter dull');
                        } else {
                            $column = $row->addColumn()->addClass('ttDates textCenter');
                            $column->addContent($formattedDate);
                            if (isset($specialDays[$date]) and $specialDays[$date]['type'] == 'Timing Change') {
                                $column->addContent(__('Timing Change'))->wrap('<span style="color: #f00" title="'.$specialDays[$date]['name'].'">', '</span>');
                            } else {
                                $column->addContent(__('School Day'));
                            }

                            $column->addCheckbox('dates[]')->setValue($i)->setClass($dayOfWeek.$values['nameShort']);

                            $column->addContent("<br/><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/ttDates_edit.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&dateStamp=".$i."'><img style='margin-top: 3px' title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a><br/>");

                            if (isset($ttDays[$date])) {
                                //$ii=1;

                                foreach ($ttDays[$date] as $day) {
                                    $pupilsightTTDayID=$day['pupilsightTTDayID'];
                                    //$column->addCheckbox('ttName['.$ii.']')->setValue($ii)->setClass($day['pupilsightTTDayID']);
                                    //$column->addContent("<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/ttDates_edit_delete.php&pupilsightTTDayID=$pupilsightTTDayID&pupilsightSchoolYearID=$pupilsightSchoolYearID&dateStamp=".$i."'><img style='margin-top: 3px' title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a><br/>");
                                    $column->addContent("<input type='checkbox' name='ttName[]' value='$pupilsightTTDayID~$pupilsightSchoolYearID~$i'>".$day['ttName'].' '.$day['dayName'])->wrap('<b>', '</b>');
                                    //$ii++;
                                }
                            }
                        }
                    }
                    ++$count;
                }
            }

            $form->addRow()->addHeading(__('Multi Add'));

            $data= array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = "SELECT pupilsightTTDay.pupilsightTTDayID as value, CONCAT(pupilsightTT.name, ': ', pupilsightTTDay.nameShort) as name
                    FROM pupilsightTTDay 
                    JOIN pupilsightTT ON (pupilsightTTDay.pupilsightTTID=pupilsightTT.pupilsightTTID) 
                    WHERE pupilsightTT.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY pupilsightTT.name, pupilsightTTDay.name";

            $table = $form->addRow()->addTable()->setClass('fullWidth smallIntBorder');
            $row = $table->addRow();
                $row->addLabel('pupilsightTTDayID', __('Day'));
                    $row->addSelect('pupilsightTTDayID')->fromQuery($pdo, $sql, $data)->addClass('mediumWidth')->selectMultiple()->required();

            $row = $table->addRow()->addClass('right');
                $row->addContent();
                $row->addSubmit();

            echo $form->getOutput();
        }

        $massdeleteurl=$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/".$_SESSION[$guid]['module']."/ttDates_edit_MassDeleteProcess.php";
        echo '<p>Please select data that needs to be deleted.</p>';
        echo "<h2><button style='background-color: #206bc4; color: white; padding: 3px; border: #206bc4;' type='button' id='move_to' value='get check box values'>Mass Delete</button></h2>";
        $script = '<script type="text/javascript">';
        $script .= '$("#move_to").on("click", function(e){
         if(confirm("Are you sure?")){   
        ';
        $script .= "e.preventDefault();";
        $script .= "";
        $script .= "$('#ttDates').attr('action', '$massdeleteurl').submit();
        }";
        $script .= '});';
        $script .= '</script>';
        echo $script;
    }
}
