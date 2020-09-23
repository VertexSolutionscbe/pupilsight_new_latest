<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Unit Planner'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/units.php') == false) {
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
        //Get Smart Workflow help message
        $category = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
        if ($category == 'Staff') {
            $smartWorkflowHelp = getSmartWorkflowHelp($connection2, $guid, 2);
            if ($smartWorkflowHelp != false) {
                echo $smartWorkflowHelp;
            }
        }

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
            $pupilsightCourseID = null;
            if (isset($_GET['pupilsightCourseID'])) {
                $pupilsightCourseID = $_GET['pupilsightCourseID'];
            }
            if ($pupilsightCourseID == '') {
                try {
                    if ($highestAction == 'Unit Planner_all') {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                        $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY nameShort';
                    } elseif ($highestAction == 'Unit Planner_learningAreas') {
                        $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                        $sql = "SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY pupilsightCourse.nameShort";
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($result->rowCount() > 0) {
                    $row = $result->fetch();
                    $pupilsightCourseID = $row['pupilsightCourseID'];
                }
            }
            if ($pupilsightCourseID != '') {
                try {
                    $data = array('pupilsightCourseID' => $pupilsightCourseID);
                    $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightCourseID=:pupilsightCourseID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($result->rowCount() == 1) {
                    $row = $result->fetch();
                }
            }

            //Work out previous and next course with same name
            $pupilsightCourseIDPrevious = '';
            $pupilsightSchoolYearIDPrevious = getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2);
            if ($pupilsightSchoolYearIDPrevious != false and isset($row['nameShort'])) {
                try {
                    $dataPrevious = array('pupilsightSchoolYearID' => $pupilsightSchoolYearIDPrevious, 'nameShort' => $row['nameShort']);
                    $sqlPrevious = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND nameShort=:nameShort';
                    $resultPrevious = $connection2->prepare($sqlPrevious);
                    $resultPrevious->execute($dataPrevious);
                } catch (PDOException $e) {
                }
                if ($resultPrevious->rowCount() == 1) {
                    $rowPrevious = $resultPrevious->fetch();
                    $pupilsightCourseIDPrevious = $rowPrevious['pupilsightCourseID'];
                }
            }
            $pupilsightCourseIDNext = '';
            $pupilsightSchoolYearIDNext = getNextSchoolYearID($pupilsightSchoolYearID, $connection2);
            if ($pupilsightSchoolYearIDNext != false and isset($row['nameShort'])) {
                try {
                    $dataNext = array('pupilsightSchoolYearID' => $pupilsightSchoolYearIDNext, 'nameShort' => $row['nameShort']);
                    $sqlNext = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND nameShort=:nameShort';
                    $resultNext = $connection2->prepare($sqlNext);
                    $resultNext->execute($dataNext);
                } catch (PDOException $e) {
                }
                if ($resultNext->rowCount() == 1) {
                    $rowNext = $resultNext->fetch();
                    $pupilsightCourseIDNext = $rowNext['pupilsightCourseID'];
                }
            }

            echo '<h2>';
            echo $pupilsightSchoolYearName;
            echo '</h2>';

            echo "<div class='linkTop'>";
                //Print year picker
                if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/units.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."&pupilsightCourseID=$pupilsightCourseIDPrevious'>".__('Previous Year').'</a> ';
                } else {
                    echo __('Previous Year').' ';
                }
				echo ' | ';
				if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
					echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/units.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."&pupilsightCourseID=$pupilsightCourseIDNext'>".__('Next Year').'</a> ';
				} else {
					echo __('Next Year').' ';
				}
            echo '</div>';


            if ($pupilsightCourseID == '') {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            }
            else {
                try {
                    if ($highestAction == 'Unit Planner_all') {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                        $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID';
                    } elseif ($highestAction == 'Unit Planner_learningAreas') {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID ORDER BY pupilsightCourse.nameShort";
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                } else {
                    $row = $result->fetch();

                    echo '<h4>';
                    echo $row['name'];
                    echo '</h4>';

                    //Fetch units
                    try {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                        $sql = 'SELECT pupilsightUnitID, pupilsightUnit.pupilsightCourseID, nameShort, pupilsightUnit.name, pupilsightUnit.description, active FROM pupilsightUnit JOIN pupilsightCourse ON pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightUnit.pupilsightCourseID=:pupilsightCourseID ORDER BY ordering, name';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    echo "<div class='linkTop'>";
                    echo "<a class='btn btn-primary' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/units_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID'>".__('Add')."</a>";
                    echo '</div>';

                    if ($result->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        echo "<form onsubmit='return confirm(\"".__('Are you sure you wish to process this action? It cannot be undone.')."\")' method='post' action='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/unitsProcessBulk.php'>";
                        echo "<fieldset style='border: none'>";
                        echo "<div class='linkTop' style='height: 27px'>"; ?>
        						<input style='margin-top: 0px; float: right' type='submit' value='<?php echo __('Go') ?>'>

                                <div id="courseClassRow" style="display: none;">
                                    <select style="width: 182px" name="pupilsightCourseIDCopyTo" id="pupilsightCourseIDCopyTo">
                                        <?php
                                        print "<option value='Please select...'>" . __('Please select...') . "</option>" ;

                                        try {
                                            $dataSelect['pupilsightSchoolYearID'] = $pupilsightSchoolYearID;
                                            $sqlWhere = '';
                                            if ($pupilsightSchoolYearIDNext != false) {
                                                $dataSelect['pupilsightSchoolYearIDNext'] = $pupilsightSchoolYearIDNext;
                                                $sqlWhere = ' OR pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearIDNext';
                                            }
                                            if ($highestAction == 'Unit Planner_all') {
                                                $sqlSelect="SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourse.nameShort AS course, pupilsightSchoolYear.name AS year FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE (pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID".$sqlWhere.") ORDER BY sequenceNumber, pupilsightCourse.nameShort" ;
                                            }
                                            else {
                                                $dataSelect['pupilsightPersonID'] = $_SESSION[$guid]["pupilsightPersonID"];
                                                $sqlSelect="SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourse.nameShort AS course, pupilsightSchoolYear.name AS year FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND (pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID".$sqlWhere.") ORDER BY sequenceNumber, pupilsightCourse.nameShort" ;
                                            }
                                            $resultSelect=$connection2->prepare($sqlSelect);
                                            $resultSelect->execute($dataSelect);
                                        }
                                        catch(PDOException $e) {
                                            print "<div class='alert alert-danger'>here" . $e->getMessage() . "</div>" ;
                                        }
                                        $yearCurrent = '';
                                        $yearLast = '';
                                        while ($rowSelect=$resultSelect->fetch()) {
                                            $yearCurrent = $rowSelect['year'];
                                            if ($yearCurrent != $yearLast) {
                                                echo '<optgroup label=\'--'.$rowSelect['year'].'--\'/>';
                                            }
                                            print "<option value='" . $rowSelect["pupilsightCourseID"] . "'>" . htmlPrep($rowSelect["course"]) . "</option>" ;
                                            $yearLast = $yearCurrent;
                                        }
                                        ?>
                                    </select>
                                    <script type="text/javascript">
                                        var pupilsightCourseIDCopyTo=new LiveValidation('pupilsightCourseIDCopyTo');
                                        pupilsightCourseIDCopyTo.add(Validate.Exclusion, { within: ['<?php echo __('Please select...') ?>'], failureMessage: "<?php echo __('Select something!') ?>"});
                                    </script>
                                </div>

        						<select name="action" id="action" style='width:120px; float: right; margin-right: 1px;'>
        							<option value="Select action"><?php echo __('Select action') ?></option>
                                    <option value="Duplicate"><?php echo __('Duplicate') ?></option>
        						</select>
        						<script type="text/javascript">
        							var action=new LiveValidation('action');
        							action.add(Validate.Exclusion, { within: ['<?php echo __('Select action') ?>'], failureMessage: "<?php echo __('Select something!') ?>"});

                                    $(document).ready(function(){
                                        $('#action').change(function () {
                                            if ($(this).val() == 'Duplicate') {
                                                $("#courseClassRow").slideDown("fast", $("#courseClassRow").css("display","block"));
                                            } else {
                                                $("#courseClassRow").css("display","none");
                                            }
                                        });
                                    });

                                </script>
        						<?php
                        echo '</div>';

                        echo "<table cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo "<th style='width: 150px'>";
                        echo __('Name');
                        echo '</th>';
                        echo "<th style='width: 400px'>";
                        echo __('Description');
                        echo '</th>';
                        echo '<th>';
                        echo __('Active');
                        echo '</th>';
                        echo "<th style='width: 140px'>";
                        echo __('Actions');
                        echo '</th>';
                        echo '<th style=\'text-align: center\'>'; ?>
        				<script type="text/javascript">
        					$(function () {
        						$('.checkall').click(function () {
        							$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
        						});
        					});
        				</script>
        				<?php
        				echo "<input type='checkbox' class='checkall'>";
                        echo '</th>';
                        echo '</tr>';

                        $count = 0;
                        $rowNum = 'odd';
                        while ($row = $result->fetch()) {
                            if ($count % 2 == 0) {
                                $rowNum = 'even';
                            } else {
                                $rowNum = 'odd';
                            }

                            if ($row['active'] != 'Y') {
                                $rowNum = 'error';
                            }

                            //COLOR ROW BY STATUS!
                            echo "<tr class=$rowNum>";
                            echo '<td>';
                            echo $row['name'];
                            echo '</td>';
                            echo "<td style='max-width: 270px'>";
                            echo $row['description'];
                            echo '</td>';
                            echo '<td>';
                            echo ynExpander($guid, $row['active']);
                            echo '</td>';
                            echo '<td>';
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/units_edit.php&pupilsightUnitID='.$row['pupilsightUnitID']."&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/units_delete.php&pupilsightUnitID='.$row['pupilsightUnitID']."&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/units_duplicate.php&pupilsightCourseID=$pupilsightCourseID&pupilsightUnitID=".$row['pupilsightUnitID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID'><img title='".__('Duplicate')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/coaapy.png'/></a> ";
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/units_dump.php&pupilsightCourseID=$pupilsightCourseID&pupilsightUnitID=".$row['pupilsightUnitID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID&sidebar=false'><img title='".__('Export')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/download.png'/></a>";
                            echo '</td>';
                            echo '<td>';
                            echo "<input name='pupilsightUnitID-$count' value='".$row['pupilsightUnitID']."' type='hidden'>";
                            echo "<input type='checkbox' name='check-$count' id='check-$count'>";
                            echo '</td>';
                            echo '</tr>';

                            ++$count;
                        }
                        echo '</table>';
                        echo '</fieldset>';

                        echo "<input name='count' value='$count' type='hidden'>";
                        echo "<input name='pupilsightCourseID' value='$pupilsightCourseID' type='hidden'>";
                        echo "<input name='pupilsightSchoolYearID' value='$pupilsightSchoolYearID' type='hidden'>";
                        echo "<input name='address' value='".$_GET['q']."' type='hidden'>";
                        echo '</form>';
                    }
                }
            }
        }
    }
    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtraUnits($guid, $connection2, $pupilsightCourseID, $pupilsightSchoolYearID);
}
