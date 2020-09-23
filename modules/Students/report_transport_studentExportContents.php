<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

include '../../config.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_transport_student.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    try {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, transport, surname, preferredName, address1, address1District, address1Country, nameShort FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY transport, surname, preferredName";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    $excel = new Pupilsight\Excel('studentTransport.xlsx');
	if ($excel->estimateCellCount($pdo) > 8000)    //  If too big, then render csv instead.
		return Pupilsight\csv::generate($pdo, 'studentTransport');
	$excel->setActiveSheetIndex(0);
	$excel->getProperties()->setTitle('Student Transport');
	$excel->getProperties()->setSubject('Student Transport');
	$excel->getProperties()->setDescription('Student Transport');

    //Create border and fill style
    $style_border = array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e')), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '766f6e'))));
    $style_head_fill = array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'B89FE2')));

    //Auto set column widths
    for($col = 'A'; $col !== 'F'; $col++)
        $excel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

	$excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, __('Transport'));
    $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_border);
    $excel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($style_head_fill);
	$excel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, __('Student'));
    $excel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($style_border);
    $excel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($style_head_fill);
	$excel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, __('Address'));
    $excel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($style_border);
    $excel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($style_head_fill);
	$excel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, __('Parents'));
    $excel->getActiveSheet()->getStyleByColumnAndRow(3, 1)->applyFromArray($style_border);
    $excel->getActiveSheet()->getStyleByColumnAndRow(3, 1)->applyFromArray($style_head_fill);
	$excel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, __('Roll Group'));
    $excel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->applyFromArray($style_border);
    $excel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->applyFromArray($style_head_fill);

	$r = 1;
    $count = 0;
    $rowNum = 'odd';
    while ($row = $result->fetch()) {
        $r++;
		$count++;
		//Column A
 		$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, $row['transport']);
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, $r)->applyFromArray($style_border);
        //Column B
 		$excel->getActiveSheet()->setCellValueByColumnAndRow(1, $r, Format::name('', $row['preferredName'], $row['surname'], 'Student', true));
        $excel->getActiveSheet()->getStyleByColumnAndRow(1, $r)->applyFromArray($style_border);
        //Column C
		$dataFamily = array('pupilsightPersonID' => $row['pupilsightPersonID']);
		$sqlFamily = 'SELECT pupilsightFamily.pupilsightFamilyID, nameAddress, homeAddress, homeAddressDistrict, homeAddressCountry
			FROM pupilsightFamily
				JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
			WHERE pupilsightPersonID=:pupilsightPersonID';
		$resultFamily = $pdo->executeQuery($dataFamily, $sqlFamily, '_');
		$x = '';
        while ($rowFamily = $resultFamily->fetch()) {
            if ($rowFamily['nameAddress'] != '') {
                $x .= $rowFamily['nameAddress'];
                if ($rowFamily['homeAddress'] != '') {
                    $x .= ', ';
                }
            }
            if (substr(rtrim($rowFamily['homeAddress']), -1) == ',') {
                $address = substr(rtrim($rowFamily['homeAddress']), 0, -1);
            } else {
                $address = rtrim($rowFamily['homeAddress']);
            }
            $address = addressFormat($address, rtrim($rowFamily['homeAddressDistrict']), rtrim($rowFamily['homeAddressCountry']));
            if ($address != false) {
                $address = explode(',', $address);
                for ($i = 0; $i < count($address); ++$i) {
                    $x .= $address[$i];
                    if ($i < (count($address) - 1)) {
                        $x .= ', ';
                    }
                }
            }
        }
  		$excel->getActiveSheet()->setCellValueByColumnAndRow(2, $r, $x);
        $excel->getActiveSheet()->getStyleByColumnAndRow(2, $r)->applyFromArray($style_border);
        //Column D
        $contact = '';
        try {
            $dataFamily = array('pupilsightPersonID' => $row['pupilsightPersonID']);
            $sqlFamily = 'SELECT pupilsightFamilyID FROM pupilsightFamilyChild WHERE pupilsightPersonID=:pupilsightPersonID';
            $resultFamily = $connection2->prepare($sqlFamily);
            $resultFamily->execute($dataFamily);
        } catch (PDOException $e) {
            $contact .= $e->getMessage().'. ';
        }
        while ($rowFamily = $resultFamily->fetch()) {
            try {
                $dataFamily2 = array('pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                $sqlFamily2 = 'SELECT pupilsightPerson.* FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightPerson.pupilsightPersonID=pupilsightFamilyAdult.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID ORDER BY contactPriority, surname, preferredName';
                $resultFamily2 = $connection2->prepare($sqlFamily2);
                $resultFamily2->execute($dataFamily2);
            } catch (PDOException $e) {
                $contact .= $e->getMessage().'. ';
            }
            while ($rowFamily2 = $resultFamily2->fetch()) {
                $contact .= Format::name($rowFamily2['title'], $rowFamily2['preferredName'], $rowFamily2['surname'], 'Parent').': ';
                $numbers = 0;
                for ($i = 1; $i < 5; ++$i) {
                    if ($rowFamily2['phone'.$i] != '') {
                        if ($rowFamily2['phone'.$i.'Type'] != '') {
                            $contact .= $rowFamily2['phone'.$i.'Type'].': ';
                        }
                        if ($rowFamily2['phone'.$i.'CountryCode'] != '') {
                            $contact .= '+'.$rowFamily2['phone'.$i.'CountryCode'].' ';
                        }
                        $contact .= $rowFamily2['phone'.$i].', ';
                        ++$numbers;
                    }
                }
                if ($numbers == 0) {
                    $contact .= __("No number available").". ";
                }
            }
        }
        if (substr($contact, -2) == ', ') {
            $contact = substr($contact, 0, -2);
        }
  		$excel->getActiveSheet()->setCellValueByColumnAndRow(3, $r, $contact);
        $excel->getActiveSheet()->getStyleByColumnAndRow(3, $r)->applyFromArray($style_border);
  		$excel->getActiveSheet()->setCellValueByColumnAndRow(4, $r, $row['nameShort']);
        $excel->getActiveSheet()->getStyleByColumnAndRow(4, $r)->applyFromArray($style_border);
    }
    if ($count == 0) {
  		$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, __('There are no records to display.'));
        $excel->getActiveSheet()->getStyleByColumnAndRow(0, $r)->applyFromArray($style_border);
    }
    $excel->exportWorksheet();
}
