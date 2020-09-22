<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../config.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightFinanceInvoiceIDs = $_SESSION[$guid]['financeInvoiceExportIDs'];
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];

    if ($pupilsightFinanceInvoiceIDs == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('List of invoices or school year have not been specified, and so this export cannot be completed.');
        echo '</div>';
    } else {

		$whereCount = 0;
		$whereSched = '(';
		$whereAdHoc = '(';
		$whereNotPending = '(';
		$data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
		foreach ($pupilsightFinanceInvoiceIDs as $pupilsightFinanceInvoiceID) {
			$data['pupilsightFinanceInvoiceID'.$whereCount] = $pupilsightFinanceInvoiceID;
			$whereSched .= 'pupilsightFinanceInvoice.pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID'.$whereCount.' OR ';
			++$whereCount;
		}
		$whereSched = substr($whereSched, 0, -4).')';

		//SQL for billing schedule AND pending
		$sql = "(SELECT pupilsightFinanceInvoice.pupilsightFinanceInvoiceID, surname, preferredName, pupilsightPerson.pupilsightPersonID, dob, gender,
				studentID, pupilsightFinanceInvoice.invoiceTo, pupilsightFinanceInvoice.status, pupilsightFinanceInvoice.invoiceIssueDate,
				pupilsightFinanceBillingSchedule.invoiceDueDate, paidDate, paidAmount, pupilsightFinanceBillingSchedule.name AS billingSchedule,
				NULL AS billingScheduleExtra, notes, pupilsightRollGroup.name AS rollGroup
			FROM pupilsightFinanceInvoice
				JOIN pupilsightFinanceBillingSchedule ON (pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID=pupilsightFinanceBillingSchedule.pupilsightFinanceBillingScheduleID)
				JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID)
				JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
				LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID
					AND pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightFinanceInvoice.pupilsightSchoolYearID)
				LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
			WHERE pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID
				AND billingScheduleType='Scheduled'
				AND pupilsightFinanceInvoice.status='Pending'
				AND $whereSched)";
		$sql .= ' UNION ';
		//SQL for Ad Hoc AND pending
		$sql .= "(SELECT pupilsightFinanceInvoice.pupilsightFinanceInvoiceID, surname, preferredName, pupilsightPerson.pupilsightPersonID, dob, gender, studentID, pupilsightFinanceInvoice.invoiceTo, pupilsightFinanceInvoice.status, invoiceIssueDate, invoiceDueDate, paidDate, paidAmount, 'Ad Hoc' AS billingSchedule, NULL AS billingScheduleExtra, notes, pupilsightRollGroup.name AS rollGroup FROM pupilsightFinanceInvoice JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightFinanceInvoice.pupilsightSchoolYearID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID AND billingScheduleType='Ad Hoc' AND pupilsightFinanceInvoice.status='Pending' AND $whereSched)";
		$sql .= ' UNION ';
		//SQL for NOT Pending
		$sql .= "(SELECT pupilsightFinanceInvoice.pupilsightFinanceInvoiceID, surname, preferredName, pupilsightPerson.pupilsightPersonID, dob, gender, studentID, pupilsightFinanceInvoice.invoiceTo, pupilsightFinanceInvoice.status, pupilsightFinanceInvoice.invoiceIssueDate, pupilsightFinanceInvoice.invoiceDueDate, paidDate, paidAmount, billingScheduleType AS billingSchedule, pupilsightFinanceBillingSchedule.name AS billingScheduleExtra, notes, pupilsightRollGroup.name AS rollGroup FROM pupilsightFinanceInvoice LEFT JOIN pupilsightFinanceBillingSchedule ON (pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID=pupilsightFinanceBillingSchedule.pupilsightFinanceBillingScheduleID) JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightFinanceInvoice.pupilsightSchoolYearID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT pupilsightFinanceInvoice.status='Pending' AND $whereSched)";
		$sql .= " ORDER BY FIND_IN_SET(status, 'Pending,Issued,Paid,Refunded,Cancelled'), invoiceIssueDate, surname, preferredName";
		if (is_null($result = $pdo->executeQuery($data, $sql))) {
			echo "<div class='error'>".$pdo->getError().'</div>';
		}

		$excel = new Pupilsight\Excel('invoices.xlsx');
		if ($excel->estimateCellCount($pdo) > 8000)    //  If too big, then render csv instead.
			return Pupilsight\csv::generate($pdo, 'Invoices');
		$excel->setActiveSheetIndex(0);
		$excel->getProperties()->setTitle('Invoices');
		$excel->getProperties()->setSubject('Invoice Export');
		$excel->getProperties()->setDescription('Invoice Export');

        //Create border and fill style
        $style_border = array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e'))));
        $style_head_fill = array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'B89FE2')));

        //Auto set column widths
        for($col = 'A'; $col !== 'I'; $col++)
            $excel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

		$excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, __("Invoice Number"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, __("Student"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, __("Roll Group"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, __("Invoice To"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(3, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(3, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, __("DOB"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, __("Gender"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(5, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(5, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, __("Status"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(6, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(6, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, __("Schedule"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(7, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(7, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, __("Total Value") . '(' . $_SESSION[$guid]["currency"] .')');
        $excel->getActiveSheet()->getStyleByColumnAndRow(8, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(8, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, __("Issue Date"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(9, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(9, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, __("Due Date"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(10, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(10, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(11, 1, __("Date Paid"));
        $excel->getActiveSheet()->getStyleByColumnAndRow(11, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(11, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->setCellValueByColumnAndRow(12, 1, __("Amount Paid") . " (" . $_SESSION[$guid]["currency"] . ")" );
        $excel->getActiveSheet()->getStyleByColumnAndRow(12, 1)->applyFromArray($style_border);
        $excel->getActiveSheet()->getStyleByColumnAndRow(12, 1)->applyFromArray($style_head_fill);
		$excel->getActiveSheet()->getStyle("1:1")->getFont()->setBold(true);

		$r = 2;
		$count = 0;

		while ($row=$result->fetch()) {
			$count++ ;
			//Column A
			$invoiceNumber=getSettingByScope( $connection2, "Finance", "invoiceNumber" ) ;
			if ($invoiceNumber=="Person ID + Invoice ID") {
				$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, ltrim($row["pupilsightPersonID"],"0") . "-" . ltrim($row["pupilsightFinanceInvoiceID"], "0"));
			}
			else if ($invoiceNumber=="Student ID + Invoice ID") {
				$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, ltrim($row["studentID"],"0") . "-" . ltrim($row["pupilsightFinanceInvoiceID"], "0"));
			}
			else {
				$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, ltrim($row["pupilsightFinanceInvoiceID"], "0"));
			}
            $excel->getActiveSheet()->getStyleByColumnAndRow(0, $r)->applyFromArray($style_border);
			//Column B
			$excel->getActiveSheet()->setCellValueByColumnAndRow(1, $r, formatName("", htmlPrep($row["preferredName"]), htmlPrep($row["surname"]), "Student", true));
            $excel->getActiveSheet()->getStyleByColumnAndRow(1, $r)->applyFromArray($style_border);
			//Column C
			$excel->getActiveSheet()->setCellValueByColumnAndRow(2, $r, $row["rollGroup"]);
            $excel->getActiveSheet()->getStyleByColumnAndRow(2, $r)->applyFromArray($style_border);
			//Column D
			$excel->getActiveSheet()->setCellValueByColumnAndRow(3, $r, $row["invoiceTo"]);
            $excel->getActiveSheet()->getStyleByColumnAndRow(3, $r)->applyFromArray($style_border);
			//Column E
            $excel->getActiveSheet()->setCellValueByColumnAndRow(4, $r, dateConvertBack($guid, $row["dob"]));
            $excel->getActiveSheet()->getStyleByColumnAndRow(4, $r)->applyFromArray($style_border);
			//Column F
			$excel->getActiveSheet()->setCellValueByColumnAndRow(5, $r, $row["gender"]);
            $excel->getActiveSheet()->getStyleByColumnAndRow(5, $r)->applyFromArray($style_border);
			//Column G
			$excel->getActiveSheet()->setCellValueByColumnAndRow(6, $r, $row["status"]);
            $excel->getActiveSheet()->getStyleByColumnAndRow(6, $r)->applyFromArray($style_border);
			//Column H
			if ($row["billingScheduleExtra"]!="")  {
				$excel->getActiveSheet()->setCellValueByColumnAndRow(7, $r, $row["billingScheduleExtra"]);
			}
			else {
				$excel->getActiveSheet()->setCellValueByColumnAndRow(7, $r, $row["billingSchedule"]);
			}
            $excel->getActiveSheet()->getStyleByColumnAndRow(7, $r)->applyFromArray($style_border);
			//Column I
			//Calculate total value
			$totalFee=0 ;
			$feeError = false ;
			$dataTotal=array("pupilsightFinanceInvoiceID"=>$row["pupilsightFinanceInvoiceID"]);
			if ($row["status"]=="Pending") {
				$sqlTotal="SELECT pupilsightFinanceInvoiceFee.fee AS fee, pupilsightFinanceFee.fee AS fee2
					FROM pupilsightFinanceInvoiceFee
						LEFT JOIN pupilsightFinanceFee ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID=pupilsightFinanceFee.pupilsightFinanceFeeID)
					WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID" ;
			}
			else {
				$sqlTotal="SELECT pupilsightFinanceInvoiceFee.fee AS fee, NULL AS fee2
					FROM pupilsightFinanceInvoiceFee
					WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID" ;
			}
			if (is_null($resultTotal=$pdo->executeQuery($dataTotal, $sqlTotal)))
			{
				$excel->getActiveSheet()->setCellValueByColumnAndRow(8, $r, 'Error calculating total');
				$feeError = true;
			}
			while ($rowTotal = $resultTotal->fetch()) {
				if (is_numeric($rowTotal["fee2"])) {
					$totalFee+=$rowTotal["fee2"] ;
				}
				else {
					$totalFee+=$rowTotal["fee"] ;
				}
			}
			$x = '';
			if (! $feeError) {
				$x .= number_format($totalFee, 2, ".", "") ;
				$excel->getActiveSheet()->setCellValueByColumnAndRow(8, $r, $x);
			}
            $excel->getActiveSheet()->getStyleByColumnAndRow(8, $r)->applyFromArray($style_border);
			//Column J
		    $excel->getActiveSheet()->setCellValueByColumnAndRow(9, $r, dateConvertBack($guid, $row["invoiceIssueDate"]));
            $excel->getActiveSheet()->getStyleByColumnAndRow(9, $r)->applyFromArray($style_border);
			//Column K
			$excel->getActiveSheet()->setCellValueByColumnAndRow(10, $r, dateConvertBack($guid, $row["invoiceDueDate"]));
            $excel->getActiveSheet()->getStyleByColumnAndRow(10, $r)->applyFromArray($style_border);
			//Column L
            if ($row["paidDate"]!="")
				$excel->getActiveSheet()->setCellValueByColumnAndRow(11, $r, dateConvertBack($guid, $row["paidDate"]));
            else
                $excel->getActiveSheet()->setCellValueByColumnAndRow(11, $r, '');
            $excel->getActiveSheet()->getStyleByColumnAndRow(11, $r)->applyFromArray($style_border);
			//Column M
			$excel->getActiveSheet()->setCellValueByColumnAndRow(12, $r, number_format($row["paidAmount"], 2, ".", ""));
            $excel->getActiveSheet()->getStyleByColumnAndRow(12, $r)->applyFromArray($style_border);
			$r++;
		}

		$_SESSION[$guid]['financeInvoiceExportIDs'] = null;
		$excel->exportWorksheet();
	}
}
