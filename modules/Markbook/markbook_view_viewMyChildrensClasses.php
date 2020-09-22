<?php

/*
Pupilsight, Flexible & Open School System
*/

    use Pupilsight\Forms\Form;

    $page->breadcrumbs->add(__('View Markbook'));

	// Lock the file so other scripts cannot call it
	if (MARKBOOK_VIEW_LOCK !== sha1( $highestAction . $_SESSION[$guid]['pupilsightPersonID'] ) . date('zWy') ) return;

	//Get settings
	$enableEffort = getSettingByScope($connection2, 'Markbook', 'enableEffort');
	$enableRubrics = getSettingByScope($connection2, 'Markbook', 'enableRubrics');
	$attainmentAltName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
	$effortAltName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');

    $entryCount = 0;
    echo '<p>';
    echo "This page shows your children's academic results throughout your school career. Only subjects with published results are shown.";
    echo '</p>';

    //Test data access field for permission
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
        //Get child list
        $count = 0;
        $options = array();
        while ($row = $result->fetch()) {
            try {
                $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                $resultChild = $connection2->prepare($sqlChild);
                $resultChild->execute($dataChild);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            while ($rowChild = $resultChild->fetch()) {
                $options[$rowChild['pupilsightPersonID']]=formatName('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
            }
        }

        if (count($options) == 0) {
            echo "<div class='alert alert-danger'>";
            echo __('Access denied.');
            echo '</div>';
        } elseif (count($options) == 1) {
            $pupilsightPersonID = key($options);
        } else {
            echo '<h2>';
            echo __('Choose Student');
            echo '</h2>';

            $pupilsightPersonID = (isset($_GET['search']))? $_GET['search'] : null;

            $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
            $form->setClass('noIntBorder fullWidth standardForm');

            $form->addHiddenValue('q', '/modules/Markbook/markbook_view.php');
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('search', __('Student'));
                $row->addSelect('search')->fromArray($options)->selected($pupilsightPersonID)->placeholder();

            $row = $form->addRow();
                $row->addSearchSubmit($pupilsight->session);

            echo $form->getOutput();
        }

        $showParentAttainmentWarning = getSettingByScope($connection2, 'Markbook', 'showParentAttainmentWarning');
        $showParentEffortWarning = getSettingByScope($connection2, 'Markbook', 'showParentEffortWarning');

        if (!empty($pupilsightPersonID) and count($options) > 0) {
            //Confirm access to this student
            try {
                $dataChild = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                $resultChild = $connection2->prepare($sqlChild);
                $resultChild->execute($dataChild);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($resultChild->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $rowChild = $resultChild->fetch();

                if ($count > 1) {
                    echo '<h2>';
                    echo 'Filter & Options';
                    echo '</h2>';
                }

                $and = '';
                $and2 = '';
                $dataList = array();
                $dataEntry = array();

                $filter = isset($_REQUEST['filter'])? $_REQUEST['filter'] : $_SESSION[$guid]['pupilsightSchoolYearID'];
                if ($filter != '*') {
                    $dataList['filter'] = $filter;
                    $and .= ' AND pupilsightSchoolYearID=:filter';
                }

                $filter2 = isset($_REQUEST['filter2'])? $_REQUEST['filter2'] : '*';
                if ($filter2 != '*') {
                    $dataList['filter2'] = $filter2;
                    $and .= ' AND pupilsightDepartmentID=:filter2';
                }

                $filter3 = isset($_REQUEST['filter3'])? $_REQUEST['filter3'] : '';
                if ($filter3 != '') {
                    $dataEntry['filter3'] = $filter3;
                    $and2 .= ' AND type=:filter3';
                }

                $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php','get');
                $form->setClass('noIntBorder fullWidth');

                $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/markbook_view.php');
                $form->addHiddenValue('search', $pupilsightPersonID);

                $sqlSelect = "SELECT pupilsightDepartmentID as value, name FROM pupilsightDepartment WHERE type='Learning Area' ORDER BY name";
                $rowFilter = $form->addRow();
                    $rowFilter->addLabel('filter2', __('Learning Areas'));
                    $rowFilter->addSelect('filter2')
                        ->fromArray(array('*' => __('All Learning Areas')))
                        ->fromQuery($pdo, $sqlSelect)
                        ->selected($filter2);

                $dataSelect = array('pupilsightPersonID' => $pupilsightPersonID);
                $sqlSelect = "SELECT pupilsightSchoolYear.pupilsightSchoolYearID as value, CONCAT(pupilsightSchoolYear.name, ' (', pupilsightYearGroup.name, ')') AS name FROM pupilsightStudentEnrolment JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY pupilsightSchoolYear.sequenceNumber";
                $rowFilter = $form->addRow();
                    $rowFilter->addLabel('filter', __('School Years'));
                    $rowFilter->addSelect('filter')
                        ->fromArray(array('*' => __('All Years')))
                        ->fromQuery($pdo, $sqlSelect, $dataSelect)
                        ->selected($filter);

                $types = getSettingByScope($connection2, 'Markbook', 'markbookType');
                if (!empty($types)) {
                    $rowFilter = $form->addRow();
                    $rowFilter->addLabel('filter3', __('Type'));
                    $rowFilter->addSelect('filter3')
                        ->fromString($types)
                        ->selected($filter3)
                        ->placeholder();
                }

                $details = isset($_GET['details'])? $_GET['details'] : 'Yes';
                $form->addHiddenValue('details', 'No');
                $showHide = $form->getFactory()->createCheckbox('details')->addClass('details')->setValue('Yes')->checked($details)->inline(true)
                    ->description(__('Show/Hide Details'))->wrap('&nbsp;<span class="small emphasis displayInlineBlock">', '</span>');

                $rowFilter = $form->addRow();
                    $rowFilter->addSearchSubmit($pupilsight->session, __('Clear Filters'), array('search'))->prepend($showHide->getOutput());

                echo $form->getOutput();

                ?>
                <script type="text/javascript">
                    /* Show/Hide detail control */
                    $(document).ready(function(){
                        var updateDetails = function (){
                            if ($('input[name=details]:checked').val()=="Yes" ) {
                                $(".detailItem").slideDown("fast", $(".detailItem").css("{'display' : 'table-row'}"));
                            }
                            else {
                                $(".detailItem").slideUp("fast");
                            }
                        }
                        $(".details").click(updateDetails);
                        updateDetails();
                    });
                </script>
                <?php

                //Get class list
                try {
                    $dataList['pupilsightPersonID'] = $pupilsightPersonID;
                    $dataList['pupilsightPersonID2'] = $pupilsightPersonID;
                    $sqlList = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourse.name, pupilsightCourseClass.pupilsightCourseClassID, pupilsightScaleGrade.value AS target FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) LEFT JOIN pupilsightMarkbookTarget ON (pupilsightMarkbookTarget.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND pupilsightMarkbookTarget.pupilsightPersonIDStudent=:pupilsightPersonID2) LEFT JOIN pupilsightScaleGrade ON (pupilsightMarkbookTarget.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID $and ORDER BY course, class";
                    $resultList = $connection2->prepare($sqlList);
                    $resultList->execute($dataList);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($resultList->rowCount() > 0) {
                    while ($rowList = $resultList->fetch()) {
                        try {
                            $dataEntry['pupilsightPersonID'] = $pupilsightPersonID;
                            $dataEntry['pupilsightCourseClassID'] = $rowList['pupilsightCourseClassID'];
                            $sqlEntry = "SELECT *, pupilsightMarkbookColumn.comment AS commentOn, pupilsightMarkbookColumn.uploadedResponse AS uploadedResponseOn, pupilsightMarkbookEntry.comment AS comment FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) WHERE pupilsightPersonIDStudent=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID AND complete='Y' AND completeDate<='".date('Y-m-d')."' AND viewableParents='Y' $and2 ORDER BY completeDate";
                            $resultEntry = $connection2->prepare($sqlEntry);
                            $resultEntry->execute($dataEntry);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".print_r($dataEntry).'<br/>'.$e->getMessage().'</div>';
                        }
                        if ($resultEntry->rowCount() > 0) {
                            echo '<h4>'.$rowList['course'].'.'.$rowList['class']." <span style='font-size:85%; font-style: italic'>(".$rowList['name'].')</span></h4>';

                            try {
                                $dataTeachers = array('pupilsightCourseClassID' => $rowList['pupilsightCourseClassID']);
                                $sqlTeachers = "SELECT title, surname, preferredName FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE role='Teacher' AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY surname, preferredName";
                                $resultTeachers = $connection2->prepare($sqlTeachers);
                                $resultTeachers->execute($dataTeachers);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            $teachers = '<p><b>Taught by:</b> ';
                            while ($rowTeachers = $resultTeachers->fetch()) {
                                $teachers = $teachers.formatName($rowTeachers['title'], $rowTeachers['preferredName'], $rowTeachers['surname'], 'Staff', false, false).', ';
                            }
                            $teachers = substr($teachers, 0, -2);
                            $teachers = $teachers.'</p>';
                            echo $teachers;

                            if ($rowList['target'] != '') {
                                echo "<div style='font-weight: bold' class='linkTop'>";
                                echo __('Target').': '.$rowList['target'];
                                echo '</div>';
                            }

                            echo "<table cellspacing='0' style='width: 100%'>";
                            echo "<tr class='head'>";
                            echo "<th style='width: 120px'>";
                                echo __('Assessment');
                            echo '</th>';
                            if ($enableModifiedAssessment == 'Y') {
                                echo "<th style='width: 75px'>";
                                    echo __('Modified');
                                echo '</th>';
                            }
                            echo "<th style='width: 75px; text-align: center'>";
                                echo (!empty($attainmentAltName))? $attainmentAltName : __('Attainment');
                            echo '</th>';
							if ($enableEffort == 'Y') {
                                echo "<th style='width: 75px; text-align: center'>";
                                    echo (!empty($effortAltName))? $effortAltName : __('Effort');
                                echo '</th>';
                            }
                            echo '<th>';
                                echo __('Comment');
                            echo '</th>';
                            echo "<th style='width: 75px'>";
                                echo __('Submission');
                            echo '</th>';
                            echo '</tr>';

                            $count = 0;
                            while ($rowEntry = $resultEntry->fetch()) {
                                if ($count % 2 == 0) {
                                    $rowNum = 'even';
                                } else {
                                    $rowNum = 'odd';
                                }
                                ++$count;
                                ++$entryCount;

                                echo "<tr class=$rowNum>";
                                echo '<td>';
                                echo "<span title='".htmlPrep($rowEntry['description'])."'><b><u>".$rowEntry['name'].'</u></b></span><br/>';
                                echo "<span style='font-size: 90%; font-style: italic; font-weight: normal'>";
                                $unit = getUnit($connection2, $rowEntry['pupilsightUnitID'], $rowEntry['pupilsightCourseClassID']);
                                if (isset($unit[0])) {
                                    echo $unit[0].'<br/>';
                                    if ($unit[1] != '') {
                                        echo '<i>'.$unit[1].' Unit</i><br/>';
                                    }
                                }
                                if ($rowEntry['completeDate'] != '') {
                                    echo __('Marked on').' '.dateConvertBack($guid, $rowEntry['completeDate']).'<br/>';
                                } else {
                                    echo __('Unmarked').'<br/>';
                                }
                                echo $rowEntry['type'];
                                if ($rowEntry['attachment'] != '' and file_exists($_SESSION[$guid]['absolutePath'].'/'.$rowEntry['attachment'])) {
                                    echo " | <a 'title='Download more information' href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowEntry['attachment']."'>More info</a>";
                                }
                                echo '</span><br/>';
                                echo '</td>';
                                if ($enableModifiedAssessment == 'Y') {
                                    if (!is_null($rowEntry['modifiedAssessment'])) {
                                        echo "<td>";
                                        echo ynExpander($guid, $rowEntry['modifiedAssessment']);
                                        echo '</td>';
                                    }
                                    else {
                                        echo "<td class='dull' style='color: #bbb; text-align: center'>";
                                        echo __('N/A');
                                        echo '</td>';
                                    }
                                }
                                if ($rowEntry['attainment'] == 'N' or ($rowEntry['pupilsightScaleIDAttainment'] == '' and $rowEntry['pupilsightRubricIDAttainment'] == '')) {
                                    echo "<td class='dull' style='color: #bbb; text-align: center'>";
                                    echo __('N/A');
                                    echo '</td>';
                                } else {
                                    echo "<td style='text-align: center'>";
                                    $attainmentExtra = '';
                                    try {
                                        $dataAttainment = array('pupilsightScaleID' => $rowEntry['pupilsightScaleIDAttainment']);
                                        $sqlAttainment = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
                                        $resultAttainment = $connection2->prepare($sqlAttainment);
                                        $resultAttainment->execute($dataAttainment);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultAttainment->rowCount() == 1) {
                                        $rowAttainment = $resultAttainment->fetch();
                                        $attainmentExtra = '<br/>'.__($rowAttainment['usage']);
                                    }
                                    $styleAttainment = "style='font-weight: bold'";
                                    if ( ($rowEntry['attainmentConcern'] == 'Y' || $rowEntry['attainmentConcern'] == 'P') and $showParentAttainmentWarning == 'Y') {
                                        $styleAttainment = getAlertStyle($alert, $rowEntry['attainmentConcern'] );
                                    }
                                    echo "<div $styleAttainment>".$rowEntry['attainmentValue'];
                                    if ($rowEntry['pupilsightRubricIDAttainment'] != '' AND $enableRubrics =='Y') {
                                        echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Markbook/markbook_view_rubric.php&pupilsightRubricID='.$rowEntry['pupilsightRubricIDAttainment'].'&pupilsightCourseClassID='.$rowEntry['pupilsightCourseClassID'].'&pupilsightMarkbookColumnID='.$rowEntry['pupilsightMarkbookColumnID']."&pupilsightPersonID=$pupilsightPersonID&mark=FALSE&type=attainment&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='View Rubric' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/rubric.png'/></a>";
                                    }
                                    echo '</div>';
                                    if ($rowEntry['attainmentValue'] != '') {
                                        echo "<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'><b>".htmlPrep(__($rowEntry['attainmentDescriptor'])).'</b>'.__($attainmentExtra).'</div>';
                                    }
                                    echo '</td>';
                                }
								if ($enableEffort == 'Y') {
	                                if ($rowEntry['effort'] == 'N' or ($rowEntry['pupilsightScaleIDEffort'] == '' and $rowEntry['pupilsightRubricIDEffort'] == '')) {
	                                    echo "<td class='dull' style='color: #bbb; text-align: center'>";
	                                    echo __('N/A');
	                                    echo '</td>';
	                                } else {
	                                    echo "<td style='text-align: center'>";
	                                    $effortExtra = '';
	                                    try {
	                                        $dataEffort = array('pupilsightScaleID' => $rowEntry['pupilsightScaleIDEffort']);
	                                        $sqlEffort = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
	                                        $resultEffort = $connection2->prepare($sqlEffort);
	                                        $resultEffort->execute($dataEffort);
	                                    } catch (PDOException $e) {
	                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
	                                    }
	                                    if ($resultEffort->rowCount() == 1) {
	                                        $rowEffort = $resultEffort->fetch();
	                                        $effortExtra = '<br/>'.__($rowEffort['usage']);
	                                    }
	                                    $styleEffort = "style='font-weight: bold'";
	                                    if ($rowEntry['effortConcern'] == 'Y' and $showParentEffortWarning == 'Y') {
	                                        $styleEffort = getAlertStyle($alert, $rowEntry['effortConcern'] );
	                                    }
	                                    echo "<div $styleEffort>".$rowEntry['effortValue'];
	                                    if ($rowEntry['pupilsightRubricIDEffort'] != '' AND $enableRubrics =='Y') {
	                                        echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Markbook/markbook_view_rubric.php&pupilsightRubricID='.$rowEntry['pupilsightRubricIDEffort'].'&pupilsightCourseClassID='.$rowEntry['pupilsightCourseClassID'].'&pupilsightMarkbookColumnID='.$rowEntry['pupilsightMarkbookColumnID']."&pupilsightPersonID=$pupilsightPersonID&mark=FALSE&type=effort&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='View Rubric' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/rubric.png'/></a>";
	                                    }
	                                    echo '</div>';
	                                    if ($rowEntry['effortValue'] != '') {
	                                        echo "<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'>";
	                                        echo '<b>'.htmlPrep(__($rowEntry['effortDescriptor'])).'</b>';
	                                        if ($effortExtra != '') {
	                                            echo __($effortExtra);
	                                        }
	                                        echo '</div>';
	                                    }
	                                    echo '</td>';
	                                }
								}
                                if ($rowEntry['commentOn'] == 'N' and $rowEntry['uploadedResponseOn'] == 'N') {
                                    echo "<td class='dull' style='color: #bbb; text-align: left'>";
                                    echo __('N/A');
                                    echo '</td>';
                                } else {
                                    echo '<td>';
                                    if ($rowEntry['comment'] != '') {
                                        if (mb_strlen($rowEntry['comment']) > 200) {
                                            echo "<script type='text/javascript'>";
                                            echo '$(document).ready(function(){';
                                            echo "\$(\".comment-$entryCount\").hide();";
                                            echo "\$(\".show_hide-$entryCount\").fadeIn(1000);";
                                            echo "\$(\".show_hide-$entryCount\").click(function(){";
                                            echo "\$(\".comment-$entryCount\").fadeToggle(1000);";
                                            echo '});';
                                            echo '});';
                                            echo '</script>';
                                            echo '<span>'.mb_substr($rowEntry['comment'], 0, 200).'...<br/>';
                                            echo "<a title='".__('View Description')."' class='show_hide-$entryCount' onclick='return false;' href='#'>Read more</a></span><br/>";
                                        } else {
                                            echo nl2br($rowEntry['comment']);
                                        }
                                        echo '<br/>';
                                    }
                                    if ($rowEntry['response'] != '') {
                                        echo "<a title='Uploaded Response' href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowEntry['response']."'>Uploaded Response</a><br/>";
                                    }
                                    echo '</td>';
                                }
                                if ($rowEntry['pupilsightPlannerEntryID'] == 0) {
                                    echo "<td class='dull' style='color: #bbb; text-align: left'>";
                                    echo __('N/A');
                                    echo '</td>';
                                } else {
                                    try {
                                        $dataSub = array('pupilsightPlannerEntryID' => $rowEntry['pupilsightPlannerEntryID']);
                                        $sqlSub = "SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND homeworkSubmission='Y'";
                                        $resultSub = $connection2->prepare($sqlSub);
                                        $resultSub->execute($dataSub);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultSub->rowCount() != 1) {
                                        echo "<td class='dull' style='color: #bbb; text-align: left'>";
                                        echo __('N/A');
                                        echo '</td>';
                                    } else {
                                        echo '<td>';
                                        $rowSub = $resultSub->fetch();

                                        try {
                                            $dataWork = array('pupilsightPlannerEntryID' => $rowEntry['pupilsightPlannerEntryID'], 'pupilsightPersonID' => $pupilsightPersonID);
                                            $sqlWork = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID ORDER BY count DESC';
                                            $resultWork = $connection2->prepare($sqlWork);
                                            $resultWork->execute($dataWork);
                                        } catch (PDOException $e) {
                                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                        }
                                        if ($resultWork->rowCount() > 0) {
                                            $rowWork = $resultWork->fetch();

                                            if ($rowWork['status'] == 'Exemption') {
                                                $linkText = __('Exemption');
                                            } elseif ($rowWork['version'] == 'Final') {
                                                $linkText = __('Final');
                                            } else {
                                                $linkText = __('Draft').' '.$rowWork['count'];
                                            }

                                            $style = '';
                                            $status = 'On Time';
                                            if ($rowWork['status'] == 'Exemption') {
                                                $status = __('Exemption');
                                            } elseif ($rowWork['status'] == 'Late') {
                                                $style = "style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'";
                                                $status = __('Late');
                                            }

                                            if ($rowWork['type'] == 'File') {
                                                echo "<span title='".$rowWork['version'].". $status. ".sprintf(__('Submitted at %1$s on %2$s'), substr($rowWork['timestamp'], 11, 5), dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10)))."' $style><a href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowWork['location']."'>$linkText</a></span>";
                                            } elseif ($rowWork['type'] == 'Link') {
                                                echo "<span title='".$rowWork['version'].". $status. ".sprintf(__('Submitted at %1$s on %2$s'), substr($rowWork['timestamp'], 11, 5), dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10)))."' $style><a target='_blank' href='".$rowWork['location']."'>$linkText</a></span>";
                                            } else {
                                                echo "<span title='$status. ".sprintf(__('Recorded at %1$s on %2$s'), substr($rowWork['timestamp'], 11, 5), dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10)))."' $style>$linkText</span>";
                                            }
                                        } else {
                                            if (date('Y-m-d H:i:s') < $rowSub['homeworkDueDateTime']) {
                                                echo "<span title='Pending'>".__('Pending').'</span>';
                                            } else {
                                                if ($rowChild['dateStart'] > $rowSub['date']) {
                                                    echo "<span title='".__('Student joined school after assessment was given.')."' style='color: #000; font-weight: normal; border: 2px none #ff0000; padding: 2px 4px'>".__('NA').'</span>';
                                                } else {
                                                    if ($rowSub['homeworkSubmissionRequired'] == 'Compulsory') {
                                                        echo "<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>".__('Incomplete').'</div>';
                                                    } else {
                                                        echo __('Not submitted online');
                                                    }
                                                }
                                            }
                                        }
                                        echo '</td>';
                                    }
                                }
                                echo '</tr>';
                                if (mb_strlen($rowEntry['comment']) > 200) {
                                    echo "<tr class='comment-$entryCount' id='comment-$entryCount'>";
                                    echo '<td colspan=6>';
                                    echo nl2br($rowEntry['comment']);
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }

                            $enableColumnWeighting = getSettingByScope($connection2, 'Markbook', 'enableColumnWeighting');
                            $enableDisplayCumulativeMarks = getSettingByScope($connection2, 'Markbook', 'enableDisplayCumulativeMarks');

                            if ($enableColumnWeighting == 'Y' && $enableDisplayCumulativeMarks == 'Y') {
                                renderStudentCumulativeMarks($pupilsight, $pdo, $pupilsightPersonID, $rowList['pupilsightCourseClassID']);
                            }

                            echo '</table>';

                            try {
                                $dataEntry2 = array('pupilsightPersonIDStudent' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sqlEntry2 = "SELECT pupilsightMarkbookEntryID, pupilsightMarkbookColumn.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightPersonIDStudent=:pupilsightPersonIDStudent AND complete='Y' AND completeDate<='".date('Y-m-d')."' AND viewableParents='Y' ORDER BY completeDate DESC, name";
                                $resultEntry2 = $connection2->prepare($sqlEntry2);
                                $resultEntry2->execute($dataEntry2);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            if ($resultEntry2->rowCount() > 0) {
                                $_SESSION[$guid]['sidebarExtra'] = "<h2 class='sidebar'>";
                                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].__('Recent Marks');
                                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].'</h2>';

                                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].'<ol>';
                                $count = 0;

                                while ($rowEntry2 = $resultEntry2->fetch() and $count < 5) {
                                    $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a href='#".$rowEntry2['pupilsightMarkbookEntryID']."'>".$rowEntry['course'].'.'.$rowEntry['class']."<br/><span style='font-size: 85%; font-style: italic'>".$rowEntry['name'].'</span></a></li>';
                                    ++$count;
                                }

                                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].'</ol>';
                            }
                        }
                    }
                }
            }
        }
    }
    if ($entryCount < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    }
