<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
ini_set('max_execution_time', 7200);


if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // print_r($_FILES);
    // die();
    //Proceed!
    $sketch_id = $_POST['sketch_id'];
    $attr_id = $_POST['attr_id'];
    $x = $_POST['x'];
    $y = $_POST['y'];
    $page_no = $_POST['page_no'];
    $width = $_POST['width'];
    $height = $_POST['height'];
    
    
    if ($sketch_id == '' && $attr_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {

            $data = array('sketch_id' => $sketch_id, 'attr_id' => $attr_id);
            $sql = 'DELETE FROM examinationReportSketchConfigureImage WHERE sketch_id=:sketch_id AND attr_id=:attr_id';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $datains = array('sketch_id' => $sketch_id, 'attr_id' => $attr_id, 'x' => $x, 'y' => $y, 'page_no' => $page_no, 'width' => $width, 'height' => $height);
            $sqlins = "INSERT INTO examinationReportSketchConfigureImage SET sketch_id=:sketch_id, attr_id=:attr_id, x=:x, y=:y, page_no=:page_no, width=:width, height=:height ";
            $resultins = $connection2->prepare($sqlins);
            $resultins->execute($datains);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        }

    }
}