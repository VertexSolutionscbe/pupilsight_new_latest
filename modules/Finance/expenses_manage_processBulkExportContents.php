<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../config.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $financeExpenseExportIDs = $_SESSION[$guid]['financeExpenseExportIDs'];
    $pupilsightFinanceBudgetCycleID = $_GET['pupilsightFinanceBudgetCycleID'];

    if ($financeExpenseExportIDs == '' or $pupilsightFinanceBudgetCycleID == '') {
        echo "<div class='error'>";
        echo __('List of invoices or budget cycle have not been specified, and so this export cannot be completed.');
        echo '</div>';
    } else {
        try {
            $whereCount = 0;
            $whereSched = '(';
            $data = array();
            foreach ($financeExpenseExportIDs as $pupilsightFinanceExpenseID) {
                $data['pupilsightFinanceExpenseID'.$whereCount] = $pupilsightFinanceExpenseID;
                $whereSched .= 'pupilsightFinanceExpense.pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID'.$whereCount.' OR ';
                ++$whereCount;
            }
            $whereSched = substr($whereSched, 0, -4).')';

            //SQL for billing schedule AND pending
            $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, pupilsightFinanceBudgetCycle.name AS budgetCycle, preferredName, surname
				FROM pupilsightFinanceExpense
					JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
					JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
					JOIN pupilsightFinanceBudgetCycle ON (pupilsightFinanceExpense.pupilsightFinanceBudgetCycleID=pupilsightFinanceBudgetCycle.pupilsightFinanceBudgetCycleID)
				WHERE $whereSched";
            $sql .= " ORDER BY FIELD(pupilsightFinanceExpense.status, 'Requested','Approved','Rejected','Cancelled','Ordered','Paid'), timestampCreator, surname, preferredName";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }



		$excel = new Pupilsight\Excel('expenses.xlsx');
		if ($excel->estimateCellCount($pdo) > 8000)    //  If too big, then render csv instead.
			return Pupilsight\csv::generate($pdo, 'Invoices');
		$excel->setActiveSheetIndex(0);
		$excel->getProperties()->setTitle('Expenses');
		$excel->getProperties()->setSubject('Expense Export');
		$excel->getProperties()->setDescription('Expense Export');

        //Create border and fill style
        $style_border = array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e'))));
        $style_head_fill = array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'B89FE2')));

        //Auto set column widths
        for($col = 'A'; $col !== 'I'; $col++)
            $excel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

		$excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, __('Expense Number'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, __('Budget'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, __('Budget Cycle'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, __('Title'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(3, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(3, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, __('Status'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, __('Cost')." (".$_SESSION[$guid]['currency'].')');
        $excel->getActiveSheet()->getStyleByColumnAndRow(5, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(5, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, __('Staff'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(6, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(6, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, __('Timestamp'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(7, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(7, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->getStyle("1:1")->getFont()->setBold(true);


        $count = 1;
        while ($row = $result->fetch()) {
            ++$count;
 			//Column A
			$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $count, $row['pupilsightFinanceExpenseID']);
            $excel->getActiveSheet()->getStyleByColumnAndRow(0, $count)->applyFromArray($style_border);
            //Column B
			$excel->getActiveSheet()->setCellValueByColumnAndRow(1, $count, $row['budget']);
            $excel->getActiveSheet()->getStyleByColumnAndRow(1, $count)->applyFromArray($style_border);
 			//Column C
			$excel->getActiveSheet()->setCellValueByColumnAndRow(2, $count, $row['budgetCycle']);
            $excel->getActiveSheet()->getStyleByColumnAndRow(2, $count)->applyFromArray($style_border);
 			//Column D
			$excel->getActiveSheet()->setCellValueByColumnAndRow(3, $count, $row['title']);
            $excel->getActiveSheet()->getStyleByColumnAndRow(3, $count)->applyFromArray($style_border);
 			//Column E
			$excel->getActiveSheet()->setCellValueByColumnAndRow(4, $count, $row['status']);
            $excel->getActiveSheet()->getStyleByColumnAndRow(4, $count)->applyFromArray($style_border);
 			//Column F
			$excel->getActiveSheet()->setCellValueByColumnAndRow(5, $count, number_format($row['cost'], 2, '.', ','));
            $excel->getActiveSheet()->getStyleByColumnAndRow(5, $count)->applyFromArray($style_border);
 			//Column G
			$excel->getActiveSheet()->setCellValueByColumnAndRow(6, $count, formatName('', $row['preferredName'], $row['surname'], 'Staff', true, true));
            $excel->getActiveSheet()->getStyleByColumnAndRow(6, $count)->applyFromArray($style_border);
 			//Column H
			$excel->getActiveSheet()->setCellValueByColumnAndRow(7, $count, $row['timestampCreator']);
            $excel->getActiveSheet()->getStyleByColumnAndRow(7, $count)->applyFromArray($style_border);
        }
        if ($count == 0) {
 			//Column A
			$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $count, __('There are no records to display.'));
        }
	    $_SESSION[$guid]['financeExpenseExportIDs'] = null;
		$excel->exportWorksheet();
    }
}
