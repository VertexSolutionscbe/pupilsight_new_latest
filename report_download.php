<?php
include "pupilsight.php";
//use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Pupilsight\Domain\Report\ReportGateway;
//use Pupilsight\Domain\Helper\HelperGateway;
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
ini_set('memory_limit', '-1');
set_time_limit(0);
try {

    function getDomain()
    {
        if (isset($_SERVER['HTTPS'])) {
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else {
            $protocol = 'http';
        }
        //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }
    $baseurl = getDomain();

    $res = array();
    $fileDownloadType = "html";
    if (isset($_POST["fd"])) {
        $fileDownloadType = $_POST["fd"];
    }

    //$fileDownloadType = "xlsx"; // for excel keep blank

    $sq = "select * from report_manager where id='" . $_POST["reportid"] . "'";
    $query = $connection2->query($sq);
    $report = $query->fetch();
    if (empty($report)) {
        $res["msg"] = "No Data available for report";
        die();
    }


    function html_table($data = [], $header = [], $total = null, $fileDownloadType, $addSerialNo = true)
    {
        try {
            $rows = [];
            $cnt = 1;
            $colLen = 1;
            foreach ($data as $row) {
                $cells = [];
                if ($addSerialNo) {
                    $cells[] = "\n<td>{$cnt}</td>";
                    $cnt++;
                }
                $colLen = 1;
                foreach ($row as $cell) {
                    $cv = "";
                    if ($cell) {
                        $cv = $cell;
                    }
                    $cells[] = "\n<td>" . $cv . "</td>";
                    $colLen++;
                }
                $rows[] = "\n<tr>" . implode("", $cells) . "</tr>\n";
            }

            //for table header
            if ($total && $fileDownloadType != "ihtml") {
                if ($colLen) {
                    $rows[] =
                        "\n<tr><td style='text-align:right' colspan='" .
                        $colLen .
                        "'><h3>Total : " .
                        $total .
                        "</h3></td>\n</tr>\n";
                }
            }
            $hflag = false;
            if (empty($header)) {
                if ($data[0]) {
                    $hflag = true;
                    $header = array_keys($data[0]);
                }
            }

            if ($header) {
                $cells = [];
                if ($addSerialNo) {
                    $cells[] = "\n<th>SN</th>";
                }
                foreach ($header as $cell) {
                    if ($hflag) {
                        $cells[] = "\n<th>" . strtoupper($cell) . "</th>";
                    } else {
                        $cells[] = "\n<th>{$cell}</th>";
                    }
                }
                if ($fileDownloadType == "ihtml") {
                    $th = "\n<thead><tr>" . implode("", $cells) . "\n</tr></thead>\n";
                } else {
                    $th = "\n<tr>" . implode("", $cells) . "\n</tr>\n";
                }
            }

            $tbl = "";
            if ($fileDownloadType == "ihtml") {
                $tbl = "\n<table id='table' class='cell-border stripe order-column hover table table-striped dataTable' style='width:100%;'>" . $th;
                $tbl .= "\n<tbody>" . implode("", $rows) . "\n</tbody>\n</table>\n";
                if ($total) {
                    $tbl .= "\n<br><h3>Total - $total</h3>";
                }
            } else {
                $tbl = "\n<table class='table' style='width:100%;'>" . $th;
                $tbl .= implode("", $rows) . "\n</table>\n";
            }
            return $tbl;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
        //return "<table class='table'>" . implode("", $rows) . "</table>";
    }

    function getTranslateArray($sq)
    {
        $sd = array("date1", "date2", "date3", "date4", "param1", "param2", "param3", "param4", "param5", "param6", "param7", "param8");
        $len = count($sd);
        $i = 0;
        $trans = array();
        while ($i < $len) {
            $pos = strpos($sq, $sd[$i]);
            if ($pos !== false) {
                $trans[":" . $sd[$i]] = $_POST[$sd[$i]];
            }
            $i++;
        }
        return $trans;
    }

    $header = [];
    $totalValue = null;


    if ($report["sql_query"]) {
        //run query
        $sq = htmlspecialchars_decode(stripslashes($report["sql_query"]), ENT_QUOTES);
        $trans = getTranslateArray($sq);
        $sq = strtr($sq, $trans);
        $query1 = $connection2->query($sq);
        $result = $query1->fetchAll();
    } else {
        //api call working on now
        $reportGateway = $container->get(ReportGateway::class);
        //$reportGateway = $container->get(HelperGateway::class);
        $result = $reportGateway->{$report["api"]}($connection2, $_POST);
    }

    if ($report["header"]) {
        $header = explode(",", $report["header"]);
    }

    //if total need to calculate then
    if ($report["total_column"]) {
        $isTotal = true;
        $totColumns = $report["total_column"];
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

    $htmlString = html_table($result, $header, $totalValue, $fileDownloadType);
    $html = "<html>";
    if ($fileDownloadType == "ihtml" || $fileDownloadType == "html") {
        $html = "<html translate='no' lang='en' class='notranslate' translate='no'>";
    }

    if ($fileDownloadType == "ihtml") {
        $html .= "\n<head>";
        $html .= "\n<style>\n.table{font: normal 13px Arial, sans-serif;}\n</style>";
        $html .= "\n<meta name='google' content='notranslate' />";
        $html .= "\n<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>";
        $html .= "\n<link rel='stylesheet' href='https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css'>";
        $html .= "\n<script src='https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js'></script>";
        $html .= "\n<script>$(document).ready(function(){
            $('#table').DataTable();
        });
        </script>";
        $html .= "\n</head>";
    } else {
        $html .= "\n<style>\n.table {
            border: solid 1px #DDEEEE;
            border-collapse: collapse;
            border-spacing: 0;
            font: normal 13px Arial, sans-serif;
        }
        \n.table th {
            background-color: #DDEFEF;
            border: solid 1px #DDEEEE;
            color: #336B6B;
            padding: 10px;
            text-align: left;
            text-shadow: 1px 1px 1px #fff;
        }
        \n.table td {
            border: solid 1px #DDEEEE;
            color: #333;
            padding: 10px;
            text-shadow: 1px 1px 1px #fff;
        }\n</style>\n";
    }

    $html .= "\n<body>";
    $html .= "\n<center>\n<br><h2>" . $_SESSION[$guid]["organisationName"] . "</h2>";
    $html .= "\n<h3>" . $report["name"] . "</h3><br/>\n</center>";
    $html .= $htmlString;
    $html .= "\n</body>";
    $html .= "</html>";


    $fileNameGen = $input = preg_replace("/[^a-zA-Z]+/", "", $report["name"]);

    if ($fileDownloadType == "html" || $fileDownloadType == "ihtml") {
        //echo "file downlod type html";
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/public/" . $fileNameGen . ".html";
        //echo "file ".$fileName;
        $res["file"] = $baseurl . "/public/" . $fileNameGen . ".html";
        //echo json_encode($res);
        file_put_contents($fileName, $html);
        chmod($fileName, 0777);
        $fileDownName = basename($fileName);
        //header("Content-type:application/html");
        header('Content-Type: application/force-download');
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
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/public/" . $fileNameGen . ".xlsx";
        $writer->save($fileName);
        chmod($fileName, 0777);

        header(
            "Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        );
        $fileDownName = basename($fileName);
        header("Content-Disposition: attachment; filename = $fileDownName");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($fileName);
        unlink($fileName);
        //echo "Downloading Started";
    }
} catch (Exception $ex) {
    echo $ex->getMessage();
}
