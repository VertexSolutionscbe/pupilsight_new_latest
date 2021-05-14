<?php
include "pupilsight.php";
//use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $fileDownloadType = "html";
    if (isset($_GET["fd"])) {
        $fileDownloadType = $_GET["fd"];
    }
    $isTotal = true;
    $totColumns = "amount_paying";
    //$fileDownloadType = "xlsx"; // for excel keep blank

    //p.admission_no
    if(isset($_GET['dt'])){
        $today = date("Y-m-d",strtotime($_GET['dt']));
    }else{
        $today = date("Y-m-d");
    }
    
    $sq =
        "select p.pupilsightPersonID, p.officialName, CONCAT(cl.name, '-',sec.name) as grade, fn.receipt_number,
        IFNULL(fm.name,'online') as payment_mode, fn.dd_cheque_date, fn.dd_cheque_no, 
          bnk.name as bank_name, fn.payment_date, fn.amount_paying
          from pupilsightPerson as p
          left join pupilsightStudentEnrolment as e on p.pupilsightPersonID = e.pupilsightPersonID
          left join pupilsightYearGroup as cl on e.pupilsightYearGroupID = cl.pupilsightYearGroupID
          left join pupilsightRollGroup as sec on e.pupilsightRollGroupID = sec.pupilsightRollGroupID
          left join fn_fees_collection as fn on p.pupilsightPersonID = fn.pupilsightPersonID
          left join fn_masters as fm on fn.payment_mode_id = fm.id
          left join fn_masters as bnk on fn.bank_id = bnk.id
          where fn.amount_paying is not null and fn.payment_date ='" .
        $today .
        "' and e.pupilsightSchoolYearID='3' group by fn.pay_gateway_id
          order by p.officialName asc";

    //echo $sq;
    //pupilsightPersonID,
    $query = $connection2->query($sq);
    $result = $query->fetchAll();

    function html_table(
        $data = [],
        $header = [],
        $total = null,
        $addSerialNo = true
    ) {
        $rows = [];
        $cnt = 1;
        $colLen = 1;
        foreach ($data as $row) {
            $cells = [];
            if ($addSerialNo) {
                $cells[] = "<td>{$cnt}</td>";
                $cnt++;
            }
            $colLen = 1;
            foreach ($row as $cell) {
                $cells[] = "<td>{$cell}</td>";
                $colLen++;
            }
            $rows[] = "<tr>" . implode("", $cells) . "</tr>";
        }

        //for table header
        if ($total) {
            if ($colLen) {
                $rows[] =
                    "<tr><td style='text-align:right' colspan='" .
                    $colLen .
                    "'><h3>Total : " .
                    $total .
                    "</h3></td></tr>";
            }
        }

        if ($header) {
            $cells = [];
            /*if ($addSerialNo) {
                $cells[] = "<th>Serial No</th>";
            }*/
            foreach ($header as $cell) {
                $cells[] = "<th>{$cell}</th>";
            }
            $th = "<tr>" . implode("", $cells) . "</tr>";
            return "<table class='table'>" .
                $th .
                implode("", $rows) .
                "</table>";
        }
        return "<table class='table'>" . implode("", $rows) . "</table>";
    }

    $header = [
        "SINo",
        "Student Id",
        "Student Name",
        "Grade",
        "Receipt No.",
        "Payment Mode",
        "DD Date",
        "DD Number",
        "Bank Details",
        "Payment Date",
        "Amount Paid",
    ];

    if ($isTotal) {
        $len = count($result);
        $i = 0;
        $totalValue = 0;

        if ($result[$i][$totColumns]) {
            while ($i < $len) {
                $totalValue += $result[$i][$totColumns];
                $i++;
            }
        }
    }

    $htmlString = html_table($result, $header, $totalValue);

    $html = "<html>";
    $html .= "<style>.table {
            border: solid 1px #DDEEEE;
            border-collapse: collapse;
            border-spacing: 0;
            font: normal 13px Arial, sans-serif;
        }
        .table th {
            background-color: #DDEFEF;
            border: solid 1px #DDEEEE;
            color: #336B6B;
            padding: 10px;
            text-align: left;
            text-shadow: 1px 1px 1px #fff;
        }
        .table td {
            border: solid 1px #DDEEEE;
            color: #333;
            padding: 10px;
            text-shadow: 1px 1px 1px #fff;
        }</style>";

    $html .= "<body>";
    $html .= "<br/><center><h2>Daily Collection Report</h2></center><br/>";
    $html .= $htmlString;
    $html .= "</body>";
    $html .= "</html>";

    if ($fileDownloadType == "html") {
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/public/report.html";

        file_put_contents($fileName, $html);

        $fileDownName = basename($fileName);
        header("Content-type:application/html");
        header("Content-Disposition: attachment; filename = $fileDownName");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($fileName);
        unlink($fileName);
    } else {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($htmlString);

        /*
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter(
        $spreadsheet,
        "Xls"
    );
    $writer->save($_SERVER["DOCUMENT_ROOT"] . "/public/write.xls");
*/

        $sheet = $spreadsheet->getActiveSheet();
        $cellIterator = $sheet
            ->getRowIterator()
            ->current()
            ->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }

        //$sheet->getActiveSheet()->mergeCells("A1:E1");

        $writer = new Xlsx($spreadsheet);
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/public/report.xlsx";
        $writer->save($fileName);

        header(
            "Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        );
        $fileDownName = basename($fileName);
        header("Content-Disposition: attachment; filename = $fileDownName");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($fileName);
        unlink($fileName);
    }
} catch (Exception $ex) {
    echo $ex->getMessage();
}

?>