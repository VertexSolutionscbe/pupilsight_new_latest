<?php

function Zip($source, $destination)
{
    if (is_string($source)) $source_arr = array($source); // convert it to array

    if (!extension_loaded('zip')) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    foreach ($source_arr as $source) {
        if (!file_exists($source)) continue;
        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', realpath($file));

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }
    }

    $zip->close();



    // Download the created zip file
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename = $destination");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile("$destination");
    unlink($destination);

    $objects = scandir($source);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            rrmdir($source . '/' . $object);
        }
    }

    // exit;

}

function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . $object) == "dir") {
                    rrmdir($dir . $object);
                } else {
                    unlink($dir . '/' . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}



$filesPath = $_SERVER["DOCUMENT_ROOT"] . "/public/report_template/report_card/";
//rrmdir($filesPath);



$zipName = $_GET['zipname'] . '.zip';

echo Zip($filesPath, $zipName);
