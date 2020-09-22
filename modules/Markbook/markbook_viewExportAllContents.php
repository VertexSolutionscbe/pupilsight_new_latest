<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Module\Markbook\MarkbookView;

include __DIR__ . '/../../config.php';

//Module includes
include __DIR__ . '/moduleFunctions.php';

//Get settings
$enableEffort = getSettingByScope($connection2, 'Markbook', 'enableEffort');
$enableRubrics = getSettingByScope($connection2, 'Markbook', 'enableRubrics');
$attainmentAlternativeName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
$attainmentAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev');
$effortAlternativeName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');
$effortAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev');

//Set up adjustment for presence of effort column or not
if ($enableEffort == 'Y')
    $effortAdjust = 0 ;
else
    $effortAdjust = 1 ;

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // Check existence of and access to this class.
    $highestAction = getHighestGroupedAction($guid, '/modules/Markbook/markbook_view.php', $connection2);
    $class = getClass($pdo, $_SESSION[$guid]['pupilsightPersonID'], $pupilsightCourseClassID, $highestAction);
    
    if (empty($class)) {
        echo '<div class="alert alert-danger">';
        echo __('You do not have access to this action.');
        echo '</div>';
        return;
    }

    $alert = getAlert($guid, $connection2, 002);

    //Count number of columns
	$data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
	$sql = 'SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY complete, completeDate DESC';
	$result = $pdo->executeQuery($data, $sql, '_');
    $columns = $result->rowCount();
    if ($columns < 1) {
        echo "<div class='alert alert-warning'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {

        require_once __DIR__ . '/src/MarkbookView.php';

        // Build the markbook object for this class
        $markbook = new MarkbookView($pupilsight, $pdo, $pupilsightCourseClassID);

        // Calculate and cache all weighting data
        if ($markbook->getSetting('enableColumnWeighting') == 'Y') {
            $markbook->cacheWeightings( );
        }

        //Print table header
		$excel = new Pupilsight\Excel('markbookAll.xlsx');
		if ($excel->estimateCellCount($pdo) > 8000)    //  If too big, then render csv instead.
			return Pupilsight\csv::generate($pdo, 'markbookColumn');
		$excel->setActiveSheetIndex(0);
		$excel->getProperties()->setTitle('All Markbook Data');
		$excel->getProperties()->setSubject('All Markbook Data');
		$excel->getProperties()->setDescription('All Markbook Data');

        // Use advanced binder - better handling of numbers, percents, etc.
        PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );

        //Create border and fill style
        $style_border = array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e'))));
        $style_head_fill = array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'B89FE2')));
        $style_head_fill2 = array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'C5D9F1')));

        //Auto set first column width
        $excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

		$excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, __('Student'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_head_fill);

        $span = 3;
        $columnID = array();
        $attainmentID = array();
        $effortID = array();
        for ($i = 0;$i < $columns;++$i) {
            $row = $result->fetch();
            if ($row === false) {
                $columnID[$i] = false;
            } else {
                $columnID[$i] = $row['pupilsightMarkbookColumnID'];
                $attainmentID[$i] = $row['pupilsightScaleIDAttainment'];
                $effortID[$i] = $row['pupilsightScaleIDEffort'];
                $pupilsightPlannerEntryID[$i] = $row['pupilsightPlannerEntryID'];
                $pupilsightRubricIDAttainment[$i] = $row['pupilsightRubricIDAttainment'];
                $pupilsightRubricIDEffort[$i] = $row['pupilsightRubricIDEffort'];
            }

            if ($columnID[$i]) {
				$excel->getActiveSheet()->setCellValueByColumnAndRow((1 + ($i * (3-$effortAdjust))), 1, $row['name']);
                $excel->getActiveSheet()->getStyleByColumnAndRow((1 + ($i * (3-$effortAdjust))), 1)->applyFromArray($style_border);
                $excel->getActiveSheet()->getStyleByColumnAndRow((1 + ($i * (3-$effortAdjust))), 1)->applyFromArray($style_head_fill);
                $excel->getActiveSheet()->getStyleByColumnAndRow((2 + ($i * (3-$effortAdjust))), 1)->applyFromArray($style_border);
                $excel->getActiveSheet()->getStyleByColumnAndRow((2 + ($i * (3-$effortAdjust))), 1)->applyFromArray($style_head_fill);
                $excel->getActiveSheet()->getStyleByColumnAndRow(((3-$effortAdjust) + ($i * (3-$effortAdjust))), 1)->applyFromArray($style_border);
                $excel->getActiveSheet()->getStyleByColumnAndRow(((3-$effortAdjust) + ($i * (3-$effortAdjust))), 1)->applyFromArray($style_head_fill);
                }

            $excel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->applyFromArray($style_border);
            $excel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->applyFromArray($style_head_fill2);

            if ($attainmentAlternativeNameAbrev != '') {
    			$x = $attainmentAlternativeNameAbrev;
    		} else {
    			$x = __('Att');
    		}
    		$excel->getActiveSheet()->setCellValueByColumnAndRow((1 + ($i * (3-$effortAdjust))), 2, $x);
            $excel->getActiveSheet()->getStyleByColumnAndRow((1 + ($i * (3-$effortAdjust))), 2)->applyFromArray($style_border);
            $excel->getActiveSheet()->getStyleByColumnAndRow((1 + ($i * (3-$effortAdjust))), 2)->applyFromArray($style_head_fill2);
            if ($enableEffort == 'Y') {
                if ($effortAlternativeNameAbrev != '') {
                    $x = $effortAlternativeNameAbrev;
                } else {
                    $x = __('Eff');
                }
        		$excel->getActiveSheet()->setCellValueByColumnAndRow((2 + ($i * (3-$effortAdjust))), 2, $x);
                $excel->getActiveSheet()->getStyleByColumnAndRow((2 + ($i * (3-$effortAdjust))), 2)->applyFromArray($style_border);
                $excel->getActiveSheet()->getStyleByColumnAndRow((2 + ($i * (3-$effortAdjust))), 2)->applyFromArray($style_head_fill2);
            }
            $excel->getActiveSheet()->setCellValueByColumnAndRow(((3-$effortAdjust) + ($i * (3-$effortAdjust))), 2, __('Com'));
            $excel->getActiveSheet()->getStyleByColumnAndRow(((3-$effortAdjust) + ($i * (3-$effortAdjust))), 2)->applyFromArray($style_border);
            $excel->getActiveSheet()->getStyleByColumnAndRow(((3-$effortAdjust) + ($i * (3-$effortAdjust))), 2)->applyFromArray($style_head_fill2);
        }

        $DAS = $markbook->getDefaultAssessmentScale();
        $markFormat = PHPExcel_Style_NumberFormat::FORMAT_GENERAL;

        if (isset($DAS['percent']) && $DAS['percent'] == '%') {
            $markFormat = PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE;
        }
        else if (isset($DAS['numeric']) && $DAS['numeric'] == 'Y') {
            $markFormat = PHPExcel_Style_NumberFormat::FORMAT_NUMBER;
        }

        // Add columns for Overall Grades, if enabled
        if ($markbook->getSetting('enableColumnWeighting') == 'Y') {
            $markSuffix = (isset($DAS['percent']))? $DAS['percent'] : '';

            $finalColumnNum = ($columns * (3-$effortAdjust));
            $finalColumnStart = $finalColumnNum+1;

            // Cumulative Average
            $finalColumnNum++;
            $excel->getActiveSheet()->getColumnDimension( $excel->num2alpha($finalColumnNum) )->setAutoSize(true);
            $excel->getActiveSheet()->setCellValueByColumnAndRow( $finalColumnNum, 2, __('Cumulative'));
            $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, 2)->applyFromArray($style_border);
            $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, 2)->applyFromArray($style_head_fill2);
            
            // Add Final Grades, if enabled & available
            if ($markbook->getSetting('enableTypeWeighting') == 'Y' && count($markbook->getGroupedMarkbookTypes('year')) > 0) {

                foreach ($markbook->getGroupedMarkbookTypes('year') as $type) {
                    // Final Weighted Types
                    $finalColumnNum++;
                    $excel->getActiveSheet()->getColumnDimension( $excel->num2alpha($finalColumnNum) )->setAutoSize(true);
                    $excel->getActiveSheet()->setCellValueByColumnAndRow( $finalColumnNum, 2, $type );
                    $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, 2)->applyFromArray($style_border);
                    $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, 2)->applyFromArray($style_head_fill2);
                }

                // Final Grade
                $finalColumnNum++;
                $excel->getActiveSheet()->getColumnDimension( $excel->num2alpha($finalColumnNum) )->setAutoSize(true);
                $excel->getActiveSheet()->setCellValueByColumnAndRow( $finalColumnNum, 2, __('Final Grade'));
                $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, 2)->applyFromArray($style_border);
                $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, 2)->applyFromArray($style_head_fill2);
            }

            $excel->getActiveSheet()->setCellValueByColumnAndRow( $finalColumnStart, 1, __('Overall Grades'));
            $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnStart, 1)->applyFromArray($style_border);
            $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnStart, 1)->applyFromArray($style_head_fill);
            $excel->getActiveSheet()->mergeCells( $excel->num2alpha($finalColumnStart).'1:' .$excel->num2alpha($finalColumnNum).'1');
        }

		$r = 2;

        $count = 0;
        $rowNum = 'odd';

		$dataStudents = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
		$sqlStudents = "SELECT title, surname, preferredName, pupilsightPerson.pupilsightPersonID, dateStart FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE role='Student' AND pupilsightCourseClassID=:pupilsightCourseClassID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY surname, preferredName";
		$resultStudents = $pdo->executeQuery($dataStudents, $sqlStudents);
        if ($resultStudents->rowCount() < 1) {
			$excel->getActiveSheet()->setCellValueByColumnAndRow(0, 3, __('There are no records to display.'));
            $excel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->applyFromArray($style_border);

        } else {
            while ($rowStudents = $resultStudents->fetch()) {
                $r++;
                ++$count;
				//Column A
				$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, formatName('', $rowStudents['preferredName'], $rowStudents['surname'], 'Student', true));
                $excel->getActiveSheet()->getStyleByColumnAndRow(0, $r)->applyFromArray($style_border);

				//Columns following A
                for ($i = 0;$i < $columns;++$i) {
                    $row = $result->fetch();
					$dataEntry = array('pupilsightMarkbookColumnID' => $columnID[($i)], 'pupilsightPersonIDStudent' => $rowStudents['pupilsightPersonID']);
					$sqlEntry = 'SELECT * FROM pupilsightMarkbookEntry WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
					$resultEntry = $pdo->executeQuery($dataEntry, $sqlEntry);

                    if ($resultEntry->rowCount() == 1) {
                        $rowEntry = $resultEntry->fetch();
                        $attainment = '';
                        if ($rowEntry['attainmentValue'] != '') {
                            $attainment = __($rowEntry['attainmentValue']);
                        }
                        if ($rowEntry['attainmentValue'] == 'Complete') {
                            $attainment = __('Com');
                        } elseif ($rowEntry['attainmentValue'] == 'Incomplete') {
                            $attainment = __('Inc');
                        }
						$excel->getActiveSheet()->setCellValueByColumnAndRow((1 + ($i * (3-$effortAdjust))), $r, htmlPrep($rowEntry['attainmentValue']));
                        $excel->getActiveSheet()->getStyleByColumnAndRow((1 + ($i * (3-$effortAdjust))), $r)->applyFromArray($style_border);
                        $excel->getActiveSheet()->getStyleByColumnAndRow((1 + ($i * (3-$effortAdjust))), $r)->getNumberFormat()->setFormatCode($markFormat);

                        $effort = '';
                        if ($rowEntry['effortValue'] != '') {
                            $effort = __($rowEntry['effortValue']);
                        }
                        if ($rowEntry['effortValue'] == 'Complete') {
                            $effort = __('Com');
                        } elseif ($rowEntry['effortValue'] == 'Incomplete') {
                            $effort = __('Inc');
                        }
 						if ($enableEffort == 'Y') {
                            $excel->getActiveSheet()->setCellValueByColumnAndRow((2 + ($i * (3-$effortAdjust))), $r, $rowEntry['effortValue']);
                            $excel->getActiveSheet()->getStyleByColumnAndRow((2 + ($i * (3-$effortAdjust))), $r)->applyFromArray($style_border);
                        }
                        $excel->getActiveSheet()->setCellValueByColumnAndRow(((3-$effortAdjust) + ($i * (3-$effortAdjust))), $r, $rowEntry['comment']);
                        $excel->getActiveSheet()->getStyleByColumnAndRow(((3-$effortAdjust) + ($i * (3-$effortAdjust))), $r)->applyFromArray($style_border);
                    } else { //Fill empty spaces
                        $excel->getActiveSheet()->setCellValueByColumnAndRow((1 + ($i * (3-$effortAdjust))), $r, '');
                        $excel->getActiveSheet()->getStyleByColumnAndRow((1 + ($i * (3-$effortAdjust))), $r)->applyFromArray($style_border);
                        $excel->getActiveSheet()->setCellValueByColumnAndRow((2 + ($i * (3-$effortAdjust))), $r, '');
                        $excel->getActiveSheet()->getStyleByColumnAndRow((2 + ($i * (3-$effortAdjust))), $r)->applyFromArray($style_border);
                        $excel->getActiveSheet()->setCellValueByColumnAndRow(((3-$effortAdjust) + ($i * (3-$effortAdjust))), $r, '');
                        $excel->getActiveSheet()->getStyleByColumnAndRow(((3-$effortAdjust) + ($i * (3-$effortAdjust))), $r)->applyFromArray($style_border);
                    }
                }

                // Output Overall Grades, if enabled
                if ($markbook->getSetting('enableColumnWeighting') == 'Y') {
                    $finalColumnNum = 1 + ($columns * (3-$effortAdjust));

                    // Cumulative Average
                    $cumulativeAverage = round($markbook->getCumulativeAverage($rowStudents['pupilsightPersonID']), 0).$markSuffix;
                    $excel->getActiveSheet()->setCellValueByColumnAndRow( $finalColumnNum, $r, $cumulativeAverage);
                    $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, $r)->applyFromArray($style_border);
                    $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, $r)->getNumberFormat()->setFormatCode($markFormat);
                    $finalColumnNum++;

                    if ($markbook->getSetting('enableTypeWeighting') == 'Y' && count($markbook->getGroupedMarkbookTypes('year')) > 0) {

                        foreach ($markbook->getGroupedMarkbookTypes('year') as $type) {
                            // Final Weighted Types
                            $typeAverage = round($markbook->getTypeAverage($rowStudents['pupilsightPersonID'], 'final', $type), 0).$markSuffix;
                            $excel->getActiveSheet()->setCellValueByColumnAndRow( $finalColumnNum, $r, $typeAverage);
                            $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, $r)->applyFromArray($style_border);
                            $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, $r)->getNumberFormat()->setFormatCode($markFormat);
                            $finalColumnNum++;
                        }

                        // Final Grade
                        $finalAverage = round($markbook->getFinalGradeAverage($rowStudents['pupilsightPersonID']), 0).$markSuffix;
                        $excel->getActiveSheet()->setCellValueByColumnAndRow( $finalColumnNum, $r, $finalAverage);
                        $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, $r)->applyFromArray($style_border);
                        $excel->getActiveSheet()->getStyleByColumnAndRow($finalColumnNum, $r)->getNumberFormat()->setFormatCode($markFormat);
                    }
                }
            }
			$excel->exportWorksheet();
        }
    }
}
