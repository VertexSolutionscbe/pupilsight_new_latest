<?php
include "pupilsight.php";
//use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $fileDownloadType = "html";
    if (isset($_POST["fd"])) {
        $fileDownloadType = $_POST["fd"];
    }
    $isTotal = false;
    $totColumns = "amount_paying";
    //$fileDownloadType = "xlsx"; // for excel keep blank

    $sq = "select * from report_manager where id='".$_POST["reportid"]."'";
    $query = $connection2->query($sq);
    $report = $query->fetch();
    if(empty($report)){
        echo "No Data available for report";
        die();
    }


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

    if($report["sql_query"]){
        $query1 = $connection2->query(htmlspecialchars_decode($report["sql_query"], ENT_QUOTES));
        $result = $query1->fetch();
    }else{
        //api call working on now
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
    $html .= "<br/><center><h2>".$report["name"]."</h2></center><br/>";
    $html .= $htmlString;
    $html .= "</body>";
    $html .= "</html>";

    $fileNameGen = $input = preg_replace("/[^a-zA-Z]+/", "", $report["name"]);

    if ($fileDownloadType == "html") {
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/public/".$fileNameGen.".html";

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
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/public/".$fileNameGen.".xlsx";
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
        echo "Downloading Started";
    }
} catch (Exception $ex) {
    echo $ex->getMessage();
}

?>