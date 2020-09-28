<?php

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

/*
Pupilsight, Flexible & Open School System
*/

//Make the display for a block, according to the input provided, where $i is a unique number appended to the block's field ids.
//Mode can be masterAdd, masterEdit, workingDeploy, workingEdit, plannerEdit, embed
//Outcomes is the result set of a mysql query of all outcomes from the unit the class belongs to
function makeBlock($guid, $connection2, $i, $mode = 'masterAdd', $title = '', $type = '', $length = '', $contents = '', $complete = 'N', $pupilsightUnitBlockID = '', $pupilsightUnitClassBlockID = '', $teachersNotes = '', $outerBlock = true)
{
    if ($outerBlock) {
        echo "<div id='blockOuter$i' class='blockOuter'>";
    }
    if ($mode != 'embed') {
        ?>
		<style>
			.sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
			.sortable div.ui-state-default { margin: 0 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
			div.ui-state-default_dud { margin: 5px 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
			html>body .sortable li { min-height: 58px; line-height: 1.2em; }
			.sortable .ui-state-highlight { margin-bottom: 5px; min-height: 72px; line-height: 1.2em; width: 100%; }
		</style>

		<script type='text/javascript'>
			$(function() {
				$( ".sortable" ).sortable({
					placeholder: "ui-state-highlight"
				});

				$( ".sortable" ).bind( "sortstart", function(event, ui) {
					$("#blockInner<?php echo $i ?>").css("display","none") ;
					$("#block<?php echo $i ?>").css("height","72px") ;
					$('#show<?php echo $i ?>').css("background-image", "<?php echo "url(\'".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png\'"?>)");
					tinyMCE.execCommand('mceRemoveEditor', false, 'contents<?php echo $i ?>') ;
					tinyMCE.execCommand('mceRemoveEditor', false, 'teachersNotes<?php echo $i ?>') ;
					$(".sortable").sortable( "refresh" ) ;
					$(".sortable").sortable( "refreshPositions" ) ;
				});
			});

		</script>
		<script type='text/javascript'>
			$(document).ready(function(){
				$("#blockInner<?php echo $i ?>").css("display","none");
				$("#block<?php echo $i ?>").css("height","72px")

				//Block contents control
				$('#show<?php echo $i ?>').unbind('click').click(function() {
					if ($("#blockInner<?php echo $i ?>").is(":visible")) {
						$("#blockInner<?php echo $i ?>").css("display","none");
						$("#block<?php echo $i ?>").css("height","72px")
						$('#show<?php echo $i ?>').css("background-image", "<?php echo "url(\'".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png\'"?>)");
						tinyMCE.execCommand('mceRemoveEditor', false, 'contents<?php echo $i ?>') ;
						tinyMCE.execCommand('mceRemoveEditor', false, 'teachersNotes<?php echo $i ?>') ;
					} else {
						$("#blockInner<?php echo $i ?>").slideDown("fast", $("#blockInner<?php echo $i ?>").css("display","table-row"));
						$("#block<?php echo $i ?>").css("height","auto")
						$('#show<?php echo $i ?>').css("background-image", "<?php echo "url(\'".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/minus.png\'"?>)");
						tinyMCE.execCommand('mceRemoveEditor', false, 'contents<?php echo $i ?>') ;
						tinyMCE.execCommand('mceAddEditor', false, 'contents<?php echo $i ?>') ;
						tinyMCE.execCommand('mceRemoveEditor', false, 'teachersNotes<?php echo $i ?>') ;
						tinyMCE.execCommand('mceAddEditor', false, 'teachersNotes<?php echo $i ?>') ;
					}
				});

				$('#delete<?php echo $i ?>').unbind('click').click(function() {
					if (confirm("<?php echo __('Are you sure you want to delete this record?') ?>")) {
						$('#block<?php echo $i ?>').fadeOut(600, function(){ $('#block<?php echo $i ?>').remove(); });
					}
				});
			});
		</script>
		<?php

    }
    ?>
	<div class='hiddenReveal' style='border: 1px solid #d8dcdf; margin: 0 0 5px' id="block<?php echo $i ?>" style='padding: 0px'>
		<table class='blank' cellspacing='0' style='width: 100%'>
			<tr>
				<td style='width: 50%'>
					<input name='order[]' type='hidden' value='<?php echo $i ?>'>
					<input <?php if ($mode == 'embed') { echo 'readonly'; } ?> maxlength=100 id='title<?php echo $i ?>' name='title<?php echo $i ?>' type='text' style='float: left; border: 1px dotted #aaa; background: none; margin-left: 3px; margin-top: 0px; font-size: 140%; font-weight: bold; width: 350px' value='<?php if ($mode != 'masterAdd') { echo htmlPrep($title); }?>' placeholder='<?php echo __('Title'); ?>'><br/>
					<input <?php if ($mode == 'embed') { echo 'readonly'; } ?> maxlength=50 id='type<?php echo $i ?>' name='type<?php echo $i ?>' type='text' style='float: left; border: 1px dotted #aaa; background: none; margin-left: 3px; margin-top: 2px; font-size: 110%; font-style: italic; width: 250px' value='<?php if ($mode != 'masterAdd') { echo htmlPrep($type); }?>' placeholder='<?php echo __('Type (e.g. discussion, outcome)'); ?>'>
					<input <?php if ($mode == 'embed') { echo 'readonly'; } ?> maxlength=3 id='length<?php echo $i ?>' name='length<?php echo $i ?>' type='text' style='float: left; border: 1px dotted #aaa; background: none; margin-left: 3px; margin-top: 2px; font-size: 110%; font-style: italic; width: 95px' value='<?php if ($mode != 'masterAdd') { echo htmlPrep($length); }?>' placeholder='<?php echo __('Length (min.)'); ?>'>
				</td>
				<td style='text-align: right; width: 50%'>
					<div style='margin-bottom: 5px'>
						<?php
                        if ($mode != 'plannerEdit' and $mode != 'embed') {
							echo "<img style='margin-top: 2px' id='delete$i' title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/> ";
						}
						if ($mode == 'workingEdit') {
                            echo "<a onclick='return confirm(\"".__('Are you sure you want to leave this page? Any unsaved changes will be lost.')."\")' style='margin-right: 2px; font-weight: normal; font-style: normal; color: #fff' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/units_edit_working_copyback.php&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'].'&pupilsightCourseID='.$_GET['pupilsightCourseID'].'&pupilsightCourseClassID='.$_GET['pupilsightCourseClassID'].'&pupilsightUnitID='.$_GET['pupilsightUnitID']."&pupilsightUnitBlockID=$pupilsightUnitBlockID&pupilsightUnitClassBlockID=$pupilsightUnitClassBlockID&pupilsightUnitClassID=".$_GET['pupilsightUnitClassID']."'><img id='copyback$i' title='Copy Back' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/copyback.png'/></a>";
						}
						if ($mode != 'embed') {
							echo "<div title='".__('Show/Hide Details')."' id='show$i' style='margin-right: 3px; margin-top: 3px; margin-left: 3px; padding-right: 1px; float: right; width: 25px; height: 25px; background-image: url(\"".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png\"); background-repeat: no-repeat'></div></br>";
						}
						?>
					</div>
					<?php
                    if ($mode == 'plannerEdit') {
                        echo '</br>';
                    }
					if ($mode != 'embed') {
						?>
						<div style='margin-right: 5px'>Complete? <input id='complete<?php echo $i ?>' name='complete<?php echo $i ?>' style='margin-right: 2px' type="checkbox" <?php if ($mode == 'masterAdd' or $mode == 'masterEdit') { echo 'disabled';
					} else {
						if ($complete == 'Y') {
							echo 'checked';
						}
					}
						?>></div>
					<?php

					}
					?>
					<input type='hidden' name='pupilsightUnitBlockID<?php echo $i ?>' value='<?php echo $pupilsightUnitBlockID ?>'>
					<input type='hidden' name='pupilsightUnitClassBlockID<?php echo $i ?>' value='<?php echo $pupilsightUnitClassBlockID ?>'>
				</td>
			</tr>
			<tr id="blockInner<?php echo $i ?>">
				<td colspan=2 style='vertical-align: top'>
					<?php
                    if ($mode == 'masterAdd') {
                        $contents = getSettingByScope($connection2, 'Planner', 'smartBlockTemplate');
                    }
    				echo "<div style='text-align: left; font-weight: bold; margin-top: 15px'>".__('Block Contents').'</div>';
                    //Block Contents
                    if ($mode != 'embed') {
                        echo getEditor($guid, false, "contents$i", $contents, 20, true, false, false, true);
                    } else {
                        echo "<div style='max-width: 595px; margin-right: 0!important; padding: 5px!important'><p>$contents</p></div>";
                    }

                    //Teacher's Notes
                    if ($mode != 'embed') {
                        echo "<div style='text-align: left; font-weight: bold; margin-top: 15px'>".__('Teacher\'s Notes').'</div>';
                        echo getEditor($guid, false, "teachersNotes$i", $teachersNotes, 20, true, false, false, true);
                    } elseif ($teachersNotes != '') {
                        echo "<div style='text-align: left; font-weight: bold; margin-top: 15px'>".__('Teacher\'s Notes').'</div>';
                        echo "<div style='max-width: 595px; margin-right: 0!important; padding: 5px!important; background-color: #F6CECB'><p>$teachersNotes</p></div>";
                    }
   				 	?>
				</td>
			</tr>
		</table>
	</div>
	<?php
    if ($outerBlock) {
        echo '</div>';
    }
}

function getThread($guid, $connection2, $pupilsightPlannerEntryID, $parent, $level, $self, $viewBy, $subView, $date, $class, $pupilsightCourseClassID, $search, $role, $links = true, $narrow = false)
{
    $output = '';

    try {
        if ($parent == null) {
            $dataDiscuss = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
            $sqlDiscuss = 'SELECT pupilsightPlannerEntryDiscuss.*, title, surname, preferredName, category FROM pupilsightPlannerEntryDiscuss JOIN pupilsightPerson ON (pupilsightPlannerEntryDiscuss.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPlannerEntryDiscussIDReplyTo IS NULL ORDER BY timestamp';
        } else {
            $dataDiscuss = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPlannerEntryDiscussIDReplyTo' => $parent, 'pupilsightPlannerEntryDiscussID' => $self);
            $sqlDiscuss = 'SELECT pupilsightPlannerEntryDiscuss.*, title, surname, preferredName, category FROM pupilsightPlannerEntryDiscuss JOIN pupilsightPerson ON (pupilsightPlannerEntryDiscuss.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPlannerEntryDiscussIDReplyTo=:pupilsightPlannerEntryDiscussIDReplyTo AND pupilsightPlannerEntryDiscussID=:pupilsightPlannerEntryDiscussID ORDER BY timestamp';
        }
        $resultDiscuss = $connection2->prepare($sqlDiscuss);
        $resultDiscuss->execute($dataDiscuss);
    } catch (PDOException $e) {
        $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($level == 0 and $resultDiscuss->rowCount() == 0) {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('There are no records to display.');
        $output .= '</div>';
    } else {
        while ($rowDiscuss = $resultDiscuss->fetch()) {
            $classExtra = '';
            $namePerson = __('{name} said', [
                'name' => formatName($rowDiscuss['title'], $rowDiscuss['preferredName'], $rowDiscuss['surname'], $rowDiscuss['category'])
            ]);
            $datetimePosted = __('Posted at {hourPosted} on {datePosted}', [
                'hourPosted' => '<b>'.substr($rowDiscuss['timestamp'], 11, 5).'</b>', 
                'datePosted' => '<b>'.dateConvertBack($guid, substr($rowDiscuss['timestamp'], 0, 10)).'</b>'
            ]);
            if ($level == 0) {
                $classExtra = 'chatBoxFirst';
            }
            $output .= "<a name='".$rowDiscuss['pupilsightPlannerEntryDiscussID']."'></a>";
            $width = (752 - ($level * 15));
            if ($narrow) {
                $width = (705 - ($level * 15));
            }
            $output .= "<table class='noIntBorder chatBox $classExtra' cellspacing='0' style='width: ".$width.'px; margin-left: '.($level * 15)."px'>";
            $output .= "<tr>";
            $output .= "<td><i>".$namePerson.'</i>:</td>';
            $output .= "<td style='text-align: right'><i>".$datetimePosted."</i></td>";
            $output .= "</tr>";
            $output .= "<tr>";
            $output .= "<td style='max-width: ".(700 - ($level * 15))."px;' colspan=2><b>".$rowDiscuss['comment'].'</b></td>';
            $output .= "</tr>";
            $output .= "<tr>";
            if ($links == true) {
                $output .= "<td style='text-align: right' colspan=2><a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full_post.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&viewBy=$viewBy&subView=$subView&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&width=1000&height=550&replyTo=".$rowDiscuss['pupilsightPlannerEntryDiscussID']."&search=$search'>Reply</a> ";
                if ($role == 'Teacher') {
                    $output .= " | <a href='".$_SESSION[$guid]['absoluteURL']."/modules/Planner/planner_view_full_post_deleteProcess.php?pupilsightPlannerEntryID=$pupilsightPlannerEntryID&viewBy=$viewBy&subView=$subView&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&width=1000&height=550&search=$search&pupilsightPlannerEntryDiscussID=".$rowDiscuss['pupilsightPlannerEntryDiscussID']."'>Delete</a>";
                }
                $output .= "</td>";
            }
            $output .= "</tr>";
            $output .= "</table>";

            //Get any replies
            $replies = true;
            try {
                $dataReplies = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPlannerEntryDiscussIDReplyTo' => $rowDiscuss['pupilsightPlannerEntryDiscussID']);
                $sqlReplies = 'SELECT pupilsightPlannerEntryDiscuss.*, title, surname, preferredName FROM pupilsightPlannerEntryDiscuss JOIN pupilsightPerson ON (pupilsightPlannerEntryDiscuss.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPlannerEntryDiscussIDReplyTo=:pupilsightPlannerEntryDiscussIDReplyTo ORDER BY timestamp';
                $resultReplies = $connection2->prepare($sqlReplies);
                $resultReplies->execute($dataReplies);
            } catch (PDOException $e) {
                $replies = false;
                $output .= print "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($replies) {
                while ($rowReplies = $resultReplies->fetch()) {
                    $output .= getThread($guid, $connection2, $pupilsightPlannerEntryID, $rowDiscuss['pupilsightPlannerEntryDiscussID'], ($level + 1), $rowReplies['pupilsightPlannerEntryDiscussID'], $viewBy, $subView, $date, $class, $pupilsightCourseClassID, $search, $role, $links);
                }
            }
        }
    }

    return $output;
}

function sidebarExtra($guid, $connection2, $todayStamp, $pupilsightPersonID, $dateStamp = '', $pupilsightCourseClassID = '')
{
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        $output = "<div class='alert alert-danger'>";
        $output .= __('The highest grouped action cannot be determined.');
        $output .= '</div>';
    } else {
        //Show date picker in sidebar
        $output = '<div class="column-no-break">';
        $output .= "<h2 class='sidebar'>";
        $output .= __('Choose A Date');
        $output .= '</h2>';

        //Count back to first Monday before first day
        $startDayStamp = $todayStamp;
        while (date('D', $startDayStamp) != 'Mon') {
            $startDayStamp = $startDayStamp - 86400;
        }

        //Count forward 6 weeks after start day
        $endDayStamp = $startDayStamp + (86400 * 41);

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

        $output .= "<table class='mini' cellspacing='0' style='width: 250px; margin-bottom: 0px;display:none;visibility:hidden;'>";
        $output .= "<tr class='head'>";
        $output .= "<th style='width: 35px; text-align: center'>";
        $output .= __('Mon');
        $output .= '</th>';
        $output .= "<th style='width: 35px; text-align: center'>";
        $output .= __('Tue');
        $output .= '</th>';
        $output .= "<th style='width: 35px; text-align: center'>";
        $output .= __('Wed');
        $output .= '</th>';
        $output .= "<th style='width: 35px; text-align: center'>";
        $output .= __('Thu');
        $output .= '</th>';
        $output .= "<th style='width: 35px; text-align: center'>";
        $output .= __('Fri');
        $output .= '</th>';
        $output .= "<th style='width: 35px; text-align: center'>";
        $output .= __('Sat');
        $output .= '</th>';
        $output .= "<th style='width: 35px; text-align: center'>";
        $output .= __('Sun');
        $output .= '</th>';
        $output .= '</tr>';

        for ($i = $startDayStamp;$i <= $endDayStamp;$i = $i + 86400) {
            if (date('D', $i) == 'Mon') {
                $output .= "<tr style='height: 25px'>";
            }

            if ($days[date('D', $i)] == 'N' or isSchoolOpen($guid, date('Y-m-d', $i), $connection2) == false) {
                $output .= "<td style='text-align: center; background-color: #bbbbbb; font-size: 10px; color: #858586'>";
                if ($i == $dateStamp) {
                    $output .= "<span style='border: 1px solid #ffffff; padding: 0px 2px 0px 1px'>".date('d', $i).'</span><br/>';
                    $output .= "<span style='font-size: 65%'>".date('M', $i).'</span>';
                } else {
                    $output .= date('d', $i).'<br/>';
                    $output .= "<span style='font-size: 65%'>".date('M', $i).'</span>';
                }
                $output .= '</td>';
            } else {
                $output .= "<td style='text-align: center; background-color: #eeeeee; font-size: 10px'>";
                if ($i == $dateStamp) {
                    if ($i == $todayStamp) {
                        $output .= "<a style='color: #6B99CE; font-weight: bold; text-decoration: none' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner.php&search=$pupilsightPersonID&date=".date('Y-m-d', $i)."'>";
                        $output .= "<span style='border: 1px solid #cc0000; padding: 0px 2px 0px 1px'>".date('d', $i).'</span><br/>';
                        $output .= "<span style='font-size: 65%'>".date('M', $i).'</span>';
                        $output .= '</a>';
                    } else {
                        $output .= "<a style='text-decoration: none' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner.php&search=$pupilsightPersonID&date=".date('Y-m-d', $i)."'>";
                        $output .= "<span style='border: 1px solid #cc0000; padding: 0px 2px 0px 1px'>".date('d', $i).'</span><br/>';
                        $output .= "<span style='font-size: 65%'>".date('M', $i).'</span>';
                        $output .= '</a>';
                    }
                } else {
                    if ($i == $todayStamp) {
                        $output .= "<a style='color: #6B99CE; font-weight: bold; text-decoration: none' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner.php&search=$pupilsightPersonID&date=".date('Y-m-d', $i)."'>";
                        $output .= date('d', $i).'<br/>';
                        $output .= "<span style='font-size: 65%'>".date('M', $i).'</span>';
                        $output .= '</a>';
                    } else {
                        $output .= "<a style='text-decoration: none' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner.php&search=$pupilsightPersonID&date=".date('Y-m-d', $i)."'>";
                        $output .= date('d', $i).'<br/>';
                        $output .= "<span style='font-size: 65%'>".date('M', $i).'</span>';
                        $output .= '</a>';
                    }
                }
                $output .= '</td>';
            }

            if (date('D', $i) == 'Sun') {
                $output .= '</tr>';
            }
            ++$count;
        }
        $output .= '</table>';

        $output .= "<form method='get' action='".$_SESSION[$guid]['absoluteURL']."/index.php'>";
        $output .= "<table class='smallIntBorder' cellspacing='0' style='width: 200px; margin: 0px 0px'>";
        $output .= '<tr>';
        $output .= "<td style='width: 200px'>";
        $output .= "<input name='q' id='q' type='hidden' value='/modules/Planner/planner.php'>";
        $output .= "<input name='search' id='search' type='hidden' value='$pupilsightPersonID'>";
        if ($dateStamp == '') {
            $dateHuman = '';
        } else {
            $dateHuman = date($_SESSION[$guid]['i18n']['dateFormatPHP'], $dateStamp);
        }
        $output .= "<input name='dateHuman' id='dateHuman' maxlength=20 type='text' value='$dateHuman' style='width:161px'>";
        $output .= "<script type='text/javascript'>";
        $output .= '$(function() {';
        $output .= "$('#dateHuman').datepicker();";
        $output .= '});';
        $output .= '</script>';
        $output .= '</td>';
        $output .= "<td class='right'>";
        $output .= "<input type='submit' class='btn btn-primary' value='".__('Go')."'>";
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';
        $output .= '</form>';
        $output .= '</div>';

        global $pdo;

        $form = Form::create('classChooser', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setTitle(__('Choose A Class'));
        $form->setClass('smallIntBorder w-full');

        $form->addHiddenValue('q', '/modules/Planner/planner.php');
        $form->addHiddenValue('search', $pupilsightPersonID);
        $form->addHiddenValue('viewBy', 'class');

        $classes = [];

        $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
        $sqlSelect = "SELECT pupilsightCourseClass.pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID ORDER BY pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";
        $resultSelect = $connection2->prepare($sqlSelect);
        $resultSelect->execute($dataSelect);

        if ($resultSelect->rowCount() > 0) {
            $classes[__('My Classes')] = $resultSelect->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        if ($highestAction == 'Lesson Planner_viewEditAllClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses') {
            $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlSelect = "SELECT pupilsightCourseClass.pupilsightCourseClassID, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";
            $resultSelect = $connection2->prepare($sqlSelect);
            $resultSelect->execute($dataSelect);

            if ($resultSelect->rowCount() > 0) {
                $classes[__('All Classes')] = $resultSelect->fetchAll(PDO::FETCH_KEY_PAIR);
            }
        }

        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        $program=array();  
        $program2=array();  
        $program1=array(''=>'Select Program');
        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program= $program1 + $program2; 

        if(!empty($_GET['pupilsightProgramID'])){
            $pupilsightProgramID = $_GET['pupilsightProgramID'];
            $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
            $result = $connection2->query($sql);
            $classesdata = $result->fetchAll();

            $classes = array();
            $classes2 = array();
            $classes1 = array('' => 'Select Class');
            foreach ($classesdata as $ct) {
                $classes2[$ct['pupilsightYearGroupID']] = $ct['name'];
            }
            $classes = $classes1 + $classes2;
        } else {
            $pupilsightProgramID = '';
        }

        $row = $form->addRow();

        $col = $row->addColumn()->setClass('newdes');
        //$col->addLabel('pupilsightProgramID', __('Program')); 
        $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->placeholder();

        $col = $row->addColumn()->setClass('newdes');
        //$col->addLabel('pupilsightYearGroupID', __('Class'));
        $col->addSelect('pupilsightCourseClassID')->setId('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightCourseClassID)->placeholder('Select Class');

        // $col = $row->addSelect('pupilsightCourseClassID', $_SESSION[$guid]['pupilsightSchoolYearID'], $pupilsightPersonID)
        //         ->setID('pupilsightCourseClassIDSidebar')
        //         ->fromArray($classes)
        //         ->selected($pupilsightCourseClassID)
        //         ->placeholder()
        //         ->setClass('float-none w-48');
        //$col = $row->addColumn()->setClass('newdes');
        //$col->addLabel('', __(''));
        $col = $row->addSubmit(__('Go'));

        $output .= $form->getOutput();


        if ($_GET['q'] != '/modules/Planner/planner_deadlines.php') {
            //Show upcoming deadlines
            $output .= '<div class="column-no-break">';
            $output .= '<h2>';
            $output .= __('Homework & Deadlines');
            $output .= '</h2>';

            try {
                if ($highestAction == 'Lesson Planner_viewMyChildrensClasses') {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID, 'dateTime' => date('Y-m-d H:i:s'), 'date1' => date('Y-m-d'), 'date2' => date('Y-m-d'), 'timeEnd' => date('H:i:s'));
                    $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND role='Student' AND viewableParents='Y' AND homeworkDueDateTime>:dateTime AND ((date<:date1) OR (date=:date2 AND timeEnd<=:timeEnd)) ORDER BY homeworkDueDateTime";
                } elseif ($highestAction == 'Lesson Planner_viewEditAllClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewMyClasses') {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID, 'dateTime' => date('Y-m-d H:i:s'), 'date1' => date('Y-m-d'), 'date2' => date('Y-m-d'), 'timeEnd' => date('H:i:s'));
                    $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND (role='Teacher' OR (role='Student' AND viewableStudents='Y')) AND homeworkDueDateTime>:dateTime AND ((date<:date1) OR (date=:date2 AND timeEnd<=:timeEnd)) ORDER BY homeworkDueDateTime";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() < 1) {
                $output .= "<div class='alert alert-sucess'>";
                $output .= __('No upcoming deadlines!');
                $output .= '</div>';
            } else {
                $output .= '<ol>';
                $count = 0;
                while ($row = $result->fetch()) {
                    if ($count < 5) {
                        $diff = (strtotime(substr($row['homeworkDueDateTime'], 0, 10)) - strtotime(date('Y-m-d'))) / 86400;
                        $style = "style='padding-right: 3px;'";
                        if ($diff < 2) {
                            $style = "style='padding-right: 3px; border-right: 10px solid #cc0000'";
                        } elseif ($diff < 4) {
                            $style = "style='padding-right: 3px; border-right: 10px solid #D87718'";
                        }
                        $output .= "<li $style>";
                        $output .= "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/planner_view_full.php&search=$pupilsightPersonID&pupilsightPlannerEntryID=".$row['pupilsightPlannerEntryID'].'&viewBy=date&date='.$row['date']."&width=1000&height=550'>".$row['course'].'.'.$row['class'].'</a><br/>';
                        $output .= "<span style='font-style: italic'>Due at ".substr($row['homeworkDueDateTime'], 11, 5).' on '.dateConvertBack($guid, substr($row['homeworkDueDateTime'], 0, 10));
                        $output .= '</li>';
                    }
                    ++$count;
                }
                $output .= '</ol>';
            }

            $output .= "<p style='padding-top: 15px; text-align: right'>";
            $output .= "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_deadlines.php&search=$pupilsightPersonID'>View Homework</a>";
            $output .= '</p>';
            $output .= '</div>';
        }
    }

    $_SESSION[$guid]['sidebarExtraPosition'] = 'bottom';

    return $output;
}

function sidebarExtraUnits($guid, $connection2, $pupilsightProgramID, $pupilsightCourseID, $pupilsightSchoolYearID)
{
    $output = '';
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        $output = "<div class='alert alert-danger'>";
        $output .= __('The highest grouped action cannot be determined.');
        $output .= '</div>';
    } else {
        //Show class picker in sidebar

        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        $program=array();  
        $program2=array();  
        $program1=array(''=>'Select Program');
        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program= $program1 + $program2; 

        if(!empty($pupilsightProgramID)){
            $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
            $result = $connection2->query($sql);
            $classesdata = $result->fetchAll();

        } else {
            $pupilsightProgramID = '';
        }

        $output .= '<h2>';
        $output .= __('Choose A Class');
        $output .= '</h2>';

        $selectCount = 0;
        $output .= "<form method='get' action='".$_SESSION[$guid]['absoluteURL']."/index.php'>";
        $output .= "<table class='mini' cellspacing='0' style='width: 100%; margin: 0px 0px'>";
        $output .= '<tr>';
        $output .= "<td style='width: 190px'>";
        $output .= "<input name='q' id='q' type='hidden' value='/modules/Planner/units.php'>";
        $output .= "<input name='pupilsightSchoolYearID' id='pupilsightSchoolYearID' type='hidden' value='$pupilsightSchoolYearID'>";
        $output .= "<select name='pupilsightProgramID' id='pupilsightProgramID' style='width:161px'>";
        $output .= "<option >Select Program</option>";
        foreach($rowdataprog as $dt){
            if($dt['pupilsightProgramID'] == $pupilsightProgramID){
                $prgSelected = 'selected';
            } else {
                $prgSelected = '';
            }
            $output .= "<option $prgSelected value='".$dt['pupilsightProgramID']."'>".$dt['name'].'</option>';
        }
        $output .= '</select>';
        $output .= '</td>';

        $output .= "<td style='width: 190px'>";
        $output .= "<select name='pupilsightCourseID' id='pupilsightYearGroupID' style='width:161px'>";
        $output .= "<option >Select Class</option>";

        if(!empty($classesdata)){
            foreach ($classesdata as $ct) {
                if($ct['pupilsightYearGroupID'] == $pupilsightCourseID){
                    $clsSelected = 'selected';
                } else {
                    $clsSelected = '';
                }
                $output .= "<option $clsSelected value='".$ct['pupilsightYearGroupID']."'>".$ct['name'].'</option>';
            }
        }
        // try {
        //     if ($highestAction == 'Unit Planner_all') {
        //         $dataSelect = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        //         $sqlSelect = 'SELECT pupilsightCourse.nameShort AS course, pupilsightSchoolYear.name AS year, pupilsightCourseID FROM pupilsightCourse JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY nameShort';
        //     } elseif ($highestAction == 'Unit Planner_learningAreas') {
        //         $dataSelect = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        //         $sqlSelect = "SELECT pupilsightCourse.nameShort AS course, pupilsightSchoolYear.name AS year, pupilsightCourseID FROM pupilsightCourse JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY pupilsightCourse.nameShort";
        //     }
        //     $resultSelect = $connection2->prepare($sqlSelect);
        //     $resultSelect->execute($dataSelect);
        // } catch (PDOException $e) {
        // }
        // while ($rowSelect = $resultSelect->fetch()) {
        //     $selected = '';
        //     if ($rowSelect['pupilsightCourseID'] == $pupilsightCourseID) {
        //         $selected = 'selected';
        //         ++$selectCount;
        //     }
        //     $output .= "<option $selected value='".$rowSelect['pupilsightCourseID']."'>".htmlPrep($rowSelect['course']).' ('.htmlPrep($rowSelect['year']).')</option>';
        // }
        $output .= '</select>';
        $output .= '</td>';
        $output .= "<td class='right'>";
        $output .= "<input type='submit' class='btn btn-primary' value='".__('Go')."'>";
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';
        $output .= '</form>';
    }

    $_SESSION[$guid]['sidebarExtraPosition'] = 'bottom';

    return $output;
}

//Make the display for a block, according to the input provided, where $i is a unique number appended to the block's field ids.
function makeBlockOutcome($guid,  $i, $type = '', $pupilsightOutcomeID = '', $title = '', $category = '', $contents = '', $id = '', $outerBlock = true, $allowOutcomeEditing = 'Y')
{
    if ($outerBlock) {
        echo "<div id='".$type."blockOuter$i' class='blockOuter'>";
    }
    ?>
		<script>
			$(function() {
				$( "#<?php echo $type ?>" ).sortable({
					placeholder: "<?php echo $type ?>-ui-state-highlight"
				});

				$( "#<?php echo $type ?>" ).bind( "sortstart", function(event, ui) {
					$("#<?php echo $type ?>BlockInner<?php echo $i ?>").css("display","none");
					$("#<?php echo $type ?>Block<?php echo $i ?>").css("height","72px") ;
					$('#<?php echo $type ?>show<?php echo $i ?>').css("background-image", "<?php echo "url(\'".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png\'"?>)");
					tinyMCE.execCommand('mceRemoveEditor', false, '<?php echo $type ?>contents<?php echo $i ?>') ;
					$("#<?php echo $type ?>").sortable( "refreshPositions" ) ;
				});

				$( "#<?php echo $type ?>" ).bind( "sortstop", function(event, ui) {
					//This line has been removed to improve performance with long lists
					//tinyMCE.execCommand('mceAddEditor', false, '<?php echo $type ?>contents<?php echo $i ?>') ;
					$("#<?php echo $type ?>Block<?php echo $i ?>").css("height","72px") ;
				});
			});
		</script>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#<?php echo $type ?>BlockInner<?php echo $i ?>").css("display","none");
				$("#<?php echo $type ?>Block<?php echo $i ?>").css("height","72px") ;

				//Block contents control
				$('#<?php echo $type ?>show<?php echo $i ?>').unbind('click').click(function() {
					if ($("#<?php echo $type ?>BlockInner<?php echo $i ?>").is(":visible")) {
						$("#<?php echo $type ?>BlockInner<?php echo $i ?>").css("display","none");
						$("#<?php echo $type ?>Block<?php echo $i ?>").css("height","72px") ;
						$('#<?php echo $type ?>show<?php echo $i ?>').css("background-image", "<?php echo "url(\'".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png\'"?>)");
						tinyMCE.execCommand('mceRemoveEditor', false, '<?php echo $type ?>contents<?php echo $i ?>') ;
					} else {
						$("#<?php echo $type ?>BlockInner<?php echo $i ?>").slideDown("fast", $("#<?php echo $type ?>BlockInner<?php echo $i ?>").css("display","table-row"));
						$("#<?php echo $type ?>Block<?php echo $i ?>").css("height","auto")
						$('#<?php echo $type ?>show<?php echo $i ?>').css("background-image", "<?php echo "url(\'".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/minus.png\'"?>)");
						tinyMCE.execCommand('mceRemoveEditor', false, '<?php echo $type ?>contents<?php echo $i ?>') ;
						tinyMCE.execCommand('mceAddEditor', false, '<?php echo $type ?>contents<?php echo $i ?>') ;
					}
				});

				$('#<?php echo $type ?>delete<?php echo $i ?>').unbind('click').click(function() {
					if (confirm("Are you sure you want to delete this record?")) {
						$('#<?php echo $type ?>blockOuter<?php echo $i ?>').fadeOut(600, function(){ $('#<?php echo $type ?><?php echo $i ?>'); });
						$('#<?php echo $type ?>blockOuter<?php echo $i ?>').remove();
						<?php echo $type ?>Used[<?php echo $type ?>Used.indexOf("<?php echo $pupilsightOutcomeID ?>")]="x" ;
					}
				});

			});
		</script>
		<div class='hiddenReveal' style='border: 1px solid #d8dcdf; margin: 0 0 5px' id="<?php echo $type ?>Block<?php echo $i ?>" style='padding: 0px'>
			<table class='blank' cellspacing='0' style='width: 100%'>
				<tr>
					<td style='width: 50%'>
						<input name='<?php echo $type ?>order[]' type='hidden' value='<?php echo $i ?>'>
						<input name='<?php echo $type ?>pupilsightOutcomeID<?php echo $i ?>' type='hidden' value='<?php echo $pupilsightOutcomeID ?>'>
						<input readonly maxlength=100 id='<?php echo $type ?>title<?php echo $i ?>' name='<?php echo $type ?>title<?php echo $i ?>' type='text' style='float: none; border: 1px dotted #aaa; background: none; margin-left: 3px; margin-top: 0px; font-size: 140%; font-weight: bold; width: 350px' value='<?php echo $title; ?>'><br/>
						<input readonly maxlength=100 id='<?php echo $type ?>category<?php echo $i ?>' name='<?php echo $type ?>category<?php echo $i ?>' type='text' style='float: left; border: 1px dotted #aaa; background: none; margin-left: 3px; margin-top: 2px; font-size: 110%; font-style: italic; width: 250px' value='<?php echo $category; ?>'>
						<script type="text/javascript">
							if($('#<?php echo $type ?>category<?php echo $i ?>').val()=="") {
								$('#<?php echo $type ?>category<?php echo $i ?>').css("border","none") ;
							}
						</script>
					</td>
					<td style='text-align: right; width: 50%'>
						<div style='margin-bottom: 25px'>
							<?php
                            echo "<img id='".$type."delete$i' title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/> ";
   	 						echo "<div id='".$type."show$i' title='".__('Show/Hide Details')."' style='margin-right: 3px; margin-left: 3px; padding-right: 1px; float: right; width: 25px; height: 25px; background-image: url(\"".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png\"); background-repeat: no-repeat'></div>"; ?>
						</div>
						<input type='hidden' name='id<?php echo $i ?>' value='<?php echo $id ?>'>
					</td>
				</tr>
				<tr id="<?php echo $type ?>BlockInner<?php echo $i ?>">
					<td colspan=2 style='vertical-align: top'>
						<?php
                            if ($allowOutcomeEditing == 'Y') {
                                echo getEditor($guid, false, $type.'contents'.$i, $contents, 20, false, false, false, true);
                            } else {
                                echo "<div style='padding: 5px'>$contents</div>";
                                echo "<input type='hidden' name='".$type.'contents'.$i."' value='".htmlPrep($contents)."'/>";
                            }
   				 			?>
					</td>
				</tr>
			</table>
		</div>
	<?php
    if ($outerBlock) {
        echo '</div>';
    }
}

//Returns all tags, in the specified school year if one is specified
function getTagList($connection2, $pupilsightSchoolYearID = null) {
    $tags = array();
    $tagsTemp = array();

    $tagCount = 0 ;
    //Get all tags
    try {
        if (is_null($pupilsightSchoolYearID)) {
            $dataList = array();
            $sqlList = 'SELECT tags FROM pupilsightUnit';
        }
        else {
            $dataList = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sqlList = "SELECT tags FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND pupilsightUnit.map='Y' AND pupilsightCourse.map='Y' AND NOT tags=''";
        }
        $resultList = $connection2->prepare($sqlList);
        $resultList->execute($dataList);
    } catch (PDOException $e) {}

    //First pass through of tags to create a raw array, ordered alphabetically
    while ($rowList = $resultList->fetch()) {
        $tagsInner = explode(',', $rowList['tags']);
        foreach ($tagsInner AS $tagInner) {
            $tagInner = mb_strtolower(trim($tagInner));
            $tagsTemp[$tagCount] = $tagInner ;
            $tagCount ++;
        }
    }
    sort($tagsTemp, SORT_STRING) ;

    //Second pass through, to remove uniques, calculate counts, etc
    $tagCount = 0 ;

    foreach ($tagsTemp AS $tagInner) {
        $unique = true ;
        $nonUniqueTagCount = null;
        foreach ($tags as $tag) {
            if ($tag[1] == $tagInner) {
                $unique = false ;
                $nonUniqueTagCount = $tag[0];
            }
        }

        if ($unique) { //If unique so far, then add it
            $tags[$tagCount][0] = $tagCount;
            $tags[$tagCount][1] = $tagInner;
            $tags[$tagCount][2] = 1;
            $tagCount ++;
        }
        else { //If not unique so far, then increment count
            $tags[$nonUniqueTagCount][2] ++ ;
        }
    }

    return $tags;
}

function getTagCloud($guid, $connection2, $pupilsightSchoolYearID = null) {
    $output = '';

    $tags = getTagList($connection2, $pupilsightSchoolYearID);

    if (count($tags) > 0) {
        $max_count = 0;
        $min_count = null;
        foreach ($tags as $tag) {
            if (is_null($min_count)) {
                $min_count = $tag[2];
            }
            else {
                if ($tag[2] < $min_count) {
                    $min_count = $tag[2];
                }
            }

            if ($tag[2] > $max_count) {
                $max_count = $tag[2];
            }
        }


        $min_font_size = 16;
        $max_font_size = 30;

        $spread = $max_count - $min_count;
        if ($spread == 0) {
            $spread = 1;
        }

        $cloud_html = '';
        $cloud_tags = array();
        for ($i = 0; $i < count($tags); ++$i) {
            $tag = $tags[$i][1];
            $count = $tags[$i][2];
            $size = $min_font_size + ($count - $min_count) * ($max_font_size - $min_font_size) / $spread;
            $cloud_tags[] = "<a style='font-size: ".floor($size)."px' class='tag_cloud' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/conceptExplorer.php&tag='.str_replace('&', '%26', $tag)."' title='$count units'>".htmlspecialchars(stripslashes($tag)).'</a>';
        }
        $output .= "<p style='margin-top: 10px; line-height: 130%'>";
        $output .= implode("\n", $cloud_tags)."\n";
        $output .= '</p>';
    } else {
        $output .= "<div class='alert alert-warning'>";
        $output .= __('There are no concepts in the system.');
        $output .= '</div>';
    }

    return $output;
}

function getResourcesTagCloud($guid, $connection2, $tagCount = 50) {
    $output = '';

    //Get array of top $tagCount tags
    $tags = array();
    $count = 0;
    $max_count = 0;
    $min_count = 0;

    try {
        $sql = "SELECT * FROM pupilsightResourceTag ORDER BY count DESC LIMIT $tagCount";
        $data = array();
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() > 0) {
        while ($row = $result->fetch()) {
            if ($count == 0) {
                $max_count = $row['count'];
                $min_count = $row['count'];
            } else {
                if ($row['count'] < $min_count) {
                    $min_count = $row['count'];
                }
            }
            $tags[$count][0] = $row['tag'];
            $tags[$count][1] = $row['count'];

            ++$count;
        }

        $tags = msort($tags, 0, true);

        $min_font_size = 16;
        $max_font_size = 30;

        $spread = $max_count - $min_count;
        if ($spread == 0) {
            $spread = 1;
        }

        $cloud_html = '';
        $cloud_tags = array();
        for ($i = 0; $i < count($tags); ++$i) {
            $tag = $tags[$i][0];
            $count = $tags[$i][1];
            $size = $min_font_size + ($count - $min_count) * ($max_font_size - $min_font_size) / $spread;
            $cloud_tags[] = "<a style='font-size: ".floor($size)."px' class='tag_cloud' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/resources_view.php&tag='.str_replace('&', '%26', $tag)."' title='$count resources'>".htmlspecialchars(stripslashes($tag)).'</a>';
        }
        $output .= "<p style='margin-top: 10px; line-height: 220%'>";
        $output .= implode("\n", $cloud_tags)."\n";
        $output .= '</p>';
    } else {
        $output .= "<div class='alert alert-warning'>";
        $output .= __('There are no resources in the system.');
        $output .= '</div>';
    }

    return $output;
}

function sidebarExtraResources($guid, $connection2)
{
    $output = '';
    $output .= '<h2>';
    $output .= __('Resource Tags');
    $output .= '</h2>';
    $output .= getResourcesTagCloud($guid, $connection2);

    return $output;
}

function getResourceLink($guid, $pupilsightResourceID, $type, $name, $content)
{
    $output = false;

    if ($type == 'Link') {
        $output = "<a target='_blank' style='font-weight: bold' href='".$content."'>".$name.'</a><br/>';
    } elseif ($type == 'File') {
        $output = "<a target='_blank' style='font-weight: bold' href='".$_SESSION[$guid]['absoluteURL'].'/'.$content."'>".$name.'</a><br/>';
    } elseif ($type == 'HTML') {
        $output = "<a style='font-weight: bold' class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Planner/resources_view_full.php&pupilsightResourceID='.$pupilsightResourceID."&width=1000&height=550'>".$name.'</a><br/>';
    }

    return $output;
}

?>
