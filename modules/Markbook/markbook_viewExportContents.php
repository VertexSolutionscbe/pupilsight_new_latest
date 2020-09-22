<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../config.php';

//Module includes
include './moduleFunctions.php';

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

    //Proceed!
	$dataStudents = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
	$sqlStudents = "SELECT title, surname, preferredName, pupilsightPerson.pupilsightPersonID, dateStart
		FROM pupilsightCourseClassPerson
			JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
		WHERE role='Student'
			AND pupilsightCourseClassID=:pupilsightCourseClassID
			AND status='Full'
			AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."')
			AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."')
		ORDER BY surname, preferredName";
	$resultStudents = $pdo->executeQuery($dataStudents, $sqlStudents, '_');
    if ($resultStudents->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {

		$excel = new Pupilsight\Excel('markbookColumn.xlsx');
		if ($excel->estimateCellCount($pdo) > 8000)    //  If too big, then render csv instead.
			return Pupilsight\csv::generate($pdo, 'markbookColumn');
		$excel->setActiveSheetIndex(0);
		$excel->getProperties()->setTitle('Markbook Data');
		$excel->getProperties()->setSubject('Markbook Data');
		$excel->getProperties()->setDescription('Markbook Data');

        //Create border and fill style
        $style_border = array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e'))));
        $style_head_fill = array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'B89FE2')));

        //Auto set column widths
        for($col = 'A'; $col !== 'E'; $col++)
            $excel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

		$excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, __('Student'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_head_fill);

        if ($attainmentAlternativeNameAbrev != '') {
            $x = $attainmentAlternativeNameAbrev;
        } else {
            $x = __('Att');
        }
		$excel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, $x);
        $excel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($style_head_fill);
        if ($enableEffort == 'Y') {
            if ($effortAlternativeNameAbrev != '') {
                $x = $effortAlternativeNameAbrev;
            } else {
                $x = __('Eff');
            }
    		$excel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, $x);
            $excel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($style_border);
            $excel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($style_head_fill);
        }
		$excel->getActiveSheet()->setCellValueByColumnAndRow((3-$effortAdjust), 1, __('Com'));
        $excel->getActiveSheet()->getStyleByColumnAndRow((3-$effortAdjust), 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow((3-$effortAdjust), 1)->applyFromArray($style_head_fill);

		$r = 1;
        while ($rowStudents = $resultStudents->fetch()) {
            //COLOR ROW BY STATUS!
			$r++;
			//Column A
			$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, formatName('', $rowStudents['preferredName'], $rowStudents['surname'], 'Student', true));
            $excel->getActiveSheet()->getStyleByColumnAndRow(0, $r)->applyFromArray($style_border);

            //Column B
			$x = '';
			$dataEntry = array('pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID, 'pupilsightPersonIDStudent' => $rowStudents['pupilsightPersonID']);
			$sqlEntry = 'SELECT *
				FROM pupilsightMarkbookEntry
				WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID
					AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
			if (is_null($resultEntry = $pdo->executeQuery($dataEntry, $sqlEntry))) {
				$x .= $pdo->getError();
			}
            if ($resultEntry->rowCount() == 1) {
                $rowEntry = $resultEntry->fetch();
                $attainment = $rowEntry['attainmentValue'];
                if ($rowEntry['attainmentValue'] == 'Complete') {
                    $attainment = 'CO';
                } elseif ($rowEntry['attainmentValue'] == 'Incomplete') {
                    $attainment = 'IC';
                }
                $x .= htmlPrep($rowEntry['attainmentValue']);
				$excel->getActiveSheet()->setCellValueByColumnAndRow(1, $r, $x);
                $excel->getActiveSheet()->getStyleByColumnAndRow(1, $r)->applyFromArray($style_border);
                $effort = $rowEntry['effortValue'];
                if ($rowEntry['effortValue'] == 'Complete') {
                    $effort = 'CO';
                } elseif ($rowEntry['effortValue'] == 'Incomplete') {
                    $effort = 'IC';
                }
				if ($enableEffort == 'Y') {
                    $excel->getActiveSheet()->setCellValueByColumnAndRow(2, $r, htmlPrep($rowEntry['effortValue']));
                    $excel->getActiveSheet()->getStyleByColumnAndRow(2, $r)->applyFromArray($style_border);
                }
                $excel->getActiveSheet()->setCellValueByColumnAndRow((3-$effortAdjust), $r, htmlPrep($rowEntry['comment']));
                $excel->getActiveSheet()->getStyleByColumnAndRow((3-$effortAdjust), $r)->applyFromArray($style_border);
            } else {
				$excel->getActiveSheet()->setCellValueByColumnAndRow(1, $r, 'No data.');
                $excel->getActiveSheet()->getStyleByColumnAndRow(1, $r)->applyFromArray($style_border);
            }
        }
    }
	$excel->exportWorksheet();
}
