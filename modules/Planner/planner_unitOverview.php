<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_unitOverview.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $viewBy = null;
        if (isset($_GET['viewBy'])) {
            $viewBy = $_GET['viewBy'];
        }
        $subView = null;
        if (isset($_GET['subView'])) {
            $subView = $_GET['subView'];
        }
        if ($viewBy != 'date' and $viewBy != 'class') {
            $viewBy = 'date';
        }
        $pupilsightCourseClassID = null;
        $date = null;
        $dateStamp = null;
        if ($viewBy == 'date') {
            $date = $_GET['date'];
            if (isset($_GET['dateHuman'])) {
                $date = dateConvert($guid, $_GET['dateHuman']);
            }
            if ($date == '') {
                $date = date('Y-m-d');
            }
            list($dateYear, $dateMonth, $dateDay) = explode('-', $date);
            $dateStamp = mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear);
        } elseif ($viewBy == 'class') {
            $class = null;
            if (isset($_GET['class'])) {
                $class = $_GET['class'];
            }
            $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
        }
        $replyTo = null;
        if (isset($_GET['replyTo'])) {
            $replyTo = $_GET['replyTo'];
        }

        $pupilsightPersonID = null;
        if (isset($_GET['search'])) {
            $pupilsightPersonID = $_GET['search'];
        }

        //Get class variable
        $pupilsightPlannerEntryID = null;
        if (isset($_GET['pupilsightPlannerEntryID'])) {
            $pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
        }
        if ($pupilsightPlannerEntryID == '') {
            echo "<div class='alert alert-warning'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        }
        //Check existence of and access to this class.
        else {
            if ($highestAction == 'Lesson Planner_viewMyChildrensClasses') {
                if ($_GET['search'] == '') {
                    echo "<div class='alert alert-warning'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    try {
                        $dataChild = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultChild->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The selected record does not exist, or you do not have access to it.');
                        echo '</div>';
                    } else {
                        $data = array('date' => $date);
                        $data['pupilsightPlannerEntryID1'] = $pupilsightPlannerEntryID;
                        $data['pupilsightPlannerEntryID2'] = $pupilsightPlannerEntryID;
                        $sql = "(SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=$pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND pupilsightPlannerEntry.pupilsightPlannerEntryID=:pupilsightPlannerEntryID1) UNION (SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date AND pupilsightPlannerEntryGuest.pupilsightPersonID=$pupilsightPersonID AND pupilsightPlannerEntry.pupilsightPlannerEntryID=:pupilsightPlannerEntryID2) ORDER BY date, timeStart";
                    }
                }
            } elseif ($highestAction == 'Lesson Planner_viewMyClasses') {
                $data = array('date' => $date, 'pupilsightPlannerEntryID1' => $pupilsightPlannerEntryID, 'pupilsightPlannerEntryID2' => $pupilsightPlannerEntryID);
                $sql = '(SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID='.$_SESSION[$guid]['pupilsightPersonID']." AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND pupilsightPlannerEntry.pupilsightPlannerEntryID=:pupilsightPlannerEntryID1) UNION (SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date AND pupilsightPlannerEntryGuest.pupilsightPersonID=".$_SESSION[$guid]['pupilsightPersonID'].' AND pupilsightPlannerEntry.pupilsightPlannerEntryID=:pupilsightPlannerEntryID2) ORDER BY date, timeStart';
            } elseif ($highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses') {
                $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, 'Teacher' AS role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY date, timeStart";
            }
            try {
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

                // target of the planner
                $target = ($viewBy === 'class') ? $row['course'].'.'.$row['class'] : dateConvertBack($guid, $date);

                // planner parameters
                $params = [];
                if ($date != '') {
                    $params['date'] = $_GET['date'];
                }
                if ($viewBy != '') {
                    $params['viewBy'] = $_GET['viewBy'] ?? '';
                }
                if ($pupilsightCourseClassID != '') {
                    $params['pupilsightCourseClassID'] = $pupilsightCourseClassID;
                }
                $params['subView'] = $subView;
                $paramsVar = '&' . http_build_query($params); // for backward compatibile uses below (should be get rid of)

                $page->breadcrumbs
                    ->add(__('Planner for {classDesc}', [
                        'classDesc' => $target,
                    ]), 'planner.php', $params)
                    ->add(__('View Lesson Plan'), 'planner_view_full.php', $params + ['pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'search' => $pupilsightPersonID])
                    ->add(__('Unit Overview'));

                if ($row['pupilsightUnitID'] == '') {
                    echo __('The selected record does not exist, or you do not have access to it.');
                } else {
                    //Get unit contents
                    try {
                        $dataUnit = array('pupilsightUnitID' => $row['pupilsightUnitID']);
                        $sqlUnit = 'SELECT * FROM pupilsightUnit WHERE pupilsightUnitID=:pupilsightUnitID';
                        $resultUnit = $connection2->prepare($sqlUnit);
                        $resultUnit->execute($dataUnit);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultUnit->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The selected record does not exist, or you do not have access to it.');
                        echo '</div>';
                    } else {
                        $rowUnit = $resultUnit->fetch();

                        echo '<h2>';
                        echo $rowUnit['name'];
                        echo '</h2>';
                        echo '<p>';
                        echo __('This page shows an overview of the unit that the current lesson belongs to, including all the outcomes, resources, lessons and chats for the classes you have access to.');
                        echo '</p>';

                        //Set up where and data array for getting items from accessible planners
                        if ($highestAction == 'Lesson Planner_viewEditAllClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses') {
                            $dataPlanners = array('pupilsightUnitID' => $row['pupilsightUnitID'], 'pupilsightCourseClassID' => $row['pupilsightCourseClassID']);
                            $sqlPlanners = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                        } elseif ($highestAction == 'Lesson Planner_viewMyClasses') {
                            $dataPlanners = array('pupilsightUnitID1' => $row['pupilsightUnitID'], 'pupilsightPersonID1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID1' => $row['pupilsightCourseClassID'], 'pupilsightUnitID2' => $row['pupilsightUnitID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID2' => $row['pupilsightCourseClassID']);
                            $sqlPlanners = "(SELECT pupilsightPlannerEntry.* FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightUnitID=:pupilsightUnitID1 AND pupilsightPersonID=:pupilsightPersonID1 AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID1 AND role='Teacher')
							UNION
							(SELECT pupilsightPlannerEntry.* FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightUnitID=:pupilsightUnitID2 AND pupilsightPersonID=:pupilsightPersonID2 AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID2 AND role='Student' AND viewableStudents='Y')";
                        } elseif ($highestAction == 'Lesson Planner_viewMyChildrensClasses') {
                            $dataPlanners = array('pupilsightUnitID' => $row['pupilsightUnitID'], 'pupilsightCourseClassID' => $row['pupilsightCourseClassID']);
                            $sqlPlanners = "SELECT * FROM pupilsightPlannerEntry WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightCourseClassID=:pupilsightCourseClassID AND viewableParents='Y'";
                        }
                        try {
                            $resultPlanners = $connection2->prepare($sqlPlanners);
                            $resultPlanners->execute($dataPlanners);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($resultPlanners->rowCount() < 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            $dataMulti = array();
                            $whereMulti = '(';
                            $multiCount = 0;
                            while ($rowPlanners = $resultPlanners->fetch()) {
                                $dataMulti['pupilsightPlannerEntryID'.$multiCount] = $rowPlanners['pupilsightPlannerEntryID'];
                                $whereMulti .= 'pupilsightPlannerEntryID=:pupilsightPlannerEntryID'.$multiCount.' OR ';
                                ++$multiCount;
                            }
                            $whereMulti = substr($whereMulti, 0, -4).')';
                            ?>
							<script type='text/javascript'>
								$(function() {
									$( "#tabs" ).tabs({
										ajaxOptions: {
											error: function( xhr, status, index, anchor ) {
												$( anchor.hash ).html(
													"Couldn't load this tab." );
											}
										}
									});
								});
							</script>
							<?php

                            echo "<div id='tabs' style='margin: 20px 0'>";
							//Tab links
							echo '<ul>';
                            echo "<li><a href='#tabs1'>".__('Unit Overview').'</a></li>';
                            echo "<li><a href='#tabs2'>".__('Smart Blocks').'</a></li>';
                            echo "<li><a href='#tabs3'>".__('Outcomes').'</a></li>';
                            echo "<li><a href='#tabs4'>".__('Lessons').'</a></li>';
                            echo "<li><a href='#tabs5'>".__('Resources').'</a></li>';
                            echo '</ul>';

							//Tab content
							//UNIT OVERVIEW
							echo "<div id='tabs1'>";
                            $shareUnitOutline = getSettingByScope($connection2, 'Planner', 'shareUnitOutline');
                            echo '<h2>';
                            echo __('Description');
                            echo '</h2>';
                            echo '<p>';
                            echo $rowUnit['description'];
                            echo '</p>';

                            if ($rowUnit['tags'] != '') {
                                echo '<h2>';
                                echo __('Concepts & Keywords');
                                echo '</h2>';
                                echo '<p>';
                                echo $rowUnit['tags'];
                                echo '</p>';
                            }
                            if ($highestAction == 'Lesson Planner_viewEditAllClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses' or $shareUnitOutline == 'Y') {
                                if ($rowUnit['details'] != '') {
                                    echo '<h2>';
                                    echo __('Unit Outline');
                                    echo '</h2>';
                                    echo '<p>';
                                    echo $rowUnit['details'];
                                    echo '</p>';
                                }
                            }
                            echo '</div>';
                            //SMART BLOCKS
                            echo "<div id='tabs2'>";
                            try {
                                $dataBlocks = array('pupilsightUnitID' => $row['pupilsightUnitID']);
                                $sqlBlocks = 'SELECT * FROM pupilsightUnitBlock WHERE pupilsightUnitID=:pupilsightUnitID ORDER BY sequenceNumber';
                                $resultBlocks = $connection2->prepare($sqlBlocks);
                                $resultBlocks->execute($dataBlocks);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            while ($rowBlocks = $resultBlocks->fetch()) {
                                if ($rowBlocks['title'] != '' or $rowBlocks['type'] != '' or $rowBlocks['length'] != '') {
                                    echo "<div class='blockView' style='min-height: 35px'>";
                                    if ($rowBlocks['type'] != '' or $rowBlocks['length'] != '') {
                                        $width = '69%';
                                    } else {
                                        $width = '100%';
                                    }
                                    echo "<div style='padding-left: 3px; width: $width; float: left;'>";
                                    if ($rowBlocks['title'] != '') {
                                        echo "<h5 style='padding-bottom: 2px'>".$rowBlocks['title'].'</h5>';
                                    }
                                    echo '</div>';
                                    if ($rowBlocks['type'] != '' or $rowBlocks['length'] != '') {
                                        echo "<div style='float: right; width: 29%; padding-right: 3px; height: 55px'>";
                                        echo "<div style='text-align: right; font-size: 85%; font-style: italic; margin-top: 3px; border-bottom: 1px solid #ddd; height: 21px'>";
                                        if ($rowBlocks['type'] != '') {
                                            echo $rowBlocks['type'];
                                            if ($rowBlocks['length'] != '') {
                                                echo ' | ';
                                            }
                                        }
                                        if ($rowBlocks['length'] != '') {
                                            echo $rowBlocks['length'].' min';
                                        }
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                }
                                if ($rowBlocks['contents'] != '') {
                                    echo "<div style='padding: 15px 3px 10px 3px; width: 100%; text-align: justify; border-bottom: 1px solid #ddd'>".$rowBlocks['contents'].'</div>';
                                }
                            }
                            echo '</div>';
                            //OUTCOMES
							echo "<div id='tabs3'>";
                            try {
                                $dataOutcomes = $dataMulti;
                                $dataOutcomes['pupilsightUnitID'] = $row['pupilsightUnitID'];
                                $sqlOutcomes = "(SELECT pupilsightOutcome.*, pupilsightPlannerEntryOutcome.content FROM pupilsightPlannerEntryOutcome JOIN pupilsightOutcome ON (pupilsightPlannerEntryOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID) WHERE $whereMulti AND active='Y')
									UNION
									(SELECT pupilsightOutcome.*, pupilsightUnitOutcome.content FROM pupilsightUnitOutcome JOIN pupilsightOutcome ON (pupilsightUnitOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID) WHERE pupilsightUnitID=:pupilsightUnitID AND active='Y')
									ORDER BY scope DESC, name";
                                $resultOutcomes = $connection2->prepare($sqlOutcomes);
                                $resultOutcomes->execute($dataOutcomes);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            if ($resultOutcomes->rowCount() < 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            } else {
                                echo "<table cellspacing='0' style='width: 100%'>";
                                echo "<tr class='head'>";
                                echo '<th>';
                                echo __('Scope');
                                echo '</th>';
                                echo '<th>';
                                echo __('Category');
                                echo '</th>';
                                echo '<th>';
                                echo __('Name');
                                echo '</th>';
                                echo '<th>';
                                echo __('Year Groups');
                                echo '</th>';
                                echo '<th>';
                                echo __('Actions');
                                echo '</th>';
                                echo '</tr>';

                                $count = 0;
                                $rowNum = 'odd';
                                while ($rowOutcomes = $resultOutcomes->fetch()) {
                                    if ($count % 2 == 0) {
                                        $rowNum = 'even';
                                    } else {
                                        $rowNum = 'odd';
                                    }

									//COLOR ROW BY STATUS!
									echo "<tr class=$rowNum>";
                                    echo '<td>';
                                    echo '<b>'.$rowOutcomes['scope'].'</b><br/>';
                                    if ($rowOutcomes['scope'] == 'Learning Area' and $rowOutcomes['pupilsightDepartmentID'] != '') {
                                        try {
                                            $dataLearningArea = array('pupilsightDepartmentID' => $rowOutcomes['pupilsightDepartmentID']);
                                            $sqlLearningArea = 'SELECT * FROM pupilsightDepartment WHERE pupilsightDepartmentID=:pupilsightDepartmentID';
                                            $resultLearningArea = $connection2->prepare($sqlLearningArea);
                                            $resultLearningArea->execute($dataLearningArea);
                                        } catch (PDOException $e) {
                                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                        }
                                        if ($resultLearningArea->rowCount() == 1) {
                                            $rowLearningAreas = $resultLearningArea->fetch();
                                            echo "<span style='font-size: 75%; font-style: italic'>".$rowLearningAreas['name'].'</span>';
                                        }
                                    }
                                    echo '</td>';
                                    echo '<td>';
                                    echo '<b>'.$rowOutcomes['category'].'</b><br/>';
                                    echo '</td>';
                                    echo '<td>';
                                    echo '<b>'.$rowOutcomes['nameShort'].'</b><br/>';
                                    echo "<span style='font-size: 75%; font-style: italic'>".$rowOutcomes['name'].'</span>';
                                    echo '</td>';
                                    echo '<td>';
                                    echo getYearGroupsFromIDList($guid, $connection2, $rowOutcomes['pupilsightYearGroupIDList']);
                                    echo '</td>';
                                    echo '<td>';
                                    echo "<script type='text/javascript'>";
                                    echo '$(document).ready(function(){';
                                    echo "\$(\".description-$count\").hide();";
                                    echo "\$(\".show_hide-$count\").fadeIn(1000);";
                                    echo "\$(\".show_hide-$count\").click(function(){";
                                    echo "\$(\".description-$count\").fadeToggle(1000);";
                                    echo '});';
                                    echo '});';
                                    echo '</script>';
                                    if ($rowOutcomes['content'] != '') {
                                        echo "<a title='".__('View Description')."' class='show_hide-$count' onclick='false' href='#'><img style='padding-left: 0px' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/page_down.png' alt='".__('Show Comment')."' onclick='return false;' /></a>";
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    if ($rowOutcomes['content'] != '') {
                                        echo "<tr class='description-$count' id='description-$count'>";
                                        echo '<td colspan=6>';
                                        echo $rowOutcomes['content'];
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tr>';

                                    ++$count;
                                }
                                echo '</table>';
                            }
                            echo '</div>';
                            //LESSONS
                            echo "<div id='tabs4'>";
                            $resourceContents = '';
                            try {
                                $dataLessons = $dataMulti;
                                $sqlLessons = "SELECT * FROM pupilsightPlannerEntry WHERE $whereMulti";
                                $resultLessons = $connection2->prepare($sqlLessons);
                                $resultLessons->execute($dataLessons);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            if ($resultLessons->rowCount() < 1) {
                                echo "<div class='alert alert-warning'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            } else {
                                while ($rowLessons = $resultLessons->fetch()) {
                                    echo '<h3>'.$rowLessons['name'].'</h3>';
                                    echo $rowLessons['description'];
                                    $resourceContents .= $rowLessons['description'];
                                    if ($rowLessons['teachersNotes'] != '' and ($highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses')) {
                                        echo "<div style='background-color: #F6CECB; padding: 0px 3px 10px 3px; width: 98%; text-align: justify; border-bottom: 1px solid #ddd'><p style='margin-bottom: 0px'><b>".__("Teacher's Notes").':</b></p> '.$rowLessons['teachersNotes'].'</div>';
                                        $resourceContents .= $rowLessons['teachersNotes'];
                                    }

                                    try {
                                        $dataBlock = array('pupilsightPlannerEntryID' => $rowLessons['pupilsightPlannerEntryID']);
                                        $sqlBlock = 'SELECT * FROM pupilsightUnitClassBlock WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY sequenceNumber';
                                        $resultBlock = $connection2->prepare($sqlBlock);
                                        $resultBlock->execute($dataBlock);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }

                                    while ($rowBlock = $resultBlock->fetch()) {
                                        echo "<h5 style='font-size: 85%'>".$rowBlock['title'].'</h5>';
                                        echo '<p>';
                                        echo '<b>'.__('Type').'</b>: '.$rowBlock['type'].'<br/>';
                                        echo '<b>'.__('Length').'</b>: '.$rowBlock['length'].'<br/>';
                                        echo '<b>'.__('Contents').'</b>: '.$rowBlock['contents'].'<br/>';
                                        $resourceContents .= $rowBlock['contents'];
                                        if ($rowBlock['teachersNotes'] != '' and ($highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses')) {
                                            echo "<div style='background-color: #F6CECB; padding: 0px 3px 10px 3px; width: 98%; text-align: justify; border-bottom: 1px solid #ddd'><p style='margin-bottom: 0px'><b>".__("Teacher's Notes").':</b></p> '.$rowBlock['teachersNotes'].'</div>';
                                            $resourceContents .= $rowBlock['teachersNotes'];
                                        }
                                        echo '</p>';
                                    }

									//Print chats
                                    try {
                                        $dataDiscuss = array('pupilsightPlannerEntryID' => $rowLessons['pupilsightPlannerEntryID']);
                                        $sqlDiscuss = 'SELECT pupilsightPlannerEntryDiscuss.*, title, surname, preferredName, category FROM pupilsightPlannerEntryDiscuss JOIN pupilsightPerson ON (pupilsightPlannerEntryDiscuss.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY timestamp';
                                        $resultDiscuss = $connection2->prepare($sqlDiscuss);
                                        $resultDiscuss->execute($dataDiscuss);
                                    } catch (PDOException $e) { print $e->getMessage();}

                                    if ($resultDiscuss->rowCount() > 0) {
                                        echo "<h5 style='font-size: 85%'>".__('Chat').'</h5>';
                                        echo '<style type="text/css">';
                                        echo 'table.chatbox { width: 90%!important }';
                                        echo '</style>';
                                        echo getThread($guid, $connection2, $rowLessons['pupilsightPlannerEntryID'], null, 0, null, null, null, null, null, $class[1], $_SESSION[$guid]['pupilsightPersonID'], 'Teacher', false, true);
                                    }
                                }
                            }
                            echo '</div>';
                            //RESOURCES
                            echo "<div id='tabs5'>";
                            $noReosurces = true;

                            if (!empty($resourceContents)) {
                                $resourceContents = '<?xml version="1.0" encoding="UTF-8"?>'.$resourceContents;

                                //Links
                                $links = '';
                                $linksArray = array();
                                $linksCount = 0;
                                $dom = new DOMDocument();
                                $dom->loadHTML($resourceContents);
                                foreach ($dom->getElementsByTagName('a') as $node) {
                                    if ($node->nodeValue != '') {
                                        $linksArray[$linksCount] = "<li><a href='".$node->getAttribute('href')."'>".$node->nodeValue.'</a></li>';
                                        ++$linksCount;
                                    }
                                }

                                $linksArray = array_unique($linksArray);
                                natcasesort($linksArray);

                                foreach ($linksArray as $link) {
                                    $links .= $link;
                                }

                                if ($links != '') {
                                    echo '<h2>';
                                    echo 'Links';
                                    echo '</h2>';
                                    echo '<ul>';
                                    echo $links;
                                    echo '</ul>';
                                    $noReosurces = false;
                                }

                                //Images
                                $images = '';
                                $imagesArray = array();
                                $imagesCount = 0;
                                $dom2 = new DOMDocument();
                                $dom2->loadHTML($resourceContents);
                                foreach ($dom2->getElementsByTagName('img') as $node) {
                                    if ($node->getAttribute('src') != '') {
                                        $imagesArray[$imagesCount] = "<img class='resource' style='margin: 10px 0; max-width: 560px' src='".$node->getAttribute('src')."'/><br/>";
                                        ++$imagesCount;
                                    }
                                }

                                $imagesArray = array_unique($imagesArray);
                                natcasesort($imagesArray);

                                foreach ($imagesArray as $image) {
                                    $images .= $image;
                                }

                                if ($images != '') {
                                    echo '<h2>';
                                    echo 'Images';
                                    echo '</h2>';
                                    echo $images;
                                    $noReosurces = false;
                                }

                                //Embeds
                                $embeds = '';
                                $embedsArray = array();
                                $embedsCount = 0;
                                $dom2 = new DOMDocument();
                                $dom2->loadHTML($resourceContents);
                                foreach ($dom2->getElementsByTagName('iframe') as $node) {
                                    if ($node->getAttribute('src') != '') {
                                        $embedsArray[$embedsCount] = "<iframe style='max-width: 560px' width='".$node->getAttribute('width')."' height='".$node->getAttribute('height')."' src='".$node->getAttribute('src')."' frameborder='".$node->getAttribute('frameborder')."'></iframe>";
                                        ++$embedsCount;
                                    }
                                }

                                $embedsArray = array_unique($embedsArray);
                                natcasesort($embedsArray);

                                foreach ($embedsArray as $embed) {
                                    $embeds .= $embed.'<br/><br/>';
                                }

                                if ($embeds != '') {
                                    echo '<h2>';
                                    echo 'Embeds';
                                    echo '</h2>';
                                    echo $embeds;
                                    $noReosurces = false;
                                }
                            }

							//No resources!
							if ($noReosurces) {
								echo "<div class='alert alert-danger'>";
								echo __('There are no records to display.');
								echo '</div>';
							}
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                }
            }
        }
    }
}
