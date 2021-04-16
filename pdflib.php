<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use mikehaertl\pdftk\Pdf;
use setasign\Fpdi\Fpdi;


/*
    Form data should be array
    $formData example
    $data = [
    'student_name' => "test user",
    'student_dob' => 'test dob',
    'student_class' => 'test class',
    'student_id' => ' test id',
    'student_mother_name' => 'test mother name',
    'student_father_name' => ' test father',
    'student_address' => 'test address'
];

$imgData[0] = [
    'pageno' => 1,
    'src' => $_SERVER['DOCUMENT_ROOT'] . "/debug/rakesh_photo.jpg",
    'x' => 10,
    'y' => 10,
    'width' => 20,
    'height' => 20
];

$templateFileName //$_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template.pdf'
$outFileName // $_SERVER['DOCUMENT_ROOT'] . '/debug/' . $filename
*/

class PDFLib
{

    public $_templateFileName;
    public $_outFileName;
    public $_formData;
    public $_imgData;
    public $_download;
    public $_deleteSource;
    public $pos = 0;
    public $isCallBack = FALSE;
    public $files = array();

    function reset()
    {
        $this->files = array();
        $this->isCallBack = FALSE;
        $this->pos = 0;
    }

    function bulkinit($templateFileName, $outFileName, $formData, $imgData, $download, $deleteSource)
    {
        $this->_templateFileName = $templateFileName;
        $this->_outFileName = $outFileName;
        $this->_formData = $formData;
        $this->_imgData = $imgData;
        $this->_download = $download;
        $this->_deleteSource = $deleteSource;
        $this->pos = 0;
        $this->files = array();
        $this->isCallBack = TRUE;
        $this->generateBulk();
    }

    function generateBulk()
    {
        $len = count($this->_templateFileName);
        if ($this->pos < $len) {
            $index = $this->pos;
            $this->generate($this->_templateFileName[$index], $this->_outFileName[$index], $this->_formData[$index], $this->_imgData[$index]);
        }
    }

    function generate($templateFileName, $outFileName, $formData, $imgData = NULL)
    {
        try {
            $pdf = new Pdf($templateFileName);
            $result = $pdf->fillForm($formData)
                ->flatten()
                ->saveAs($outFileName);
            if ($result === false) {
                $error = $pdf->getError();
                print_r($error);
            }
            chmod($outFileName, 0777);
        } catch (Exception $ex) {
            echo "\n<br>Fill Form " . $ex->getMessage();
            echo "\n<br>";
            print_r($ex);
        }

        //addimg image in pdf
        if ($imgData) {
            try {
                $fpdi = new Fpdi();
                $pageCount = $fpdi->setSourceFile($outFileName);
                for ($j = 1; $j <= $pageCount; $j++) {
                    $fpdi->AddPage();
                    $template = $fpdi->importPage($j);
                    $fpdi->useTemplate($template);
                    $len = count($imgData);
                    $i = 0;
                    while ($i < $len) {
                        $img = $imgData[$i];
                        if ($img["pageno"] == $j) {
                            $fpdi->Image($img["src"], $img["x"], $img["y"], $img["width"], $img["height"]);
                        }
                        $i++;
                    }
                }

                $fpdi->Output($outFileName, "F");
            } catch (Exception $ex) {
                echo "\n<br>Image Date " . $ex->getMessage();
                echo "\n<br>";
                print_r($ex);
            }
        }

        $this->files[] = $outFileName;
        $this->pos++;
        if ($this->isCallBack) {
            $this->generateBulk();
        }
        return true;
    }

    function download($fileName = NULL)
    {
        if (empty($fileName)) {
            $fileName = $this->files[0];
        }

        $onlyFileName = basename($fileName);
        /*header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $onlyFileName);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fileName));
        readfile($fileName);*/


        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $onlyFileName);
        readfile($fileName);
    }

    function deleteSource()
    {
        $fi = $this->files;
        $len = count($fi);
        $i = 0;
        while ($i < $len) {
            if (is_file($fi[$i])) {
                unlink($fi[$i]);
            }
            $i++;
        }
    }


    function createZipAndDownload($zipFileName)
    {
        $files = $this->files;
        try {
            unlink($zipFileName);
        } catch (Exception $ex) {
            print_r($ex);
        }

        try {
            // Create instance of ZipArchive. and open the zip folder.
            $zip = new ZipArchive();
            if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
                exit("cannot open <$zipFileName>\n");
            }

            // Adding every attachments files into the ZIP.
            //print_r($files);
            foreach ($files as $file) {
                $fn = basename($file);
                $zip->addFile($file, $fn);
            }
            $zip->close();

            header("Content-type: application/zip");
            header("Content-Disposition: attachment; filename = $zipFileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            readfile($zipFileName);
            unlink($_SERVER['DOCUMENT_ROOT'] . "/" . $zipFileName);
        } catch (Exception $ex) {
            print_r($ex->getMessage());
        }
    }
}
