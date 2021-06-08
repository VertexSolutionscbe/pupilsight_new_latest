<?php
include 'pupilsight.php';
$session = $container->get('session');

$loc = $_SERVER['DOCUMENT_ROOT']."/public/archive/report_card"; //file location
//$files = glob("$loc/*.{pdf}", GLOB_BRACE); //only html files
$files = expandDirectories($loc);
//print_r($files);
$len = count($files);
$i = 0;
$result = array();

while($i<$len){
    $fileName = basename($files[$i]);
    $fn = explode("_",$fileName);
    $studentName = $fn[0];
    $fld = str_replace($loc, "", $files[$i]);
    $fd = explode("/",$fld);
    //print_r($fd);
    $year = $fd[1];
    $st_class = $fd[2];
    $section = $fd[3];
    echo "\n<br>".$fileName." | ".trim($studentName)."|".$year."|".$st_class."|".$section;
    $i++;
}

function expandDirectories($base_dir) {
    $directories = array();
    foreach(scandir($base_dir) as $file) {
        if($file == '.' || $file == '..') continue;

        $dir = $base_dir.DIRECTORY_SEPARATOR.$file;

        if(is_dir($dir)) {
            $directories = array_merge($directories, expandDirectories($dir));
        }else{
            if(strstr($dir,".pdf")){
                $directories []= $dir;
            }
        }
    }
    return $directories;
}
die();
?>