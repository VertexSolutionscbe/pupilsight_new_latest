<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Module\Rubrics\Visualise;

function rubricEdit($guid, $connection2, $pupilsightRubricID, $scaleName = '', $search = '', $filter2 = '')
{
    global $pdo;

    $output = false;

    $data = array('pupilsightRubricID' => $pupilsightRubricID);

    //Get rows, columns and cells
    $sqlRows = "SELECT * FROM pupilsightRubricRow WHERE pupilsightRubricID=:pupilsightRubricID ORDER BY sequenceNumber";
    $resultRows = $pdo->executeQuery($data, $sqlRows);
    $rowCount = $resultRows->rowCount();

    $sqlColumns = "SELECT * FROM pupilsightRubricColumn WHERE pupilsightRubricID=:pupilsightRubricID ORDER BY sequenceNumber";
    $resultColumns = $pdo->executeQuery($data, $sqlColumns);
    $columnCount = $resultColumns->rowCount();

    $sqlCells = "SELECT * FROM pupilsightRubricCell WHERE pupilsightRubricID=:pupilsightRubricID";
    $resultCells = $pdo->executeQuery($data, $sqlCells);
    $cellCount = $resultCells->rowCount();

    $sqlGradeScales = "SELECT pupilsightScaleGrade.pupilsightScaleGradeID, pupilsightScaleGrade.* FROM pupilsightRubricColumn
        JOIN pupilsightScaleGrade ON (pupilsightRubricColumn.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID)
        WHERE pupilsightRubricColumn.pupilsightRubricID=:pupilsightRubricID";
    $resultGradeScales = $pdo->executeQuery($data, $sqlGradeScales);
    $gradeScales = ($resultGradeScales->rowCount() > 0)? $resultGradeScales->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

    $sqlOutcomes = "SELECT pupilsightOutcome.pupilsightOutcomeID, pupilsightOutcome.* FROM pupilsightRubricRow
        JOIN pupilsightOutcome ON (pupilsightRubricRow.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID)
        WHERE pupilsightRubricRow.pupilsightRubricID=:pupilsightRubricID";
    $resultOutcomes = $pdo->executeQuery($data, $sqlOutcomes);
    $outcomes = ($resultOutcomes->rowCount() > 0)? $resultOutcomes->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

    if ($rowCount <= 0 or $columnCount <= 0) {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('The rubric cannot be drawn.');
        $output .= '</div>';
    } else {
        $rows = $resultRows->fetchAll();
        $columns = $resultColumns->fetchAll();

        $cells = array();
        while ($rowCells = $resultCells->fetch()) {
            $cells[$rowCells['pupilsightRubricRowID']][$rowCells['pupilsightRubricColumnID']] = $rowCells;
        }

        $output .= "<div class='linkTop'>";
        $output .= "<a onclick='return confirm(\"Are you sure you want to edit rows and columns? Any unsaved changes will be lost.\")' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/rubrics_edit_editRowsColumns.php&pupilsightRubricID=$pupilsightRubricID&search=$search&filter2=$filter2'>Edit Rows & Columns<i title='Edit' class='mdi mdi-lead-pencil fa-2x'></i></a>";
        $output .= '</div>';

        $form = Form::create('editRubric', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/rubrics_edit_editCellProcess.php?pupilsightRubricID='.$pupilsightRubricID.'&search='.$search.'&filter2='.$filter2);

        $form->setClass('rubricTable fullWidth');
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $row = $form->addRow()->addClass();
            $row->addContent()->addClass('rubricCellEmpty');

        // Column Headers
        for ($n = 0; $n < $columnCount; ++$n) {
            $col = $row->addColumn()->addClass('rubricHeading');

            // Display grade scale, otherwise column title
            if (!empty($gradeScales[$columns[$n]['pupilsightScaleGradeID']])) {
                $gradeScaleGrade = $gradeScales[$columns[$n]['pupilsightScaleGradeID']];
                $col->addContent('<b>'.$gradeScaleGrade['descriptor'].'</b>')
                    ->append(' ('.$gradeScaleGrade['value'].')')
                    ->append('<br/><span class="small emphasis">'.__($scaleName).' '.__('Scale').'</span>');
            } else {
                $col->addContent($columns[$n]['title'])->wrap('<b>', '</b>');
            }

            $col->addContent("<a onclick='return confirm(\"".__('Are you sure you want to delete this column? Any unsaved changes will be lost.')."\")' href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/rubrics_edit_deleteColumnProcess.php?pupilsightRubricID=$pupilsightRubricID&pupilsightRubricColumnID=".$columns[$n]['pupilsightRubricColumnID'].'&address='.$_GET['q']."&search=$search&filter2=$filter2'><i title='".__('Delete')."' class='mdi mdi-trash-can-outline mdi-24px px-2' style='margin: 2px 0px 0px 0px'></i></a>");
        }

        // Rows
        $count = 0;
        for ($i = 0; $i < $rowCount; ++$i) {
            $row = $form->addRow();
            $col = $row->addColumn()->addClass('rubricHeading');

            // Row Header
            if (!empty($outcomes[$rows[$i]['pupilsightOutcomeID']])) {
                $outcome = $outcomes[$rows[$i]['pupilsightOutcomeID']];
                $col->addContent('<b>'.__($outcome['name']).'</b>')
                    ->append(!empty($outcome['category'])? ('<i> - <br/>'.$outcome['category'].'</i>') : '')
                    ->append('<br/><span class="small emphasis">'.$outcome['scope'].' '.__('Outcome').'</span>');
                $rows[$i]['title'] = $outcome['name'];
            } else {
                $col->addContent($rows[$i]['title'])->wrap('<b>', '</b>');
            }

            $col->addContent("<a onclick='return confirm(\"".__('Are you sure you want to delete this row? Any unsaved changes will be lost.')."\")' href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/rubrics_edit_deleteRowProcess.php?pupilsightRubricID=$pupilsightRubricID&pupilsightRubricRowID=".$rows[$i]['pupilsightRubricRowID'].'&address='.$_GET['q']."&search=$search&filter2=$filter2'><i title='".__('Delete')."' class='mdi mdi-trash-can-outline mdi-24px px-2' style='margin: 2px 0px 0px 0px'></i></a><br/>");

            for ($n = 0; $n < $columnCount; ++$n) {
                $cell = @$cells[$rows[$i]['pupilsightRubricRowID']][$columns[$n]['pupilsightRubricColumnID']];
                $row->addTextArea("cell[$count]")->setValue(isset($cell['contents'])? $cell['contents']: '')->setClass('rubricCell rubricCellEdit');

                $form->addHiddenValue("pupilsightRubricCellID[$count]", isset($cell['pupilsightRubricCellID'])? $cell['pupilsightRubricCellID']: '');
                $form->addHiddenValue("pupilsightRubricColumnID[$count]", $columns[$n]['pupilsightRubricColumnID']);
                $form->addHiddenValue("pupilsightRubricRowID[$count]", $rows[$i]['pupilsightRubricRowID']);

                $count++;
            }
        }

        $row = $form->addRow();
            $row->addSubmit();

        $output .= $form->getOutput();
    }

    return $output;
}

//If $mark=TRUE, then marking tools are made available, otherwise it is view only
function rubricView($guid, $connection2, $pupilsightRubricID, $mark, $pupilsightPersonID = '', $contextDBTable = '', $contextDBTableIDField = '', $contextDBTableID = '', $contextDBTablePupilsightRubricIDField = '', $contextDBTableNameField = '', $contextDBTableDateField = '')
{
    global $pdo, $page, $pupilsight;

    $output = false;
    $hasContexts = $contextDBTable != '' and $contextDBTableIDField != '' and $contextDBTableID != '' and $contextDBTablePupilsightRubricIDField != '' and $contextDBTableNameField != '' and $contextDBTableDateField != '';

    try {
        $data = array('pupilsightRubricID' => $pupilsightRubricID);
        $sql = 'SELECT * FROM pupilsightRubric WHERE pupilsightRubricID=:pupilsightRubricID';
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
        $values = $result->fetch();

        //Get rows, columns and cells
        $sqlRows = "SELECT * FROM pupilsightRubricRow WHERE pupilsightRubricID=:pupilsightRubricID ORDER BY sequenceNumber";
        $resultRows = $pdo->executeQuery($data, $sqlRows);
        $rowCount = $resultRows->rowCount();

        $sqlColumns = "SELECT * FROM pupilsightRubricColumn WHERE pupilsightRubricID=:pupilsightRubricID ORDER BY sequenceNumber";
        $resultColumns = $pdo->executeQuery($data, $sqlColumns);
        $columnCount = $resultColumns->rowCount();

        $sqlCells = "SELECT * FROM pupilsightRubricCell WHERE pupilsightRubricID=:pupilsightRubricID";
        $resultCells = $pdo->executeQuery($data, $sqlCells);
        $cellCount = $resultCells->rowcount();

        $sqlGradeScales = "SELECT pupilsightScaleGrade.pupilsightScaleGradeID, pupilsightScaleGrade.*, pupilsightScale.name FROM pupilsightRubricColumn
            JOIN pupilsightScaleGrade ON (pupilsightRubricColumn.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID)
            JOIN pupilsightScale ON (pupilsightScale.pupilsightScaleID=pupilsightScaleGrade.pupilsightScaleID)
            WHERE pupilsightRubricColumn.pupilsightRubricID=:pupilsightRubricID";
        $resultGradeScales = $pdo->executeQuery($data, $sqlGradeScales);
        $gradeScales = ($resultGradeScales->rowCount() > 0)? $resultGradeScales->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

        $sqlOutcomes = "SELECT pupilsightOutcome.pupilsightOutcomeID, pupilsightOutcome.* FROM pupilsightRubricRow
            JOIN pupilsightOutcome ON (pupilsightRubricRow.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID)
            WHERE pupilsightRubricRow.pupilsightRubricID=:pupilsightRubricID";
        $resultOutcomes = $pdo->executeQuery($data, $sqlOutcomes);
        $outcomes = ($resultOutcomes->rowCount() > 0)? $resultOutcomes->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

        // Check if outcomes are specified in unit
        $unitOutcomes = array();
        if ($hasContexts) {
            $dataUnitOutcomes = array();
            $sqlUnitOutcomes = "SHOW COLUMNS FROM `$contextDBTable` LIKE 'pupilsightUnitID'";
            $resultUnitOutcomes = $pdo->executeQuery($dataUnitOutcomes, $sqlUnitOutcomes);

            if ($resultUnitOutcomes->rowCount() > 0) {
                $dataUnitOutcomes = array('pupilsightRubricID' => $pupilsightRubricID, 'contextDBTableID' => $contextDBTableID);
                $sqlUnitOutcomes = "SELECT pupilsightUnitOutcome.pupilsightOutcomeID, pupilsightUnitOutcome.pupilsightUnitOutcomeID FROM pupilsightRubricRow
                    JOIN pupilsightOutcome ON (pupilsightRubricRow.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID)
                    JOIN pupilsightUnitOutcome ON (pupilsightUnitOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID)
                    JOIN `$contextDBTable` ON (`$contextDBTable`.pupilsightUnitID=pupilsightUnitOutcome.pupilsightUnitID AND `$contextDBTableIDField`=:contextDBTableID)
                    WHERE pupilsightRubricRow.pupilsightRubricID=:pupilsightRubricID";
                $resultUnitOutcomes = $pdo->executeQuery($dataUnitOutcomes, $sqlUnitOutcomes);
                $unitOutcomes = ($resultUnitOutcomes->rowCount() > 0)? $resultUnitOutcomes->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();
            }
        }

        // Load rubric data for this student
        $dataEntries = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightPersonID' => $pupilsightPersonID, 'contextDBTable' => $contextDBTable, 'contextDBTableID' => $contextDBTableID);
        $sqlEntries = "SELECT pupilsightRubricEntry.pupilsightRubricCellID, pupilsightRubricEntry.* FROM pupilsightRubricCell
            LEFT JOIN pupilsightRubricEntry ON (pupilsightRubricEntry.pupilsightRubricCellID=pupilsightRubricCell.pupilsightRubricCellID)
            WHERE pupilsightRubricCell.pupilsightRubricID=:pupilsightRubricID
            AND pupilsightRubricEntry.pupilsightPersonID=:pupilsightPersonID
            AND pupilsightRubricEntry.contextDBTable=:contextDBTable
            AND pupilsightRubricEntry.contextDBTableID=:contextDBTableID";
        $resultEntries = $pdo->executeQuery($dataEntries, $sqlEntries);
        $entries = ($resultEntries->rowCount() > 0)? $resultEntries->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();


        if ($rowCount <= 0 or $columnCount <= 0) {
            $output .= "<div class='alert alert-danger'>";
            $output .= __('The rubric cannot be drawn.');
            $output .= '</div>';
        } else {
            $rows = $resultRows->fetchAll();
            $columns = $resultColumns->fetchAll();

            $cells = array();
            while ($rowCells = $resultCells->fetch()) {
                $cells[$rowCells['pupilsightRubricRowID']][$rowCells['pupilsightRubricColumnID']] = $rowCells;
            }

            //Get other uses of this rubric in this context, and store for use in visualisation
            $contexts = array();
            if ($hasContexts) {
                $dataContext = array('pupilsightPersonID' => $pupilsightPersonID);
                $sqlContext = "SELECT pupilsightRubricEntry.*, $contextDBTable.*, pupilsightRubricEntry.*, pupilsightRubricCell.*, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameshort AS class
                    FROM pupilsightRubricEntry
                    JOIN $contextDBTable ON (pupilsightRubricEntry.contextDBTableID=$contextDBTable.$contextDBTableIDField
                        AND pupilsightRubricEntry.pupilsightRubricID=$contextDBTable.$contextDBTablePupilsightRubricIDField)
                    JOIN pupilsightRubricCell ON (pupilsightRubricEntry.pupilsightRubricCellID=pupilsightRubricCell.pupilsightRubricCellID)
                    LEFT JOIN pupilsightCourseClass ON ($contextDBTable.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                    LEFT JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                    WHERE contextDBTable='$contextDBTable'
                    AND pupilsightRubricEntry.pupilsightPersonID=:pupilsightPersonID
                    AND NOT $contextDBTableDateField IS NULL
                    ORDER BY $contextDBTableDateField DESC";
                $resultContext = $pdo->executeQuery($dataContext,  $sqlContext);

                if ($resultContext->rowCount() > 0) {
                    while ($rowContext = $resultContext->fetch()) {
                        $context = $rowContext['course'].'.'.$rowContext['class'].' - '.$rowContext[$contextDBTableNameField].' ('.dateConvertBack($guid, $rowContext[$contextDBTableDateField]).')';
                        $cells[$rowContext['pupilsightRubricRowID']][$rowContext['pupilsightRubricColumnID']]['context'][] = $context;

                        array_push($contexts, array('pupilsightRubricEntry' => $rowContext['pupilsightRubricEntry'], 'pupilsightRubricID' => $rowContext['pupilsightRubricID'], 'pupilsightPersonID' => $rowContext['pupilsightPersonID'], 'pupilsightRubricCellID' => $rowContext['pupilsightRubricCellID'], 'contextDBTable' => $rowContext['contextDBTable'], 'contextDBTableID' => $rowContext['contextDBTableID']));
                    }
                }
            }

            //Controls for viewing mode
            if ($pupilsightPersonID != '') {
                $output .= "<div class='linkTop'>";
                $output .= "Viewing Mode: <select name='type' id='type' class='type' style='width: 152px; float: none'>";
                $output .= "<option id='type' name='type' value='Current'>".__('Current').'</option>';
                $output .= "<option id='type' name='type' value='Visualise'>".__('Visualise').'</option>';
                $output .= "<option id='type' name='type' value='Historical'>".__('Historical Data').'</option>';
                $output .= '</select>';
                $output .= '</div>';
            }

            //Div to contain rubric for current and historicla views
            $output .= "<div id='rubric'>";

                if ($mark == true) {
                    $output .= '<p>';
                    $output .= __('Click on any of the cells below to highlight them. Data is saved automatically after each click.');
                    $output .= '</p>';
                }

                $form = Form::create('viewRubric', $_SESSION[$guid]['absoluteURL'].'/index.php');
                $form->setClass('rubricTable fullWidth');

                $row = $form->addRow()->addClass();
                    $row->addContent()->addClass('');

                if ($hasContexts) {
                    $form->toggleVisibilityByClass('currentView')->onSelect('type')->when('Current');
                    $form->toggleVisibilityByClass('historical')->onSelect('type')->when('Historical');
                }

                    // Column Headers
                    for ($n = 0; $n < $columnCount; ++$n) {
                        $column = $row->addColumn()->addClass('rubricHeading');

                        // Display grade scale, otherwise column title
                        if (!empty($gradeScales[$columns[$n]['pupilsightScaleGradeID']])) {
                            $gradeScaleGrade = $gradeScales[$columns[$n]['pupilsightScaleGradeID']];
                            $column->addContent('<b>'.$gradeScaleGrade['descriptor'].'</b>')
                                ->append(' ('.$gradeScaleGrade['value'].')')
                                ->append('<br/><span class="small emphasis">'.__($gradeScaleGrade['name']).' '.__('Scale').'</span>');
                        } else {
                            $column->addContent($columns[$n]['title'])->wrap('<b>', '</b>');
                        }
                    }

                    // Rows
                    $count = 0;
                    for ($i = 0; $i < $rowCount; ++$i) {
                        $row = $form->addRow();
                        $col = $row->addColumn()->addClass('rubricHeading rubricRowHeading');

                        // Row Header
                        if (!empty($outcomes[$rows[$i]['pupilsightOutcomeID']])) {
                            $outcome = $outcomes[$rows[$i]['pupilsightOutcomeID']];
                            $content = $col->addContent('<b>'.__($outcome['name']).'</b>')
                                ->append(!empty($outcome['category'])? ('<i> - <br/>'.$outcome['category'].'</i>') : '')
                                ->append('<br/><span class="small emphasis">'.$outcome['scope'].' '.__('Outcome').'</span>')
                                ->wrap('<span title="'.$outcome['description'].'">', '</span>');
                            // Highlight unit outcomes with a checkmark
                            if (isset($unitOutcomes[$rows[$i]['pupilsightOutcomeID']])) {
                                $content->append('<img style="float: right" title="'.__('This outcome is one of the unit outcomes.').'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/iconTick.png"/>');
                            }
                            $rows[$i]['title'] = $outcomes[$rows[$i]['pupilsightOutcomeID']]['name'];
                            $rows[$i]['title'];
                        } else {
                            $col->addContent($rows[$i]['title'])->wrap('<b>', '</b>');
                        }

                        // Cells
                        for ($n = 0; $n < $columnCount; ++$n) {
                            if (!isset($cells[$rows[$i]['pupilsightRubricRowID']][$columns[$n]['pupilsightRubricColumnID']])) {
                                $row->addColumn()->addClass('rubricCell');
                                continue;
                            }

                            $cell = $cells[$rows[$i]['pupilsightRubricRowID']][$columns[$n]['pupilsightRubricColumnID']];

                            $highlightClass = isset($entries[$cell['pupilsightRubricCellID']])? 'rubricCellHighlight' : '';
                            $markableClass = ($mark == true)? 'markableCell' : '';

                            $col = $row->addColumn()->addClass('rubricCell '.$highlightClass);
                                $col->addContent($cell['contents'])
                                    ->addClass('currentView '.$markableClass)
                                    ->append('<span class="cellID" data-cell="'.$cell['pupilsightRubricCellID'].'"></span>');

                            // Add historical contexts if applicable, shown/hidden by dropdown
                            $countHistorical = isset($cell['context']) ? count($cell['context']) : 0;
                            if ($hasContexts && $countHistorical > 0) {
                                $historicalContent = '';
                                for ($h = 0; $h < min(7, $countHistorical); ++$h) {
                                    $historicalContent .= ($h + 1) . ') ' . $cell['context'][$h] . '<br/>';
                                }

                                $col->addContent($historicalContent)
                                    ->addClass('historical')
                                    ->prepend('<b><u>' . __('Total Occurences:') . ' ' . $countHistorical . '</u></b><br/>')
                                    ->append(($countHistorical > 7)? '<b>'.__('Older occurrences not shown...').'</b>' : '')
                                    ->append('<span class="cellID" data-cell="' . $cell['pupilsightRubricCellID'] . '"></span>');
                            }
                        }
                    }

                    if ($mark == true) {
                        $output .= "<script type='text/javascript'>";
                        $output .= '$(document).ready(function(){';
                        $output .= '$(".markableCell").parent().click(function(){';
                            $output .= "var mode = '';";
                            $output .= "var cellID = $(this).find('.cellID').data('cell');";
                            $output .= "if ($(this).hasClass('rubricCellHighlight') == false ) {";
                                $output .= "$(this).addClass('rubricCellHighlight');";
                                $output .= "mode = 'Add';";
                            $output .= '} else {';
                                $output .= "$(this).removeClass('rubricCellHighlight');";
                                $output .= "mode = 'Remove';";
                            $output .= '}';
                            $output .= 'var request=$.ajax({ url: "'.$_SESSION[$guid]['absoluteURL'].'/modules/Rubrics/rubrics_data_saveAjax.php", type: "GET", data: {mode: mode, pupilsightRubricID : "' . $pupilsightRubricID.'", pupilsightPersonID : "'.$pupilsightPersonID.'", pupilsightRubricCellID : cellID, contextDBTable : "'.$contextDBTable.'",contextDBTableID : "'.$contextDBTableID.'"}, dataType: "html"});';
                            $output .= '});';
                        $output .= '});';
                        $output .= '</script>';
                    }


                $output .= $form->getOutput();

            $output .= "</div>";

            //Div to contain visualisation
            $output .= "<div id='visualise' style='display: none'>";
                $output .= "<p>";
                    $output .= __("This view offers a visual representation of all rubric data for the current student, this year, in the current context:");
                $output .= "</p>";

                require_once __DIR__ . '/src/Visualise.php';
                $visualise = new Visualise($pupilsight->session->get('absoluteURL'), $page, $pupilsightPersonID, $columns, $rows, $cells, $contexts);

                $output .= $visualise->renderVisualise();

            $output .= "</div>";

            //Function to show/hide rubric/visualisation
            $output .= "<script type='text/javascript'>
                 $(document).ready(function(){
                    $('#type').change(function () {
                        if ($(this).val() == 'Current' || $(this).val() == 'Historical') {
                            $('#rubric').slideDown('fast', $('#rubric').css('display','block'));
                            $('#visualise').css('display','none');
                        } else {
                            $('#visualise').slideDown('fast', $('#visualise').css('display','block'));
                            $('#rubric').css('display','none');
                        }
                    });
                });
            </script>";
        }

        // Append the Rubric stylesheet to the current page - for Markbook view of Rubric (only if it's not already included)
        $output .= '<script>';
        $output .= "if (!$('link[href*=\"./modules/Rubrics/css/module.css\"]').length) {";
        $output .= "$('<link>').appendTo('head').attr({type: 'text/css', rel: 'stylesheet', href: './modules/Rubrics/css/module.css'})";
        $output .= '}';
        $output .= '</script>';
    }

    return $output;
}
