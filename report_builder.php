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
    
    $sq = "call students";
    $query = $connection2->query($sq);
    $report = $query->fetchAll();

    $len = count($report);
    $i = 0;
    $result = array();
    $head = $_POST["header"];
    while($i<$len){
        $res = array();
        $jlen = count($head);
        $j = 0;
        while($j<$jlen){
            $res[$head[$j]]=$report[$i][$head[$j]];
            $j++;
        }
        $result[]=$res;
        $i++;
    }

    function html_table($data = [],$header = [],$total = null, $fileDownloadType, $addSerialNo = true) {
        try{
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
                    if($cell){
                        $cv = $cell;
                    }
                    $cells[] = "\n<td>".$cv."</td>";
                    $colLen++;
                }
                $rows[] = "\n<tr>" . implode("", $cells) . "</tr>\n";
            }

            //for table header
            if ($total && $fileDownloadType!="ihtml") {
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
                if($data[0]){
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
                    if($hflag){
                        $cells[] = "\n<th>".strtoupper($cell)."</th>";
                    }else{
                        $cells[] = "\n<th>{$cell}</th>";
                    }
                }
                if($fileDownloadType=="ihtml"){
                    $th = "\n<thead><tr>" . implode("", $cells) . "\n</tr></thead>\n";
                }else{
                    $th = "\n<tr>" . implode("", $cells) . "\n</tr>\n";
                }
                
            }

            $tbl = "";
            if($fileDownloadType=="ihtml"){
                $tbl = "\n<table id='table' class='cell-border stripe order-column hover table table-striped dataTable' style='width:100%;'>" .$th;
                $tbl .= "\n<tbody>".implode("", $rows) ."\n</tbody>\n</table>\n";
                if ($total){
                    $tbl .= "\n<br><h3>Total - $total</h3>";
                }
            }else{
                $tbl = "\n<table class='table' style='width:100%;'>" .$th;
                $tbl .= implode("", $rows) ."\n</table>\n";
            }
            return $tbl;
        }catch(Exception $ex){
            echo $ex->getMessage();
        }
        //return "<table class='table'>" . implode("", $rows) . "</table>";
    }

    $fileDownloadType = "html";
    $htmlString = html_table($result, $head, null, $fileDownloadType);
    $html = "<html>";
    if($fileDownloadType=="ihtml" || $fileDownloadType=="html"){
        $html = "<html translate='no' lang='en' class='notranslate' translate='no'>";
    }
    
    if($fileDownloadType=="ihtml"){
        $html .="\n<head>";
        $html .="\n<style>\n.table{font: normal 13px Arial, sans-serif;}\n</style>";
        $html .="\n<meta name='google' content='notranslate' />";
        $html .= "\n<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>";
        $html .="\n<link rel='stylesheet' href='https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css'>";
        $html .= "\n<script src='https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js'></script>";
        $html .= "\n<script>$(document).ready(function(){
            $('#table').DataTable();
        });
        </script>";
        $html .="\n</head>";
    }else{
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
    $html .= "\n<center>\n<br><h2>".$_SESSION[$guid]["organisationName"]."</h2>";
    $html .= "\n<h3>Test Report</h3><br/>\n</center>";
    $html .= $htmlString;
    $html .= "\n</body>";
    $html .= "</html>";
    $fileNameGen = "test";

    $fileName = $_SERVER["DOCUMENT_ROOT"] . "/public/".$fileNameGen.".html";
    //echo "file ".$fileName;
    $res["file"]=$baseurl."/public/".$fileNameGen.".html";
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

    //print_r($result);

?>