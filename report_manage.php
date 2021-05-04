<?php
include "pupilsight.php";
//use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    //$fileDownloadType = "html";
    $fileDownloadType = "xlsx"; // for excel keep blank
    $sq =
        "select pupilsightPersonID, officialName from pupilsightPerson order by officialName asc";

    $query = $connection2->query($sq);
    $result = $query->fetchAll();

    function html_table($data = [], $header = [], $addSerialNo = true)
    {
        $rows = [];
        $cnt = 1;
        foreach ($data as $row) {
            $cells = [];
            if ($addSerialNo) {
                $cells[] = "<td>{$cnt}</td>";
                $cnt++;
            }
            foreach ($row as $cell) {
                $cells[] = "<td>{$cell}</td>";
            }
            $rows[] = "<tr>" . implode("", $cells) . "</tr>";
        }

        //for table header
        if ($header) {
            $cells = [];
            if ($addSerialNo) {
                $cells[] = "<th>Serial No</th>";
            }
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

    $header = ["ID", "Name"];
    $htmlString = html_table($result, $header);

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
    $html .=
        "<br/><center><h2>Report Header for Some School</h2></center><br/>";
    $html .= $htmlString;
    $html .= "</body>";
    $html .= "</html>";

    if ($fileDownloadType == "html") {
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/public/test.html";

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
        $sheet->getActiveSheet()->mergeCells("A1:E1");

        $writer = new Xlsx($spreadsheet);
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/public/hello_world.xlsx";
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
