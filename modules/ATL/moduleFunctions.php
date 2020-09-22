<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

function getATLRecord($guid, $connection2, $pupilsightPersonID)
{
    $output = '';

    //Get school years in reverse order
    try {
        $dataYears = array('pupilsightPersonID' => $pupilsightPersonID);
        $sqlYears = "SELECT * FROM pupilsightSchoolYear JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE (status='Current' OR status='Past') AND pupilsightPersonID=:pupilsightPersonID ORDER BY sequenceNumber DESC";
        $resultYears = $connection2->prepare($sqlYears);
        $resultYears->execute($dataYears);
    } catch (PDOException $e) {
        $output .= "<div class='error'>".$e->getMessage().'</div>';
    }

    if ($resultYears->rowCount() < 1) {
        $output .= "<div class='error'>";
        $output .= __('There are no records to display.');
        $output .= '</div>';
    } else {
        $results = false;
        while ($rowYears = $resultYears->fetch()) {
            //Get and output ATLs
            try {
                $dataATL = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightPersonID2' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $rowYears['pupilsightSchoolYearID']);
                $sqlATL = "SELECT DISTINCT atlColumn.*, atlEntry.*, pupilsightCourse.name AS course, pupilsightCourseClass.nameShort AS class, pupilsightPerson.dateStart FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN atlColumn ON (atlColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN atlEntry ON (atlEntry.atlColumnID=atlColumn.atlColumnID) JOIN pupilsightPerson ON (atlEntry.pupilsightPersonIDStudent=pupilsightPerson.pupilsightPersonID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID1 AND atlEntry.pupilsightPersonIDStudent=:pupilsightPersonID2 AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND completeDate<='".date('Y-m-d')."' AND pupilsightCourseClass.reportable='Y' AND pupilsightCourseClassPerson.reportable='Y' ORDER BY completeDate DESC, pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";
                $resultATL = $connection2->prepare($sqlATL);
                $resultATL->execute($dataATL);
            } catch (PDOException $e) {
                $output .= "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($resultATL->rowCount() > 0) {
                $results = true;
                $output .= '<h4>';
                $output .= $rowYears['name'];
                $output .= '</h4>';
                $output .= "<table cellspacing='0' style='width: 100%'>";
                $output .= "<tr class='head'>";
                $output .= "<th style='width: 350px'>";
                $output .= 'Assessment';
                $output .= '</th>';
                $output .= '</th>';
                $output .= "<th style='text-align: center'>";
                $output .= __('Rubric');
                $output .= '</th>';
                $output .= '</tr>';

                $count = 0;
                while ($rowATL = $resultATL->fetch()) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;

                    $output .= "<tr class=$rowNum>";
                    $output .= '<td>';
                    $output .= "<span title='".htmlPrep($rowATL['description'])."'><b><u>".$rowATL['course'].'<br/>'.$rowATL['name'].'</u></b></span><br/>';
                    $output .= "<span style='font-size: 90%; font-style: italic; font-weight: normal'>";
                    if ($rowATL['completeDate'] != '') {
                        $output .= 'Marked on '.dateConvertBack($guid, $rowATL['completeDate']).'<br/>';
                    } else {
                        $output .= 'Unmarked<br/>';
                    }
                    $output .= '</span><br/>';
                    $output .= '</td>';
                    if ($rowATL['pupilsightRubricID'] == '') {
                        $output .= "<td class='dull' style='color: #bbb; text-align: center'>";
                        $output .= __('N/A');
                        $output .= '</td>';
                    } else {
                        $output .= "<td style='text-align: center'>";
                        $output .= "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/ATL/atl_view_rubric.php&pupilsightRubricID='.$rowATL['pupilsightRubricID'].'&pupilsightCourseClassID='.$rowATL['pupilsightCourseClassID'].'&atlColumnID='.$rowATL['atlColumnID']."&pupilsightPersonID=$pupilsightPersonID&mark=FALSE&type=effort&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='View Rubric' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/rubric.png'/></a>";
                        $output .= '</td>';
                    }

                    $output .= '</tr>';
                }

                $output .= '</table>';
            }
        }
        if ($results == false) {
            $output .= "<div class='error'>";
            $output .= __('There are no records to display.');
            $output .= '</div>';
        }
    }

    return $output;
}

function sidebarExtra($guid, $connection2, $pupilsightCourseClassID, $mode = 'manage', $highestAction = '')
{
    $output = '';

    $output .= '<div class="column-no-break">';
    $output .= '<h2>';
    $output .= __('View Classes');
    $output .= '</h2>';

    $selectCount = 0;

    global $pdo;

    $form = Form::create('classSelect', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/'.($mode == 'write'? 'atl_write.php' : 'atl_manage.php'));

    $row = $form->addRow();
        $row->addSelectClass('pupilsightCourseClassID', $_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID'])
            ->selected($pupilsightCourseClassID)
            ->placeholder()
            ->setClass('float-none w-48');
        $row->addSubmit(__('Go'));

    $output .= $form->getOutput();
    $output .= '</div>';

    return $output;
}
