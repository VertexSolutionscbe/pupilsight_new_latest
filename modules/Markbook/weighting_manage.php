<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Markbook/weighting_manage.php') == false) {
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

        if (getSettingByScope($connection2, 'Markbook', 'enableColumnWeighting') != 'Y') {
            //Acess denied
            echo "<div class='alert alert-danger'>";
            echo __('Your request failed because you do not have access to this action.');
            echo '</div>';
            return;
        }

        //Get class variable
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';

        if ($pupilsightCourseClassID == '') {
            $pupilsightCourseClassID = (isset($_SESSION[$guid]['markbookClass']))? $_SESSION[$guid]['markbookClass'] : '';
        }

        if ($pupilsightCourseClassID == '') {
            echo '<h1>';
            echo __('Manage Weighting');
            echo '</h1>';
            echo "<div class='alert alert-warning'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';

            //Get class chooser
            echo classChooser($guid, $pdo, $pupilsightCourseClassID);
            return;
        }
        //Check existence of and access to this class.
        else {
            try {
                if ($highestAction == 'Manage Weightings_everything') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                } else {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo '<h1>';
                echo __('Manage Weightings');
                echo '</h1>';
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $row = $result->fetch();

                $page->breadcrumbs->add(__('Manage {courseClass} Weightings', [
                    'courseClass' => Format::courseClassName($row['course'], $row['class']),
                ]));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                //Get teacher list
                $teacherList = getTeacherList($pdo, $pupilsightCourseClassID);
                $teaching = (isset($teacherList[ $_SESSION[$guid]['pupilsightPersonID'] ]) );

                //Print mark
                echo '<h3>';
                echo __('Markbook Weightings');
                echo '</h3>';

                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT * FROM pupilsightMarkbookWeight WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY calculate, weighting DESC';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($teaching || $highestAction == 'Manage Weightings_everything') {
                    echo "<div class='linkTop'>";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/weighting_manage_add.php&pupilsightCourseClassID=$pupilsightCourseClassID'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
                    echo '</div>';
                }

                if ($result->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    echo "<table class='colorOddEven' cellspacing='0' style='width: 100%'>";
                    echo "<tr class='head'>";
                    echo '<th>';
                    echo __('Type');
                    echo '</th>';
                    echo '<th width="200px">';
                    echo __('Description');
                    echo '</th>';
                    echo '<th>';
                    echo __('Weighting');
                    echo '</th>';
                    echo '<th>';
                    echo __('Percent of');
                    echo '</th>';
                    echo '<th>';
                    echo __('Reportable?');
                    echo '</th>';
                    echo '<th style="width:80px">';
                    echo __('Actions');
                    echo '</th>';
                    echo '</tr>';

                    $count = 0;
                    $totalTermWeight = 0;
                    $totalYearWeight = 0;

                    $weightings = $result->fetchAll();
                    foreach ($weightings as $row) {

                        if ($row['calculate'] == 'term' && $row['reportable'] == 'Y') {
                            $totalTermWeight += floatval($row['weighting']);
                        } else if ($row['calculate'] == 'year' && $row['reportable'] == 'Y') {
                            $totalYearWeight += floatval($row['weighting']);
                        }

                        echo "<tr>";
                        echo '<td>';
                        echo $row['type'];
                        echo '</td>';
                        echo '<td>';
                        echo $row['description'];
                        echo '</td>';
                        echo '<td>';
                        echo floatval($row['weighting']).'%';
                        echo '</td>';
                        echo '<td>';
                        echo ($row['calculate'] == 'term')? __('Cumulative Average') : __('Final Grade');
                        echo '</td>';
                        echo '<td>';
                        echo ($row['reportable'] == 'Y')? __('Yes') : __('No');
                        echo '</td>';
                        echo '<td>';
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/weighting_manage_edit.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightMarkbookWeightID=".$row['pupilsightMarkbookWeightID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                        echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/weighting_manage_delete.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightMarkbookWeightID=".$row['pupilsightMarkbookWeightID']."&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
                        echo '</td>';
                        echo '</tr>';

                        ++$count;
                    }
                    echo '</table><br/>';


                    // Sample term calculation
                    if ($totalTermWeight > 0) {

                        echo '<h4>';
                        echo __('Sample Calculation') .': '. __('Cumulative Average');
                        echo '</h4>';

                        if ($totalTermWeight != 100) {
                            echo "<div class='alert alert-warning'>";
                            printf ( __('Total cumulative weighting is %s. Calculated averages may not be accurate if the total weighting does not add up to 100%%.'), floatval($totalTermWeight).'%' );
                            echo '</div>';
                        }

                        echo '<table class="blank" style="font-size:100%;min-width: 250px;">';
                        $count = 0;
                        foreach ($weightings as $row) {
                            if ($row['calculate'] != 'term') continue;
                            if ($row['reportable'] == 'N') continue;
                            echo '<tr>';
                                echo '<td style="width:20px;text-align:right;">'. (($count != 0)? '+' : '') .'</td>';
                                printf( '<td style="text-align:left">%s%% of %s</td>', floatval($row['weighting']), $row['description'] );
                            echo '</tr>';

                            $count++;
                        }
                        echo '<tr>';
                            echo '<td colspan=2 style="border-top: 2px solid #999999 !important;height: 5px !important;"></td>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<td style="text-align:right;">=</td>';
                            echo '<td style="text-align:left">'. __('Cumulative Average') .'</td>';
                        echo '</tr>';
                        echo '</table><br/>';
                    }

                    if ($totalYearWeight > 0) {
                        echo '<h4>';
                        echo __('Sample Calculation') .': '. __('Final Grade');
                        echo '</h4>';

                        if ($totalYearWeight >= 100 || (100 - $totalYearWeight) <= 0) {
                            echo "<div class='alert alert-warning'>";
                            printf ( __('Total final grade weighting is %s. Calculated averages may not be accurate if the total weighting  exceeds 100%%.'), floatval($totalYearWeight).'%' );
                            echo '</div>';
                        }

                        // Sample whole year calculation
                        echo '<table class="blank" style="font-size:100%;min-width: 250px;">';

                        echo '<tr>';
                            echo '<td style="width:20px;"></td>';
                            printf( '<td style="text-align:left">%s%% of %s</td>', floatval( max(0, 100 - $totalYearWeight) ), __('Cumulative Average') );
                        echo '</tr>';

                        foreach ($weightings as $row) {
                            if ($row['calculate'] != 'year') continue;
                            if ($row['reportable'] == 'N') continue;
                            echo '<tr>';
                                echo '<td style="width:20px;text-align:right;">'. '+' .'</td>';
                                printf( '<td style="text-align:left">%s%% of %s</td>', floatval($row['weighting']), $row['description'] );
                            echo '</tr>';

                            $count++;
                        }
                        echo '<tr>';
                            echo '<td colspan=2 style="border-top: 2px solid #999999 !important;height: 5px !important;"></td>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<td style="text-align:right;">=</td>';
                            echo '<td style="text-align:left">'. __('Final Grade') .'</td>';
                        echo '</tr>';
                        echo '</table>';
                    }


                }

                echo '<br/>&nbsp;<br/>';

                echo '<h3>';
                echo __('Copy Weightings');
                echo '</h3>';

                $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/weighting_manage_copyProcess.php?pupilsightCourseClassID='.$pupilsightCourseClassID);
                $form->setFactory(DatabaseFormFactory::create($pdo));
                $form->setClass('noIntBorder fullWidth');

                $col = $form->addRow()->addColumn()->addClass('inline right');
                    $col->addContent(__('Copy from').' '.__('Class').':');
                    $col->addSelectClass('pupilsightWeightingCopyClassID', $_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID'])->setClass('mediumWidth');
                    $col->addSubmit(__('Go'));

                echo $form->getOutput();
            }
        }
    }

    // Print the sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $pdo, $_SESSION[$guid]['pupilsightPersonID'], $pupilsightCourseClassID, 'weighting_manage.php');
}
