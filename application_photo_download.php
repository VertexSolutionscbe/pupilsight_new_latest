<?php
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');

$form_id = $_GET["form_id"];
$field_name = $_GET["field_name"];


if (!empty($form_id) && (!empty($field_name))) {
    echo "Please wait and don't close the page until download will finish.";
    try {
        $sq = "select s.application_id, e.submission_id, e.field_value from wp_fluentform_entry_details as e, wp_fluentform_submissions as s where e.form_id='" . $form_id . "' and e.field_name='" . $field_name . "' and s.id=e.submission_id;";
        //$sq = "select submission_id,field_value from wp_fluentform_entry_details ";
        //$sq .= "where form_id='" . $form_id . "' and field_name='" . $field_name . "'  ";

        //echo $sq;
        $result = $connection2->query($sq);

        $rs = $result->fetchAll();
        //print_r($rs);
        $len = count($rs);
        $i = 0;
        $root = $_SERVER['DOCUMENT_ROOT'];
        //$dstdir = $root . "/wp/wp-content/uploads/" . $form_id . "_" . $field_name;
        $dstdir = $root . "/public/" . $form_id . "_" . $field_name;

        if (is_dir($dstdir)) {
            chmod($dstdir, 0777);
            rmdir($dstdir);
        }

        mkdir($dstdir, 0777, true);

        $zip = new ZipArchive;
        $download = $form_id . "_" . $field_name . '_download.zip';
        $zip->open($download, ZipArchive::CREATE);

        while ($i < $len) {
            $id = $rs[$i]["application_id"];
            if (empty($id)) {
                $id = $rs[$i]["submission_id"];
            }
            $id = preg_replace("/[^A-Za-z0-9]/", '-', $id);

            $imageid = $rs[$i]["field_value"];
            if ($imageid) {
                //wp/wp-content/uploads/fluentform/
                $st = explode("wp/wp-content/uploads/fluentform/", $imageid);
                if (isset($st[1])) {
                    $path = $root . "/wp/wp-content/uploads/fluentform/" . $st[1];
                    if (file_exists($path)) {
                        //echo "\n<br>" . $path;
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        $newfile = $dstdir . "/" . $id . "." . $ext;
                        //echo "\n<br>" . $newfile;
                        copy($path, $newfile);
                        chmod($newfile, 0777);
                        $zip->addFile($newfile);
                    } else {
                        echo "no file uploaded";
                    }
                }
            }
            $i++;
        }

        $zip->close();
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename = $download");
        header('Content-Length: ' . filesize($download));
        header("Location: $download");
        chmod($dstdir, 0777);
        removeFolder($dstdir);
        echo "\n<br>Download Completed";


        function removeFolder($folderName)
        {
            try {
                if (is_dir($folderName)) {
                    $folderHandle = opendir($folderName);
                }

                if (!$folderHandle) {
                    return false;
                }

                while ($file = readdir($folderHandle)) {
                    if ($file != "." && $file != "..") {

                        if (!is_dir($folderName . "/" . $file)) {
                            unlink($folderName . "/" . $file);
                        } else {
                            removeFolder($folderName . '/' . $file);
                        }
                    }
                }

                closedir($folderHandle);
                rmdir($folderName);
            } catch (Exception $ex) {
                print_r($ex);
            }
            return true;
        }
        //echo json_encode($dt);
    } catch (Exception $ex) {
        print_r($ex);
    }
}
