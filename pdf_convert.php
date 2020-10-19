<?php

function convert($fileName, $inFilePath, $outFilePath = NULL, $deleteSourceFile = FALSE, $debug = FALSE)
{
    if (empty($outFilePath)) {
        $outFilePath = $inFilePath;
    }
    $file = $inFilePath . $fileName;
    if (file_exists($file)) {
        chmod($file, 0777);
        $commandPath = "lowriter --convert-to pdf " . $file;
        $command = escapeshellcmd($commandPath);
        $convertFilePath = "";

        $highlight = shell_exec($command);
        $tmpFile = explode("->", $highlight);

        //get filename from terminal
        $filePath = trim(substr($tmpFile[1], 0, strpos($tmpFile[1], ".pdf"))) . ".pdf";

        if (file_exists($filePath)) {
            $convertFilePath = $filePath;
            chmod($convertFilePath, 0777);
        }

        if ($debug) {
            echo "convert file: " . $convertFilePath;
            print_r($tmpFile);
        }

        $baseFile = pathinfo($fileName);
        $baseFileName = $baseFile["filename"];

        if ($outFilePath) {
            try {
                if (!is_dir($outFilePath)) {
                    mkdir($outFilePath, 0777, true); //Create directory.
                    chmod($outFilePath, 0777);
                } else {
                    chmod($outFilePath, 0777);
                }
            } catch (Exception $ex) {
                if ($debug) {
                    print_r($ex);
                }
            }

            if (file_exists($convertFilePath)) {
                rename($convertFilePath, $outFilePath . $baseFileName . ".pdf");
            }
        }

        if ($deleteSourceFile) {
            try {
                unlink($file);
            } catch (Exception $ex) {
                if ($debug) {
                    print_r($ex);
                }
            }
        }
    }
}

function convertBulk($inDir, $debug = FALSE)
{
    if (is_dir($inDir)) {
        $files = scandir($inDir);
        $len = count($files);
        $i = 0;
        while ($i < $len) {
            $pos = strpos($files[$i], "docx");
            if ($pos) {
                convert($files[$i], $inDir, $inDir, TRUE, TRUE);
            }
            $i++;
        }
    }
}

function convertDir($inFilePath, $debug = FALSE)
{
    //error_reporting(E_ALL);
    $file = $inFilePath . "*.docx";
    //print_r($file);
    //die();
    if (is_dir($inFilePath)) {
        try {
            //$command = escapeshellcmd("cd " . $inFilePath);
            //shell_exec($command);
            try {
                chmod($inFilePath, 0777);
            } catch (Exception $ex) {
                if ($debug) {
                    print_r($ex);
                }
            }

            //echo "come here";
            //$commandPath = "lowriter --convert-to pdf " . $file;
            $commandPath = "cd " . $inFilePath . "; lowriter --convert-to pdf *.docx";
            $command = escapeshellcmd($commandPath);

            if ($debug) {
                $highlight = shell_exec($command);
                print_r($highlight);
            } else {
                shell_exec($command);
            }
        } catch (Exception $ex) {
            print_r($ex);
        }
    } else {
        return "The file $file does not exist";
    }
}

/*
$inFilePath = $_SERVER["DOCUMENT_ROOT"] . "/thirdparty/phpword/templates/";
$outFilePath = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";
$fileName = "receipt_bak_delete.docx";

echo convert($fileName, $inFilePath, $outFilePath, TRUE, TRUE);
*/
//$file = get_basename($fileName);
//echo "ext ".$ext;

//echo $file["filename"];
