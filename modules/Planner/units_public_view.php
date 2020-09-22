<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// common variables
$makeUnitsPublic = getSettingByScope($connection2, 'Planner', 'makeUnitsPublic');
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
$pupilsightUnitID = $_GET['pupilsightUnitID'] ?? '';

$page->breadcrumbs
    ->add(__('Learn With Us'), 'units_public.php', [
        'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
    ])
    ->add(__('View Unit'));

if ($makeUnitsPublic != 'Y') {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Check if courseschool year specified
    if ($pupilsightUnitID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightUnitID' => $pupilsightUnitID);
            $sql = "SELECT pupilsightCourse.nameShort AS courseName, pupilsightSchoolYearID, pupilsightUnit.* FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightUnitID=:pupilsightUnitID AND sharedPublic='Y'";
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
            $row = $result->fetch(); ?>
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
            echo '<h2>';
            echo $row['name'];
            echo '</h2>';

            echo "<div id='tabs' style='width: 100%; margin: 20px 0'>";
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
            echo "<li><a href='#tabs1'>".__('Overview').'</a></li>';
            echo "<li><a href='#tabs2'>".__('Content').'</a></li>';
            echo "<li><a href='#tabs3'>".__('Resources').'</a></li>';
            echo "<li><a href='#tabs4'>".__('Outcomes').'</a></li>';
            echo '</ul>';

                //Tabs
                echo "<div id='tabs1'>";
            echo '<h4>';
            echo __('Description');
            echo '</h4>';
            if ($row['description'] == '') {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                echo '<p>';
                echo $row['description'];
                echo '</p>';
            }

            if ($row['license'] != '') {
                echo '<h4>';
                echo __('License');
                echo '</h4>';
                echo '<p>';
                echo __('This work is shared under the following license:').' '.$row['license'];
                echo '</p>';
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

            $resourceContents = '';

            while ($rowBlocks = $resultBlocks->fetch()) {
                if ($rowBlocks['title'] != '' or $rowBlocks['type'] != '' or $rowBlocks['length'] != '') {
                    echo '<hr/>';
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
                        echo "<div style='float: right; width: 29%; padding-right: 3px; height: 25px'>";
                        echo "<div style='text-align: right; font-size: 75%; font-style: italic; margin-top: 5px; border-bottom: 1px solid #ddd; height: 21px'>";
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
            echo '</div>';
        }
    }
}
