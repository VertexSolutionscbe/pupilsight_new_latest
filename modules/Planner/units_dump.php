<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// common variables
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
$pupilsightCourseID = $_GET['pupilsightCourseID'] ?? '';
$pupilsightUnitID = $_GET['pupilsightUnitID'] ?? '';

$page->breadcrumbs
    ->add(__('Unit Planner'), 'units.php', [
        'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
        'pupilsightCourseID' => $pupilsightCourseID,
    ])
    ->add(__('Dump Unit'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_dump.php') == false && isActionAccessible($guid, $connection2, '/modules/Planner/scopeAndSequence.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Check if courseschool year specified
    if ($pupilsightCourseID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
            $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID';
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
            $yearName = $row['name'];
            $pupilsightDepartmentID = $row['pupilsightDepartmentID'];

            //Check if unit specified
            if ($pupilsightUnitID == '') {
                echo "<div class='alert alert-danger'>";
                echo __('You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                if ($pupilsightUnitID == '') {
                    echo "<div class='alert alert-danger'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    try {
                        $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseID' => $pupilsightCourseID);
                        $sql = 'SELECT pupilsightCourse.nameShort AS courseName, pupilsightSchoolYearID, pupilsightUnit.* FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightUnit.pupilsightCourseID=:pupilsightCourseID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($result->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The specified record cannot be found.');
                        echo '</div>';
                    } else {
                        //Let's go!
                        $row = $result->fetch();

                        echo '<p>';
                        echo sprintf(__('This page allows you to view all of the content of a selected unit (%1$s). If you wish to take this unit out of Pupilsight, simply copy and paste the contents into a word processing application.'), '<b><u>'.$row['courseName'].' - '.$row['name'].'</u></b>');
                        echo '</p>';

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
                            //Prep classes in this unit
                            try {
                                $dataClass = array('pupilsightUnitID' => $pupilsightUnitID);
                                $sqlClass = 'SELECT pupilsightUnitClass.pupilsightCourseClassID, pupilsightCourseClass.nameShort FROM pupilsightUnitClass JOIN pupilsightCourseClass ON (pupilsightUnitClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightUnitID=:pupilsightUnitID ORDER BY nameShort';
                                $resultClass = $connection2->prepare($sqlClass);
                                $resultClass->execute($dataClass);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                        //Tab links
                        echo '<ul>';
                        echo "<li><a href='#tabs1'>".__('Unit Overview').'</a></li>';
                        echo "<li><a href='#tabs2'>".__('Smart Blocks').'</a></li>';
                        echo "<li><a href='#tabs3'>".__('Resources').'</a></li>';
                        echo "<li><a href='#tabs4'>".__('Outcomes').'</a></li>';
                        $classes = array();
                        $classCount = 0;
                        while ($rowClass = $resultClass->fetch()) {
                            echo "<li><a href='#tabs".($classCount + 5)."'>".$row['courseName'].'.'.$rowClass['nameShort'].'</a></li>';
                            $classes[$classCount][0] = $rowClass['nameShort'];
                            $classes[$classCount][1] = $rowClass['pupilsightCourseClassID'];
                            ++$classCount;
                        }
                        echo '</ul>';

                        //Tabs
                        echo "<div id='tabs1'>";
                        if ($row['details'] == '') {
                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            echo '<h2>';
                            echo __('Description');
                            echo '</h2>';
                            echo '<p>';
                            echo $row['description'];
                            echo '</p>';

                            if ($row['tags'] != '') {
                                echo '<h2>';
                                echo __('Concepts & Keywords');
                                echo '</h2>';
                                echo '<p>';
                                echo $row['tags'];
                                echo '</p>';
                            }
                            if ($row['details'] != '') {
                                echo '<h2>';
                                echo __('Unit Outline');
                                echo '</h2>';
                                echo '<p>';
                                echo $row['details'];
                                echo '</p>';
                            }
                        }
                        echo '</div>';
                        echo "<div id='tabs2'>";
                        try {
                            $dataBlocks = array('pupilsightUnitID' => $pupilsightUnitID);
                            $sqlBlocks = 'SELECT * FROM pupilsightUnitBlock WHERE pupilsightUnitID=:pupilsightUnitID ORDER BY sequenceNumber';
                            $resultBlocks = $connection2->prepare($sqlBlocks);
                            $resultBlocks->execute($dataBlocks);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        $resourceContents = $row['details'];

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
                                $resourceContents .= $rowBlocks['contents'];
                            }
                            if ($rowBlocks['teachersNotes'] != '') {
                                echo "<div style='background-color: #F6CECB; padding: 0px 3px 10px 3px; width: 98%; text-align: justify; border-bottom: 1px solid #ddd'><p style='margin-bottom: 0px'><b>".__("Teacher's Notes").':</b></p> '.$rowBlocks['teachersNotes'].'</div>';
                                $resourceContents .= $rowBlocks['teachersNotes'];
                            }
                        }

                        echo '</div>';
                        echo "<div id='tabs3'>";
                        //Resources
                        $noReosurces = true;

                        if (!empty($resourceContents)) {
                            $resourceContents = '<?xml version="1.0" encoding="UTF-8"?>'.$resourceContents;

                            //Links
                            $links = '';
                            $linksArray = array();
                            $linksCount = 0;
                            $dom = new DOMDocument();
                            @$dom->loadHTML($resourceContents);
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
                            @$dom2->loadHTML($resourceContents);
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
                            @$dom2->loadHTML($resourceContents);
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
                        echo "<div id='tabs4'>";
                            //Spit out outcomes
                            try {
                                $dataBlocks = array('pupilsightUnitID' => $pupilsightUnitID);
                                $sqlBlocks = "SELECT pupilsightUnitOutcome.*, scope, name, nameShort, category, pupilsightYearGroupIDList FROM pupilsightUnitOutcome JOIN pupilsightOutcome ON (pupilsightUnitOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID) WHERE pupilsightUnitID=:pupilsightUnitID AND active='Y' ORDER BY sequenceNumber";
                                $resultBlocks = $connection2->prepare($sqlBlocks);
                                $resultBlocks->execute($dataBlocks);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            if ($resultBlocks->rowCount() > 0) {
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
                                while ($rowBlocks = $resultBlocks->fetch()) {
                                    if ($count % 2 == 0) {
                                        $rowNum = 'even';
                                    } else {
                                        $rowNum = 'odd';
                                    }

                                    //COLOR ROW BY STATUS!
                                    echo "<tr class=$rowNum>";
                                    echo '<td>';
                                    echo '<b>'.$rowBlocks['scope'].'</b><br/>';
                                    if ($rowBlocks['scope'] == 'Learning Area' and $pupilsightDepartmentID != '') {
                                        try {
                                            $dataLearningArea = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
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
                                    echo '<b>'.$rowBlocks['category'].'</b><br/>';
                                    echo '</td>';
                                    echo '<td>';
                                    echo '<b>'.$rowBlocks['nameShort'].'</b><br/>';
                                    echo "<span style='font-size: 75%; font-style: italic'>".$rowBlocks['name'].'</span>';
                                    echo '</td>';
                                    echo '<td>';
                                    echo getYearGroupsFromIDList($guid, $connection2, $rowBlocks['pupilsightYearGroupIDList']);
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
                                    if ($rowBlocks['content'] != '') {
                                        echo "<a title='".__('View Description')."' class='show_hide-$count' onclick='false' href='#'><img style='padding-left: 0px' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/page_down.png' alt='".__('Show Comment')."' onclick='return false;' /></a>";
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    if ($rowBlocks['content'] != '') {
                                        echo "<tr class='description-$count' id='description-$count'>";
                                        echo '<td colspan=6>';
                                        echo $rowBlocks['content'];
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tr>';

                                    ++$count;
                                }
                                echo '</table>';
                            }

                            echo '</div>';
                            $classCount = 0;
                            foreach ($classes as $class) {
                                echo "<div id='tabs".($classCount + 5)."'>";

                                //Print Lessons
                                echo '<h2>'.__('Lessons').'</h2>';
                                try {
                                    $dataLessons = array('pupilsightCourseClassID' => $class[1], 'pupilsightUnitID' => $pupilsightUnitID);
                                    $sqlLessons = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightUnitID=:pupilsightUnitID ORDER BY date';
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
                                        echo $rowLessons['description'].'<br/>';
                                        if ($rowLessons['teachersNotes'] != '') {
                                            echo "<div style='background-color: #F6CECB; padding: 0px 3px 10px 3px; width: 98%; text-align: justify; border-bottom: 1px solid #ddd'><p style='margin-bottom: 0px'><b>".__("Teacher's Notes").':</b></p> '.$rowLessons['teachersNotes'].'</div>';
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
                                            if ($rowBlock['teachersNotes'] != '') {
                                                echo "<div style='background-color: #F6CECB; padding: 0px 3px 10px 3px; width: 98%; text-align: justify; border-bottom: 1px solid #ddd'><p style='margin-bottom: 0px'><b>".__("Teacher's Notes").':</b></p> '.$rowBlock['teachersNotes'].'</div>';
                                            }
                                            echo '</p>';
                                        }

                                        //Print chats
                                        echo "<h5 style='font-size: 85%'>".__('Chat').'</h5>';
                                    echo getThread($guid, $connection2, $rowLessons['pupilsightPlannerEntryID'], null, 0, null, null, null, null, null, $class[1], $_SESSION[$guid]['pupilsightPersonID'], 'Teacher', false);
                                }
                            }
                            echo '</div>';
                            ++$classCount;
                        }
                        echo '</div>';
                    }
                }
            }
        }
    }
}
?>
