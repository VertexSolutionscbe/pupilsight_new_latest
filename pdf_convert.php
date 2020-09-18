<?php

function convert($fileName, $inFilePath, $outFilePath = NULL, $deleteSourceFile = FALSE, $debug = FALSE)
{
    $file = $inFilePath . $fileName;
    if (file_exists($file)) {
        $commandPath = "lowriter --convert-to pdf " . $file;
        $command = escapeshellcmd($commandPath);

        if ($debug) {
            $highlight = shell_exec($command);
            print_r($highlight);
        } else {
            shell_exec($command);
        }

        $baseFile = pathinfo($fileName);
        $baseFileName = $baseFile["filename"];
        $convertFilePath = $_SERVER['DOCUMENT_ROOT'] . "/" . $baseFileName . ".pdf";
        
        try {
            chmod($convertFilePath, 0777);
        } catch (Exception $ex) {
            if ($debug) {
                print_r($ex);
            }
        }

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


            rename($convertFilePath, $outFilePath . $baseFileName . ".pdf");
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
    } else {
        return "The file $file does not exist";
    }
}

$inFilePath = $_SERVER["DOCUMENT_ROOT"] . "/thirdparty/phpword/templates/";
$outFilePath = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";
$fileName = "receipt_bak_delete.docx";

echo convert($fileName, $inFilePath, $outFilePath, TRUE, TRUE);

//$file = get_basename($fileName);
//echo "ext ".$ext;

//echo $file["filename"];
