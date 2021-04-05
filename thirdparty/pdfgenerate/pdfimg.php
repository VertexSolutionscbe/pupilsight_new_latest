<?php


require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use setasign\Fpdi\Fpdi;

echo "start processing";
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'] . "/debug/AMBER_GRADE2_NEW.pdf");
$template = $pdf->importPage(1);
$pdf->useTemplate($template);
$x = 100;
$y = 100;
$width = 10;
$height = 10;

$pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/debug/rakesh_photo.jpg", $x, $y, $width, $height);
$outputPath = $_SERVER['DOCUMENT_ROOT'] . "/debug/AMBER_GRADE2_update.pdf";
$pdf->Output($outputPath, "F");
echo "<br>done";
